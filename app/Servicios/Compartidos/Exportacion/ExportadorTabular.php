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
            'excel' => $this->excel($filename, $columns, $rows, $report),
            'pdf' => $this->pdf($filename, $columns, $rows, $report),
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
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, array_map(self::safeSpreadsheetText(...), $columns));
            foreach ($rows as $row) {
                fputcsv($output, array_map(static fn (string|int|float|null $value): string => self::safeSpreadsheetText((string) $value), $row));
            }
            fclose($output);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  array<string, mixed>  $report
     */
    private function excel(string $filename, array $columns, array $rows, array $report): Response
    {
        if (! class_exists(ZipArchive::class)) {
            return $this->excelWithoutZipArchive($filename, $columns, $rows, $report);
        }
        $temporaryFile = tempnam(sys_get_temp_dir(), 'tc-xlsx-');
        abort_if($temporaryFile === false, 500, 'No se pudo preparar el archivo Excel.');

        $zip = new ZipArchive;
        try {
            abort_unless($zip->open($temporaryFile, ZipArchive::OVERWRITE) === true, 500, 'No se pudo crear el archivo Excel.');

            $context = $this->reportContext($filename, $report, count($columns));
            $logo = $this->logoBytes();
            $worksheet = $this->buildWorksheet($columns, $rows, $context, $logo !== null);
            $logoContentTypes = $logo === null ? '' : '<Default Extension="png" ContentType="image/png"/><Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';

            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'.$logoContentTypes.'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', $this->workbookXml($worksheet['last_layout_column'], $worksheet['last_row'], $worksheet['header_row']));
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
            $zip->addFromString('xl/styles.xml', $this->stylesXml());
            $zip->addFromString('xl/worksheets/sheet1.xml', $worksheet['xml']);

            if ($logo !== null) {
                $zip->addFromString('xl/media/technology-cell-logo.png', $logo);
                $zip->addFromString('xl/worksheets/_rels/sheet1.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/></Relationships>');
                $zip->addFromString('xl/drawings/drawing1.xml', $this->drawingXml());
                $zip->addFromString('xl/drawings/_rels/drawing1.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/technology-cell-logo.png"/></Relationships>');
            }

            $zip->close();
            $binary = file_get_contents($temporaryFile);
            abort_if($binary === false, 500, 'No se pudo leer el archivo Excel.');

            return response($binary, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$this->safeFilename($filename).'.xlsx"',
            ]);
        } finally {
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
    private function excelWithoutZipArchive(string $filename, array $columns, array $rows, array $report): Response
    {
        $context = $this->reportContext($filename, $report, count($columns));
        $logo = $this->logoBytes();
        $worksheet = $this->buildWorksheet($columns, $rows, $context, $logo !== null);
        $logoContentTypes = $logo === null
            ? ''
            : '<Default Extension="png" ContentType="image/png"/><Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';

        $entries = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'.$logoContentTypes.'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => $this->workbookXml($worksheet['last_layout_column'], $worksheet['last_row'], $worksheet['header_row']),
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => $this->stylesXml(),
            'xl/worksheets/sheet1.xml' => $worksheet['xml'],
        ];

        if ($logo !== null) {
            $entries['xl/media/technology-cell-logo.png'] = $logo;
            $entries['xl/worksheets/_rels/sheet1.xml.rels'] = '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/></Relationships>';
            $entries['xl/drawings/drawing1.xml'] = $this->drawingXml();
            $entries['xl/drawings/_rels/drawing1.xml.rels'] = '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/technology-cell-logo.png"/></Relationships>';
        }

        return response($this->buildZipPackage($entries), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$this->safeFilename($filename).'.xlsx"',
        ]);
    }

    /**
     * @param  array<string, string>  $entries
     */
    private function buildZipPackage(array $entries): string
    {
        $body = '';
        $centralDirectory = '';
        $offset = 0;
        $entryCount = 0;

        foreach ($entries as $name => $content) {
            $compressed = gzdeflate($content, 6);
            abort_if($compressed === false, 500, 'No se pudo comprimir el archivo Excel.');
            $nameLength = strlen($name);
            $contentLength = strlen($content);
            $compressedLength = strlen($compressed);
            $crc = crc32($content);

            $localHeader = pack('VvvvvvVVVvv', 0x04034B50, 20, 0, 8, 0, 0, $crc, $compressedLength, $contentLength, $nameLength, 0).$name.$compressed;
            $body .= $localHeader;
            $centralDirectory .= pack('VvvvvvvVVVvvvvvVV', 0x02014B50, 20, 20, 0, 8, 0, 0, $crc, $compressedLength, $contentLength, $nameLength, 0, 0, 0, 0, 0, $offset).$name;
            $offset += strlen($localHeader);
            $entryCount++;
        }

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
    private function buildWorksheet(array $columns, array $rows, array $context, bool $hasLogo): array
    {
        $columnCount = max(1, count($columns));
        $layoutColumnCount = max(4, $columnCount);
        $lastLayoutColumn = $this->columnLetter($layoutColumnCount - 1);
        $lastColumn = $this->columnLetter($columnCount - 1);
        $indicators = $context['indicators'];
        $indicatorGroups = array_chunk($indicators, 4);
        $detailSectionRow = $indicators === [] ? 15 : 12 + (count($indicatorGroups) * 3) + 1;
        $headerRow = $detailSectionRow + 1;
        $cells = [];
        $merges = [];
        $rowHeights = [];
        $columnWidths = $this->excelColumnWidths($columns, $rows, $layoutColumnCount);

        foreach ([1 => (string) $context['brand'], 2 => (string) $context['title'], 3 => (string) $context['subtitle'], 4 => 'Período consultado: '.(string) $context['period']] as $rowNumber => $text) {
            for ($column = 0; $column < $layoutColumnCount; $column++) {
                $this->addWorksheetCell($cells, $rowNumber, $column, '', $rowNumber === 2 ? 2 : ($rowNumber === 3 || $rowNumber === 4 ? 3 : 1));
            }
            $textColumn = $hasLogo ? 2 : 0;
            $this->addWorksheetCell($cells, $rowNumber, $textColumn, $text, $rowNumber === 2 ? 2 : ($rowNumber === 3 || $rowNumber === 4 ? 3 : 1));
            if ($textColumn < $layoutColumnCount - 1) {
                $merges[] = $this->columnLetter($textColumn).$rowNumber.':'.$lastLayoutColumn.$rowNumber;
            }
        }

        $titleStartColumn = $hasLogo ? 2 : 0;
        $titleCapacity = max(20, (int) array_sum(array_slice($columnWidths, $titleStartColumn)));
        $rowHeights[1] = 28;
        $rowHeights[2] = max(36, min(72, (int) ceil(mb_strlen((string) $context['title']) / $titleCapacity) * 22));
        $rowHeights[3] = max(27, min(54, (int) ceil(mb_strlen((string) $context['subtitle']) / $titleCapacity) * 18));
        $rowHeights[4] = 25;
        for ($column = 0; $column < $layoutColumnCount; $column++) {
            $this->addWorksheetCell($cells, 5, $column, '', self::XLSX_STYLE_ACCENT);
        }
        $rowHeights[5] = 7;

        $emittedAt = $this->dateParts((string) $context['generated_at']);
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

        foreach ($metadataRows as $rowNumber => $pairs) {
            $this->addWorksheetCell($cells, $rowNumber, 0, $pairs[0][0], 4);
            $this->addWorksheetCell($cells, $rowNumber, 1, $pairs[0][1], 5);
            $this->addWorksheetCell($cells, $rowNumber, 2, $pairs[1][0], 4);
            $this->addWorksheetCell($cells, $rowNumber, 3, $pairs[1][1], 5);
        }

        $this->addWorksheetCell($cells, 9, 0, 'Alcance por sede', 4);
        $this->addMergedWorksheetCell($cells, $merges, 9, 1, $layoutColumnCount - 1, (string) $context['scope'], 5);
        $this->addWorksheetCell($cells, 10, 0, 'Filtros aplicados', 4);
        $this->addMergedWorksheetCell($cells, $merges, 10, 1, $layoutColumnCount - 1, $this->filterSummary($context['filters']), 5);
        $rowHeights[6] = 30;
        $rowHeights[7] = 30;
        $rowHeights[8] = 30;
        $rowHeights[9] = 25;
        $rowHeights[10] = 36;

        $this->addMergedWorksheetCell($cells, $merges, 12, 0, $layoutColumnCount - 1, 'Resumen del período', self::XLSX_STYLE_SECTION);
        $rowHeights[12] = 24;

        if ($indicatorGroups !== []) {
            foreach ($indicatorGroups as $groupIndex => $indicatorGroup) {
                $indicatorCount = count($indicatorGroup);
                $baseColumnCount = intdiv($layoutColumnCount, $indicatorCount);
                $extraColumnCount = $layoutColumnCount % $indicatorCount;
                $labelRow = 13 + ($groupIndex * 3);
                $start = 0;

                foreach ($indicatorGroup as $index => $indicator) {
                    $span = $baseColumnCount + ($index < $extraColumnCount ? 1 : 0);
                    $end = $start + $span - 1;
                    $labelStyle = self::XLSX_STYLE_CARD_LABEL_BASE + $index;
                    $valueStyle = self::XLSX_STYLE_CARD_VALUE_BASE + $index;
                    $this->addMergedWorksheetCell($cells, $merges, $labelRow, $start, $end, (string) ($indicator['label'] ?? ''), $labelStyle);
                    $this->addMergedWorksheetCell($cells, $merges, $labelRow + 1, $start, $end, $indicator['value'] ?? '', $valueStyle);
                    $start = $end + 1;
                }

                $rowHeights[$labelRow] = 25;
                $rowHeights[$labelRow + 1] = 36;
                $rowHeights[$labelRow + 2] = 10;
            }
        } else {
            $this->addMergedWorksheetCell($cells, $merges, 13, 0, $layoutColumnCount - 1, 'No se proporcionaron indicadores para este reporte.', self::XLSX_STYLE_PANEL_CONTENT);
            $rowHeights[13] = 30;
        }

        $this->addMergedWorksheetCell($cells, $merges, $detailSectionRow, 0, $layoutColumnCount - 1, (string) $context['detail_title'], self::XLSX_STYLE_SECTION);
        $rowHeights[$detailSectionRow] = 24;
        $rowHeights[$headerRow] = 32;

        foreach ($columns as $index => $column) {
            $this->addWorksheetCell($cells, $headerRow, $index, $column, 8);
        }

        $dataStartRow = $headerRow + 1;
        foreach ($rows as $rowIndex => $row) {
            foreach (array_values($row) as $columnIndex => $value) {
                $type = $this->columnType($context, $columnIndex);
                $alternate = $rowIndex % 2 === 1;
                $style = $this->worksheetBodyStyle($type, $value, $alternate, (string) ($columns[$columnIndex] ?? ''));
                $this->addWorksheetCell($cells, $dataStartRow + $rowIndex, $columnIndex, $value, $style, $type);
            }
        }

        $lastDataRow = max($headerRow, $dataStartRow + count($rows) - 1);
        $lastRow = $lastDataRow;
        if (is_array($context['summary_row'])) {
            $lastRow++;
            $summary = $context['summary_row'];
            $summaryValues = array_values((array) ($summary['values'] ?? []));
            $summaryLabel = (string) ($summary['label'] ?? 'Total');
            $summaryValues[0] = $summaryLabel;
            foreach (range(0, $columnCount - 1) as $columnIndex) {
                $type = $this->columnType($context, $columnIndex);
                $summaryStyle = match ($type) {
                    'currency' => 22,
                    'integer' => 23,
                    default => 11,
                };
                $this->addWorksheetCell($cells, $lastRow, $columnIndex, $summaryValues[$columnIndex] ?? '', $summaryStyle, $type);
            }
        }

        $observations = array_values(array_filter(array_map('strval', $context['observations']), static fn (string $value): bool => trim($value) !== ''));
        if ($observations === []) {
            $observations = ['Sin observaciones adicionales.'];
        }

        $panelHeadingRow = $lastRow + 2;
        $panelBodyStartRow = $panelHeadingRow + 1;
        $leftColumnEnd = max(0, intdiv($layoutColumnCount, 2) - 1);
        $rightColumnStart = $leftColumnEnd + 1;
        $this->addMergedWorksheetCell($cells, $merges, $panelHeadingRow, 0, $leftColumnEnd, 'Observaciones', self::XLSX_STYLE_SECTION);
        $this->addMergedWorksheetCell($cells, $merges, $panelHeadingRow, $rightColumnStart, $layoutColumnCount - 1, 'Indicador principal', self::XLSX_STYLE_SECTION);
        $rowHeights[$panelHeadingRow] = 24;

        $primaryIndicator = $context['primary_indicator'];
        $leftWidth = array_sum(array_slice($columnWidths, 0, $leftColumnEnd + 1));
        $rightWidth = array_sum(array_slice($columnWidths, $rightColumnStart));
        $observationCharacterLimit = max(18, (int) $leftWidth - 4);
        $rightCharacterLimit = max(18, (int) $rightWidth - 4);
        $primaryLines = $this->primaryIndicatorLines($primaryIndicator);
        $panelRowCount = max(count($observations), count($primaryLines));

        for ($panelRowIndex = 0; $panelRowIndex < $panelRowCount; $panelRowIndex++) {
            $rowNumber = $panelBodyStartRow + $panelRowIndex;
            $observation = $observations[$panelRowIndex] ?? '';
            $primaryLine = $primaryLines[$panelRowIndex] ?? '';
            $this->addMergedWorksheetCell($cells, $merges, $rowNumber, 0, $leftColumnEnd, $observation, self::XLSX_STYLE_PANEL_CONTENT);
            $this->addMergedWorksheetCell($cells, $merges, $rowNumber, $rightColumnStart, $layoutColumnCount - 1, $primaryLine, self::XLSX_STYLE_PANEL_CONTENT);

            $leftLines = count($this->wrapText($observation, $observationCharacterLimit));
            $rightLines = count($this->wrapText($primaryLine, $rightCharacterLimit));
            $rowHeights[$rowNumber] = max(28, max($leftLines, $rightLines) * 14 + 10);
        }

        $footerTitleRow = $panelBodyStartRow + $panelRowCount + 1;
        $this->addMergedWorksheetCell($cells, $merges, $footerTitleRow, 0, $layoutColumnCount - 1, 'TECHNOLOGY CELL — CELL SAC', self::XLSX_STYLE_FOOTER_TITLE);
        $this->addMergedWorksheetCell($cells, $merges, $footerTitleRow + 1, 0, $layoutColumnCount - 1, 'Documento generado por el sistema Technology CELL.', self::XLSX_STYLE_FOOTER_TEXT);
        $rowHeights[$footerTitleRow] = 28;
        $rowHeights[$footerTitleRow + 1] = 24;
        $lastRow = $footerTitleRow + 1;

        ksort($cells);
        $sheetRows = '';
        foreach ($cells as $rowNumber => $rowCells) {
            ksort($rowCells);
            $height = $rowHeights[$rowNumber] ?? null;
            $heightAttributes = $height === null ? '' : ' ht="'.$height.'" customHeight="1"';
            $sheetRows .= '<row r="'.$rowNumber.'"'.$heightAttributes.'>'.implode('', $rowCells).'</row>';
        }

        $columnsXml = '<cols>';
        foreach ($columnWidths as $index => $width) {
            $columnsXml .= '<col min="'.($index + 1).'" max="'.($index + 1).'" width="'.number_format($width, 1, '.', '').'" customWidth="1"/>';
        }
        $columnsXml .= '</cols>';

        $mergeXml = $merges === [] ? '' : '<mergeCells count="'.count($merges).'">'.implode('', array_map(static fn (string $merge): string => '<mergeCell ref="'.$merge.'"/>', $merges)).'</mergeCells>';
        $drawingXml = $hasLogo ? '<drawing r:id="rId1"/>' : '';
        $autoFilter = $lastDataRow >= $headerRow ? '<autoFilter ref="A'.$headerRow.':'.$lastColumn.$lastDataRow.'"/>' : '';
        $orientation = $context['orientation'];
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheetPr><pageSetUpPr fitToPage="1"/></sheetPr><dimension ref="A1:'.$lastLayoutColumn.$lastRow.'"/><sheetViews><sheetView showGridLines="0" workbookViewId="0"><pane ySplit="'.$headerRow.'" topLeftCell="A'.($headerRow + 1).'" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A'.($headerRow + 1).'" sqref="A'.($headerRow + 1).'"/></sheetView></sheetViews>'.$columnsXml.'<sheetData>'.$sheetRows.'</sheetData>'.$autoFilter.$mergeXml.'<printOptions horizontalCentered="0"/><pageMargins left="0.3" right="0.3" top="0.55" bottom="0.55" header="0.2" footer="0.25"/><pageSetup paperSize="9" orientation="'.$orientation.'" fitToWidth="1" fitToHeight="0"/><headerFooter><oddFooter>&amp;C TECHNOLOGY CELL — CELL SAC &amp;LDocumento generado por el sistema Technology CELL&amp;R&amp;P / &amp;N</oddFooter></headerFooter>'.$drawingXml.'</worksheet>';

        return ['xml' => $xml, 'last_column' => $lastColumn, 'last_layout_column' => $lastLayoutColumn, 'last_row' => $lastRow, 'header_row' => $headerRow];
    }

    /**
     * @param  array<int, array<int, string>>  $cells
     */
    private function addWorksheetCell(array &$cells, int $row, int $column, mixed $value, int $style, string $type = 'text'): void
    {
        $reference = $this->columnLetter($column).$row;
        $attributes = ' r="'.$reference.'" s="'.$style.'"';
        $value = $value ?? '';

        if ($this->isNumericType($type) && is_numeric($value)) {
            $numericValue = (float) $value;
            if ($type === 'integer') {
                $numericValue = (int) $value;
            }
            $cells[$row][$column] = '<c'.$attributes.' t="n"><v>'.rtrim(rtrim(number_format($numericValue, 10, '.', ''), '0'), '.').'</v></c>';

            return;
        }

        if (in_array($type, ['date', 'datetime'], true)) {
            $serial = $this->excelDateSerial($value, $type === 'date');
            if ($serial !== null) {
                $cells[$row][$column] = '<c'.$attributes.' t="n"><v>'.number_format($serial, 10, '.', '').'</v></c>';

                return;
            }
        }

        $safe = htmlspecialchars(self::safeSpreadsheetText((string) $value), ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $cells[$row][$column] = '<c'.$attributes.' t="inlineStr"><is><t xml:space="preserve">'.$safe.'</t></is></c>';
    }

    /**
     * @param  array<int, array<int, string>>  $cells
     * @param  array<int, string>  $merges
     */
    private function addMergedWorksheetCell(array &$cells, array &$merges, int $row, int $start, int $end, mixed $value, int $style): void
    {
        $this->addWorksheetCell($cells, $row, $start, $value, $style);
        for ($column = $start + 1; $column <= $end; $column++) {
            $this->addWorksheetCell($cells, $row, $column, '', $style);
        }

        if ($start < $end) {
            $merges[] = $this->columnLetter($start).$row.':'.$this->columnLetter($end).$row;
        }
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @return array<int, float>
     */
    private function excelColumnWidths(array $columns, array $rows, int $layoutColumnCount): array
    {
        $widths = [];
        for ($column = 0; $column < $layoutColumnCount; $column++) {
            $maximum = mb_strlen((string) ($columns[$column] ?? ''));
            foreach ($rows as $row) {
                $maximum = max($maximum, mb_strlen((string) ($row[$column] ?? '')));
            }
            $widths[] = min(42, max(12, $maximum + 2));
        }

        return $widths;
    }

    /**
     * Ejecuta la operación worksheetBodyStyle definida por la clase ExportadorTabular.
     *
     * @param  mixed  $type  Parámetro recibido por la operación.
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @param  mixed  $alternate  Parámetro recibido por la operación.
     * @param  mixed  $heading  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    private function worksheetBodyStyle(string $type, mixed $value, bool $alternate, string $heading): int
    {
        if (mb_stripos($heading, 'estado') !== false) {
            return match ($this->statusTone((string) $value)) {
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
     * Ejecuta la operación stylesXml definida por la clase ExportadorTabular.
     *
     * @return string Resultado de la operación.
     */
    private function stylesXml(): string
    {
        $formats = '<numFmts count="6"><numFmt numFmtId="'.self::XLSX_NUMFMT_CURRENCY.'" formatCode="#,##0.00 &quot;PEN&quot;"/><numFmt numFmtId="'.self::XLSX_NUMFMT_DATE.'" formatCode="dd/mm/yyyy"/><numFmt numFmtId="'.self::XLSX_NUMFMT_DATETIME.'" formatCode="dd/mm/yyyy hh:mm"/><numFmt numFmtId="'.self::XLSX_NUMFMT_NUMBER.'" formatCode="#,##0.00"/><numFmt numFmtId="'.self::XLSX_NUMFMT_INTEGER.'" formatCode="#,##0"/><numFmt numFmtId="'.self::XLSX_NUMFMT_PERCENT.'" formatCode="0.0%"/></numFmts>';
        $fonts = '<fonts count="9"><font><sz val="10"/><name val="Calibri"/><color rgb="FF233047"/></font><font><b/><sz val="10"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font><font><b/><sz val="20"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font><font><sz val="9"/><name val="Calibri"/><color rgb="FFE8F2FF"/></font><font><b/><sz val="9"/><name val="Calibri"/><color rgb="FF17406D"/></font><font><sz val="9"/><name val="Calibri"/><color rgb="FF233047"/></font><font><b/><sz val="9"/><name val="Calibri"/><color rgb="FF125A9C"/></font><font><b/><sz val="13"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font><font><b/><sz val="13"/><name val="Calibri"/><color rgb="FF09264A"/></font></fonts>';
        $fills = '<fills count="14"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF09264A"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF145DA0"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDCEEFF"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF2F7FC"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE5F5EB"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFF1CC"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFCE4E4"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0D3A68"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFEAF4FD"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFECE8FF"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF26B8E8"/><bgColor indexed="64"/></patternFill></fill></fills>';
        $borders = '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFD8E2EF"/></left><right style="thin"><color rgb="FFD8E2EF"/></right><top style="thin"><color rgb="FFD8E2EF"/></top><bottom style="thin"><color rgb="FFD8E2EF"/></bottom><diagonal/></border></borders>';
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
        $xfs = '';
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
    private function workbookXml(string $lastColumn, int $lastRow, int $headerRow): string
    {
        $printArea = '$A$1:$'.$lastColumn.'$'.$lastRow;

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><bookViews><workbookView/></bookViews><sheets><sheet name="Reporte" sheetId="1" r:id="rId1"/></sheets><definedNames><definedName name="_xlnm.Print_Titles" localSheetId="0">\'Reporte\'!$'.$headerRow.':$'.$headerRow.'</definedName><definedName name="_xlnm.Print_Area" localSheetId="0">\'Reporte\'!'.$printArea.'</definedName></definedNames><calcPr calcId="191029"/></workbook>';
    }

    /**
     * Ejecuta la operación drawingXml definida por la clase ExportadorTabular.
     *
     * @return string Resultado de la operación.
     */
    private function drawingXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><xdr:twoCellAnchor editAs="oneCell"><xdr:from><xdr:col>0</xdr:col><xdr:colOff>50000</xdr:colOff><xdr:row>0</xdr:row><xdr:rowOff>30000</xdr:rowOff></xdr:from><xdr:to><xdr:col>2</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>4</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:to><xdr:pic><xdr:nvPicPr><xdr:cNvPr id="2" name="Technology CELL"/><xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr></xdr:nvPicPr><xdr:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill><xdr:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr></xdr:pic><xdr:clientData/></xdr:twoCellAnchor></xdr:wsDr>';
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  array<string, mixed>  $report
     */
    private function pdf(string $filename, array $columns, array $rows, array $report): Response
    {
        $context = $this->reportContext($filename, $report, count($columns));
        $orientation = $context['orientation'];
        $pageWidth = $orientation === 'landscape' ? 842 : 595;
        $pageHeight = $orientation === 'landscape' ? 595 : 842;
        $margin = 30;
        $tableWidth = $pageWidth - (2 * $margin);
        $columnWidths = $this->pdfColumnWidths($columns, $rows, $tableWidth);
        $logo = $this->pdfLogoObjects(5);
        $logoObjectId = $logo['image_id'];
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ] + $logo['objects'];
        $pages = [];
        $pageContent = '';
        $pageNumber = 0;
        $startPage = function (bool $first) use (&$pageContent, &$pageNumber, &$pages, $pageWidth, $pageHeight, $margin, $tableWidth, $columns, $columnWidths, $context, $logoObjectId): float {
            if ($pageNumber > 0) {
                $pages[] = $pageContent;
            }
            $pageNumber++;
            $pageContent = $this->pdfPageHeader($pageWidth, $pageHeight, $margin, $context, $first, $logoObjectId !== null);
            $top = $first
                ? $this->pdfFirstPageTableTop($pageWidth, $pageHeight, $margin, $context)
                : $pageHeight - 91;
            $detailTitle = $first ? (string) $context['detail_title'] : (string) $context['detail_title'].' · continuación';
            $pageContent .= $this->pdfSectionBar($detailTitle, $margin, $top + 18, $tableWidth, 14);
            $pageContent .= $this->pdfTableHeader($columns, $columnWidths, $margin, $top);

            return $top - $this->pdfTableHeaderHeight($columns, $columnWidths);
        };

        $currentY = $startPage(true);
        $appendTableRow = function (array $wrapped, bool $alternate, bool $summary = false) use (&$currentY, &$pageContent, $startPage, $columnWidths, $margin, $columns, $context): void {
            $lineCount = max(array_map('count', $wrapped));
            $lineOffset = 0;

            while ($lineOffset < $lineCount) {
                if ($currentY - 20 < 38) {
                    $currentY = $startPage(false);
                }

                $availableLines = max(1, (int) floor(($currentY - 46) / 8));
                $fragmentLineCount = min($lineCount - $lineOffset, $availableLines);
                $fragment = array_map(
                    static fn (array $lines): array => array_slice($lines, $lineOffset, $fragmentLineCount),
                    $wrapped,
                );
                $rowHeight = max(20, ($fragmentLineCount * 8) + 8);
                $pageContent .= $this->pdfTableRow($fragment, $columnWidths, $margin, $currentY, $rowHeight, $alternate, $columns, $context, $summary);
                $currentY -= $rowHeight;
                $lineOffset += $fragmentLineCount;

                if ($lineOffset < $lineCount) {
                    $currentY = $startPage(false);
                }
            }
        };

        foreach ($rows as $rowIndex => $row) {
            $formatted = [];
            foreach (array_values($row) as $columnIndex => $value) {
                $formatted[] = $this->displayValue($value, $this->columnType($context, $columnIndex));
            }
            $wrapped = $this->wrapRow($formatted, $columnWidths, 6.8);
            $appendTableRow($wrapped, $rowIndex % 2 === 1);
        }

        if (is_array($context['summary_row'])) {
            $summary = $context['summary_row'];
            $values = array_values((array) ($summary['values'] ?? []));
            $values[0] = (string) ($summary['label'] ?? 'Total');
            $formatted = [];
            foreach ($values as $columnIndex => $value) {
                $formatted[] = $this->displayValue($value, $this->columnType($context, $columnIndex));
            }
            $wrapped = $this->wrapRow($formatted, $columnWidths, 6.8);
            $appendTableRow($wrapped, false, true);
        }

        $observationLines = [];
        $leftPanelWidth = ($tableWidth - 12) / 2;
        $rightPanelWidth = $leftPanelWidth;
        $observationCharacterLimit = max(24, (int) (($leftPanelWidth - 16) / 4.0));
        foreach ($context['observations'] as $observation) {
            array_push($observationLines, ...$this->wrapText((string) $observation, $observationCharacterLimit));
        }
        if ($observationLines === []) {
            $observationLines = ['Sin observaciones adicionales.'];
        }

        $primaryIndicator = $context['primary_indicator'];
        $primaryProgress = is_array($primaryIndicator) ? $this->primaryProgress($primaryIndicator) : null;
        $primaryLines = $this->primaryIndicatorLines($primaryIndicator);
        $maximumPanelLines = max(1, (int) floor(($pageHeight - 230) / 9));
        foreach (array_chunk($observationLines, $maximumPanelLines) as $panelIndex => $panelLines) {
            $rightLineCount = max(count($primaryLines), $primaryProgress === null ? 0 : 3);
            $lineCount = max(count($panelLines), $rightLineCount);
            $panelHeight = 45 + ($lineCount * 9);
            if ($currentY - $panelHeight < 42) {
                $currentY = $startPage(false);
            }

            $panelTop = $currentY - 8;
            $panelBodyTop = $panelTop - 16;
            $panelBodyHeight = $panelHeight - 16;
            $rightPanelX = $margin + $leftPanelWidth + 12;
            $pageContent .= $this->pdfSectionBar($panelIndex === 0 ? 'Observaciones' : 'Observaciones · continuación', $margin, $panelTop, $leftPanelWidth, 16);
            $pageContent .= $this->pdfSectionBar('Indicador principal', $rightPanelX, $panelTop, $rightPanelWidth, 16);
            $pageContent .= $this->pdfRect($margin, $panelBodyTop - $panelBodyHeight, $leftPanelWidth, $panelBodyHeight, [0.96, 0.98, 1], [0.80, 0.87, 0.94]);
            $pageContent .= $this->pdfRect($rightPanelX, $panelBodyTop - $panelBodyHeight, $rightPanelWidth, $panelBodyHeight, [0.96, 0.98, 1], [0.80, 0.87, 0.94]);

            foreach ($panelLines as $lineIndex => $line) {
                $pageContent .= $this->pdfText($line, $margin + 8, $panelBodyTop - 13 - ($lineIndex * 9), 7.2, [0.16, 0.22, 0.31]);
            }

            if ($primaryIndicator === null || $primaryLines === ['No se proporcionó un indicador principal.']) {
                $pageContent .= $this->pdfText($primaryLines[0], $rightPanelX + 8, $panelBodyTop - 17, 7.2, [0.16, 0.22, 0.31]);
            } else {
                $pageContent .= $this->pdfText($primaryLines[0], $rightPanelX + 8, $panelBodyTop - 13, 7.2, [0.08, 0.27, 0.48], true);
                $value = trim((string) ($primaryIndicator['value'] ?? ''));
                if ($primaryProgress !== null) {
                    $barWidth = $rightPanelWidth - 54;
                    $barX = $rightPanelX + 8;
                    $barY = $panelBodyTop - 37;
                    $pageContent .= $this->pdfProgressBar($barX, $barY, $barWidth, 9, $primaryProgress);
                    $value = $value !== '' ? $value : number_format($primaryProgress, 1, '.', ',').'%';
                    $pageContent .= $this->pdfText($value, $barX + $barWidth + 6, $barY + 1, 7.2, [0.04, 0.25, 0.48], true);
                } elseif ($value !== '') {
                    $pageContent .= $this->pdfText($value, $rightPanelX + 8, $panelBodyTop - 28, 8, [0.04, 0.25, 0.48], true);
                }
            }

            $currentY = $panelTop - $panelHeight - 10;
        }
        $pages[] = $pageContent;

        $pageObjectIds = [];
        $contentObjectIds = [];
        $nextObjectId = max(array_keys($objects)) + 1;
        foreach ($pages as $index => $page) {
            $pageObjectIds[$index] = $nextObjectId++;
            $contentObjectIds[$index] = $nextObjectId++;
        }

        $kids = implode(' ', array_map(static fn (int $id): string => $id.' 0 R', $pageObjectIds));
        $objects[2] = '<< /Type /Pages /Kids ['.$kids.'] /Count '.count($pageObjectIds).' >>';
        foreach ($pages as $index => $page) {
            $page .= $this->pdfFooter($pageWidth, $pageHeight, $margin, $context, $index + 1, count($pages));
            $contentId = $contentObjectIds[$index];
            $pageId = $pageObjectIds[$index];
            $objects[$contentId] = '<< /Length '.strlen($page).">>\nstream\n{$page}endstream";
            $resources = '<< /Font << /F1 3 0 R /F2 4 0 R >>'.($logoObjectId === null ? '' : ' /XObject << /Im0 '.$logoObjectId.' 0 R >>').' >>';
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.$pageWidth.' '.$pageHeight.'] /Resources '.$resources.' /Contents '.$contentId.' 0 R >>';
        }

        ksort($objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$id])."\n";
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->safeFilename($filename).'.pdf"',
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function pdfPageHeader(int $width, int $height, int $margin, array $context, bool $first, bool $hasLogo): string
    {
        $content = '';
        $bannerHeight = $first ? 88 : 50;
        $content .= $this->pdfRect(0, $height - $bannerHeight, $width, $bannerHeight, [0.035, 0.15, 0.29]);
        $content .= $this->pdfRect($width - 19, $height - $bannerHeight, 7, $bannerHeight, [0.08, 0.47, 0.78]);
        $content .= $this->pdfRect($width - 12, $height - $bannerHeight, 12, $bannerHeight, [0.06, 0.67, 0.91]);
        if ($first) {
            $content .= $this->pdfRect($margin, $height - 75, 112, 58, [1, 1, 1]);
            if ($hasLogo) {
                $content .= "q\n102 0 0 40 ".($margin + 5).' '.($height - 66)." cm\n/Im0 Do\nQ\n";
            } else {
                $content .= $this->pdfText('TECHNOLOGY CELL', $margin + 8, $height - 45, 10, [0.06, 0.32, 0.69], true);
            }
            $x = $margin + 128;
            $content .= $this->pdfText((string) $context['brand'].' — CELL SAC', $x, $height - 30, 10, [1, 1, 1], true);
            $titleWidth = $width - $x - $margin - 24;
            $titleFontSize = min(16, max(8, $titleWidth / max(1, mb_strlen((string) $context['title']) * 0.56)));
            $titleTextWidth = mb_strlen((string) $context['title']) * $titleFontSize * 0.52;
            $titleX = $x + max(0, ($titleWidth - $titleTextWidth) / 2);
            $content .= $this->pdfText((string) $context['title'], $titleX, $height - 50, $titleFontSize, [1, 1, 1], true);
            $subtitleFontSize = min(7.6, max(5.8, $titleWidth / max(1, mb_strlen((string) $context['subtitle']) * 0.52)));
            $subtitleTextWidth = mb_strlen((string) $context['subtitle']) * $subtitleFontSize * 0.52;
            $subtitleX = $x + max(0, ($titleWidth - $subtitleTextWidth) / 2);
            $content .= $this->pdfText((string) $context['subtitle'], $subtitleX, $height - 68, $subtitleFontSize, [0.88, 0.94, 1]);

            $emitted = $this->dateParts((string) $context['generated_at']);
            $content .= $this->pdfText('Empresa: '.(string) $context['company'], $margin, $height - 105, 7.2, [0.08, 0.16, 0.27], true);
            $content .= $this->pdfText('Módulo: '.(string) $context['module'], $width / 2, $height - 105, 7.2, [0.08, 0.16, 0.27], true);
            $content .= $this->pdfText('Reporte: '.(string) $context['report_name'], $margin, $height - 119, 7, [0.18, 0.23, 0.32]);
            $content .= $this->pdfText('Generado por: '.(string) $context['generated_by'], $width / 2, $height - 119, 7, [0.18, 0.23, 0.32]);
            $content .= $this->pdfText('Rol: '.(string) $context['role'], $margin, $height - 133, 7, [0.18, 0.23, 0.32]);
            $content .= $this->pdfText('Emisión: '.$emitted['date'].' '.$emitted['time'], $width / 2, $height - 133, 7, [0.18, 0.23, 0.32]);
            $content .= $this->pdfText('Período: '.(string) $context['period'].' · Sede: '.(string) $context['scope'], $margin, $height - 147, 7, [0.18, 0.23, 0.32]);
            $filters = $this->filterSummary($context['filters']);
            $filterLines = $this->wrapText('Filtros: '.$filters, max(34, (int) (($width - 2 * $margin) / 4.1)));
            foreach ($filterLines as $index => $line) {
                $content .= $this->pdfText($line, $margin, $height - 161 - ($index * 9), 6.7, [0.31, 0.37, 0.47]);
            }

            $summaryLayout = $this->pdfSummaryLayout($width, $height, $margin, $context);
            $content .= $this->pdfSectionBar('Resumen del período', $margin, $summaryLayout['summary_top'], $width - (2 * $margin), 15);
            if ($context['indicators'] === []) {
                $content .= $this->pdfRect($margin, $summaryLayout['content_top'] - 27, $width - (2 * $margin), 27, [0.91, 0.96, 1], [0.77, 0.85, 0.94]);
                $content .= $this->pdfText('No se proporcionaron indicadores para este reporte.', $margin + 8, $summaryLayout['content_top'] - 16, 7.2, [0.10, 0.30, 0.50]);
            } else {
                foreach ($summaryLayout['cards']['items'] as $index => $card) {
                    $cardX = $margin + ($index * ($summaryLayout['cards']['card_width'] + $summaryLayout['cards']['gap']));
                    [$fill, $border, $text] = $this->pdfIndicatorPalette($index);
                    $content .= $this->pdfRect($cardX, $summaryLayout['card_bottom'], $summaryLayout['cards']['card_width'], $summaryLayout['cards']['height'], $fill, $border);

                    foreach ($card['label_lines'] as $lineIndex => $line) {
                        $content .= $this->pdfText($line, $cardX + 7, $summaryLayout['content_top'] - 10 - ($lineIndex * 7), 6.5, $text, true);
                    }

                    $valueTop = $summaryLayout['content_top'] - 10 - (count($card['label_lines']) * 7) - 3;
                    foreach ($card['value_lines'] as $lineIndex => $line) {
                        $content .= $this->pdfText($line, $cardX + 7, $valueTop - ($lineIndex * 11), 10, [0.04, 0.25, 0.48], true);
                    }
                }
            }
        } else {
            $content .= $this->pdfText((string) $context['brand'].' — CELL SAC', $margin, $height - 21, 8, [1, 1, 1], true);
            $content .= $this->pdfText((string) $context['title'].' · Continuación', $margin, $height - 38, 10, [1, 1, 1], true);
        }

        return $content;
    }

    /**
     * @param  array<int, array{label?:string,value?:string|int|float|null}>  $indicators
     * @param  array<string, mixed>  $context
     */
    private function pdfFirstPageTableTop(int $width, int $height, int $margin, array $context): float
    {
        return $this->pdfSummaryLayout($width, $height, $margin, $context)['table_top'];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{summary_top:float,content_top:float,card_bottom:float,detail_top:float,table_top:float,cards:array{items:array<int,array{label_lines:array<int,string>,value_lines:array<int,string>}>,card_width:float,gap:float,height:float}}
     */
    private function pdfSummaryLayout(int $width, int $height, int $margin, array $context): array
    {
        $filterLines = $this->wrapText('Filtros: '.$this->filterSummary($context['filters']), max(34, (int) (($width - 2 * $margin) / 4.1)));
        $lastFilterBaseline = $height - 161 - ((count($filterLines) - 1) * 9);
        $summaryTop = $lastFilterBaseline - 7;
        $contentTop = $summaryTop - 20;
        $cards = $context['indicators'] === []
            ? ['items' => [], 'card_width' => 0.0, 'gap' => 0.0, 'height' => 27.0]
            : $this->pdfIndicatorLayout($context['indicators'], $width, $margin);
        $cardBottom = $contentTop - $cards['height'];
        $detailTop = $cardBottom - 7;
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
    private function pdfIndicatorLayout(array $indicators, int $width, int $margin): array
    {
        $gap = 7.0;
        $cardWidth = ($width - (2 * $margin) - ($gap * (count($indicators) - 1))) / count($indicators);
        $labelLimit = max(8, (int) (($cardWidth - 14) / (6.5 * 0.53)));
        $valueLimit = max(6, (int) (($cardWidth - 14) / (10 * 0.53)));
        $cards = [];
        $height = 34.0;

        foreach ($indicators as $indicator) {
            $labelLines = $this->wrapText((string) ($indicator['label'] ?? ''), $labelLimit);
            $valueLines = $this->wrapText((string) ($indicator['value'] ?? ''), $valueLimit);
            $height = max($height, 10 + ((count($labelLines) - 1) * 7) + 3 + 10 + ((count($valueLines) - 1) * 11) + 8);
            $cards[] = ['label_lines' => $labelLines, 'value_lines' => $valueLines];
        }

        return ['items' => $cards, 'card_width' => $cardWidth, 'gap' => $gap, 'height' => $height];
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, float>  $widths
     */
    private function pdfTableHeader(array $columns, array $widths, int $margin, float $top): string
    {
        $content = '';
        $x = $margin;
        $height = $this->pdfTableHeaderHeight($columns, $widths);
        foreach ($columns as $index => $column) {
            $cellWidth = $widths[$index] ?? 40;
            $content .= $this->pdfRect($x, $top - $height, $cellWidth, $height, [0.08, 0.33, 0.57], [0.78, 0.86, 0.94]);
            $lines = $this->wrapText((string) $column, max(5, (int) (($cellWidth - 8) / 4.2)));
            foreach ($lines as $lineIndex => $line) {
                $content .= $this->pdfText($line, $x + 4, $top - 10 - ($lineIndex * 8), 6.6, [1, 1, 1], true);
            }
            $x += $cellWidth;
        }

        return $content;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, float>  $widths
     */
    private function pdfTableHeaderHeight(array $columns, array $widths): float
    {
        $lineCount = 1;
        foreach ($columns as $index => $column) {
            $limit = max(5, (int) ((($widths[$index] ?? 40) - 8) / 4.2));
            $lineCount = max($lineCount, count($this->wrapText((string) $column, $limit)));
        }

        return max(24, ($lineCount * 8) + 8);
    }

    /**
     * @param  array<int, array<int, string>>  $wrapped
     * @param  array<int, float>  $widths
     * @param  array<int, string>  $columns
     * @param  array<string, mixed>  $context
     */
    private function pdfTableRow(array $wrapped, array $widths, int $margin, float $top, float $height, bool $alternate, array $columns, array $context, bool $summary = false): string
    {
        $content = '';
        $x = $margin;
        $y = $top - $height;
        foreach ($widths as $columnIndex => $cellWidth) {
            $value = $wrapped[$columnIndex][0] ?? '';
            $fill = $summary ? [0.05, 0.23, 0.42] : ($alternate ? [0.94, 0.97, 0.99] : [1, 1, 1]);
            $text = $summary ? [1, 1, 1] : [0.13, 0.18, 0.25];
            if (! $summary && mb_stripos((string) ($columns[$columnIndex] ?? ''), 'estado') !== false) {
                [$fill, $text] = match ($this->statusTone($value)) {
                    'positive' => [[0.89, 0.96, 0.91], [0.12, 0.39, 0.24]],
                    'warning' => [[1, 0.96, 0.82], [0.52, 0.34, 0.05]],
                    'danger' => [[0.99, 0.90, 0.90], [0.58, 0.15, 0.15]],
                    default => [$fill, $text],
                };
            }
            $content .= $this->pdfRect($x, $y, $cellWidth, $height, $fill, [0.84, 0.88, 0.93]);
            foreach ($wrapped[$columnIndex] ?? [] as $lineIndex => $line) {
                $content .= $this->pdfText($line, $x + 4, $top - 10 - ($lineIndex * 8), 6.8, $text, $summary);
            }
            $x += $cellWidth;
        }

        return $content;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function pdfFooter(int $width, int $height, int $margin, array $context, int $page, int $pageCount): string
    {
        $content = $this->pdfRect(0, 0, $width, 31, [0.035, 0.15, 0.29]);
        $content .= $this->pdfRect(0, 0, 6, 31, [0.06, 0.67, 0.91]);
        $content .= $this->pdfText('TECHNOLOGY CELL — CELL SAC', $margin, 20, 7, [1, 1, 1], true);
        $content .= $this->pdfText('Documento generado por el sistema Technology CELL.', $margin, 8, 6.2, [0.88, 0.94, 1]);
        $content .= $this->pdfText('Emitido: '.($this->dateParts((string) $context['generated_at'])['date']).' · Página '.$page.' de '.$pageCount, $width - $margin - 145, 12, 6.2, [1, 1, 1]);

        return $content;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @return array<int, float>
     */
    private function pdfColumnWidths(array $columns, array $rows, float $totalWidth): array
    {
        $weights = [];
        foreach ($columns as $columnIndex => $column) {
            $maximum = min(36, mb_strlen((string) $column));
            foreach ($rows as $row) {
                $maximum = max($maximum, min(36, mb_strlen((string) ($row[$columnIndex] ?? ''))));
            }
            $weights[] = max(8, $maximum);
        }
        $weightTotal = array_sum($weights) ?: 1;

        return array_map(static fn (int $weight): float => $totalWidth * ($weight / $weightTotal), $weights);
    }

    /**
     * @param  array<int, string>  $values
     * @param  array<int, float>  $widths
     * @return array<int, array<int, string>>
     */
    private function wrapRow(array $values, array $widths, float $fontSize): array
    {
        $wrapped = [];
        foreach ($widths as $index => $width) {
            $characters = max(4, (int) (($width - 8) / ($fontSize * 0.53)));
            $wrapped[] = $this->wrapText((string) ($values[$index] ?? ''), $characters);
        }

        return $wrapped;
    }

    /**
     * @return array<int, string>
     */
    private function wrapText(string $value, int $limit): array
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        if ($value === '') {
            return [''];
        }
        $lines = [];
        $line = '';
        foreach (preg_split('/\s+/u', $value) ?: [$value] as $word) {
            while (mb_strlen($word) > $limit) {
                if ($line !== '') {
                    $lines[] = $line;
                    $line = '';
                }
                $lines[] = mb_substr($word, 0, $limit);
                $word = mb_substr($word, $limit);
            }
            $candidate = $line === '' ? $word : $line.' '.$word;
            if (mb_strlen($candidate) > $limit && $line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines ?: [''];
    }

    /**
     * @param  array<int, float>|null  $stroke
     */
    private function pdfRect(float $x, float $y, float $width, float $height, array $fill, ?array $stroke = null): string
    {
        $content = sprintf("%.3F %.3F %.3F rg\n", $fill[0], $fill[1], $fill[2]);
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
    private function pdfText(string $value, float $x, float $y, float $size, array $color, bool $bold = false): string
    {
        $latin = iconv('UTF-8', 'Windows-1252//TRANSLIT', $value);
        $latin = $latin === false ? '' : $latin;
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
    private function pdfSectionBar(string $label, float $x, float $top, float $width, float $height): string
    {
        $bottom = $top - $height;
        $content = $this->pdfRect($x, $bottom, $width, $height, [0.04, 0.29, 0.55]);
        $content .= $this->pdfRect($x, $bottom, 4, $height, [0.06, 0.67, 0.91]);
        $fontSize = min(8.0, max(5.5, ($width - 18) / max(1, mb_strlen($label) * 0.54)));
        $content .= $this->pdfText($label, $x + 9, $bottom + 4, $fontSize, [1, 1, 1], true);

        return $content;
    }

    /**
     * @return array{0:array{0:float,1:float,2:float},1:array{0:float,1:float,2:float},2:array{0:float,1:float,2:float}}
     */
    private function pdfIndicatorPalette(int $index): array
    {
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
    private function pdfProgressBar(float $x, float $y, float $width, float $height, float $percentage): string
    {
        $percentage = max(0, min(100, $percentage));
        $content = $this->pdfRect($x, $y, $width, $height, [0.82, 0.88, 0.94]);
        if ($percentage > 0) {
            $content .= $this->pdfRect($x, $y, $width * ($percentage / 100), $height, [0.03, 0.43, 0.81]);
        }

        return $content;
    }

    /**
     * @return array{image_id:?int,objects:array<int,string>}
     */
    private function pdfLogoObjects(int $firstId): array
    {
        $image = $this->decodePng($this->logoBytes());
        if ($image === null) {
            return ['image_id' => null, 'objects' => []];
        }

        $imageId = $firstId;
        $objects = [];
        $softMask = '';
        if ($image['alpha'] !== null) {
            $maskId = $firstId + 1;
            $maskBytes = gzcompress($image['alpha']);
            if ($maskBytes === false) {
                return ['image_id' => null, 'objects' => []];
            }
            $objects[$maskId] = '<< /Type /XObject /Subtype /Image /Width '.$image['width'].' /Height '.$image['height'].' /ColorSpace /DeviceGray /BitsPerComponent 8 /Filter /FlateDecode /Length '.strlen($maskBytes).">>\nstream\n".$maskBytes."\nendstream";
            $softMask = ' /SMask '.$maskId.' 0 R';
        }
        $imageBytes = gzcompress($image['rgb']);
        if ($imageBytes === false) {
            return ['image_id' => null, 'objects' => []];
        }
        $objects[$imageId] = '<< /Type /XObject /Subtype /Image /Width '.$image['width'].' /Height '.$image['height'].' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /FlateDecode'.$softMask.' /Length '.strlen($imageBytes).">>\nstream\n".$imageBytes."\nendstream";

        return ['image_id' => $imageId, 'objects' => $objects];
    }

    /**
     * @return array{width:int,height:int,rgb:string,alpha:?string}|null
     */
    private function decodePng(?string $png): ?array
    {
        if ($png === null || ! str_starts_with($png, "\x89PNG\r\n\x1a\n")) {
            return null;
        }
        $offset = 8;
        $compressed = '';
        $header = null;
        while ($offset + 8 <= strlen($png)) {
            $length = unpack('Nlength', substr($png, $offset, 4))['length'];
            $type = substr($png, $offset + 4, 4);
            $data = substr($png, $offset + 8, $length);
            if ($type === 'IHDR') {
                $header = unpack('Nwidth/Nheight/Cdepth/Ccolor/Ccompression/Cfilter/Cinterlace', $data);
            } elseif ($type === 'IDAT') {
                $compressed .= $data;
            } elseif ($type === 'IEND') {
                break;
            }
            $offset += $length + 12;
        }
        if ($header === null || $header['depth'] !== 8 || ! in_array($header['color'], [2, 6], true) || $header['interlace'] !== 0 || $header['width'] * $header['height'] > 4_000_000) {
            return null;
        }

        $raw = zlib_decode($compressed);
        if ($raw === false) {
            return null;
        }
        $channels = $header['color'] === 6 ? 4 : 3;
        $stride = $header['width'] * $channels;
        $previous = array_fill(0, $stride, 0);
        $rgb = '';
        $alpha = $channels === 4 ? '' : null;

        for ($row = 0; $row < $header['height']; $row++) {
            $rowOffset = $row * ($stride + 1);
            $filter = ord($raw[$rowOffset]);
            $filtered = array_values(unpack('C*', substr($raw, $rowOffset + 1, $stride)));
            for ($index = 0; $index < $stride; $index++) {
                $left = $index >= $channels ? $filtered[$index - $channels] : 0;
                $up = $previous[$index];
                $upperLeft = $index >= $channels ? $previous[$index - $channels] : 0;
                $predictor = match ($filter) {
                    1 => $left,
                    2 => $up,
                    3 => intdiv($left + $up, 2),
                    4 => $this->paethPredictor($left, $up, $upperLeft),
                    default => 0,
                };
                $filtered[$index] = ($filtered[$index] + $predictor) & 255;
            }
            for ($pixel = 0; $pixel < $header['width']; $pixel++) {
                $base = $pixel * $channels;
                $rgb .= chr($filtered[$base]).chr($filtered[$base + 1]).chr($filtered[$base + 2]);
                if ($alpha !== null) {
                    $alpha .= chr($filtered[$base + 3]);
                }
            }
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
    private function paethPredictor(int $left, int $up, int $upperLeft): int
    {
        $estimate = $left + $up - $upperLeft;
        $leftDistance = abs($estimate - $left);
        $upDistance = abs($estimate - $up);
        $upperLeftDistance = abs($estimate - $upperLeft);

        return $leftDistance <= $upDistance && $leftDistance <= $upperLeftDistance ? $left : ($upDistance <= $upperLeftDistance ? $up : $upperLeft);
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>
     */
    private function reportContext(string $filename, array $report, int $columnCount): array
    {
        $readableName = trim(preg_replace('/[-_]+/', ' ', $filename) ?? $filename);
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
        $context = array_merge($defaults, $report);
        $context['detail_title'] = is_string($context['detail_title'] ?? null) && trim($context['detail_title']) !== ''
            ? trim($context['detail_title'])
            : $this->dynamicDetailTitle($context);
        $context['indicators'] = is_array($context['indicators']) ? array_values($context['indicators']) : [];
        $context['filters'] = is_array($context['filters']) ? $context['filters'] : [];
        $context['column_types'] = is_array($context['column_types']) ? $context['column_types'] : [];
        $context['observations'] = is_array($context['observations']) ? array_values($context['observations']) : [];
        $context['primary_indicator'] = is_array($context['primary_indicator'] ?? null) ? $context['primary_indicator'] : null;
        if (! in_array($context['orientation'], ['portrait', 'landscape'], true)) {
            $context['orientation'] = 'portrait';
        }

        return $context;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function dynamicDetailTitle(array $context): string
    {
        $descriptor = mb_strtolower(implode(' ', array_filter([
            (string) ($context['title'] ?? ''),
            (string) ($context['report_name'] ?? ''),
            (string) ($context['module'] ?? ''),
        ])));
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

        foreach ($topics as $needle => $label) {
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
    private function primaryIndicatorLines(?array $indicator): array
    {
        if ($indicator === null) {
            return ['No se proporcionó un indicador principal.'];
        }

        $label = trim((string) ($indicator['label'] ?? ''));
        $value = $indicator['value'] ?? null;
        $valueText = is_scalar($value) ? trim((string) $value) : '';
        $progress = $this->primaryProgress($indicator);

        if ($label === '' && $valueText === '' && $progress === null) {
            return ['No se proporcionó un indicador principal.'];
        }

        $lines = [$label !== '' ? $label : 'Indicador'];
        if ($progress !== null) {
            $filledBlocks = (int) round($progress / 10);
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
    private function primaryProgress(array $indicator): ?float
    {
        $progress = $indicator['progress'] ?? null;
        if (! is_numeric($progress)) {
            return null;
        }

        $percentage = (float) $progress;

        return is_finite($percentage) && $percentage >= 0 && $percentage <= 100 ? $percentage : null;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function columnType(array $context, int $index): string
    {
        $type = $context['column_types'][$index] ?? 'text';

        return in_array($type, ['text', 'integer', 'number', 'currency', 'percent', 'date', 'datetime'], true) ? $type : 'text';
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $type  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function isNumericType(string $type): bool
    {
        return in_array($type, ['integer', 'number', 'currency', 'percent'], true);
    }

    /**
     * Ejecuta la operación excelDateSerial definida por la clase ExportadorTabular.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @param  mixed  $dateOnly  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function excelDateSerial(mixed $value, bool $dateOnly): ?float
    {
        $date = $this->toDateTime($value);
        if ($date === null) {
            return null;
        }
        if ($dateOnly) {
            $date = $date->setTime(0, 0);
        }

        return ($date->getTimestamp() / 86400) + 25569;
    }

    /**
     * @return array{date:string,time:string}
     */
    private function dateParts(string $value): array
    {
        $date = $this->toDateTime($value);
        if ($date === null) {
            return ['date' => $value, 'time' => ''];
        }

        return ['date' => $date->format('d/m/Y'), 'time' => $date->format('H:i')];
    }

    /**
     * Ejecuta la operación toDateTime definida por la clase ExportadorTabular.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function toDateTime(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }
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
     * Ejecuta la operación displayValue definida por la clase ExportadorTabular.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @param  mixed  $type  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function displayValue(mixed $value, string $type): string
    {
        if ($value === null) {
            return '';
        }
        if (in_array($type, ['date', 'datetime'], true)) {
            $date = $this->toDateTime($value);
            if ($date !== null) {
                return $date->format($type === 'date' ? 'd/m/Y' : 'd/m/Y H:i');
            }
        }
        if ($type === 'currency' && is_numeric($value)) {
            return 'PEN '.number_format((float) $value, 2, '.', ',');
        }
        if ($type === 'percent' && is_numeric($value)) {
            return number_format((float) $value, 1, '.', ',').'%';
        }
        if ($type === 'integer' && is_numeric($value)) {
            return number_format((float) $value, 0, '.', ',');
        }

        return (string) $value;
    }

    /**
     * @param  array<int, array{label?:string,value?:string|int|float|null}>  $filters
     */
    private function filterSummary(array $filters): string
    {
        $items = [];
        foreach ($filters as $label => $value) {
            if (is_array($value)) {
                $name = (string) ($value['label'] ?? $label);
                $content = $value['value'] ?? null;
            } else {
                $name = (string) $label;
                $content = $value;
            }
            if ($content === null || $content === '') {
                continue;
            }
            $items[] = $name.': '.(is_scalar($content) ? (string) $content : '');
        }

        return $items === [] ? 'Sin filtros adicionales' : implode(' · ', $items);
    }

    /**
     * Ejecuta la operación statusTone definida por la clase ExportadorTabular.
     *
     * @param  mixed  $status  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function statusTone(string $status): string
    {
        $normalized = mb_strtolower(trim($status));
        if (preg_match('/cancel|anulad|rechaz|fallid|inactiv|vencid|bloquead/u', $normalized) === 1) {
            return 'danger';
        }
        if (preg_match('/pendient|proceso|revisi|reserv|programad|borrador/u', $normalized) === 1) {
            return 'warning';
        }
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
    private function columnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(($index % 26) + 65).$letter;
            $index = intdiv($index, 26) - 1;
        }

        return $letter;
    }

    /**
     * Ejecuta la operación logoBytes definida por la clase ExportadorTabular.
     *
     * @return mixed Resultado de la operación.
     */
    private function logoBytes(): ?string
    {
        $path = base_path(self::LOGO_PATH);

        return is_file($path) ? file_get_contents($path) ?: null : null;
    }

    /**
     * Ejecuta la operación safeFilename definida por la clase ExportadorTabular.
     *
     * @param  mixed  $filename  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function safeFilename(string $filename): string
    {
        return trim(preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?? 'reporte', '-.');
    }

    /**
     * Ejecuta la operación safeSpreadsheetText definida por la clase ExportadorTabular.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private static function safeSpreadsheetText(string $value): string
    {
        $isValidUtf8 = preg_match('//u', $value) === 1;
        $hasFormulaPrefix = ! $isValidUtf8 || preg_match('/^[\p{C}\p{Z}\s]*[=+\-@]/u', $value) === 1;

        return $hasFormulaPrefix && ! is_numeric($value) ? "'".$value : $value;
    }
}
