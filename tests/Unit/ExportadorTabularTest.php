<?php

use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use Tests\TestCase;

uses(TestCase::class);

test('genera un xlsx corporativo con metadatos, formatos numéricos e imágenes seguras', function () {
    $exporter = new ExportadorTabular;
    $response = $exporter->download('excel', 'ventas', ['Pedido', 'Fecha', 'Estado', 'Importe PEN'], [
        [1, '2026-11-02 10:15:00', 'Pendiente', '1250.50'],
        [2, '=1+1', 'Anulado', '-1+1'],
    ], [
        'title' => 'REPORTE DE VENTAS',
        'subtitle' => 'Pedidos registrados; el importe no representa cobros.',
        'module' => 'Dashboard Global / Ventas',
        'period' => 'Últimos 7 días',
        'generated_by' => 'María Admin',
        'role' => 'Administrador',
        'filters' => ['Canal' => 'ecommerce'],
        'indicators' => [['label' => 'Pedidos incluidos', 'value' => 2]],
        'primary_indicator' => ['label' => '% de pedidos entregados', 'value' => '50.0%', 'progress' => 50],
        'observations' => ['Muestra de QA sintética.'],
        'column_types' => [0 => 'integer', 1 => 'datetime', 3 => 'currency'],
        'summary_row' => ['label' => 'Pedidos filtrados: 2', 'values' => ['', '', '', '1250.50']],
    ]);
    $archiveFile = tempnam(sys_get_temp_dir(), 'tc-xlsx-test-');
    file_put_contents($archiveFile, $response->getContent());

    try {
        $archive = new ZipArchive;
        $opened = $archive->open($archiveFile);
        $sheet = $opened === true ? $archive->getFromName('xl/worksheets/sheet1.xml') : false;
        $styles = $opened === true ? $archive->getFromName('xl/styles.xml') : false;
        $workbook = $opened === true ? $archive->getFromName('xl/workbook.xml') : false;
        $logo = $opened === true ? $archive->getFromName('xl/media/technology-cell-logo.png') : false;
        $xmlPartsValid = $opened === true;
        if ($opened === true) {
            $previousLibxmlSetting = libxml_use_internal_errors(true);
            for ($index = 0; $index < $archive->numFiles; $index++) {
                $name = $archive->getNameIndex($index);
                if (! is_string($name) || (! str_ends_with($name, '.xml') && ! str_ends_with($name, '.rels'))) {
                    continue;
                }

                $xmlContent = $archive->getFromName($name);
                $xmlDocument = new DOMDocument;
                if (! is_string($xmlContent) || ! $xmlDocument->loadXML($xmlContent)) {
                    $xmlPartsValid = false;
                    break;
                }
            }
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlSetting);
            $archive->close();
        }
    } finally {
        unlink($archiveFile);
    }

    expect($response->headers->get('Content-Type'))->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->and($response->headers->get('Content-Disposition'))->toContain('.xlsx')
        ->and($opened)->toBeTrue()
        ->and($sheet)->toContain('REPORTE DE VENTAS')
        ->toContain('María Admin')
        ->toContain('Canal: ecommerce')
        ->toContain('Resumen del período')
        ->toContain('Detalle de ventas')
        ->toContain('Indicador principal')
        ->toContain('█████░░░░░ 50.0%')
        ->toContain('Muestra de QA sintética.')
        ->toContain('TECHNOLOGY CELL — CELL SAC')
        ->toContain('Documento generado por el sistema Technology CELL.')
        ->toContain('<autoFilter')
        ->toContain('state="frozen"')
        ->toContain('<row r="2" ht="36" customHeight="1">')
        ->toContain('t="n"')
        ->toContain('PEN')
        ->not->toContain('<f>')
        ->not->toContain('<script>')
        ->and($styles)->toContain('FF09264A')
        ->toContain('FFDCEEFF')
        ->toContain('FFE5F5EB')
        ->toContain('FFECE8FF')
        ->toContain('FFFFF1CC')
        ->toContain('FF26B8E8')
        ->toContain('dd/mm/yyyy hh:mm')
        ->and($xmlPartsValid)->toBeTrue()
        ->and($workbook)->toContain("'Reporte'!\$A\$1:\$D\$27")
        ->and($logo)->toStartWith("\x89PNG");
});

test('evita solapar indicadores y conserva los metadatos en el área imprimible', function () {
    $response = (new ExportadorTabular)->download('excel', 'compras', ['Orden', 'Fecha'], [], [
        'title' => 'REPORTE DE COMPRAS',
        'indicators' => [
            ['label' => 'Órdenes', 'value' => 3],
            ['label' => 'Monto', 'value' => 'S/ 1,250.00'],
            ['label' => 'Recibidas', 'value' => 2],
            ['label' => 'Pendientes', 'value' => 1],
            ['label' => 'En evaluación', 'value' => 1],
            ['label' => 'Devueltas', 'value' => 0],
        ],
    ]);
    $archiveFile = tempnam(sys_get_temp_dir(), 'tc-xlsx-layout-');
    file_put_contents($archiveFile, $response->getContent());
    $archive = new ZipArchive;
    $opened = $archive->open($archiveFile);
    $sheet = $opened === true ? $archive->getFromName('xl/worksheets/sheet1.xml') : false;
    $workbook = $opened === true ? $archive->getFromName('xl/workbook.xml') : false;
    if ($opened === true) {
        $archive->close();
    }
    unlink($archiveFile);

    expect($opened)->toBeTrue()
        ->and($sheet)->toContain('En evaluación')
        ->toContain('Devueltas')
        ->toContain('Resumen del período')
        ->toContain('Detalle de compras')
        ->toContain('<c r="A13" s="25"')
        ->toContain('<c r="D14" s="32"')
        ->toContain('ySplit="20"')
        ->toContain('autoFilter ref="A20:B20"')
        ->and($workbook)->toContain("'Reporte'!\$A\$1:\$D\$26");
});

test('genera un pdf A4 paginado con encabezado repetido, resumen y pie institucional', function () {
    $rows = [];
    for ($index = 1; $index <= 80; $index++) {
        $rows[] = [$index, '2026-11-02 10:15:00', $index % 2 === 0 ? 'Confirmado' : 'Pendiente', 'Pedido de muestra '.$index, 'TC-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT), 'Observación del registro '.$index];
    }
    $rows[0][5] = str_repeat('Detalle extenso con texto especial áéíóú & < > ', 90).'FIN_DEL_TEXTO';
    $response = (new ExportadorTabular)->download('pdf', 'productos', ['ID', 'Fecha', 'Estado', 'Detalle', 'Código', 'Observaciones'], $rows, [
        'title' => 'REPORTE DE PRODUCTOS',
        'subtitle' => 'Datos de catálogo sintetizados para verificación.',
        'module' => 'Inventario y Logística',
        'period' => 'Catálogo actual',
        'generated_by' => 'Usuario de prueba',
        'role' => 'Administrador',
        'filters' => ['Estado' => 'Todos'],
        'indicators' => [['label' => 'Filas', 'value' => 80]],
        'primary_indicator' => ['label' => 'Pedidos confirmados', 'value' => '50.0%', 'progress' => 50],
        'column_types' => [0 => 'integer', 1 => 'datetime'],
        'summary_row' => ['label' => 'Filas del conjunto: 80', 'values' => []],
    ]);
    $pdf = $response->getContent();
    preg_match_all('/\\/Type \\/Page \\/Parent/', $pdf, $pageMatches);
    $pageCount = count($pageMatches[0]);
    preg_match('/startxref\\s+(\\d+)\\s+%%EOF$/', $pdf, $xrefMatches);
    $xrefOffset = isset($xrefMatches[1]) ? (int) $xrefMatches[1] : -1;
    preg_match('/^xref\n0 (\d+)\n((?:\d{10} \d{5} [nf] \n)+)/', substr($pdf, max(0, $xrefOffset)), $xrefSection);
    $xrefEntries = isset($xrefSection[2]) ? explode("\n", trim($xrefSection[2])) : [];
    $xrefOffsetsValid = isset($xrefSection[1]) && count($xrefEntries) === (int) $xrefSection[1];
    foreach (array_slice($xrefEntries, 1) as $index => $entry) {
        if (! str_ends_with($entry, 'n')) {
            continue;
        }

        $objectId = $index + 1;
        $objectOffset = (int) substr($entry, 0, 10);
        if (! str_starts_with(substr($pdf, $objectOffset), $objectId.' 0 obj')) {
            $xrefOffsetsValid = false;
            break;
        }
    }
    preg_match_all('/\\((?:ID)\\) Tj/', $pdf, $repeatedHeaderMatches);

    expect($response->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($pdf)->toStartWith('%PDF-1.4')
        ->toContain('REPORTE DE PRODUCTOS')
        ->toContain('Usuario de prueba')
        ->toContain('TECHNOLOGY CELL')
        ->toContain('Documento generado por el sistema Technology CELL.')
        ->toContain('Resumen del per'.chr(0xED).'odo')
        ->toContain('Detalle de productos')
        ->toContain('Indicador principal')
        ->toContain('Pedidos confirmados')
        ->toContain('50.0%')
        ->toContain('0.030 0.430 0.810 rg')
        ->toContain('/Subtype /Image')
        ->toContain('/SMask')
        ->toContain('/MediaBox [0 0 842 595]')
        ->toContain('FIN_DEL_TEXTO')
        ->toContain('startxref')
        ->toContain('%%EOF')
        ->and($xrefOffset)->toBeGreaterThan(0)
        ->and(substr($pdf, $xrefOffset, 4))->toBe('xref')
        ->and($xrefOffsetsValid)->toBeTrue()
        ->and($pageCount)->toBeGreaterThan(1)
        ->and(count($repeatedHeaderMatches[0]))->toBe($pageCount)
        ->and($pdf)->toContain('P'.chr(0xE1).'gina 1 de '.$pageCount);
});

test('no dibuja una barra si el porcentaje principal está fuera del rango válido', function () {
    $report = [
        'title' => 'REPORTE DE PEDIDOS',
        'report_name' => 'Reporte de pedidos',
        'primary_indicator' => ['label' => 'Progreso reportado', 'value' => '125%', 'progress' => 125],
    ];
    $exporter = new ExportadorTabular;
    $excelResponse = $exporter->download('excel', 'pedidos', ['Pedido'], [['PED-QA-1']], $report);
    $pdfResponse = $exporter->download('pdf', 'pedidos', ['Pedido'], [['PED-QA-1']], $report);
    $archiveFile = tempnam(sys_get_temp_dir(), 'tc-xlsx-progress-');
    file_put_contents($archiveFile, $excelResponse->getContent());
    $archive = new ZipArchive;
    $opened = $archive->open($archiveFile);
    $sheet = $opened === true ? $archive->getFromName('xl/worksheets/sheet1.xml') : false;
    if ($opened === true) {
        $archive->close();
    }
    unlink($archiveFile);
    $pdf = $pdfResponse->getContent();

    expect($opened)->toBeTrue()
        ->and($sheet)->toContain('Detalle de pedidos')
        ->toContain('Progreso reportado')
        ->toContain('125%')
        ->not->toContain('█')
        ->and($pdf)->toContain('Detalle de pedidos')
        ->toContain('Progreso reportado')
        ->toContain('125%')
        ->not->toContain('0.030 0.430 0.810 rg');
});
