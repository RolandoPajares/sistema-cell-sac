<?php

namespace App\Servicios\Compartidos\Exportacion;

use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Define la responsabilidad de la clase ExportadorTabular dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ExportadorTabular
{
    private const LOGO_PATH = 'public/assets/cell-legacy/img/marca/logoadmin.png';

    private const XLSX_NUMFMT_CURRENCY = 164;

    private const XLSX_NUMFMT_DATE = 165;

    private const XLSX_NUMFMT_DATETIME = 166;

    private const XLSX_NUMFMT_NUMBER = 167;

    private const XLSX_NUMFMT_INTEGER = 168;

    private const XLSX_NUMFMT_PERCENT = 169;

    private const XLSX_STYLE_SECTION = 24;

    private const XLSX_STYLE_CARD_LABEL_BASE = 25;

    private const XLSX_STYLE_CARD_VALUE_BASE = 29;

    private const XLSX_STYLE_PANEL_CONTENT = 33;

    private const XLSX_STYLE_FOOTER_TITLE = 34;

    private const XLSX_STYLE_FOOTER_TEXT = 35;

    private const XLSX_STYLE_ACCENT = 36;

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  array<string, mixed>  $report
     */
    public function download(string $format, string $filename, array $columns, array $rows, array $report = []): Response|StreamedResponse
    {
        return match ($format) {
            'csv' => $this->csv($filename, $columns, $rows),
            'excel' => $this->generarExcel($filename, $columns, $rows, $report),
            'pdf' => $this->generarPdf($filename, $columns, $rows, $report),
            default => abort(404),
        };
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     */
    private function csv(string $filename, array $columns, array $rows): StreamedResponse
    {
        return response()->streamDownload(static function () use ($columns, $rows): void {
            // Guarda en $output el resultado necesario para continuar la operacion.
            $output = fopen('php://output', 'w');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($output === false) {
                return;
            }
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, array_map(self::textoHojaCalculoSeguro(...), $columns));
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($rows as $row) {
                fputcsv($output, array_map(static fn (string|int|float|null $value): string => self::textoHojaCalculoSeguro((string) $value), $row));
            }
            fclose($output);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  array<string, mixed>  $report
     */
    private function generarExcel(string $filename, array $columns, array $rows, array $report): Response
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! class_exists(ZipArchive::class)) {
            return $this->generarExcelSinZip($filename, $columns, $rows, $report);
        }
        // Guarda en $temporaryFile el resultado necesario para continuar la operacion.
        $temporaryFile = tempnam(sys_get_temp_dir(), 'tc-xlsx-');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($temporaryFile === false, 500, 'No se pudo preparar el archivo Excel.');

        // Guarda en $zip el resultado necesario para continuar la operacion.
        $zip = new ZipArchive;
        try {
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($zip->open($temporaryFile, ZipArchive::OVERWRITE) === true, 500, 'No se pudo crear el archivo Excel.');

            // Guarda en $context el resultado necesario para continuar la operacion.
            $context = $this->contextoReporte($filename, $report, count($columns));
            // Guarda en $logo el resultado necesario para continuar la operacion.
            $logo = $this->bytesLogo();
            $worksheet = $this->construirHojaCalculo($columns, $rows, $context, $logo !== null);
            $logoContentTypes = $logo === null ? '' : '<Default Extension="png" ContentType="image/png"/><Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';

            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'.$logoContentTypes.'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', $this->xmlLibroTrabajo($worksheet['last_layout_column'], $worksheet['last_row'], $worksheet['header_row']));
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
            $zip->addFromString('xl/styles.xml', $this->xmlEstilos());
            $zip->addFromString('xl/worksheets/sheet1.xml', $worksheet['xml']);

            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($logo !== null) {
                $zip->addFromString('xl/media/technology-cell-logo.png', $logo);
                $zip->addFromString('xl/worksheets/_rels/sheet1.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/></Relationships>');
                $zip->addFromString('xl/drawings/drawing1.xml', $this->xmlDibujo());
                $zip->addFromString('xl/drawings/_rels/drawing1.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/technology-cell-logo.png"/></Relationships>');
            }

            $zip->close();
            // Guarda en $binary el resultado necesario para continuar la operacion.
            $binary = file_get_contents($temporaryFile);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($binary === false, 500, 'No se pudo leer el archivo Excel.');

            return response($binary, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$this->nombreArchivoSeguro($filename).'.xlsx"',
            ]);
        } finally {
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (file_exists($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

    /**
     * Genera el paquete XLSX con el formato ZIP de Open XML cuando la extensión
     * ZipArchive no está habilitada. No cambia la configuración de PHP ni crea
     * un archivo con extensión falsa: cada entrada usa compresión DEFLATE y el
     * paquete contiene las partes XML requeridas por Excel.
     *
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  array<string, mixed>  $report
     */
    private function generarExcelSinZip(string $filename, array $columns, array $rows, array $report): Response
    {
        // Guarda en $context el resultado necesario para continuar la operacion.
        $context = $this->contextoReporte($filename, $report, count($columns));
        // Guarda en $logo el resultado necesario para continuar la operacion.
        $logo = $this->bytesLogo();
        $worksheet = $this->construirHojaCalculo($columns, $rows, $context, $logo !== null);
        $logoContentTypes = $logo === null
            ? ''
            : '<Default Extension="png" ContentType="image/png"/><Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';

        // Agrupa en $entries los datos que se utilizaran en el siguiente paso.
        $entries = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'.$logoContentTypes.'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => $this->xmlLibroTrabajo($worksheet['last_layout_column'], $worksheet['last_row'], $worksheet['header_row']),
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => $this->xmlEstilos(),
            'xl/worksheets/sheet1.xml' => $worksheet['xml'],
        ];

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($logo !== null) {
            $entries['xl/media/technology-cell-logo.png'] = $logo;
            $entries['xl/worksheets/_rels/sheet1.xml.rels'] = '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/></Relationships>';
            $entries['xl/drawings/drawing1.xml'] = $this->xmlDibujo();
            $entries['xl/drawings/_rels/drawing1.xml.rels'] = '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/technology-cell-logo.png"/></Relationships>';
        }

        return response($this->paqueteZip($entries), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$this->nombreArchivoSeguro($filename).'.xlsx"',
        ]);
    }

    /**
     * @param  array<string, string>  $entries
     */
    private function paqueteZip(array $entries): string
    {
        // Guarda en $body el resultado necesario para continuar la operacion.
        $body = '';
        // Guarda en $centralDirectory el resultado necesario para continuar la operacion.
        $centralDirectory = '';
        // Guarda en $offset el resultado necesario para continuar la operacion.
        $offset = 0;
        // Guarda en $entryCount el resultado necesario para continuar la operacion.
        $entryCount = 0;

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($entries as $name => $content) {
            // Guarda en $compressed el resultado necesario para continuar la operacion.
            $compressed = gzdeflate($content, 6);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($compressed === false, 500, 'No se pudo comprimir el archivo Excel.');
            // Guarda en $nameLength el resultado necesario para continuar la operacion.
            $nameLength = strlen($name);
            // Guarda en $contentLength el resultado necesario para continuar la operacion.
            $contentLength = strlen($content);
            // Guarda en $compressedLength el resultado necesario para continuar la operacion.
            $compressedLength = strlen($compressed);
            // Guarda en $crc el resultado necesario para continuar la operacion.
            $crc = crc32($content);

            // Guarda en $localHeader el resultado necesario para continuar la operacion.
            $localHeader = pack('VvvvvvVVVvv', 0x04034B50, 20, 0, 8, 0, 0, $crc, $compressedLength, $contentLength, $nameLength, 0).$name.$compressed;
            $body .= $localHeader;
            $centralDirectory .= pack('VvvvvvvVVVvvvvvVV', 0x02014B50, 20, 20, 0, 8, 0, 0, $crc, $compressedLength, $contentLength, $nameLength, 0, 0, 0, 0, 0, $offset).$name;
            $offset += strlen($localHeader);
            $entryCount++;
        }

        // Guarda en $centralOffset el resultado necesario para continuar la operacion.
        $centralOffset = strlen($body);
        $body .= $centralDirectory;
        $body .= pack('VvvvvVVv', 0x06054B50, 0, 0, $entryCount, $entryCount, strlen($centralDirectory), $centralOffset, 0);

        return $body;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  array<string, mixed>  $context
     * @return array{xml:string,last_column:string,last_layout_column:string,last_row:int,header_row:int}
     */
    private function construirHojaCalculo(array $columns, array $rows, array $context, bool $hasLogo): array
    {
        // Guarda en $columnCount el resultado necesario para continuar la operacion.
        $columnCount = max(1, count($columns));
        // Guarda en $layoutColumnCount el resultado necesario para continuar la operacion.
        $layoutColumnCount = max(4, $columnCount);
        // Guarda en $lastLayoutColumn el resultado necesario para continuar la operacion.
        $lastLayoutColumn = $this->letraColumna($layoutColumnCount - 1);
        // Guarda en $lastColumn el resultado necesario para continuar la operacion.
        $lastColumn = $this->letraColumna($columnCount - 1);
        // Guarda en $indicators el resultado necesario para continuar la operacion.
        $indicators = $context['indicators'];
        // Guarda en $indicatorGroups el resultado necesario para continuar la operacion.
        $indicatorGroups = array_chunk($indicators, 4);
        $detailSectionRow = $indicators === [] ? 15 : 12 + (count($indicatorGroups) * 3) + 1;
        // Guarda en $headerRow el resultado necesario para continuar la operacion.
        $headerRow = $detailSectionRow + 1;
        // Agrupa en $cells los datos que se utilizaran en el siguiente paso.
        $cells = [];
        // Agrupa en $merges los datos que se utilizaran en el siguiente paso.
        $merges = [];
        // Agrupa en $rowHeights los datos que se utilizaran en el siguiente paso.
        $rowHeights = [];
        // Guarda en $columnWidths el resultado necesario para continuar la operacion.
        $columnWidths = $this->anchosColumnasExcel($columns, $rows, $layoutColumnCount);

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ([1 => (string) $context['brand'], 2 => (string) $context['title'], 3 => (string) $context['subtitle'], 4 => 'Período consultado: '.(string) $context['period']] as $rowNumber => $text) {
            for ($column = 0; $column < $layoutColumnCount; $column++) {
                // Delega esta parte de la operacion al servicio o dependencia correspondiente.
                $this->agregarCeldaHojaCalculo($cells, $rowNumber, $column, '', $rowNumber === 2 ? 2 : ($rowNumber === 3 || $rowNumber === 4 ? 3 : 1));
            }
            // Guarda en $textColumn el resultado necesario para continuar la operacion.
            $textColumn = $hasLogo ? 2 : 0;
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->agregarCeldaHojaCalculo($cells, $rowNumber, $textColumn, $text, $rowNumber === 2 ? 2 : ($rowNumber === 3 || $rowNumber === 4 ? 3 : 1));
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($textColumn < $layoutColumnCount - 1) {
                $merges[] = $this->letraColumna($textColumn).$rowNumber.':'.$lastLayoutColumn.$rowNumber;
            }
        }

        // Guarda en $titleStartColumn el resultado necesario para continuar la operacion.
        $titleStartColumn = $hasLogo ? 2 : 0;
        // Guarda en $titleCapacity el resultado necesario para continuar la operacion.
        $titleCapacity = max(20, (int) array_sum(array_slice($columnWidths, $titleStartColumn)));
        $rowHeights[1] = 28;
        $rowHeights[2] = max(36, min(72, (int) ceil(mb_strlen((string) $context['title']) / $titleCapacity) * 22));
        $rowHeights[3] = max(27, min(54, (int) ceil(mb_strlen((string) $context['subtitle']) / $titleCapacity) * 18));
        $rowHeights[4] = 25;
        for ($column = 0; $column < $layoutColumnCount; $column++) {
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->agregarCeldaHojaCalculo($cells, 5, $column, '', self::XLSX_STYLE_ACCENT);
        }
        $rowHeights[5] = 7;

        // Guarda en $emittedAt el resultado necesario para continuar la operacion.
        $emittedAt = $this->partesFecha((string) $context['generated_at']);
        // Agrupa en $metadataRows los datos que se utilizaran en el siguiente paso.
        $metadataRows = [
            6 => [
                ['Empresa', (string) $context['company']],
                ['Módulo', (string) $context['module']],
            ],
            7 => [
                ['Reporte', (string) $context['report_name']],
                ['Generado por', (string) $context['generated_by']],
            ],
            8 => [
                ['Rol', (string) $context['role']],
                ['Fecha y hora', $emittedAt['date'].' '.$emittedAt['time']],
            ],
        ];

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($metadataRows as $rowNumber => $pairs) {
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->agregarCeldaHojaCalculo($cells, $rowNumber, 0, $pairs[0][0], 4);
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->agregarCeldaHojaCalculo($cells, $rowNumber, 1, $pairs[0][1], 5);
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->agregarCeldaHojaCalculo($cells, $rowNumber, 2, $pairs[1][0], 4);
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->agregarCeldaHojaCalculo($cells, $rowNumber, 3, $pairs[1][1], 5);
        }

        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->agregarCeldaHojaCalculo($cells, 9, 0, 'Alcance por sede', 4);
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->agregarCeldaCombinada($cells, $merges, 9, 1, $layoutColumnCount - 1, (string) $context['scope'], 5);
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->agregarCeldaHojaCalculo($cells, 10, 0, 'Filtros aplicados', 4);
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->agregarCeldaCombinada($cells, $merges, 10, 1, $layoutColumnCount - 1, $this->resumenFiltros($context['filters']), 5);
        $rowHeights[6] = 30;
        $rowHeights[7] = 30;
        $rowHeights[8] = 30;
        $rowHeights[9] = 25;
        $rowHeights[10] = 36;

        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->agregarCeldaCombinada($cells, $merges, 12, 0, $layoutColumnCount - 1, 'Resumen del período', self::XLSX_STYLE_SECTION);
        $rowHeights[12] = 24;

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($indicatorGroups !== []) {
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($indicatorGroups as $groupIndex => $indicatorGroup) {
                // Guarda en $indicatorCount el resultado necesario para continuar la operacion.
                $indicatorCount = count($indicatorGroup);
                // Guarda en $baseColumnCount el resultado necesario para continuar la operacion.
                $baseColumnCount = intdiv($layoutColumnCount, $indicatorCount);
                // Guarda en $extraColumnCount el resultado necesario para continuar la operacion.
                $extraColumnCount = $layoutColumnCount % $indicatorCount;
                // Guarda en $labelRow el resultado necesario para continuar la operacion.
                $labelRow = 13 + ($groupIndex * 3);
                // Guarda en $start el resultado necesario para continuar la operacion.
                $start = 0;

                // Recorre los elementos recibidos para validar o procesar cada registro.
                foreach ($indicatorGroup as $index => $indicator) {
                    // Guarda en $span el resultado necesario para continuar la operacion.
                    $span = $baseColumnCount + ($index < $extraColumnCount ? 1 : 0);
                    // Guarda en $end el resultado necesario para continuar la operacion.
                    $end = $start + $span - 1;
                    // Guarda en $labelStyle el resultado necesario para continuar la operacion.
                    $labelStyle = self::XLSX_STYLE_CARD_LABEL_BASE + $index;
                    // Guarda en $valueStyle el resultado necesario para continuar la operacion.
                    $valueStyle = self::XLSX_STYLE_CARD_VALUE_BASE + $index;
                    // Delega esta parte de la operacion al servicio o dependencia correspondiente.
                    $this->agregarCeldaCombinada($cells, $merges, $labelRow, $start, $end, (string) ($indicator['label'] ?? ''), $labelStyle);
                    // Delega esta parte de la operacion al servicio o dependencia correspondiente.
                    $this->agregarCeldaCombinada($cells, $merges, $labelRow + 1, $start, $end, $indicator['value'] ?? '', $valueStyle);
                    // Guarda en $start el resultado necesario para continuar la operacion.
                    $start = $end + 1;
                }

                $rowHeights[$labelRow] = 25;
                $rowHeights[$labelRow + 1] = 36;
                $rowHeights[$labelRow + 2] = 10;
            }
        } else {
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->agregarCeldaCombinada($cells, $merges, 13, 0, $layoutColumnCount - 1, 'No se proporcionaron indicadores para este reporte.', self::XLSX_STYLE_PANEL_CONTENT);
            $rowHeights[13] = 30;
        }

        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->agregarCeldaCombinada($cells, $merges, $detailSectionRow, 0, $layoutColumnCount - 1, (string) $context['detail_title'], self::XLSX_STYLE_SECTION);
        $rowHeights[$detailSectionRow] = 24;
        $rowHeights[$headerRow] = 32;

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($columns as $index => $column) {
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->agregarCeldaHojaCalculo($cells, $headerRow, $index, $column, 8);
        }

        // Guarda en $dataStartRow el resultado necesario para continuar la operacion.
        $dataStartRow = $headerRow + 1;
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($rows as $rowIndex => $row) {
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach (array_values($row) as $columnIndex => $value) {
                // Guarda en $type el resultado necesario para continuar la operacion.
                $type = $this->tipoColumna($context, $columnIndex);
                $alternate = $rowIndex % 2 === 1;
                // Guarda en $style el resultado necesario para continuar la operacion.
                $style = $this->estiloCuerpoHoja($type, $value, $alternate, (string) ($columns[$columnIndex] ?? ''));
                // Delega esta parte de la operacion al servicio o dependencia correspondiente.
                $this->agregarCeldaHojaCalculo($cells, $dataStartRow + $rowIndex, $columnIndex, $value, $style, $type);
            }
        }

        // Guarda en $lastDataRow el resultado necesario para continuar la operacion.
        $lastDataRow = max($headerRow, $dataStartRow + count($rows) - 1);
        // Guarda en $lastRow el resultado necesario para continuar la operacion.
        $lastRow = $lastDataRow;
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (is_array($context['summary_row'])) {
            $lastRow++;
            // Guarda en $summary el resultado necesario para continuar la operacion.
            $summary = $context['summary_row'];
            // Guarda en $summaryValues el resultado necesario para continuar la operacion.
            $summaryValues = array_values((array) ($summary['values'] ?? []));
            // Guarda en $summaryLabel el resultado necesario para continuar la operacion.
            $summaryLabel = (string) ($summary['label'] ?? 'Total');
            $summaryValues[0] = $summaryLabel;
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach (range(0, $columnCount - 1) as $columnIndex) {
                // Guarda en $type el resultado necesario para continuar la operacion.
                $type = $this->tipoColumna($context, $columnIndex);
                // Guarda en $summaryStyle el resultado necesario para continuar la operacion.
                $summaryStyle = match ($type) {
                    'currency' => 22,
                    'integer' => 23,
                    default => 11,
                };
                // Delega esta parte de la operacion al servicio o dependencia correspondiente.
                $this->agregarCeldaHojaCalculo($cells, $lastRow, $columnIndex, $summaryValues[$columnIndex] ?? '', $summaryStyle, $type);
            }
        }

        $observations = array_values(array_filter(array_map('strval', $context['observations']), static fn (string $value): bool => trim($value) !== ''));
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($observations === []) {
            // Agrupa en $observations los datos que se utilizaran en el siguiente paso.
            $observations = ['Sin observaciones adicionales.'];
        }

        // Guarda en $panelHeadingRow el resultado necesario para continuar la operacion.
        $panelHeadingRow = $lastRow + 2;
        // Guarda en $panelBodyStartRow el resultado necesario para continuar la operacion.
        $panelBodyStartRow = $panelHeadingRow + 1;
        // Guarda en $leftColumnEnd el resultado necesario para continuar la operacion.
        $leftColumnEnd = max(0, intdiv($layoutColumnCount, 2) - 1);
        // Guarda en $rightColumnStart el resultado necesario para continuar la operacion.
        $rightColumnStart = $leftColumnEnd + 1;
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->agregarCeldaCombinada($cells, $merges, $panelHeadingRow, 0, $leftColumnEnd, 'Observaciones', self::XLSX_STYLE_SECTION);
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->agregarCeldaCombinada($cells, $merges, $panelHeadingRow, $rightColumnStart, $layoutColumnCount - 1, 'Indicador principal', self::XLSX_STYLE_SECTION);
        $rowHeights[$panelHeadingRow] = 24;

        // Guarda en $primaryIndicator el resultado necesario para continuar la operacion.
        $primaryIndicator = $context['primary_indicator'];
        // Guarda en $leftWidth el resultado necesario para continuar la operacion.
        $leftWidth = array_sum(array_slice($columnWidths, 0, $leftColumnEnd + 1));
        // Guarda en $rightWidth el resultado necesario para continuar la operacion.
        $rightWidth = array_sum(array_slice($columnWidths, $rightColumnStart));
        // Guarda en $observationCharacterLimit el resultado necesario para continuar la operacion.
        $observationCharacterLimit = max(18, (int) $leftWidth - 4);
        // Guarda en $rightCharacterLimit el resultado necesario para continuar la operacion.
        $rightCharacterLimit = max(18, (int) $rightWidth - 4);
        // Guarda en $primaryLines el resultado necesario para continuar la operacion.
        $primaryLines = $this->lineasIndicadorPrincipal($primaryIndicator);
        // Guarda en $panelRowCount el resultado necesario para continuar la operacion.
        $panelRowCount = max(count($observations), count($primaryLines));

        for ($panelRowIndex = 0; $panelRowIndex < $panelRowCount; $panelRowIndex++) {
            // Guarda en $rowNumber el resultado necesario para continuar la operacion.
            $rowNumber = $panelBodyStartRow + $panelRowIndex;
            // Guarda en $observation el resultado necesario para continuar la operacion.
            $observation = $observations[$panelRowIndex] ?? '';
            // Guarda en $primaryLine el resultado necesario para continuar la operacion.
            $primaryLine = $primaryLines[$panelRowIndex] ?? '';
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->agregarCeldaCombinada($cells, $merges, $rowNumber, 0, $leftColumnEnd, $observation, self::XLSX_STYLE_PANEL_CONTENT);
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->agregarCeldaCombinada($cells, $merges, $rowNumber, $rightColumnStart, $layoutColumnCount - 1, $primaryLine, self::XLSX_STYLE_PANEL_CONTENT);

            // Guarda en $leftLines el resultado necesario para continuar la operacion.
            $leftLines = count($this->ajustarTexto($observation, $observationCharacterLimit));
            // Guarda en $rightLines el resultado necesario para continuar la operacion.
            $rightLines = count($this->ajustarTexto($primaryLine, $rightCharacterLimit));
            $rowHeights[$rowNumber] = max(28, max($leftLines, $rightLines) * 14 + 10);
        }

        // Guarda en $footerTitleRow el resultado necesario para continuar la operacion.
        $footerTitleRow = $panelBodyStartRow + $panelRowCount + 1;
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->agregarCeldaCombinada($cells, $merges, $footerTitleRow, 0, $layoutColumnCount - 1, 'TECHNOLOGY CELL — CELL SAC', self::XLSX_STYLE_FOOTER_TITLE);
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->agregarCeldaCombinada($cells, $merges, $footerTitleRow + 1, 0, $layoutColumnCount - 1, 'Documento generado por el sistema Technology CELL.', self::XLSX_STYLE_FOOTER_TEXT);
        $rowHeights[$footerTitleRow] = 28;
        $rowHeights[$footerTitleRow + 1] = 24;
        // Guarda en $lastRow el resultado necesario para continuar la operacion.
        $lastRow = $footerTitleRow + 1;

        ksort($cells);
        // Guarda en $sheetRows el resultado necesario para continuar la operacion.
        $sheetRows = '';
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($cells as $rowNumber => $rowCells) {
            ksort($rowCells);
            // Guarda en $height el resultado necesario para continuar la operacion.
            $height = $rowHeights[$rowNumber] ?? null;
            $heightAttributes = $height === null ? '' : ' ht="'.$height.'" customHeight="1"';
            $sheetRows .= '<row r="'.$rowNumber.'"'.$heightAttributes.'>'.implode('', $rowCells).'</row>';
        }

        // Guarda en $columnsXml el resultado necesario para continuar la operacion.
        $columnsXml = '<cols>';
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($columnWidths as $index => $width) {
            $columnsXml .= '<col min="'.($index + 1).'" max="'.($index + 1).'" width="'.number_format($width, 1, '.', '').'" customWidth="1"/>';
        }
        $columnsXml .= '</cols>';

        $mergeXml = $merges === [] ? '' : '<mergeCells count="'.count($merges).'">'.implode('', array_map(static fn (string $merge): string => '<mergeCell ref="'.$merge.'"/>', $merges)).'</mergeCells>';
        // Guarda en $drawingXml el resultado necesario para continuar la operacion.
        $drawingXml = $hasLogo ? '<drawing r:id="rId1"/>' : '';
        // Guarda en $autoFilter el resultado necesario para continuar la operacion.
        $autoFilter = $lastDataRow >= $headerRow ? '<autoFilter ref="A'.$headerRow.':'.$lastColumn.$lastDataRow.'"/>' : '';
        // Guarda en $orientation el resultado necesario para continuar la operacion.
        $orientation = $context['orientation'];
        // Guarda en $xml el resultado necesario para continuar la operacion.
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheetPr><pageSetUpPr fitToPage="1"/></sheetPr><dimension ref="A1:'.$lastLayoutColumn.$lastRow.'"/><sheetViews><sheetView showGridLines="0" workbookViewId="0"><pane ySplit="'.$headerRow.'" topLeftCell="A'.($headerRow + 1).'" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A'.($headerRow + 1).'" sqref="A'.($headerRow + 1).'"/></sheetView></sheetViews>'.$columnsXml.'<sheetData>'.$sheetRows.'</sheetData>'.$autoFilter.$mergeXml.'<printOptions horizontalCentered="0"/><pageMargins left="0.3" right="0.3" top="0.55" bottom="0.55" header="0.2" footer="0.25"/><pageSetup paperSize="9" orientation="'.$orientation.'" fitToWidth="1" fitToHeight="0"/><headerFooter><oddFooter>&amp;C TECHNOLOGY CELL — CELL SAC &amp;LDocumento generado por el sistema Technology CELL&amp;R&amp;P / &amp;N</oddFooter></headerFooter>'.$drawingXml.'</worksheet>';

        return ['xml' => $xml, 'last_column' => $lastColumn, 'last_layout_column' => $lastLayoutColumn, 'last_row' => $lastRow, 'header_row' => $headerRow];
    }

    /**
     * @param  array<int, array<int, string>>  $cells
     */
    private function agregarCeldaHojaCalculo(array &$cells, int $row, int $column, mixed $value, int $style, string $type = 'text'): void
    {
        // Guarda en $reference el resultado necesario para continuar la operacion.
        $reference = $this->letraColumna($column).$row;
        // Guarda en $attributes el resultado necesario para continuar la operacion.
        $attributes = ' r="'.$reference.'" s="'.$style.'"';
        // Guarda en $value el resultado necesario para continuar la operacion.
        $value = $value ?? '';

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->esTipoNumerico($type) && is_numeric($value)) {
            // Guarda en $numericValue el resultado necesario para continuar la operacion.
            $numericValue = (float) $value;
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($type === 'integer') {
                // Guarda en $numericValue el resultado necesario para continuar la operacion.
                $numericValue = (int) $value;
            }
            $cells[$row][$column] = '<c'.$attributes.' t="n"><v>'.rtrim(rtrim(number_format($numericValue, 10, '.', ''), '0'), '.').'</v></c>';

            return;
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (in_array($type, ['date', 'datetime'], true)) {
            $serial = $this->fechaSerialExcel($value, $type === 'date');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($serial !== null) {
                $cells[$row][$column] = '<c'.$attributes.' t="n"><v>'.number_format($serial, 10, '.', '').'</v></c>';

                return;
            }
        }

        // Guarda en $safe el resultado necesario para continuar la operacion.
        $safe = htmlspecialchars(self::textoHojaCalculoSeguro((string) $value), ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $cells[$row][$column] = '<c'.$attributes.' t="inlineStr"><is><t xml:space="preserve">'.$safe.'</t></is></c>';
    }

    /**
     * @param  array<int, array<int, string>>  $cells
     * @param  array<int, string>  $merges
     */
    private function agregarCeldaCombinada(array &$cells, array &$merges, int $row, int $start, int $end, mixed $value, int $style): void
    {
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->agregarCeldaHojaCalculo($cells, $row, $start, $value, $style);
        for ($column = $start + 1; $column <= $end; $column++) {
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->agregarCeldaHojaCalculo($cells, $row, $column, '', $style);
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($start < $end) {
            $merges[] = $this->letraColumna($start).$row.':'.$this->letraColumna($end).$row;
        }
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @return array<int, float>
     */
    private function anchosColumnasExcel(array $columns, array $rows, int $layoutColumnCount): array
    {
        // Agrupa en $widths los datos que se utilizaran en el siguiente paso.
        $widths = [];
        for ($column = 0; $column < $layoutColumnCount; $column++) {
            // Guarda en $maximum el resultado necesario para continuar la operacion.
            $maximum = mb_strlen((string) ($columns[$column] ?? ''));
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($rows as $row) {
                // Guarda en $maximum el resultado necesario para continuar la operacion.
                $maximum = max($maximum, mb_strlen((string) ($row[$column] ?? '')));
            }
            $widths[] = min(42, max(12, $maximum + 2));
        }

        return $widths;
    }

    /**
     * Calcula el estilo del cuerpo de una hoja de cálculo.
     *
     * @param  mixed  $type  Parámetro recibido por la operación.
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @param  mixed  $alternate  Parámetro recibido por la operación.
     * @param  mixed  $heading  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    private function estiloCuerpoHoja(string $type, mixed $value, bool $alternate, string $heading): int
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (mb_stripos($heading, 'estado') !== false) {
            return match ($this->tonoEstado((string) $value)) {
                'positive' => 18,
                'warning' => 19,
                'danger' => 20,
                default => $alternate ? 10 : 9,
            };
        }

        return match ($type) {
            'currency' => $alternate ? 13 : 12,
            'date' => $alternate ? 15 : 14,
            'datetime' => $alternate ? 15 : 14,
            'integer' => $alternate ? 17 : 16,
            default => $alternate ? 10 : 9,
        };
    }

    /**
     * Genera el XML de estilos de la hoja de cálculo.
     *
     * @return string Resultado de la operación.
     */
    private function xmlEstilos(): string
    {
        // Guarda en $formats el resultado necesario para continuar la operacion.
        $formats = '<numFmts count="6"><numFmt numFmtId="'.self::XLSX_NUMFMT_CURRENCY.'" formatCode="#,##0.00 &quot;PEN&quot;"/><numFmt numFmtId="'.self::XLSX_NUMFMT_DATE.'" formatCode="dd/mm/yyyy"/><numFmt numFmtId="'.self::XLSX_NUMFMT_DATETIME.'" formatCode="dd/mm/yyyy hh:mm"/><numFmt numFmtId="'.self::XLSX_NUMFMT_NUMBER.'" formatCode="#,##0.00"/><numFmt numFmtId="'.self::XLSX_NUMFMT_INTEGER.'" formatCode="#,##0"/><numFmt numFmtId="'.self::XLSX_NUMFMT_PERCENT.'" formatCode="0.0%"/></numFmts>';
        // Guarda en $fonts el resultado necesario para continuar la operacion.
        $fonts = '<fonts count="9"><font><sz val="10"/><name val="Calibri"/><color rgb="FF233047"/></font><font><b/><sz val="10"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font><font><b/><sz val="20"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font><font><sz val="9"/><name val="Calibri"/><color rgb="FFE8F2FF"/></font><font><b/><sz val="9"/><name val="Calibri"/><color rgb="FF17406D"/></font><font><sz val="9"/><name val="Calibri"/><color rgb="FF233047"/></font><font><b/><sz val="9"/><name val="Calibri"/><color rgb="FF125A9C"/></font><font><b/><sz val="13"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font><font><b/><sz val="13"/><name val="Calibri"/><color rgb="FF09264A"/></font></fonts>';
        // Guarda en $fills el resultado necesario para continuar la operacion.
        $fills = '<fills count="14"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF09264A"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF145DA0"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDCEEFF"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF2F7FC"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE5F5EB"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFF1CC"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFCE4E4"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0D3A68"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFEAF4FD"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFECE8FF"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF26B8E8"/><bgColor indexed="64"/></patternFill></fill></fills>';
        // Guarda en $borders el resultado necesario para continuar la operacion.
        $borders = '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFD8E2EF"/></left><right style="thin"><color rgb="FFD8E2EF"/></right><top style="thin"><color rgb="FFD8E2EF"/></top><bottom style="thin"><color rgb="FFD8E2EF"/></bottom><diagonal/></border></borders>';
        // Agrupa en $styleDefinitions los datos que se utilizaran en el siguiente paso.
        $styleDefinitions = [
            [0, 0, 0, 0, 'left'],
            [1, 2, 0, 0, 'left'],
            [2, 2, 0, 0, 'center'],
            [3, 2, 0, 0, 'center'],
            [4, 4, 0, 0, 'left'],
            [0, 0, 1, 0, 'left'],
            [6, 4, 1, 0, 'center'],
            [7, 3, 1, 0, 'center'],
            [1, 3, 1, 0, 'center'],
            [0, 5, 1, 0, 'left'],
            [0, 6, 1, 0, 'left'],
            [1, 10, 1, 0, 'left'],
            [0, 5, 1, self::XLSX_NUMFMT_CURRENCY, 'right'],
            [0, 6, 1, self::XLSX_NUMFMT_CURRENCY, 'right'],
            [0, 5, 1, self::XLSX_NUMFMT_DATETIME, 'center'],
            [0, 6, 1, self::XLSX_NUMFMT_DATETIME, 'center'],
            [0, 5, 1, self::XLSX_NUMFMT_INTEGER, 'right'],
            [0, 6, 1, self::XLSX_NUMFMT_INTEGER, 'right'],
            [4, 7, 1, 0, 'center'],
            [4, 8, 1, 0, 'center'],
            [4, 9, 1, 0, 'center'],
            [5, 0, 0, 0, 'center'],
            [1, 10, 1, self::XLSX_NUMFMT_CURRENCY, 'right'],
            [1, 10, 1, self::XLSX_NUMFMT_INTEGER, 'right'],
            [1, 3, 1, 0, 'left'],
            [6, 4, 1, 0, 'center'],
            [6, 7, 1, 0, 'center'],
            [6, 12, 1, 0, 'center'],
            [6, 8, 1, 0, 'center'],
            [8, 4, 1, 0, 'center'],
            [8, 7, 1, 0, 'center'],
            [8, 12, 1, 0, 'center'],
            [8, 8, 1, 0, 'center'],
            [5, 11, 1, 0, 'left'],
            [1, 2, 1, 0, 'center'],
            [3, 2, 1, 0, 'center'],
            [1, 13, 1, 0, 'center'],
        ];
        // Guarda en $xfs el resultado necesario para continuar la operacion.
        $xfs = '';
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($styleDefinitions as [$fontId, $fillId, $borderId, $numFmtId, $horizontal]) {
            $xfs .= '<xf numFmtId="'.$numFmtId.'" fontId="'.$fontId.'" fillId="'.$fillId.'" borderId="'.$borderId.'" xfId="0"'.($numFmtId === 0 ? '' : ' applyNumberFormat="1"').' applyFont="1" applyFill="1" applyBorder="'.($borderId === 0 ? '0' : '1').'" applyAlignment="1"><alignment horizontal="'.$horizontal.'" vertical="center" wrapText="1"/></xf>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'.$formats.$fonts.$fills.$borders.'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="'.count($styleDefinitions).'">'.$xfs.'</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  array<string, mixed>  $context
     */
    private function xmlLibroTrabajo(string $lastColumn, int $lastRow, int $headerRow): string
    {
        // Guarda en $printArea el resultado necesario para continuar la operacion.
        $printArea = '$A$1:$'.$lastColumn.'$'.$lastRow;

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><bookViews><workbookView/></bookViews><sheets><sheet name="Reporte" sheetId="1" r:id="rId1"/></sheets><definedNames><definedName name="_xlnm.Print_Titles" localSheetId="0">\'Reporte\'!$'.$headerRow.':$'.$headerRow.'</definedName><definedName name="_xlnm.Print_Area" localSheetId="0">\'Reporte\'!'.$printArea.'</definedName></definedNames><calcPr calcId="191029"/></workbook>';
    }

    /**
     * Ejecuta la operación drawingXml definida por la clase ExportadorTabular.
     *
     * @return string Resultado de la operación.
     */
    private function xmlDibujo(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><xdr:twoCellAnchor editAs="oneCell"><xdr:from><xdr:col>0</xdr:col><xdr:colOff>50000</xdr:colOff><xdr:row>0</xdr:row><xdr:rowOff>30000</xdr:rowOff></xdr:from><xdr:to><xdr:col>2</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>4</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:to><xdr:pic><xdr:nvPicPr><xdr:cNvPr id="2" name="Technology CELL"/><xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr></xdr:nvPicPr><xdr:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill><xdr:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr></xdr:pic><xdr:clientData/></xdr:twoCellAnchor></xdr:wsDr>';
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  array<string, mixed>  $report
     */
    private function generarPdf(string $filename, array $columns, array $rows, array $report): Response
    {
        // Guarda en $context el resultado necesario para continuar la operacion.
        $context = $this->contextoReporte($filename, $report, count($columns));
        // Guarda en $orientation el resultado necesario para continuar la operacion.
        $orientation = $context['orientation'];
        $pageWidth = $orientation === 'landscape' ? 842 : 595;
        $pageHeight = $orientation === 'landscape' ? 595 : 842;
        // Guarda en $margin el resultado necesario para continuar la operacion.
        $margin = 30;
        // Guarda en $tableWidth el resultado necesario para continuar la operacion.
        $tableWidth = $pageWidth - (2 * $margin);
        // Guarda en $columnWidths el resultado necesario para continuar la operacion.
        $columnWidths = $this->anchosColumnasPdf($columns, $rows, $tableWidth);
        // Guarda en $logo el resultado necesario para continuar la operacion.
        $logo = $this->objetosLogoPdf(5);
        // Guarda en $logoObjectId el resultado necesario para continuar la operacion.
        $logoObjectId = $logo['image_id'];
        // Agrupa en $objects los datos que se utilizaran en el siguiente paso.
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ] + $logo['objects'];
        // Agrupa en $pages los datos que se utilizaran en el siguiente paso.
        $pages = [];
        // Guarda en $pageContent el resultado necesario para continuar la operacion.
        $pageContent = '';
        // Guarda en $pageNumber el resultado necesario para continuar la operacion.
        $pageNumber = 0;
        // Guarda en $startPage el resultado necesario para continuar la operacion.
        $startPage = function (bool $first) use (&$pageContent, &$pageNumber, &$pages, $pageWidth, $pageHeight, $margin, $tableWidth, $columns, $columnWidths, $context, $logoObjectId): float {
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($pageNumber > 0) {
                $pages[] = $pageContent;
            }
            $pageNumber++;
            $pageContent = $this->encabezadoPdf($pageWidth, $pageHeight, $margin, $context, $first, $logoObjectId !== null);
            // Guarda en $top el resultado necesario para continuar la operacion.
            $top = $first
                ? $this->inicioTablaPdfPrimeraPagina($pageWidth, $pageHeight, $margin, $context)
                : $pageHeight - 91;
            // Guarda en $detailTitle el resultado necesario para continuar la operacion.
            $detailTitle = $first ? (string) $context['detail_title'] : (string) $context['detail_title'].' · continuación';
            $pageContent .= $this->barraSeccionPdf($detailTitle, $margin, $top + 18, $tableWidth, 14);
            $pageContent .= $this->encabezadoTablaPdf($columns, $columnWidths, $margin, $top);

            return $top - $this->altoEncabezadoTablaPdf($columns, $columnWidths);
        };

        // Guarda en $currentY el resultado necesario para continuar la operacion.
        $currentY = $startPage(true);
        // Guarda en $appendTableRow el resultado necesario para continuar la operacion.
        $appendTableRow = function (array $wrapped, bool $alternate, bool $summary = false) use (&$currentY, &$pageContent, $startPage, $columnWidths, $margin, $columns, $context): void {
            // Guarda en $lineCount el resultado necesario para continuar la operacion.
            $lineCount = max(array_map('count', $wrapped));
            // Guarda en $lineOffset el resultado necesario para continuar la operacion.
            $lineOffset = 0;

            while ($lineOffset < $lineCount) {
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($currentY - 20 < 38) {
                    // Guarda en $currentY el resultado necesario para continuar la operacion.
                    $currentY = $startPage(false);
                }

                // Guarda en $availableLines el resultado necesario para continuar la operacion.
                $availableLines = max(1, (int) floor(($currentY - 46) / 8));
                // Guarda en $fragmentLineCount el resultado necesario para continuar la operacion.
                $fragmentLineCount = min($lineCount - $lineOffset, $availableLines);
                // Guarda en $fragment el resultado necesario para continuar la operacion.
                $fragment = array_map(
                    static fn (array $lines): array => array_slice($lines, $lineOffset, $fragmentLineCount),
                    $wrapped,
                );
                // Guarda en $rowHeight el resultado necesario para continuar la operacion.
                $rowHeight = max(20, ($fragmentLineCount * 8) + 8);
                $pageContent .= $this->filaTablaPdf($fragment, $columnWidths, $margin, $currentY, $rowHeight, $alternate, $columns, $context, $summary);
                $currentY -= $rowHeight;
                $lineOffset += $fragmentLineCount;

                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($lineOffset < $lineCount) {
                    // Guarda en $currentY el resultado necesario para continuar la operacion.
                    $currentY = $startPage(false);
                }
            }
        };

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($rows as $rowIndex => $row) {
            // Agrupa en $formatted los datos que se utilizaran en el siguiente paso.
            $formatted = [];
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach (array_values($row) as $columnIndex => $value) {
                $formatted[] = $this->valorPresentacion($value, $this->tipoColumna($context, $columnIndex));
            }
            // Guarda en $wrapped el resultado necesario para continuar la operacion.
            $wrapped = $this->ajustarFila($formatted, $columnWidths, 6.8);
            $appendTableRow($wrapped, $rowIndex % 2 === 1);
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (is_array($context['summary_row'])) {
            // Guarda en $summary el resultado necesario para continuar la operacion.
            $summary = $context['summary_row'];
            // Guarda en $values el resultado necesario para continuar la operacion.
            $values = array_values((array) ($summary['values'] ?? []));
            $values[0] = (string) ($summary['label'] ?? 'Total');
            // Agrupa en $formatted los datos que se utilizaran en el siguiente paso.
            $formatted = [];
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($values as $columnIndex => $value) {
                $formatted[] = $this->valorPresentacion($value, $this->tipoColumna($context, $columnIndex));
            }
            // Guarda en $wrapped el resultado necesario para continuar la operacion.
            $wrapped = $this->ajustarFila($formatted, $columnWidths, 6.8);
            $appendTableRow($wrapped, false, true);
        }

        // Agrupa en $observationLines los datos que se utilizaran en el siguiente paso.
        $observationLines = [];
        // Guarda en $leftPanelWidth el resultado necesario para continuar la operacion.
        $leftPanelWidth = ($tableWidth - 12) / 2;
        // Guarda en $rightPanelWidth el resultado necesario para continuar la operacion.
        $rightPanelWidth = $leftPanelWidth;
        // Guarda en $observationCharacterLimit el resultado necesario para continuar la operacion.
        $observationCharacterLimit = max(24, (int) (($leftPanelWidth - 16) / 4.0));
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($context['observations'] as $observation) {
            array_push($observationLines, ...$this->ajustarTexto((string) $observation, $observationCharacterLimit));
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($observationLines === []) {
            // Agrupa en $observationLines los datos que se utilizaran en el siguiente paso.
            $observationLines = ['Sin observaciones adicionales.'];
        }

        // Guarda en $primaryIndicator el resultado necesario para continuar la operacion.
        $primaryIndicator = $context['primary_indicator'];
        // Guarda en $primaryProgress el resultado necesario para continuar la operacion.
        $primaryProgress = is_array($primaryIndicator) ? $this->progresoPrincipal($primaryIndicator) : null;
        // Guarda en $primaryLines el resultado necesario para continuar la operacion.
        $primaryLines = $this->lineasIndicadorPrincipal($primaryIndicator);
        // Guarda en $maximumPanelLines el resultado necesario para continuar la operacion.
        $maximumPanelLines = max(1, (int) floor(($pageHeight - 230) / 9));
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach (array_chunk($observationLines, $maximumPanelLines) as $panelIndex => $panelLines) {
            $rightLineCount = max(count($primaryLines), $primaryProgress === null ? 0 : 3);
            // Guarda en $lineCount el resultado necesario para continuar la operacion.
            $lineCount = max(count($panelLines), $rightLineCount);
            // Guarda en $panelHeight el resultado necesario para continuar la operacion.
            $panelHeight = 45 + ($lineCount * 9);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($currentY - $panelHeight < 42) {
                // Guarda en $currentY el resultado necesario para continuar la operacion.
                $currentY = $startPage(false);
            }

            // Guarda en $panelTop el resultado necesario para continuar la operacion.
            $panelTop = $currentY - 8;
            // Guarda en $panelBodyTop el resultado necesario para continuar la operacion.
            $panelBodyTop = $panelTop - 16;
            // Guarda en $panelBodyHeight el resultado necesario para continuar la operacion.
            $panelBodyHeight = $panelHeight - 16;
            // Guarda en $rightPanelX el resultado necesario para continuar la operacion.
            $rightPanelX = $margin + $leftPanelWidth + 12;
            $pageContent .= $this->barraSeccionPdf($panelIndex === 0 ? 'Observaciones' : 'Observaciones · continuación', $margin, $panelTop, $leftPanelWidth, 16);
            $pageContent .= $this->barraSeccionPdf('Indicador principal', $rightPanelX, $panelTop, $rightPanelWidth, 16);
            $pageContent .= $this->rectanguloPdf($margin, $panelBodyTop - $panelBodyHeight, $leftPanelWidth, $panelBodyHeight, [0.96, 0.98, 1], [0.80, 0.87, 0.94]);
            $pageContent .= $this->rectanguloPdf($rightPanelX, $panelBodyTop - $panelBodyHeight, $rightPanelWidth, $panelBodyHeight, [0.96, 0.98, 1], [0.80, 0.87, 0.94]);

            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($panelLines as $lineIndex => $line) {
                $pageContent .= $this->textoPdf($line, $margin + 8, $panelBodyTop - 13 - ($lineIndex * 9), 7.2, [0.16, 0.22, 0.31]);
            }

            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($primaryIndicator === null || $primaryLines === ['No se proporcionó un indicador principal.']) {
                $pageContent .= $this->textoPdf($primaryLines[0], $rightPanelX + 8, $panelBodyTop - 17, 7.2, [0.16, 0.22, 0.31]);
            } else {
                $pageContent .= $this->textoPdf($primaryLines[0], $rightPanelX + 8, $panelBodyTop - 13, 7.2, [0.08, 0.27, 0.48], true);
                // Guarda en $value el resultado necesario para continuar la operacion.
                $value = trim((string) ($primaryIndicator['value'] ?? ''));
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($primaryProgress !== null) {
                    // Guarda en $barWidth el resultado necesario para continuar la operacion.
                    $barWidth = $rightPanelWidth - 54;
                    // Guarda en $barX el resultado necesario para continuar la operacion.
                    $barX = $rightPanelX + 8;
                    // Guarda en $barY el resultado necesario para continuar la operacion.
                    $barY = $panelBodyTop - 37;
                    $pageContent .= $this->barraProgresoPdf($barX, $barY, $barWidth, 9, $primaryProgress);
                    $value = $value !== '' ? $value : number_format($primaryProgress, 1, '.', ',').'%';
                    $pageContent .= $this->textoPdf($value, $barX + $barWidth + 6, $barY + 1, 7.2, [0.04, 0.25, 0.48], true);
                } elseif ($value !== '') {
                    $pageContent .= $this->textoPdf($value, $rightPanelX + 8, $panelBodyTop - 28, 8, [0.04, 0.25, 0.48], true);
                }
            }

            // Guarda en $currentY el resultado necesario para continuar la operacion.
            $currentY = $panelTop - $panelHeight - 10;
        }
        $pages[] = $pageContent;

        // Agrupa en $pageObjectIds los datos que se utilizaran en el siguiente paso.
        $pageObjectIds = [];
        // Agrupa en $contentObjectIds los datos que se utilizaran en el siguiente paso.
        $contentObjectIds = [];
        // Guarda en $nextObjectId el resultado necesario para continuar la operacion.
        $nextObjectId = max(array_keys($objects)) + 1;
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($pages as $index => $page) {
            $pageObjectIds[$index] = $nextObjectId++;
            $contentObjectIds[$index] = $nextObjectId++;
        }

        $kids = implode(' ', array_map(static fn (int $id): string => $id.' 0 R', $pageObjectIds));
        $objects[2] = '<< /Type /Pages /Kids ['.$kids.'] /Count '.count($pageObjectIds).' >>';
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($pages as $index => $page) {
            $page .= $this->piePdf($pageWidth, $pageHeight, $margin, $context, $index + 1, count($pages));
            // Guarda en $contentId el resultado necesario para continuar la operacion.
            $contentId = $contentObjectIds[$index];
            // Guarda en $pageId el resultado necesario para continuar la operacion.
            $pageId = $pageObjectIds[$index];
            $objects[$contentId] = '<< /Length '.strlen($page).">>\nstream\n{$page}endstream";
            $resources = '<< /Font << /F1 3 0 R /F2 4 0 R >>'.($logoObjectId === null ? '' : ' /XObject << /Im0 '.$logoObjectId.' 0 R >>').' >>';
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.$pageWidth.' '.$pageHeight.'] /Resources '.$resources.' /Contents '.$contentId.' 0 R >>';
        }

        ksort($objects);
        // Guarda en $pdf el resultado necesario para continuar la operacion.
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        // Agrupa en $offsets los datos que se utilizaran en el siguiente paso.
        $offsets = [0];
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }
        // Guarda en $xref el resultado necesario para continuar la operacion.
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$id])."\n";
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->nombreArchivoSeguro($filename).'.pdf"',
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function encabezadoPdf(int $width, int $height, int $margin, array $context, bool $first, bool $hasLogo): string
    {
        // Guarda en $content el resultado necesario para continuar la operacion.
        $content = '';
        // Guarda en $bannerHeight el resultado necesario para continuar la operacion.
        $bannerHeight = $first ? 88 : 50;
        $content .= $this->rectanguloPdf(0, $height - $bannerHeight, $width, $bannerHeight, [0.035, 0.15, 0.29]);
        $content .= $this->rectanguloPdf($width - 19, $height - $bannerHeight, 7, $bannerHeight, [0.08, 0.47, 0.78]);
        $content .= $this->rectanguloPdf($width - 12, $height - $bannerHeight, 12, $bannerHeight, [0.06, 0.67, 0.91]);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($first) {
            $content .= $this->rectanguloPdf($margin, $height - 75, 112, 58, [1, 1, 1]);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($hasLogo) {
                $content .= "q\n102 0 0 40 ".($margin + 5).' '.($height - 66)." cm\n/Im0 Do\nQ\n";
            } else {
                $content .= $this->textoPdf('TECHNOLOGY CELL', $margin + 8, $height - 45, 10, [0.06, 0.32, 0.69], true);
            }
            // Guarda en $x el resultado necesario para continuar la operacion.
            $x = $margin + 128;
            $content .= $this->textoPdf((string) $context['brand'].' — CELL SAC', $x, $height - 30, 10, [1, 1, 1], true);
            // Guarda en $titleWidth el resultado necesario para continuar la operacion.
            $titleWidth = $width - $x - $margin - 24;
            // Guarda en $titleFontSize el resultado necesario para continuar la operacion.
            $titleFontSize = min(16, max(8, $titleWidth / max(1, mb_strlen((string) $context['title']) * 0.56)));
            // Guarda en $titleTextWidth el resultado necesario para continuar la operacion.
            $titleTextWidth = mb_strlen((string) $context['title']) * $titleFontSize * 0.52;
            // Guarda en $titleX el resultado necesario para continuar la operacion.
            $titleX = $x + max(0, ($titleWidth - $titleTextWidth) / 2);
            $content .= $this->textoPdf((string) $context['title'], $titleX, $height - 50, $titleFontSize, [1, 1, 1], true);
            // Guarda en $subtitleFontSize el resultado necesario para continuar la operacion.
            $subtitleFontSize = min(7.6, max(5.8, $titleWidth / max(1, mb_strlen((string) $context['subtitle']) * 0.52)));
            // Guarda en $subtitleTextWidth el resultado necesario para continuar la operacion.
            $subtitleTextWidth = mb_strlen((string) $context['subtitle']) * $subtitleFontSize * 0.52;
            // Guarda en $subtitleX el resultado necesario para continuar la operacion.
            $subtitleX = $x + max(0, ($titleWidth - $subtitleTextWidth) / 2);
            $content .= $this->textoPdf((string) $context['subtitle'], $subtitleX, $height - 68, $subtitleFontSize, [0.88, 0.94, 1]);

            // Guarda en $emitted el resultado necesario para continuar la operacion.
            $emitted = $this->partesFecha((string) $context['generated_at']);
            $content .= $this->textoPdf('Empresa: '.(string) $context['company'], $margin, $height - 105, 7.2, [0.08, 0.16, 0.27], true);
            $content .= $this->textoPdf('Módulo: '.(string) $context['module'], $width / 2, $height - 105, 7.2, [0.08, 0.16, 0.27], true);
            $content .= $this->textoPdf('Reporte: '.(string) $context['report_name'], $margin, $height - 119, 7, [0.18, 0.23, 0.32]);
            $content .= $this->textoPdf('Generado por: '.(string) $context['generated_by'], $width / 2, $height - 119, 7, [0.18, 0.23, 0.32]);
            $content .= $this->textoPdf('Rol: '.(string) $context['role'], $margin, $height - 133, 7, [0.18, 0.23, 0.32]);
            $content .= $this->textoPdf('Emisión: '.$emitted['date'].' '.$emitted['time'], $width / 2, $height - 133, 7, [0.18, 0.23, 0.32]);
            $content .= $this->textoPdf('Período: '.(string) $context['period'].' · Sede: '.(string) $context['scope'], $margin, $height - 147, 7, [0.18, 0.23, 0.32]);
            // Guarda en $filters el resultado necesario para continuar la operacion.
            $filters = $this->resumenFiltros($context['filters']);
            // Guarda en $filterLines el resultado necesario para continuar la operacion.
            $filterLines = $this->ajustarTexto('Filtros: '.$filters, max(34, (int) (($width - 2 * $margin) / 4.1)));
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($filterLines as $index => $line) {
                $content .= $this->textoPdf($line, $margin, $height - 161 - ($index * 9), 6.7, [0.31, 0.37, 0.47]);
            }

            // Guarda en $summaryLayout el resultado necesario para continuar la operacion.
            $summaryLayout = $this->distribucionResumenPdf($width, $height, $margin, $context);
            $content .= $this->barraSeccionPdf('Resumen del período', $margin, $summaryLayout['summary_top'], $width - (2 * $margin), 15);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($context['indicators'] === []) {
                $content .= $this->rectanguloPdf($margin, $summaryLayout['content_top'] - 27, $width - (2 * $margin), 27, [0.91, 0.96, 1], [0.77, 0.85, 0.94]);
                $content .= $this->textoPdf('No se proporcionaron indicadores para este reporte.', $margin + 8, $summaryLayout['content_top'] - 16, 7.2, [0.10, 0.30, 0.50]);
            } else {
                // Recorre los elementos recibidos para validar o procesar cada registro.
                foreach ($summaryLayout['cards']['items'] as $index => $card) {
                    // Guarda en $cardX el resultado necesario para continuar la operacion.
                    $cardX = $margin + ($index * ($summaryLayout['cards']['card_width'] + $summaryLayout['cards']['gap']));
                    [$fill, $border, $text] = $this->paletaIndicadorPdf($index);
                    $content .= $this->rectanguloPdf($cardX, $summaryLayout['card_bottom'], $summaryLayout['cards']['card_width'], $summaryLayout['cards']['height'], $fill, $border);

                    // Recorre los elementos recibidos para validar o procesar cada registro.
                    foreach ($card['label_lines'] as $lineIndex => $line) {
                        $content .= $this->textoPdf($line, $cardX + 7, $summaryLayout['content_top'] - 10 - ($lineIndex * 7), 6.5, $text, true);
                    }

                    // Guarda en $valueTop el resultado necesario para continuar la operacion.
                    $valueTop = $summaryLayout['content_top'] - 10 - (count($card['label_lines']) * 7) - 3;
                    // Recorre los elementos recibidos para validar o procesar cada registro.
                    foreach ($card['value_lines'] as $lineIndex => $line) {
                        $content .= $this->textoPdf($line, $cardX + 7, $valueTop - ($lineIndex * 11), 10, [0.04, 0.25, 0.48], true);
                    }
                }
            }
        } else {
            $content .= $this->textoPdf((string) $context['brand'].' — CELL SAC', $margin, $height - 21, 8, [1, 1, 1], true);
            $content .= $this->textoPdf((string) $context['title'].' · Continuación', $margin, $height - 38, 10, [1, 1, 1], true);
        }

        return $content;
    }

    /**
     * @param  array<int, array{label?:string,value?:string|int|float|null}>  $indicators
     * @param  array<string, mixed>  $context
     */
    private function inicioTablaPdfPrimeraPagina(int $width, int $height, int $margin, array $context): float
    {
        return $this->distribucionResumenPdf($width, $height, $margin, $context)['table_top'];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{summary_top:float,content_top:float,card_bottom:float,detail_top:float,table_top:float,cards:array{items:array<int,array{label_lines:array<int,string>,value_lines:array<int,string>}>,card_width:float,gap:float,height:float}}
     */
    private function distribucionResumenPdf(int $width, int $height, int $margin, array $context): array
    {
        // Guarda en $filterLines el resultado necesario para continuar la operacion.
        $filterLines = $this->ajustarTexto('Filtros: '.$this->resumenFiltros($context['filters']), max(34, (int) (($width - 2 * $margin) / 4.1)));
        // Guarda en $lastFilterBaseline el resultado necesario para continuar la operacion.
        $lastFilterBaseline = $height - 161 - ((count($filterLines) - 1) * 9);
        // Guarda en $summaryTop el resultado necesario para continuar la operacion.
        $summaryTop = $lastFilterBaseline - 7;
        // Guarda en $contentTop el resultado necesario para continuar la operacion.
        $contentTop = $summaryTop - 20;
        $cards = $context['indicators'] === []
            ? ['items' => [], 'card_width' => 0.0, 'gap' => 0.0, 'height' => 27.0]
            : $this->distribucionIndicadoresPdf($context['indicators'], $width, $margin);
        // Guarda en $cardBottom el resultado necesario para continuar la operacion.
        $cardBottom = $contentTop - $cards['height'];
        // Guarda en $detailTop el resultado necesario para continuar la operacion.
        $detailTop = $cardBottom - 7;
        // Guarda en $tableTop el resultado necesario para continuar la operacion.
        $tableTop = $detailTop - 18;

        return [
            'summary_top' => $summaryTop,
            'content_top' => $contentTop,
            'card_bottom' => $cardBottom,
            'detail_top' => $detailTop,
            'table_top' => $tableTop,
            'cards' => $cards,
        ];
    }

    /**
     * @param  array<int, array{label?:string,value?:string|int|float|null}>  $indicators
     * @return array{items:array<int,array{label_lines:array<int,string>,value_lines:array<int,string>}>,card_width:float,gap:float,height:float}
     */
    private function distribucionIndicadoresPdf(array $indicators, int $width, int $margin): array
    {
        // Guarda en $gap el resultado necesario para continuar la operacion.
        $gap = 7.0;
        // Guarda en $cardWidth el resultado necesario para continuar la operacion.
        $cardWidth = ($width - (2 * $margin) - ($gap * (count($indicators) - 1))) / count($indicators);
        // Guarda en $labelLimit el resultado necesario para continuar la operacion.
        $labelLimit = max(8, (int) (($cardWidth - 14) / (6.5 * 0.53)));
        // Guarda en $valueLimit el resultado necesario para continuar la operacion.
        $valueLimit = max(6, (int) (($cardWidth - 14) / (10 * 0.53)));
        // Agrupa en $cards los datos que se utilizaran en el siguiente paso.
        $cards = [];
        // Guarda en $height el resultado necesario para continuar la operacion.
        $height = 34.0;

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($indicators as $indicator) {
            // Guarda en $labelLines el resultado necesario para continuar la operacion.
            $labelLines = $this->ajustarTexto((string) ($indicator['label'] ?? ''), $labelLimit);
            // Guarda en $valueLines el resultado necesario para continuar la operacion.
            $valueLines = $this->ajustarTexto((string) ($indicator['value'] ?? ''), $valueLimit);
            // Guarda en $height el resultado necesario para continuar la operacion.
            $height = max($height, 10 + ((count($labelLines) - 1) * 7) + 3 + 10 + ((count($valueLines) - 1) * 11) + 8);
            $cards[] = ['label_lines' => $labelLines, 'value_lines' => $valueLines];
        }

        return ['items' => $cards, 'card_width' => $cardWidth, 'gap' => $gap, 'height' => $height];
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, float>  $widths
     */
    private function encabezadoTablaPdf(array $columns, array $widths, int $margin, float $top): string
    {
        // Guarda en $content el resultado necesario para continuar la operacion.
        $content = '';
        // Guarda en $x el resultado necesario para continuar la operacion.
        $x = $margin;
        // Guarda en $height el resultado necesario para continuar la operacion.
        $height = $this->altoEncabezadoTablaPdf($columns, $widths);
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($columns as $index => $column) {
            // Guarda en $cellWidth el resultado necesario para continuar la operacion.
            $cellWidth = $widths[$index] ?? 40;
            $content .= $this->rectanguloPdf($x, $top - $height, $cellWidth, $height, [0.08, 0.33, 0.57], [0.78, 0.86, 0.94]);
            // Guarda en $lines el resultado necesario para continuar la operacion.
            $lines = $this->ajustarTexto((string) $column, max(5, (int) (($cellWidth - 8) / 4.2)));
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($lines as $lineIndex => $line) {
                $content .= $this->textoPdf($line, $x + 4, $top - 10 - ($lineIndex * 8), 6.6, [1, 1, 1], true);
            }
            $x += $cellWidth;
        }

        return $content;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, float>  $widths
     */
    private function altoEncabezadoTablaPdf(array $columns, array $widths): float
    {
        // Guarda en $lineCount el resultado necesario para continuar la operacion.
        $lineCount = 1;
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($columns as $index => $column) {
            // Guarda en $limit el resultado necesario para continuar la operacion.
            $limit = max(5, (int) ((($widths[$index] ?? 40) - 8) / 4.2));
            // Guarda en $lineCount el resultado necesario para continuar la operacion.
            $lineCount = max($lineCount, count($this->ajustarTexto((string) $column, $limit)));
        }

        return max(24, ($lineCount * 8) + 8);
    }

    /**
     * @param  array<int, array<int, string>>  $wrapped
     * @param  array<int, float>  $widths
     * @param  array<int, string>  $columns
     * @param  array<string, mixed>  $context
     */
    private function filaTablaPdf(array $wrapped, array $widths, int $margin, float $top, float $height, bool $alternate, array $columns, array $context, bool $summary = false): string
    {
        // Guarda en $content el resultado necesario para continuar la operacion.
        $content = '';
        // Guarda en $x el resultado necesario para continuar la operacion.
        $x = $margin;
        // Guarda en $y el resultado necesario para continuar la operacion.
        $y = $top - $height;
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($widths as $columnIndex => $cellWidth) {
            // Guarda en $value el resultado necesario para continuar la operacion.
            $value = $wrapped[$columnIndex][0] ?? '';
            // Guarda en $fill el resultado necesario para continuar la operacion.
            $fill = $summary ? [0.05, 0.23, 0.42] : ($alternate ? [0.94, 0.97, 0.99] : [1, 1, 1]);
            // Guarda en $text el resultado necesario para continuar la operacion.
            $text = $summary ? [1, 1, 1] : [0.13, 0.18, 0.25];
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (! $summary && mb_stripos((string) ($columns[$columnIndex] ?? ''), 'estado') !== false) {
                [$fill, $text] = match ($this->tonoEstado($value)) {
                    'positive' => [[0.89, 0.96, 0.91], [0.12, 0.39, 0.24]],
                    'warning' => [[1, 0.96, 0.82], [0.52, 0.34, 0.05]],
                    'danger' => [[0.99, 0.90, 0.90], [0.58, 0.15, 0.15]],
                    default => [$fill, $text],
                };
            }
            $content .= $this->rectanguloPdf($x, $y, $cellWidth, $height, $fill, [0.84, 0.88, 0.93]);
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($wrapped[$columnIndex] ?? [] as $lineIndex => $line) {
                $content .= $this->textoPdf($line, $x + 4, $top - 10 - ($lineIndex * 8), 6.8, $text, $summary);
            }
            $x += $cellWidth;
        }

        return $content;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function piePdf(int $width, int $height, int $margin, array $context, int $page, int $pageCount): string
    {
        // Guarda en $content el resultado necesario para continuar la operacion.
        $content = $this->rectanguloPdf(0, 0, $width, 31, [0.035, 0.15, 0.29]);
        $content .= $this->rectanguloPdf(0, 0, 6, 31, [0.06, 0.67, 0.91]);
        $content .= $this->textoPdf('TECHNOLOGY CELL — CELL SAC', $margin, 20, 7, [1, 1, 1], true);
        $content .= $this->textoPdf('Documento generado por el sistema Technology CELL.', $margin, 8, 6.2, [0.88, 0.94, 1]);
        $content .= $this->textoPdf('Emitido: '.($this->partesFecha((string) $context['generated_at'])['date']).' · Página '.$page.' de '.$pageCount, $width - $margin - 145, 12, 6.2, [1, 1, 1]);

        return $content;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @return array<int, float>
     */
    private function anchosColumnasPdf(array $columns, array $rows, float $totalWidth): array
    {
        // Agrupa en $weights los datos que se utilizaran en el siguiente paso.
        $weights = [];
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($columns as $columnIndex => $column) {
            // Guarda en $maximum el resultado necesario para continuar la operacion.
            $maximum = min(36, mb_strlen((string) $column));
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($rows as $row) {
                // Guarda en $maximum el resultado necesario para continuar la operacion.
                $maximum = max($maximum, min(36, mb_strlen((string) ($row[$columnIndex] ?? ''))));
            }
            $weights[] = max(8, $maximum);
        }
        // Guarda en $weightTotal el resultado necesario para continuar la operacion.
        $weightTotal = array_sum($weights) ?: 1;

        return array_map(static fn (int $weight): float => $totalWidth * ($weight / $weightTotal), $weights);
    }

    /**
     * @param  array<int, string>  $values
     * @param  array<int, float>  $widths
     * @return array<int, array<int, string>>
     */
    private function ajustarFila(array $values, array $widths, float $fontSize): array
    {
        // Agrupa en $wrapped los datos que se utilizaran en el siguiente paso.
        $wrapped = [];
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($widths as $index => $width) {
            // Guarda en $characters el resultado necesario para continuar la operacion.
            $characters = max(4, (int) (($width - 8) / ($fontSize * 0.53)));
            $wrapped[] = $this->ajustarTexto((string) ($values[$index] ?? ''), $characters);
        }

        return $wrapped;
    }

    /**
     * @return array<int, string>
     */
    private function ajustarTexto(string $value, int $limit): array
    {
        // Guarda en $value el resultado necesario para continuar la operacion.
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($value === '') {
            return [''];
        }
        // Agrupa en $lines los datos que se utilizaran en el siguiente paso.
        $lines = [];
        // Guarda en $line el resultado necesario para continuar la operacion.
        $line = '';
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach (preg_split('/\s+/u', $value) ?: [$value] as $word) {
            while (mb_strlen($word) > $limit) {
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($line !== '') {
                    $lines[] = $line;
                    // Guarda en $line el resultado necesario para continuar la operacion.
                    $line = '';
                }
                $lines[] = mb_substr($word, 0, $limit);
                // Guarda en $word el resultado necesario para continuar la operacion.
                $word = mb_substr($word, $limit);
            }
            $candidate = $line === '' ? $word : $line.' '.$word;
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (mb_strlen($candidate) > $limit && $line !== '') {
                $lines[] = $line;
                // Guarda en $line el resultado necesario para continuar la operacion.
                $line = $word;
            } else {
                // Guarda en $line el resultado necesario para continuar la operacion.
                $line = $candidate;
            }
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines ?: [''];
    }

    /**
     * @param  array<int, float>|null  $stroke
     */
    private function rectanguloPdf(float $x, float $y, float $width, float $height, array $fill, ?array $stroke = null): string
    {
        // Guarda en $content el resultado necesario para continuar la operacion.
        $content = sprintf("%.3F %.3F %.3F rg\n", $fill[0], $fill[1], $fill[2]);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($stroke !== null) {
            $content .= sprintf("%.3F %.3F %.3F RG\n", $stroke[0], $stroke[1], $stroke[2]);
            $content .= sprintf("%.2F %.2F %.2F %.2F re B\n", $x, $y, $width, $height);
        } else {
            $content .= sprintf("%.2F %.2F %.2F %.2F re f\n", $x, $y, $width, $height);
        }

        return $content;
    }

    /**
     * @param  array{0:float,1:float,2:float}  $color
     */
    private function textoPdf(string $value, float $x, float $y, float $size, array $color, bool $bold = false): string
    {
        // Guarda en $latin el resultado necesario para continuar la operacion.
        $latin = iconv('UTF-8', 'Windows-1252//TRANSLIT', $value);
        $latin = $latin === false ? '' : $latin;
        // Guarda en $escaped el resultado necesario para continuar la operacion.
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $latin);

        return sprintf("%.3F %.3F %.3F rg\nBT /%s %.2F Tf %.2F %.2F Td (%s) Tj ET\n", $color[0], $color[1], $color[2], $bold ? 'F2' : 'F1', $size, $x, $y, $escaped);
    }

    /**
     * Ejecuta la operación pdfSectionBar definida por la clase ExportadorTabular.
     *
     * @param  mixed  $label  Parámetro recibido por la operación.
     * @param  mixed  $x  Parámetro recibido por la operación.
     * @param  mixed  $top  Parámetro recibido por la operación.
     * @param  mixed  $width  Parámetro recibido por la operación.
     * @param  mixed  $height  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function barraSeccionPdf(string $label, float $x, float $top, float $width, float $height): string
    {
        // Guarda en $bottom el resultado necesario para continuar la operacion.
        $bottom = $top - $height;
        // Guarda en $content el resultado necesario para continuar la operacion.
        $content = $this->rectanguloPdf($x, $bottom, $width, $height, [0.04, 0.29, 0.55]);
        $content .= $this->rectanguloPdf($x, $bottom, 4, $height, [0.06, 0.67, 0.91]);
        // Guarda en $fontSize el resultado necesario para continuar la operacion.
        $fontSize = min(8.0, max(5.5, ($width - 18) / max(1, mb_strlen($label) * 0.54)));
        $content .= $this->textoPdf($label, $x + 9, $bottom + 4, $fontSize, [1, 1, 1], true);

        return $content;
    }

    /**
     * @return array{0:array{0:float,1:float,2:float},1:array{0:float,1:float,2:float},2:array{0:float,1:float,2:float}}
     */
    private function paletaIndicadorPdf(int $index): array
    {
        // Agrupa en $palette los datos que se utilizaran en el siguiente paso.
        $palette = [
            [[0.88, 0.94, 0.99], [0.73, 0.84, 0.94], [0.10, 0.30, 0.50]],
            [[0.88, 0.96, 0.91], [0.72, 0.89, 0.78], [0.12, 0.39, 0.24]],
            [[0.92, 0.90, 0.99], [0.81, 0.78, 0.94], [0.30, 0.25, 0.62]],
            [[1.00, 0.96, 0.83], [0.94, 0.86, 0.61], [0.52, 0.34, 0.05]],
        ];

        return $palette[$index % count($palette)];
    }

    /**
     * Ejecuta la operación pdfProgressBar definida por la clase ExportadorTabular.
     *
     * @param  mixed  $x  Parámetro recibido por la operación.
     * @param  mixed  $y  Parámetro recibido por la operación.
     * @param  mixed  $width  Parámetro recibido por la operación.
     * @param  mixed  $height  Parámetro recibido por la operación.
     * @param  mixed  $percentage  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function barraProgresoPdf(float $x, float $y, float $width, float $height, float $percentage): string
    {
        // Guarda en $percentage el resultado necesario para continuar la operacion.
        $percentage = max(0, min(100, $percentage));
        // Guarda en $content el resultado necesario para continuar la operacion.
        $content = $this->rectanguloPdf($x, $y, $width, $height, [0.82, 0.88, 0.94]);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($percentage > 0) {
            $content .= $this->rectanguloPdf($x, $y, $width * ($percentage / 100), $height, [0.03, 0.43, 0.81]);
        }

        return $content;
    }

    /**
     * @return array{image_id:?int,objects:array<int,string>}
     */
    private function objetosLogoPdf(int $firstId): array
    {
        // Guarda en $image el resultado necesario para continuar la operacion.
        $image = $this->decodificarPng($this->bytesLogo());
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($image === null) {
            return ['image_id' => null, 'objects' => []];
        }

        // Guarda en $imageId el resultado necesario para continuar la operacion.
        $imageId = $firstId;
        // Agrupa en $objects los datos que se utilizaran en el siguiente paso.
        $objects = [];
        // Guarda en $softMask el resultado necesario para continuar la operacion.
        $softMask = '';
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($image['alpha'] !== null) {
            // Guarda en $maskId el resultado necesario para continuar la operacion.
            $maskId = $firstId + 1;
            // Guarda en $maskBytes el resultado necesario para continuar la operacion.
            $maskBytes = gzcompress($image['alpha']);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($maskBytes === false) {
                return ['image_id' => null, 'objects' => []];
            }
            $objects[$maskId] = '<< /Type /XObject /Subtype /Image /Width '.$image['width'].' /Height '.$image['height'].' /ColorSpace /DeviceGray /BitsPerComponent 8 /Filter /FlateDecode /Length '.strlen($maskBytes).">>\nstream\n".$maskBytes."\nendstream";
            // Guarda en $softMask el resultado necesario para continuar la operacion.
            $softMask = ' /SMask '.$maskId.' 0 R';
        }
        // Guarda en $imageBytes el resultado necesario para continuar la operacion.
        $imageBytes = gzcompress($image['rgb']);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($imageBytes === false) {
            return ['image_id' => null, 'objects' => []];
        }
        $objects[$imageId] = '<< /Type /XObject /Subtype /Image /Width '.$image['width'].' /Height '.$image['height'].' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /FlateDecode'.$softMask.' /Length '.strlen($imageBytes).">>\nstream\n".$imageBytes."\nendstream";

        return ['image_id' => $imageId, 'objects' => $objects];
    }

    /**
     * @return array{width:int,height:int,rgb:string,alpha:?string}|null
     */
    private function decodificarPng(?string $png): ?array
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($png === null || ! str_starts_with($png, "\x89PNG\r\n\x1a\n")) {
            return null;
        }
        // Guarda en $offset el resultado necesario para continuar la operacion.
        $offset = 8;
        // Guarda en $compressed el resultado necesario para continuar la operacion.
        $compressed = '';
        // Guarda en $header el resultado necesario para continuar la operacion.
        $header = null;
        while ($offset + 8 <= strlen($png)) {
            // Guarda en $length el resultado necesario para continuar la operacion.
            $length = unpack('Nlength', substr($png, $offset, 4))['length'];
            // Guarda en $type el resultado necesario para continuar la operacion.
            $type = substr($png, $offset + 4, 4);
            // Guarda en $data el resultado necesario para continuar la operacion.
            $data = substr($png, $offset + 8, $length);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($type === 'IHDR') {
                // Guarda en $header el resultado necesario para continuar la operacion.
                $header = unpack('Nwidth/Nheight/Cdepth/Ccolor/Ccompression/Cfilter/Cinterlace', $data);
            } elseif ($type === 'IDAT') {
                $compressed .= $data;
            } elseif ($type === 'IEND') {
                break;
            }
            $offset += $length + 12;
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($header === null || $header['depth'] !== 8 || ! in_array($header['color'], [2, 6], true) || $header['interlace'] !== 0 || $header['width'] * $header['height'] > 4_000_000) {
            return null;
        }

        // Guarda en $raw el resultado necesario para continuar la operacion.
        $raw = zlib_decode($compressed);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($raw === false) {
            return null;
        }
        $channels = $header['color'] === 6 ? 4 : 3;
        // Guarda en $stride el resultado necesario para continuar la operacion.
        $stride = $header['width'] * $channels;
        // Guarda en $previous el resultado necesario para continuar la operacion.
        $previous = array_fill(0, $stride, 0);
        // Guarda en $rgb el resultado necesario para continuar la operacion.
        $rgb = '';
        $alpha = $channels === 4 ? '' : null;

        for ($row = 0; $row < $header['height']; $row++) {
            // Guarda en $rowOffset el resultado necesario para continuar la operacion.
            $rowOffset = $row * ($stride + 1);
            // Guarda en $filter el resultado necesario para continuar la operacion.
            $filter = ord($raw[$rowOffset]);
            // Guarda en $filtered el resultado necesario para continuar la operacion.
            $filtered = array_values(unpack('C*', substr($raw, $rowOffset + 1, $stride)));
            for ($index = 0; $index < $stride; $index++) {
                // Guarda en $left el resultado necesario para continuar la operacion.
                $left = $index >= $channels ? $filtered[$index - $channels] : 0;
                // Guarda en $up el resultado necesario para continuar la operacion.
                $up = $previous[$index];
                // Guarda en $upperLeft el resultado necesario para continuar la operacion.
                $upperLeft = $index >= $channels ? $previous[$index - $channels] : 0;
                // Guarda en $predictor el resultado necesario para continuar la operacion.
                $predictor = match ($filter) {
                    1 => $left,
                    2 => $up,
                    3 => intdiv($left + $up, 2),
                    4 => $this->predictorPaeth($left, $up, $upperLeft),
                    default => 0,
                };
                $filtered[$index] = ($filtered[$index] + $predictor) & 255;
            }
            for ($pixel = 0; $pixel < $header['width']; $pixel++) {
                // Guarda en $base el resultado necesario para continuar la operacion.
                $base = $pixel * $channels;
                $rgb .= chr($filtered[$base]).chr($filtered[$base + 1]).chr($filtered[$base + 2]);
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($alpha !== null) {
                    $alpha .= chr($filtered[$base + 3]);
                }
            }
            // Guarda en $previous el resultado necesario para continuar la operacion.
            $previous = $filtered;
        }

        return ['width' => $header['width'], 'height' => $header['height'], 'rgb' => $rgb, 'alpha' => $alpha];
    }

    /**
     * Ejecuta la operación paethPredictor definida por la clase ExportadorTabular.
     *
     * @param  mixed  $left  Parámetro recibido por la operación.
     * @param  mixed  $up  Parámetro recibido por la operación.
     * @param  mixed  $upperLeft  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    private function predictorPaeth(int $left, int $up, int $upperLeft): int
    {
        // Guarda en $estimate el resultado necesario para continuar la operacion.
        $estimate = $left + $up - $upperLeft;
        // Guarda en $leftDistance el resultado necesario para continuar la operacion.
        $leftDistance = abs($estimate - $left);
        // Guarda en $upDistance el resultado necesario para continuar la operacion.
        $upDistance = abs($estimate - $up);
        // Guarda en $upperLeftDistance el resultado necesario para continuar la operacion.
        $upperLeftDistance = abs($estimate - $upperLeft);

        return $leftDistance <= $upDistance && $leftDistance <= $upperLeftDistance ? $left : ($upDistance <= $upperLeftDistance ? $up : $upperLeft);
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>
     */
    private function contextoReporte(string $filename, array $report, int $columnCount): array
    {
        // Guarda en $readableName el resultado necesario para continuar la operacion.
        $readableName = trim(preg_replace('/[-_]+/', ' ', $filename) ?? $filename);
        // Agrupa en $defaults los datos que se utilizaran en el siguiente paso.
        $defaults = [
            'brand' => 'TECHNOLOGY CELL',
            'company' => 'CELL SAC — Technology CELL',
            'title' => 'REPORTE DE '.mb_strtoupper($readableName),
            'subtitle' => 'Resultados obtenidos por la consulta autorizada del módulo.',
            'period' => 'No especificado',
            'module' => 'Reportes',
            'report_name' => 'Reporte de '.mb_strtolower($readableName),
            'generated_by' => 'No disponible',
            'role' => 'No disponible',
            'scope' => 'Según el alcance aplicado por el módulo.',
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'filters' => [],
            'indicators' => [],
            'column_types' => [],
            'summary_row' => null,
            'observations' => [],
            'orientation' => $columnCount >= 6 ? 'landscape' : 'portrait',
        ];
        // Guarda en $context el resultado necesario para continuar la operacion.
        $context = array_merge($defaults, $report);
        $context['detail_title'] = is_string($context['detail_title'] ?? null) && trim($context['detail_title']) !== ''
            ? trim($context['detail_title'])
            : $this->tituloDetalleDinamico($context);
        $context['indicators'] = is_array($context['indicators']) ? array_values($context['indicators']) : [];
        $context['filters'] = is_array($context['filters']) ? $context['filters'] : [];
        $context['column_types'] = is_array($context['column_types']) ? $context['column_types'] : [];
        $context['observations'] = is_array($context['observations']) ? array_values($context['observations']) : [];
        $context['primary_indicator'] = is_array($context['primary_indicator'] ?? null) ? $context['primary_indicator'] : null;
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! in_array($context['orientation'], ['portrait', 'landscape'], true)) {
            $context['orientation'] = 'portrait';
        }

        return $context;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function tituloDetalleDinamico(array $context): string
    {
        // Guarda en $descriptor el resultado necesario para continuar la operacion.
        $descriptor = mb_strtolower(implode(' ', array_filter([
            (string) ($context['title'] ?? ''),
            (string) ($context['report_name'] ?? ''),
            (string) ($context['module'] ?? ''),
        ])));
        // Agrupa en $topics los datos que se utilizaran en el siguiente paso.
        $topics = [
            'compr' => 'compras',
            'venta' => 'ventas',
            'pedido' => 'pedidos',
            'producto' => 'productos',
            'inventario' => 'inventario',
            'cliente' => 'clientes',
            'proveedor' => 'proveedores',
            'cotiz' => 'cotizaciones',
            'devol' => 'devoluciones',
            'financ' => 'finanzas',
            'factur' => 'facturación',
            'auditor' => 'auditoría',
            'sede' => 'sedes',
            'usuario' => 'usuarios',
            'campañ' => 'campañas',
        ];

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($topics as $needle => $label) {
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (preg_match('/(?:^|[^\p{L}])'.preg_quote($needle, '/').'\p{L}*/u', $descriptor) === 1) {
                return 'Detalle de '.$label;
            }
        }

        return 'Detalle de resultados';
    }

    /**
     * @param  array<string, mixed>|null  $indicator
     * @return array<int, string>
     */
    private function lineasIndicadorPrincipal(?array $indicator): array
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($indicator === null) {
            return ['No se proporcionó un indicador principal.'];
        }

        // Guarda en $label el resultado necesario para continuar la operacion.
        $label = trim((string) ($indicator['label'] ?? ''));
        // Guarda en $value el resultado necesario para continuar la operacion.
        $value = $indicator['value'] ?? null;
        // Guarda en $valueText el resultado necesario para continuar la operacion.
        $valueText = is_scalar($value) ? trim((string) $value) : '';
        // Guarda en $progress el resultado necesario para continuar la operacion.
        $progress = $this->progresoPrincipal($indicator);

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($label === '' && $valueText === '' && $progress === null) {
            return ['No se proporcionó un indicador principal.'];
        }

        $lines = [$label !== '' ? $label : 'Indicador'];
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($progress !== null) {
            // Guarda en $filledBlocks el resultado necesario para continuar la operacion.
            $filledBlocks = (int) round($progress / 10);
            // Guarda en $bar el resultado necesario para continuar la operacion.
            $bar = str_repeat('█', $filledBlocks).str_repeat('░', 10 - $filledBlocks);
            $displayValue = $valueText !== '' ? $valueText : number_format($progress, 1, '.', ',').'%';
            $lines[] = $bar.' '.$displayValue;
        } elseif ($valueText !== '') {
            $lines[] = $valueText;
        }

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $indicator
     */
    private function progresoPrincipal(array $indicator): ?float
    {
        // Guarda en $progress el resultado necesario para continuar la operacion.
        $progress = $indicator['progress'] ?? null;
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! is_numeric($progress)) {
            return null;
        }

        // Guarda en $percentage el resultado necesario para continuar la operacion.
        $percentage = (float) $progress;

        return is_finite($percentage) && $percentage >= 0 && $percentage <= 100 ? $percentage : null;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function tipoColumna(array $context, int $index): string
    {
        // Guarda en $type el resultado necesario para continuar la operacion.
        $type = $context['column_types'][$index] ?? 'text';

        return in_array($type, ['text', 'integer', 'number', 'currency', 'percent', 'date', 'datetime'], true) ? $type : 'text';
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $type  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function esTipoNumerico(string $type): bool
    {
        return in_array($type, ['integer', 'number', 'currency', 'percent'], true);
    }

    /**
     * Convierte una fecha al número de serie utilizado por Excel.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @param  mixed  $dateOnly  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function fechaSerialExcel(mixed $value, bool $dateOnly): ?float
    {
        // Guarda en $date el resultado necesario para continuar la operacion.
        $date = $this->convertirFechaHora($value);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($date === null) {
            return null;
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($dateOnly) {
            // Guarda en $date el resultado necesario para continuar la operacion.
            $date = $date->setTime(0, 0);
        }

        return ($date->getTimestamp() / 86400) + 25569;
    }

    /**
     * @return array{date:string,time:string}
     */
    private function partesFecha(string $value): array
    {
        // Guarda en $date el resultado necesario para continuar la operacion.
        $date = $this->convertirFechaHora($value);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($date === null) {
            return ['date' => $value, 'time' => ''];
        }

        return ['date' => $date->format('d/m/Y'), 'time' => $date->format('H:i')];
    }

    /**
     * Convierte un valor compatible en una fecha y hora inmutable.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function convertirFechaHora(mixed $value): ?DateTimeImmutable
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($value instanceof DateTimeInterface) {
            // Devuelve el resultado obtenido mediante el modelo correspondiente.
            return DateTimeImmutable::createFromInterface($value);
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Convierte un valor al formato de presentación del reporte.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @param  mixed  $type  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function valorPresentacion(mixed $value, string $type): string
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($value === null) {
            return '';
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (in_array($type, ['date', 'datetime'], true)) {
            // Guarda en $date el resultado necesario para continuar la operacion.
            $date = $this->convertirFechaHora($value);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($date !== null) {
                return $date->format($type === 'date' ? 'd/m/Y' : 'd/m/Y H:i');
            }
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($type === 'currency' && is_numeric($value)) {
            return 'PEN '.number_format((float) $value, 2, '.', ',');
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($type === 'percent' && is_numeric($value)) {
            return number_format((float) $value, 1, '.', ',').'%';
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($type === 'integer' && is_numeric($value)) {
            return number_format((float) $value, 0, '.', ',');
        }

        return (string) $value;
    }

    /**
     * @param  array<int, array{label?:string,value?:string|int|float|null}>  $filters
     */
    private function resumenFiltros(array $filters): string
    {
        // Agrupa en $items los datos que se utilizaran en el siguiente paso.
        $items = [];
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($filters as $label => $value) {
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (is_array($value)) {
                // Guarda en $name el resultado necesario para continuar la operacion.
                $name = (string) ($value['label'] ?? $label);
                // Guarda en $content el resultado necesario para continuar la operacion.
                $content = $value['value'] ?? null;
            } else {
                // Guarda en $name el resultado necesario para continuar la operacion.
                $name = (string) $label;
                // Guarda en $content el resultado necesario para continuar la operacion.
                $content = $value;
            }
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($content === null || $content === '') {
                continue;
            }
            $items[] = $name.': '.(is_scalar($content) ? (string) $content : '');
        }

        return $items === [] ? 'Sin filtros adicionales' : implode(' · ', $items);
    }

    /**
     * Ejecuta la operación tonoEstado definida por la clase ExportadorTabular.
     *
     * @param  mixed  $status  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function tonoEstado(string $status): string
    {
        // Guarda en $normalized el resultado necesario para continuar la operacion.
        $normalized = mb_strtolower(trim($status));
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (preg_match('/cancel|anulad|rechaz|fallid|inactiv|vencid|bloquead/u', $normalized) === 1) {
            return 'danger';
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (preg_match('/pendient|proceso|revisi|reserv|programad|borrador/u', $normalized) === 1) {
            return 'warning';
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (preg_match('/activo|confirmad|entregad|aprobado|completad|recibid|pagad/u', $normalized) === 1) {
            return 'positive';
        }

        return 'neutral';
    }

    /**
     * Ejecuta la operación columnLetter definida por la clase ExportadorTabular.
     *
     * @param  mixed  $index  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function letraColumna(int $index): string
    {
        // Guarda en $letter el resultado necesario para continuar la operacion.
        $letter = '';
        while ($index >= 0) {
            // Guarda en $letter el resultado necesario para continuar la operacion.
            $letter = chr(($index % 26) + 65).$letter;
            // Guarda en $index el resultado necesario para continuar la operacion.
            $index = intdiv($index, 26) - 1;
        }

        return $letter;
    }

    /**
     * Ejecuta la operación logoBytes definida por la clase ExportadorTabular.
     *
     * @return mixed Resultado de la operación.
     */
    private function bytesLogo(): ?string
    {
        // Guarda en $path el resultado necesario para continuar la operacion.
        $path = base_path(self::LOGO_PATH);

        return is_file($path) ? file_get_contents($path) ?: null : null;
    }

    /**
     * Ejecuta la operación nombreArchivoSeguro definida por la clase ExportadorTabular.
     *
     * @param  mixed  $filename  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function nombreArchivoSeguro(string $filename): string
    {
        return trim(preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?? 'reporte', '-.');
    }

    /**
     * Ejecuta la operación safeSpreadsheetText definida por la clase ExportadorTabular.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private static function textoHojaCalculoSeguro(string $value): string
    {
        $isValidUtf8 = preg_match('//u', $value) === 1;
        $hasFormulaPrefix = ! $isValidUtf8 || preg_match('/^[\p{C}\p{Z}\s]*[=+\-@]/u', $value) === 1;

        return $hasFormulaPrefix && ! is_numeric($value) ? "'".$value : $value;
    }
}
