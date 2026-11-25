<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$reportType = $reportType ?? 'monthly';
$reportTitles = [
    'monthly' => 'Monthly sales report',
    'stock' => 'Stock report',
    'popular' => 'Popular mobile products',
];
$pageTitle = $reportTitles[$reportType] ?? $reportTitles['monthly'];
$reportCurrency = (string)setting('currency_code', 'MMK');
$currentYear = (int)date('Y');
$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$year = ($year && $year >= 2000 && $year <= $currentYear + 1) ? $year : $currentYear;

$years = db()->query(
    "SELECT DISTINCT YEAR(created_at) AS report_year FROM orders ORDER BY report_year DESC"
)->fetchAll(PDO::FETCH_COLUMN);
$years = array_values(array_unique(array_merge([$currentYear], array_map('intval', $years))));
sort($years, SORT_NUMERIC);

$monthlySales = array_fill(1, 12, ['orders' => 0, 'revenue' => 0.0, 'units' => 0]);
$stmt = db()->prepare(
    "SELECT MONTH(created_at) AS month_num, COUNT(*) AS order_count,
            COALESCE(SUM(total), 0) AS revenue
     FROM orders
     WHERE YEAR(created_at) = :year AND status <> 'cancelled'
     GROUP BY MONTH(created_at)"
);
$stmt->execute([':year' => $year]);
foreach ($stmt->fetchAll() as $row) {
    $month = (int)$row['month_num'];
    $monthlySales[$month]['orders'] = (int)$row['order_count'];
    $monthlySales[$month]['revenue'] = (float)$row['revenue'];
}
$stmt = db()->prepare(
    "SELECT MONTH(o.created_at) AS month_num, COALESCE(SUM(oi.quantity), 0) AS units
     FROM orders o
     JOIN order_items oi ON oi.order_id = o.id
     WHERE YEAR(o.created_at) = :year AND o.status <> 'cancelled'
     GROUP BY MONTH(o.created_at)"
);
$stmt->execute([':year' => $year]);
foreach ($stmt->fetchAll() as $row) {
    $monthlySales[(int)$row['month_num']]['units'] = (int)$row['units'];
}

$export = (string)($_GET['export'] ?? '');
$stockFromDate = trim((string)($_GET['from_date'] ?? ''));
$stockToDate = trim((string)($_GET['to_date'] ?? ''));
$stockSearch = trim((string)($_GET['search'] ?? ''));
$stockSearch = mb_substr($stockSearch, 0, 100);
$stockPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$stockPage = $stockPage && $stockPage > 0 ? $stockPage : 1;
$stockPageSize = max(5, min(100, (int)setting('stock_report_per_page', '10')));
$stockDateError = '';
$parseReportDate = static function (string $value): string {
    if ($value === '') return '';
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $dateErrors = DateTimeImmutable::getLastErrors();
    return $date && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
        && $date->format('Y-m-d') === $value ? $value : '';
};
$stockFromDate = $parseReportDate($stockFromDate);
$stockToDate = $parseReportDate($stockToDate);
if ($stockFromDate !== '' && $stockToDate !== '' && $stockFromDate > $stockToDate) {
    $stockDateError = 'The From date must be on or before the To date.';
}

$stockWhere = [];
$stockParams = [];
if ($stockFromDate !== '') {
    $stockWhere[] = 'p.created_at >= :stock_from_date';
    $stockParams[':stock_from_date'] = $stockFromDate . ' 00:00:00';
}
if ($stockToDate !== '') {
    $stockWhere[] = 'p.created_at < :stock_to_date';
    $stockParams[':stock_to_date'] = (new DateTimeImmutable($stockToDate))->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
}
if ($stockSearch !== '') {
    $stockWhere[] = '(p.name LIKE :stock_search_name OR p.sku LIKE :stock_search_sku OR c.name LIKE :stock_search_category)';
    $stockTerm = '%' . $stockSearch . '%';
    $stockParams[':stock_search_name'] = $stockTerm;
    $stockParams[':stock_search_sku'] = $stockTerm;
    $stockParams[':stock_search_category'] = $stockTerm;
}
if ($stockDateError !== '') $stockWhere[] = '1 = 0';
$stockWhereSql = $stockWhere ? 'WHERE ' . implode(' AND ', $stockWhere) : '';
$stockBaseSql = "FROM products p LEFT JOIN categories c ON c.id = p.category_id $stockWhereSql";

$stockSummaryStmt = db()->prepare(
    "SELECT COUNT(*) AS product_count,
            COALESCE(SUM(p.stock), 0) AS stock_units,
            COALESCE(SUM(p.stock * COALESCE(NULLIF(p.sale_price, 0), p.price)), 0) AS inventory_value
     $stockBaseSql"
);
$stockSummaryStmt->execute($stockParams);
$stockSummary = $stockSummaryStmt->fetch();
$stockFilteredCount = (int)($stockSummary['product_count'] ?? 0);
$stockTotalPages = max(1, (int)ceil($stockFilteredCount / $stockPageSize));
$stockPage = min($stockPage, $stockTotalPages);
$stockOffset = ($stockPage - 1) * $stockPageSize;
$stockIsExport = $reportType === 'stock' && in_array($export, ['pdf', 'stock'], true);
$stockSql = "SELECT p.id, p.name, p.sku, p.stock, p.price, p.sale_price, p.status,
                    c.name AS category_name,
                    p.stock * COALESCE(NULLIF(p.sale_price, 0), p.price) AS inventory_value
             $stockBaseSql
             ORDER BY p.stock ASC, p.name ASC";
if (!$stockIsExport) $stockSql .= ' LIMIT :stock_limit OFFSET :stock_offset';
$stockStmt = db()->prepare($stockSql);
foreach ($stockParams as $param => $value) $stockStmt->bindValue($param, $value);
if (!$stockIsExport) {
    $stockStmt->bindValue(':stock_limit', $stockPageSize, PDO::PARAM_INT);
    $stockStmt->bindValue(':stock_offset', $stockOffset, PDO::PARAM_INT);
}
$stockStmt->execute();
$stockRows = $stockStmt->fetchAll();
$stockExportFrom = $stockFromDate !== '' ? $stockFromDate : 'all-dates';
$stockExportTo = $stockToDate !== '' ? $stockToDate : 'all-dates';
$stockExportQuery = http_build_query([
    'from_date' => $stockFromDate,
    'to_date' => $stockToDate,
    'search' => $stockSearch,
]);

$popularRows = db()->query(
    "SELECT oi.product_id, COALESCE(p.name, oi.product_name) AS product_name,
            COALESCE(p.sku, oi.product_sku) AS sku,
            COALESCE(c.name, 'Mobile product') AS category_name,
            SUM(oi.quantity) AS units_sold,
            SUM(oi.line_total) AS sales_total
     FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     LEFT JOIN products p ON p.id = oi.product_id
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE o.status <> 'cancelled'
       AND (LOWER(COALESCE(c.name, '')) LIKE '%mobile%'
            OR LOWER(COALESCE(c.name, '')) LIKE '%phone%'
            OR LOWER(COALESCE(c.name, '')) LIKE '%smartphone%')
     GROUP BY oi.product_id, product_name, sku, category_name
     ORDER BY units_sold DESC, sales_total DESC"
)->fetchAll();

if ($export === 'pdf') {
    $currency = $reportCurrency;
    $headers = [];
    $widths = [];
    $tableRows = [];
    $summary = '';
    if ($reportType === 'monthly') {
        $headers = ['No.', 'Month', 'Orders', 'Units Sold', 'Sales (' . $currency . ')'];
        $widths = [36, 160, 100, 120, 358];
        foreach ($monthlySales as $month => $totals) {
            $tableRows[] = [count($tableRows) + 1, date('F', mktime(0, 0, 0, $month, 1)), $totals['orders'], $totals['units'], number_format($totals['revenue'], 2, '.', ',')];
        }
        $monthlyTotalIndex = count($tableRows);
        $tableRows[] = ['', 'Total', array_sum(array_column($monthlySales, 'orders')), array_sum(array_column($monthlySales, 'units')), number_format(array_sum(array_column($monthlySales, 'revenue')), 2, '.', ',')];
    } elseif ($reportType === 'stock') {
        $headers = ['No.', 'Product', 'SKU', 'Category', 'Stock', 'Stock Status', 'Unit Price (' . $currency . ')', 'Inventory Value (' . $currency . ')'];
        $widths = [34, 140, 80, 96, 48, 86, 120, 170];
        foreach ($stockRows as $index => $product) {
            $unitPrice = (float)($product['sale_price'] ?: $product['price']);
            $tableRows[] = [$index + 1, $product['name'], $product['sku'] ?: '—', $product['category_name'] ?: 'Uncategorized', (int)$product['stock'], $product['stock'] <= 0 ? 'Out of stock' : ($product['stock'] < 10 ? 'Low stock' : 'In stock'), number_format($unitPrice, 2, '.', ','), number_format((float)$product['inventory_value'], 2, '.', ',')];
        }
        $stockTotalIndex = count($tableRows);
        $totalStockUnits = array_sum(array_map(static fn($p) => (int)$p['stock'], $stockRows));
        $totalInventoryValue = array_sum(array_map(static fn($p) => (float)$p['inventory_value'], $stockRows));
        $tableRows[] = ['', 'Total', '', '', number_format($totalStockUnits), '', '', number_format($totalInventoryValue, 2, '.', ',')];
        $summary = 'From date: ' . ($stockFromDate ?: 'any date')
            . ' | To date: ' . ($stockToDate ?: 'any date') . ' | Current stock snapshot.';
    } else {
        $headers = ['No.', 'Product', 'SKU', 'Category', 'Units sold', 'Sales (' . $currency . ')'];
        $widths = [34, 230, 100, 150, 110, 150];
        $summary = 'Ranked by units sold across non-cancelled orders.';
        foreach ($popularRows as $index => $product) {
            $tableRows[] = [$index + 1, $product['product_name'], $product['sku'] ?: '—', $product['category_name'], (int)$product['units_sold'], number_format((float)$product['sales_total'], 2, '.', ',')];
        }
        if (!$popularRows) $tableRows[] = ['—', 'No mobile product sales yet.', '', '', '', ''];
        $popularTotalIndex = count($tableRows);
        $tableRows[] = ['', 'Total', '', '', array_sum(array_map(static fn($p) => (int)$p['units_sold'], $popularRows)), number_format(array_sum(array_map(static fn($p) => (float)$p['sales_total'], $popularRows)), 2, '.', ',')];
    }

    // Convert text to the standard PDF font's supported character set.
    $pdfEncode = static function ($text): string {
        $text = (string)$text;
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
            if ($converted !== false) $text = $converted;
        }
        return preg_replace('/[^\x20-\x7E\x80-\xFF]/', '', $text) ?? '';
    };
    $pdfText = static function (float $x, float $y, string $text, int $size = 8, string $font = 'F1'): string {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        return "BT /$font $size Tf $x $y Td ($text) Tj ET\n";
    };
    $cellLines = [];
    foreach ($tableRows as $rowIndex => $row) {
        foreach ($row as $column => $value) {
            $row[$column] = $pdfEncode($value);
        }
        $wrapped = [];
        foreach ($row as $column => $value) {
            $capacity = max(5, (int)floor(($widths[$column] - 12) / 4.25));
            $wrapped[$column] = explode("\n", wordwrap($value, $capacity, "\n", true));
        }
        $cellLines[$rowIndex] = ['cells' => $wrapped, 'height' => max(25, max(array_map('count', $wrapped)) * 10 + 10), 'is_total' => ($reportType === 'stock' && $rowIndex === ($stockTotalIndex ?? -1)) || ($reportType === 'monthly' && $rowIndex === ($monthlyTotalIndex ?? -1)) || ($reportType === 'popular' && $rowIndex === ($popularTotalIndex ?? -1))];
    }

    // Decode the site's RGBA PNG mark so it can be embedded as a PDF image.
    $logoImage = null;
    $logoPath = ROOT_PATH . '/assets/images/mobilehub-logo.png';
    $png = is_file($logoPath) ? file_get_contents($logoPath) : false;
    if ($png !== false && substr($png, 0, 8) === "\x89PNG\r\n\x1a\n") {
        $pngWidth = unpack('N', substr($png, 16, 4))[1];
        $pngHeight = unpack('N', substr($png, 20, 4))[1];
        $bitDepth = ord($png[24]);
        $colorType = ord($png[25]);
        $offset = 8;
        $idat = '';
        while ($offset + 12 <= strlen($png)) {
            $chunkLength = unpack('N', substr($png, $offset, 4))[1];
            $chunkType = substr($png, $offset + 4, 4);
            $chunkData = substr($png, $offset + 8, $chunkLength);
            if ($chunkType === 'IDAT') $idat .= $chunkData;
            $offset += 12 + $chunkLength;
            if ($chunkType === 'IEND') break;
        }
        $decoded = ($bitDepth === 8 && $colorType === 6 && function_exists('gzuncompress')) ? @gzuncompress($idat) : false;
        if ($decoded !== false && strlen($decoded) === $pngHeight * ($pngWidth * 4 + 1)) {
            $rgb = $alpha = '';
            $previous = array_fill(0, $pngWidth * 4, 0);
            $position = 0;
            for ($y = 0; $y < $pngHeight; $y++) {
                $filter = ord($decoded[$position++]);
                $current = [];
                for ($x = 0; $x < $pngWidth * 4; $x++) {
                    $rawByte = ord($decoded[$position++]);
                    $left = $x >= 4 ? $current[$x - 4] : 0;
                    $above = $previous[$x];
                    $upperLeft = $x >= 4 ? $previous[$x - 4] : 0;
                    if ($filter === 1) $rawByte += $left;
                    elseif ($filter === 2) $rawByte += $above;
                    elseif ($filter === 3) $rawByte += (int)floor(($left + $above) / 2);
                    elseif ($filter === 4) {
                        $p = $left + $above - $upperLeft;
                        $pa = abs($p - $left); $pb = abs($p - $above); $pc = abs($p - $upperLeft);
                        $rawByte += ($pa <= $pb && $pa <= $pc) ? $left : (($pb <= $pc) ? $above : $upperLeft);
                    }
                    $current[$x] = $rawByte & 255;
                }
                for ($x = 0; $x < $pngWidth * 4; $x += 4) {
                    $rgb .= chr($current[$x]) . chr($current[$x + 1]) . chr($current[$x + 2]);
                    $alpha .= chr($current[$x + 3]);
                }
                $previous = $current;
            }
            $logoImage = ['width' => $pngWidth, 'height' => $pngHeight, 'rgb' => gzcompress($rgb), 'alpha' => gzcompress($alpha)];
        }
    }

    // Paginate the tabular rows, repeating the column headings on each page.
    $tablePages = [];
    $pageRows = [];
    $usedHeight = 0;
    foreach ($cellLines as $row) {
        if ($pageRows && $usedHeight + $row['height'] > 405) {
            $tablePages[] = $pageRows;
            $pageRows = [];
            $usedHeight = 0;
        }
        $pageRows[] = $row;
        $usedHeight += $row['height'];
    }
    if ($pageRows || !$tablePages) $tablePages[] = $pageRows;

    $objects = [];
    $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
    $kids = [];
    foreach ($tablePages as $pageIndex => $rowsOnPage) {
        $pageId = 4 + ($pageIndex * 2);
        $streamId = $pageId + 1;
        $kids[] = $pageId . ' 0 R';
        $content = '';
        $subtitle = $reportType === 'monthly'
            ? 'Monthly performance overview - cancelled orders are excluded.'
            : ($reportType === 'stock'
                ? 'Inventory levels and valuation by product.'
                : 'Best-selling mobile products ranked by units sold.');
        $content .= "0.07 0.11 0.24 rg\n0 505 842 90 re f\n";
        $content .= "0.31 0.27 0.85 rg\n0 501 842 4 re f\n";
        if ($pageIndex === 0) {
            $content .= "1 1 1 rg\n";
            $content .= $pdfText(36, 552, strtoupper($pageTitle) . ($reportType === 'monthly' ? ' - ' . $year : ''), 18, 'F2');
            $content .= $pdfText(36, 530, $subtitle, 9);
            if ($summary !== '') $content .= $pdfText(36, 514, $summary, 8);
        } else {
            $content .= "1 1 1 rg\n";
            $content .= $pdfText(36, 552, strtoupper($pageTitle) . ($reportType === 'monthly' ? ' - ' . $year : ''), 16, 'F2');
            $content .= $pdfText(36, 530, $subtitle, 9);
        }
        if ($logoImage) {
            $content .= "1 1 1 rg\n704 525 106 42 re f\n";
            $content .= "q\n92 0 0 31 711 530 cm\n/Logo Do\nQ\n";
        }

        $tableTop = 478;
        $tableWidth = array_sum($widths);
        $content .= "0.31 0.27 0.85 rg\n36 " . ($tableTop - 25) . " $tableWidth 25 re f\n";
        $content .= "1 1 1 rg\n";
        $x = 42;
        foreach ($headers as $column => $label) {
            $content .= $pdfText($x, $tableTop - 16, $label, 8, 'F2');
            $x += $widths[$column];
        }
        $content .= "0.12 0.16 0.24 rg\n";
        $y = $tableTop - 25;
        foreach ($rowsOnPage as $rowIndex => $row) {
            $height = $row['height'];
            if ($row['is_total']) $content .= "0.89 0.91 0.99 rg\n36 " . ($y - $height) . " $tableWidth $height re f\n";
            elseif ($rowIndex % 2 === 0) $content .= "0.96 0.97 0.99 rg\n36 " . ($y - $height) . " $tableWidth $height re f\n";
            // The row fill changes the PDF fill color, so reset it before drawing cell text.
            $content .= "0.12 0.16 0.24 rg\n";
            $content .= "0.82 0.84 0.88 RG\n0.5 w\n36 $y m " . (36 + $tableWidth) . " $y l S\n";
            $x = 42;
            foreach ($row['cells'] as $column => $lines) {
                $baseline = $y - 14;
                foreach ($lines as $lineIndex => $line) {
                    $content .= $pdfText($x, $baseline - ($lineIndex * 10), $line, 8, $row['is_total'] ? 'F2' : 'F1');
                }
                $x += $widths[$column];
            }
            $y -= $height;
        }
        $content .= "0.82 0.84 0.88 RG\n0.5 w\n36 $y m " . (36 + $tableWidth) . " $y l S\n";
        $gridX = 36;
        $content .= "0.84 0.86 0.90 RG\n0.4 w\n";
        foreach ($widths as $columnWidth) {
            $content .= "$gridX " . ($tableTop - 25) . " m $gridX $y l S\n";
            $gridX += $columnWidth;
        }
        $content .= "$gridX " . ($tableTop - 25) . " m $gridX $y l S\n";
        $content .= "0.82 0.84 0.88 RG\n0.5 w\n36 42 m 806 42 l S\n";
        $content .= $pdfText(36, 27, 'Generated: ' . date('Y-m-d H:i:s'), 7);
        $content .= $pdfText(750, 27, 'Page ' . ($pageIndex + 1) . ' of ' . count($tablePages), 7);
        $resources = '<< /Font << /F1 3 0 R /F2 4 0 R >>';
        // Page and stream ids start after the two font objects.
        $pageId = 5 + ($pageIndex * 2);
        $streamId = $pageId + 1;
        $kids[$pageIndex] = $pageId . ' 0 R';
        $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources ' . $resources . ($logoImage ? ' /XObject << /Logo ' . (5 + (count($tablePages) * 2) + 1) . ' 0 R >>' : '') . ' >> /Contents ' . $streamId . ' 0 R >>';
        $objects[$streamId] = "<< /Length " . strlen($content) . ">>\nstream\n" . $content . "endstream";
    }
    $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
    $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
    if ($logoImage) {
        $alphaId = 5 + (count($tablePages) * 2);
        $imageId = $alphaId + 1;
        $objects[$alphaId] = '<< /Type /XObject /Subtype /Image /Width ' . $logoImage['width'] . ' /Height ' . $logoImage['height'] . ' /ColorSpace /DeviceGray /BitsPerComponent 8 /Filter /FlateDecode /Length ' . strlen($logoImage['alpha']) . ">>\nstream\n" . $logoImage['alpha'] . "\nendstream";
        $objects[$imageId] = '<< /Type /XObject /Subtype /Image /Width ' . $logoImage['width'] . ' /Height ' . $logoImage['height'] . ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /FlateDecode /SMask ' . $alphaId . ' 0 R /Length ' . strlen($logoImage['rgb']) . ">>\nstream\n" . $logoImage['rgb'] . "\nendstream";
    }
    ksort($objects);
    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $id => $object) {
        $offsets[$id] = strlen($pdf);
        $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
    }
    $xrefOffset = strlen($pdf);
    $objectCount = max(array_keys($objects)) + 1;
    $pdf .= "xref\n0 " . $objectCount . "\n0000000000 65535 f \n";
    for ($id = 1; $id < $objectCount; $id++) {
        $pdf .= sprintf('%010d 00000 n ', $offsets[$id] ?? 0) . "\n";
    }
    $pdf .= 'trailer' . "\n<< /Size " . $objectCount . " /Root 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

    $filename = $reportType === 'monthly'
        ? "monthly-sales-$year.pdf"
        : ($reportType === 'stock'
            ? "stock-report-$stockExportFrom-$stockExportTo.pdf"
            : "$reportType-report.pdf");
    log_activity('report.generate', 'Generated ' . $pageTitle . ' (PDF)' . ($reportType === 'monthly' ? ' for ' . $year : ''));
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
}

if (in_array($export, ['monthly', 'stock', 'popular'], true)) {
    $filename = $export === 'monthly'
        ? "monthly-sales-$year.csv"
        : ($export === 'stock'
            ? "stock-report-$stockExportFrom-$stockExportTo.csv"
            : "$export-report.csv");
    log_activity('report.generate', 'Generated ' . $pageTitle . ' (CSV)' . ($reportType === 'monthly' ? ' for ' . $year : ''));
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $writeRow = static function ($out, array $cells): void {
        $safe = array_map(static function ($value) {
            $value = (string)$value;
            return preg_match('/^[=+@\-]/', $value) ? "'" . $value : $value;
        }, $cells);
        // Pass an explicit empty escape character to avoid PHP 8.4's
        // deprecated default and keep warning text out of the CSV response.
        fputcsv($out, $safe, ',', '"', '');
    };
    if ($export === 'monthly') {
        $writeRow($out, ['No.', 'Month', 'Orders', 'Units Sold', 'Sales (' . $reportCurrency . ')']);
        $number = 1;
        foreach ($monthlySales as $month => $totals) {
            $writeRow($out, [$number++, date('F', mktime(0, 0, 0, $month, 1)), $totals['orders'], $totals['units'], number_format($totals['revenue'], 2, '.', '')]);
        }
        $writeRow($out, ['', 'Total', array_sum(array_column($monthlySales, 'orders')), array_sum(array_column($monthlySales, 'units')), number_format(array_sum(array_column($monthlySales, 'revenue')), 2, '.', '')]);
    } elseif ($export === 'stock') {
        $writeRow($out, ['No.', 'Product', 'SKU', 'Category', 'Stock', 'Unit price (' . $reportCurrency . ')', 'Inventory value (' . $reportCurrency . ')', 'Product status']);
        foreach ($stockRows as $index => $product) {
            $writeRow($out, [$index + 1, $product['name'], $product['sku'], $product['category_name'], $product['stock'], $product['sale_price'] ?: $product['price'], $product['inventory_value'], $product['status']]);
        }
        $writeRow($out, ['', 'Total', '', '', array_sum(array_map(static fn($p) => (int)$p['stock'], $stockRows)), '', array_sum(array_map(static fn($p) => (float)$p['inventory_value'], $stockRows)), '']);
    } else {
        $writeRow($out, ['No.', 'Product', 'SKU', 'Category', 'Units Sold', 'Sales (' . $reportCurrency . ')']);
        foreach ($popularRows as $index => $product) {
            $writeRow($out, [$index + 1, $product['product_name'], $product['sku'], $product['category_name'], $product['units_sold'], $product['sales_total']]);
        }
        $writeRow($out, ['', 'Total', '', '', array_sum(array_map(static fn($p) => (int)$p['units_sold'], $popularRows)), array_sum(array_map(static fn($p) => (float)$p['sales_total'], $popularRows))]);
    }
    fclose($out);
    exit;
}

$monthlyRevenue = array_sum(array_column($monthlySales, 'revenue'));
$monthlyOrders = array_sum(array_column($monthlySales, 'orders'));
$monthlyUnits = array_sum(array_column($monthlySales, 'units'));
$stockUnits = (int)($stockSummary['stock_units'] ?? 0);
$stockValue = (float)($stockSummary['inventory_value'] ?? 0);
$popularUnits = array_sum(array_map(static fn($p) => (int)$p['units_sold'], $popularRows));
$popularSales = array_sum(array_map(static fn($p) => (float)$p['sales_total'], $popularRows));

include __DIR__ . '/includes/header.php';
?>

<div class="card mb-3">
  <div class="card-body d-flex flex-wrap align-items-end justify-content-between gap-3">
    <div>
      <h2 class="h5 mb-1"><?= e($pageTitle) ?></h2>
      <p class="text-muted mb-0"><?= $reportType === 'monthly' ? 'Review order count, units sold, and sales by month.' : ($reportType === 'stock' ? 'Review current inventory levels and stock value.' : 'See which mobile products sell the most.') ?></p>
    </div>
    <?php if ($reportType === 'monthly'): ?>
      <form method="get" class="d-flex align-items-end gap-2">
        <div>
          <label class="form-label small text-muted mb-1" for="reportYear">Sales year</label>
          <select class="form-select" id="reportYear" name="year">
            <?php foreach ($years as $availableYear): ?>
              <option value="<?= (int)$availableYear ?>" <?= $year === (int)$availableYear ? 'selected' : '' ?>><?= (int)$availableYear ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="btn btn-primary" type="submit">View year</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php if ($reportType === 'monthly'): ?>
<section class="card mb-3" aria-labelledby="monthlySalesTitle">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h3 class="h5 mb-1" id="monthlySalesTitle">Monthly sales · <?= $year ?></h3>
        <p class="text-muted small mb-0">Cancelled orders are excluded from sales totals.</p>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-primary btn-sm" href="?year=<?= $year ?>&amp;export=monthly"><i class="bi bi-download me-1"></i>Export CSV</a>
        <a class="btn btn-outline-secondary btn-sm" href="?year=<?= $year ?>&amp;export=pdf"><i class="bi bi-file-earmark-pdf me-1"></i>Export PDF</a>
      </div>
    </div>
    <div class="row g-2 mb-3">
      <div class="col-sm-4"><div class="border rounded p-3 h-100"><div class="small text-muted">Sales (<?= e($reportCurrency) ?>)</div><div class="fs-5 fw-semibold"><?= number_format($monthlyRevenue, 2) ?></div></div></div>
      <div class="col-sm-4"><div class="border rounded p-3 h-100"><div class="small text-muted">Orders</div><div class="fs-5 fw-semibold"><?= number_format($monthlyOrders) ?></div></div></div>
      <div class="col-sm-4"><div class="border rounded p-3 h-100"><div class="small text-muted">Units sold</div><div class="fs-5 fw-semibold"><?= number_format($monthlyUnits) ?></div></div></div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>No.</th><th>Month</th><th class="text-end">Orders</th><th class="text-end">Units sold</th><th class="text-end">Sales (<?= e($reportCurrency) ?>)</th></tr></thead>
        <tbody>
          <?php foreach ($monthlySales as $month => $totals): $monthNumber = array_search($month, array_keys($monthlySales), true) + 1; ?>
            <tr><td><?= $monthNumber ?></td><td><?= e(date('F', mktime(0, 0, 0, $month, 1))) ?></td><td class="text-end"><?= number_format($totals['orders']) ?></td><td class="text-end"><?= number_format($totals['units']) ?></td><td class="text-end"><?= number_format($totals['revenue'], 2) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot><tr class="fw-bold"><td></td><td>Total</td><td class="text-end"><?= number_format($monthlyOrders) ?></td><td class="text-end"><?= number_format($monthlyUnits) ?></td><td class="text-end"><?= number_format($monthlyRevenue, 2) ?></td></tr></tfoot>
      </table>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($reportType === 'stock'): ?>
<section class="card mb-3" aria-labelledby="stockReportTitle">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h3 class="h5 mb-1" id="stockReportTitle">Stock report</h3>
        <p class="text-muted small mb-0">Filter by product added date. Stock and inventory values show the current snapshot.</p>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-primary btn-sm" href="?<?= e($stockExportQuery) ?>&amp;export=stock"><i class="bi bi-download me-1"></i>Export CSV</a>
        <a class="btn btn-outline-secondary btn-sm" href="?<?= e($stockExportQuery) ?>&amp;export=pdf"><i class="bi bi-file-earmark-pdf me-1"></i>Export PDF</a>
      </div>
    </div>
    <?php if ($stockDateError !== ''): ?><div class="alert alert-warning py-2" role="alert"><?= e($stockDateError) ?></div><?php endif; ?>
    <form method="get" class="row g-2 align-items-end mb-3" role="search" aria-label="Filter stock report">
      <div class="col-12 col-md-4">
        <label class="form-label small mb-1" for="stockSearch">Search</label>
        <input class="form-control" type="search" id="stockSearch" name="search" value="<?= e($stockSearch) ?>" placeholder="Product, SKU, or category">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small mb-1" for="stockFromDate">From date</label>
        <input class="form-control" type="date" id="stockFromDate" name="from_date" value="<?= e($stockFromDate) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small mb-1" for="stockToDate">To date</label>
        <input class="form-control" type="date" id="stockToDate" name="to_date" value="<?= e($stockToDate) ?>">
      </div>
      <div class="col-6 col-md-2 d-grid">
        <label class="form-label small mb-1 opacity-0" aria-hidden="true">Action</label>
        <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Generate</button>
      </div>
      <div class="col-6 col-md-2 d-grid">
        <label class="form-label small mb-1 opacity-0" aria-hidden="true">Action</label>
        <a class="btn btn-outline-secondary" href="<?= e(admin_url('stock_report.php')) ?>">Clear</a>
      </div>
    </form>
    <p class="small text-muted mb-2"><?= number_format($stockFilteredCount) ?> matching products</p>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>No.</th><th>Product</th><th>SKU</th><th>Category</th><th class="text-end">Stock</th><th>Stock status</th><th class="text-end">Unit price (<?= e($reportCurrency) ?>)</th><th class="text-end">Inventory value (<?= e($reportCurrency) ?>)</th></tr></thead>
        <tbody>
          <?php foreach ($stockRows as $index => $product): $stock = (int)$product['stock']; $stockLabel = $stock <= 0 ? 'Out of stock' : ($stock < 10 ? 'Low stock' : 'In stock'); $stockClass = $stock <= 0 ? 'text-bg-danger' : ($stock < 10 ? 'text-bg-warning' : 'text-bg-success'); ?>
            <tr>
              <td><?= $stockOffset + $index + 1 ?></td>
              <td><a href="<?= e(admin_url('product_form.php?id=' . (int)$product['id'])) ?>" class="text-decoration-none fw-semibold"><?= e($product['name']) ?></a></td>
              <td><?= e($product['sku'] ?: '—') ?></td><td><?= e($product['category_name'] ?: 'Uncategorized') ?></td>
              <td class="text-end"><?= number_format($stock) ?></td><td><span class="badge <?= $stockClass ?>"><?= $stockLabel ?></span></td>
              <td class="text-end"><?= number_format((float)($product['sale_price'] ?: $product['price']), 2) ?></td><td class="text-end"><?= number_format((float)$product['inventory_value'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$stockRows): ?><tr><td colspan="8" class="text-center text-muted py-4">No products found.</td></tr><?php endif; ?>
        </tbody>
        <?php if ($stockFilteredCount): ?><tfoot><tr class="fw-bold"><td></td><td>Total Products</td><td></td><td></td><td class="text-end"><?= number_format($stockUnits) ?></td><td></td><td></td><td class="text-end"><?= number_format($stockValue, 2) ?></td></tr></tfoot><?php endif; ?>
      </table>
    </div>
    <?php if ($stockTotalPages > 1): ?>
      <nav class="mt-3" aria-label="Stock report pages">
        <ul class="pagination pagination-sm justify-content-end mb-0">
          <li class="page-item <?= $stockPage <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= e($stockExportQuery) ?>&amp;page=<?= max(1, $stockPage - 1) ?>" aria-label="Previous">Previous</a>
          </li>
          <?php
          $pageStart = max(1, $stockPage - 2);
          $pageEnd = min($stockTotalPages, $stockPage + 2);
          for ($pageNumber = $pageStart; $pageNumber <= $pageEnd; $pageNumber++):
          ?>
            <li class="page-item <?= $pageNumber === $stockPage ? 'active' : '' ?>">
              <a class="page-link" href="?<?= e($stockExportQuery) ?>&amp;page=<?= $pageNumber ?>" aria-current="<?= $pageNumber === $stockPage ? 'page' : 'false' ?>"><?= $pageNumber ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $stockPage >= $stockTotalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= e($stockExportQuery) ?>&amp;page=<?= min($stockTotalPages, $stockPage + 1) ?>" aria-label="Next">Next</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($reportType === 'popular'): ?>
<section class="card" aria-labelledby="popularProductsTitle">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h3 class="h5 mb-1" id="popularProductsTitle">Popular mobile products</h3>
        <p class="text-muted small mb-0">Ranked by units sold across non-cancelled orders.</p>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-primary btn-sm" href="?year=<?= $year ?>&amp;export=popular"><i class="bi bi-download me-1"></i>Export CSV</a>
        <a class="btn btn-outline-secondary btn-sm" href="?year=<?= $year ?>&amp;export=pdf"><i class="bi bi-file-earmark-pdf me-1"></i>Export PDF</a>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>No.</th><th>Product</th><th>SKU</th><th>Category</th><th class="text-end">Units sold</th><th class="text-end">Sales (<?= e($reportCurrency) ?>)</th></tr></thead>
        <tbody>
          <?php foreach ($popularRows as $index => $product): ?>
            <tr><td><?= $index + 1 ?></td><td class="fw-semibold"><?= e($product['product_name']) ?></td><td><?= e($product['sku'] ?: '—') ?></td><td><?= e($product['category_name']) ?></td><td class="text-end"><?= number_format((int)$product['units_sold']) ?></td><td class="text-end"><?= number_format((float)$product['sales_total'], 2) ?></td></tr>
          <?php endforeach; ?>
          <?php if (!$popularRows): ?><tr><td colspan="6" class="text-center text-muted py-4">No mobile product sales yet.</td></tr><?php endif; ?>
        </tbody>
        <tfoot><tr class="fw-bold"><td></td><td>Total</td><td></td><td></td><td class="text-end"><?= number_format($popularUnits) ?></td><td class="text-end"><?= number_format($popularSales, 2) ?></td></tr></tfoot>
      </table>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
