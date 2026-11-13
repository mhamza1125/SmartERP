<?php

namespace App\Exports;

use App\Http\View\Composers\PrintComposer;
use App\Support\InvoiceTotals;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Commercial / proforma invoice as an editable Excel workbook, laid out like the printed invoice
 * (print/delivery-commercial, print/order-proforma) and built from the same data and
 * InvoiceTotals figures. Line totals and sums are live formulas, so edited quantities or prices
 * recalculate; their starting values equal the printed invoice.
 */
class InvoiceSpreadsheet
{
    private const NUMBER = '#,##0.00';

    private const QTY = '#,##0.##';

    private const HEAD_FILL = 'E9EEF5';

    private const LABEL_FILL = 'F4F6F9';

    private Worksheet $sheet;

    private int $row = 1;

    /** Last column letter of the layout (number of item table columns). */
    private string $last;

    private int $columns;

    private function __construct(private Spreadsheet $book, int $columns, string $title)
    {
        $this->columns = $columns;
        $this->last = Coordinate::stringFromColumnIndex($columns);
        $this->sheet = $book->getActiveSheet();
        $this->sheet->setTitle(substr($title, 0, 31));
        $book->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $book->getProperties()->setTitle($title)->setCreator('SmartERP');

        $setup = $this->sheet->getPageSetup();
        $setup->setPaperSize(PageSetup::PAPERSIZE_A4)->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToWidth(1)->setFitToHeight(0);
        $this->sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.4)->setRight(0.4);
        $this->sheet->setShowGridlines(false);
    }

    // =========================================================================================
    // Documents
    // =========================================================================================

    /**
     * @param  array  $data  DeliveryController::commercialInvoiceData()
     */
    public static function commercial(array $data): Spreadsheet
    {
        $delivery = InvoiceTotals::toArray($data['delivery']);
        $items = $data['deliveryItem'];
        $totals = $data['totals'];
        $lineOrders = ! empty($data['showLineOrders']);
        $company = $data['company'] ?? null;

        // Sr | Article No | (Order No) | Product | Size | QTY | Price | Total
        $headers = array_merge(['Sr.', 'Article No'], $lineOrders ? ['Order No'] : [], ['Product', 'Size', 'QTY', 'Price', 'Total']);
        $x = new self(new Spreadsheet(), count($headers), 'Commercial Invoice');
        $x->header($company, 'Commercial Invoice');

        $orderNumbers = (! empty($data['isMultiOrder']) && ! empty($data['relatedOrders']))
            ? collect($data['relatedOrders'])->map(fn ($order) => ((array) $order)['order_no'] ?? '')->filter()->implode(', ')
            : ($delivery['order_no'] ?? 'N/A');
        $x->metaGrid(array_filter([
            'Invoice No' => $delivery['delivery_no'] ?? 'N/A',
            'Invoice Date' => now()->format('d-m-Y'),
            (! empty($data['isMultiOrder']) && ! empty($data['relatedOrders']) ? 'Order Numbers' : 'Order Number') => $orderNumbers,
            'Total QTY' => $x->qtyText($totals['totalQuantity']),
            'Total Packages' => $data['packingList']['carton_count'] ?? null,
            'UOM' => $data['uom'] ?? null,
            'HS Code' => $data['hsCode'] ?? null,
            'FI No' => $data['fiNo'] ?? null,
            'NTN' => $company->ntn ?? null,
            'REX No' => $company->rex_no ?? null,
            'Payment Terms' => $totals['paymentTerms'] ?: null,
            'Payment Requested' => $totals['isPartialPayment'] ? $totals['percentLabel'].' of invoice value' : null,
        ], fn ($value) => $value !== null && $value !== ''));

        $x->party('CUSTOMER', [
            trim(($delivery['fname'] ?? 'N/A').' '.($delivery['lname'] ?? '')),
            ! empty($delivery['tshipping']) ? $delivery['tshipping'] : ($delivery['address'] ?? 'N/A'),
            'Destination Port: '.($delivery['tport_no'] ?? 'N/A'),
        ]);

        $rows = [];
        foreach ($items->values() as $i => $item) {
            $rows[] = array_merge(
                [$i + 1, (string) $item->article_no],
                $lineOrders ? [(string) ($item->line_order_no ?? '-')] : [],
                [(string) $item->name, (string) ($item->hname ?? 'N/A'), (float) $item->quantity, (float) ($item->price ?? 0)]
            );
        }
        [$qtyTotal, $itemsTotal] = $x->itemTable($headers, $rows, 'Items Total');

        $amountCell = $itemsTotal;
        if ($totals['freightCharges'] > 0) {
            $freight = $x->footerLine('Freight Charges', $totals['freightCharges']);
            $amountCell = "{$itemsTotal}+{$freight}";
        }
        $x->row++;
        $x->notes('Description / Notes', $data['orderDescriptions'] ?? []);

        $currency = $totals['currencyName'];
        if ($totals['isPartialPayment']) {
            $value = $x->totalLine('Invoice Value ('.trim($totals['totalLabel'], ':').'):', "={$amountCell}", $currency, false);
            $percent = $totals['paymentPercent'];
            $x->totalLine('Payment Due ('.$totals['percentLabel'].' of Invoice Value):', "=ROUND({$value}*{$percent}/100,2)", $currency, true);
            $x->words($totals['amountDue'], $currency);
        } else {
            $x->totalLine($totals['totalLabel'], "={$amountCell}", $currency, true);
            $x->words($totals['grandTotal'], $currency);
        }

        if (! empty($data['statementOfOrigin'])) {
            $x->textBlock('Statement of Origin', (string) $data['statementOfOrigin']);
        }
        $x->bankDetails($data['bankDetails'] ?? null);
        $x->certification($company);

        return $x->book;
    }

    /**
     * @param  array  $data  OrderController::proformaData()
     */
    public static function proforma(array $data): Spreadsheet
    {
        $order = InvoiceTotals::toArray($data['order']);
        $items = $data['orderItem'];
        $totals = $data['totals'];
        $company = $data['company'] ?? null;

        $headers = ['Sr.', 'Article No', 'Product', 'Size', 'QTY', 'Price', 'Total'];
        $x = new self(new Spreadsheet(), count($headers), 'Proforma Invoice');
        $x->header($company, 'Proforma Invoice');

        $x->metaGrid(array_filter([
            'P-Invoice No' => 'PI-'.($order['order_id'] ?? ''),
            'P-Invoice Date' => formatDate($order['order_date'] ?? null, 'N/A'),
            'Order No' => $order['order_no'] ?? 'N/A',
            'Delivery Date' => ! empty($order['due_date']) ? formatDate($order['due_date']) : null,
            'Total QTY' => $x->qtyText($totals['totalQuantity']),
            'UOM' => $data['uom'] ?? null,
            'HS Code' => $data['hsCode'] ?? null,
            'NTN' => $company->ntn ?? null,
            'REX No' => $company->rex_no ?? null,
            'Payment Terms' => $order['payment_terms'] ?? null,
        ], fn ($value) => $value !== null && $value !== ''));

        $x->party('CUSTOMER', array_filter([
            trim(($order['fname'] ?? 'N/A').' '.($order['lname'] ?? '')),
            $order['address'] ?? null,
        ]));

        $rows = [];
        foreach ($items->values() as $i => $item) {
            $rows[] = [$i + 1, (string) $item->article_no, (string) $item->pname, (string) $item->name, (float) $item->quantity, (float) ($item->price ?? 0)];
        }
        [, $itemsTotal] = $x->itemTable($headers, $rows, 'Total');
        $x->row++;

        $description = trim(strip_tags((string) ($order['description'] ?? '')));
        $x->notes('Description / Notes', $description !== '' ? [$description] : []);

        $x->totalLine($totals['totalLabel'], "={$itemsTotal}", $totals['currencyName'], true);
        $x->words($totals['grandTotal'], $totals['currencyName']);
        $x->bankDetails($data['bankDetails'] ?? null);
        $x->certification($company);

        return $x->book;
    }

    public static function download(Spreadsheet $book, string $filename): StreamedResponse
    {
        return new StreamedResponse(function () use ($book) {
            (new Xlsx($book))->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0, no-cache, no-store',
        ]);
    }

    // =========================================================================================
    // Sections
    // =========================================================================================

    /** Logo + company name / address left, contacts right, document title below. */
    private function header($company, string $title): void
    {
        $logo = $this->logoPath($company);
        $textFrom = 'A';
        if ($logo) {
            $drawing = new Drawing();
            $drawing->setPath($logo)->setHeight(52)->setCoordinates('A1')->setOffsetX(4)->setOffsetY(4)->setWorksheet($this->sheet);
            $textFrom = 'C';
        }
        $mid = Coordinate::stringFromColumnIndex(max(3, $this->columns - 2));
        $right = Coordinate::stringFromColumnIndex($this->columns - 1);

        $this->sheet->setCellValue($textFrom.'1', (string) ($company->name ?? ''));
        $this->sheet->mergeCells("{$textFrom}1:{$mid}1");
        $this->sheet->getStyle($textFrom.'1')->getFont()->setBold(true)->setSize(14);
        $this->sheet->setCellValue($textFrom.'2', (string) ($company->address ?? ''));
        $this->sheet->mergeCells("{$textFrom}2:{$mid}3");
        $this->sheet->getStyle($textFrom.'2')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        // Same contact lines as the print header (placeholders such as "." are left out)
        $known = PrintComposer::contacts($company);
        $contacts = array_values(array_filter([
            $known['phone'] ? 'Phone: '.$known['phone'] : null,
            $known['email'] ? 'Email: '.$known['email'] : null,
            $known['website'] ? 'Web: '.$known['website'] : null,
        ]));
        foreach ($contacts as $i => $line) {
            $cell = $right.($i + 1);
            $this->sheet->setCellValue($cell, $line);
            $this->sheet->mergeCells($cell.':'.$this->last.($i + 1));
            $this->sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $this->sheet->getStyle($cell)->getFont()->setSize(9);
        }
        foreach ([1, 2, 3] as $r) {
            $this->sheet->getRowDimension($r)->setRowHeight(15);
        }

        $this->row = 5;
        $this->sheet->setCellValue('A5', strtoupper($title));
        $this->sheet->mergeCells("A5:{$this->last}5");
        $this->sheet->getStyle('A5')->getFont()->setBold(true)->setSize(13);
        $this->sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->sheet->getStyle("A4:{$this->last}4")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $this->row = 7;
    }

    /** Label / value boxes, two per row (like the boxed meta band of the print). */
    private function metaGrid(array $fields): void
    {
        $half = intdiv($this->columns, 2);
        $pairs = array_chunk($fields, 2, true);
        foreach ($pairs as $pair) {
            $col = 1;
            foreach ($pair as $label => $value) {
                $labelCell = Coordinate::stringFromColumnIndex($col).$this->row;
                $valueFrom = Coordinate::stringFromColumnIndex($col + 1);
                $valueTo = Coordinate::stringFromColumnIndex($col === 1 ? $half : $this->columns);
                $this->sheet->setCellValue($labelCell, $label);
                $this->sheet->setCellValueExplicit($valueFrom.$this->row, (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                if ($valueFrom !== $valueTo) {
                    $this->sheet->mergeCells("{$valueFrom}{$this->row}:{$valueTo}{$this->row}");
                }
                $this->sheet->getStyle($labelCell)->getFont()->setBold(true)->setSize(9);
                $this->fill($labelCell, self::LABEL_FILL);
                $this->sheet->getStyle(Coordinate::stringFromColumnIndex($col).$this->row.":{$valueTo}{$this->row}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('C9D1DC');
                $this->sheet->getStyle("{$valueFrom}{$this->row}")->getAlignment()->setWrapText(true);
                $col = $half + 1;
            }
            $this->row++;
        }
        $this->row++;
    }

    private function party(string $label, array $lines): void
    {
        $this->sheet->setCellValue('A'.$this->row, $label);
        $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('555555');
        $this->row++;
        foreach (array_values($lines) as $i => $line) {
            $cell = 'A'.$this->row;
            $this->sheet->setCellValue($cell, (string) $line);
            $this->sheet->mergeCells("{$cell}:{$this->last}{$this->row}");
            $this->sheet->getStyle($cell)->getAlignment()->setWrapText(true);
            if ($i === 0) {
                $this->sheet->getStyle($cell)->getFont()->setBold(true)->setSize(11);
            }
            $this->row++;
        }
        $this->row++;
    }

    /**
     * Items table: the last three columns are QTY, Price and Total (= QTY x Price formula),
     * with a footer line summing QTY and Total.
     *
     * @return array{0: string, 1: string} cells holding the QTY total and the amount total
     */
    private function itemTable(array $headers, array $rows, string $footerLabel): array
    {
        $qtyCol = Coordinate::stringFromColumnIndex($this->columns - 2);
        $priceCol = Coordinate::stringFromColumnIndex($this->columns - 1);
        $totalCol = $this->last;

        foreach ($headers as $i => $header) {
            $this->sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1).$this->row, $header);
        }
        $headRange = "A{$this->row}:{$totalCol}{$this->row}";
        $this->sheet->getStyle($headRange)->getFont()->setBold(true);
        $this->fill($headRange, self::HEAD_FILL);
        $this->sheet->getStyle("{$qtyCol}{$this->row}:{$totalCol}{$this->row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $this->sheet->getStyle("A{$this->row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $tableTop = $this->row;
        $this->row++;
        $first = $this->row;

        foreach ($rows as $values) {
            foreach ($values as $i => $value) {
                $cell = Coordinate::stringFromColumnIndex($i + 1).$this->row;
                if (is_string($value)) {
                    $this->sheet->setCellValueExplicit($cell, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                } else {
                    $this->sheet->setCellValue($cell, $value);
                }
            }
            $this->sheet->setCellValue("{$totalCol}{$this->row}", "={$qtyCol}{$this->row}*{$priceCol}{$this->row}");
            $this->row++;
        }
        $lastRow = max($first, $this->row - 1);
        if ($rows) {
            $this->sheet->getStyle("{$qtyCol}{$first}:{$qtyCol}{$lastRow}")->getNumberFormat()->setFormatCode(self::QTY);
            $this->sheet->getStyle("{$priceCol}{$first}:{$totalCol}{$lastRow}")->getNumberFormat()->setFormatCode(self::NUMBER);
            $this->sheet->getStyle("A{$first}:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $this->sheet->getStyle("A{$first}:{$totalCol}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        }

        // Footer: label | QTY sum | blank | Total sum
        $labelTo = Coordinate::stringFromColumnIndex($this->columns - 3);
        $this->sheet->setCellValue("A{$this->row}", $footerLabel);
        $this->sheet->mergeCells("A{$this->row}:{$labelTo}{$this->row}");
        $this->sheet->getStyle("A{$this->row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $this->sheet->setCellValue("{$qtyCol}{$this->row}", $rows ? "=SUM({$qtyCol}{$first}:{$qtyCol}{$lastRow})" : 0);
        $this->sheet->setCellValue("{$totalCol}{$this->row}", $rows ? "=SUM({$totalCol}{$first}:{$totalCol}{$lastRow})" : 0);
        $this->sheet->getStyle("{$qtyCol}{$this->row}")->getNumberFormat()->setFormatCode(self::QTY);
        $this->sheet->getStyle("{$totalCol}{$this->row}")->getNumberFormat()->setFormatCode(self::NUMBER);
        $this->sheet->getStyle("A{$this->row}:{$totalCol}{$this->row}")->getFont()->setBold(true);
        $this->fill("A{$this->row}:{$totalCol}{$this->row}", self::LABEL_FILL);
        $this->sheet->getStyle("A{$tableTop}:{$totalCol}{$this->row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('B8C2CF');
        $cells = ["{$qtyCol}{$this->row}", "{$totalCol}{$this->row}"];
        $this->row++;

        $this->autoWidths($headers);

        return $cells;
    }

    /** An extra amount line under the items table (e.g. freight); returns its cell. */
    private function footerLine(string $label, float $amount): string
    {
        $labelTo = Coordinate::stringFromColumnIndex($this->columns - 1);
        $this->sheet->setCellValue("A{$this->row}", $label);
        $this->sheet->mergeCells("A{$this->row}:{$labelTo}{$this->row}");
        $this->sheet->getStyle("A{$this->row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $cell = "{$this->last}{$this->row}";
        $this->sheet->setCellValue($cell, $amount);
        $this->sheet->getStyle($cell)->getNumberFormat()->setFormatCode(self::NUMBER);
        $this->sheet->getStyle("A{$this->row}:{$this->last}{$this->row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('B8C2CF');
        $this->row++;

        return $cell;
    }

    /** Label + amount (formula) + currency, right aligned; returns the amount cell. */
    private function totalLine(string $label, string $formula, string $currency, bool $grand): string
    {
        $labelTo = Coordinate::stringFromColumnIndex($this->columns - 2);
        $amountCol = Coordinate::stringFromColumnIndex($this->columns - 1);
        $this->sheet->setCellValue("A{$this->row}", $label);
        $this->sheet->mergeCells("A{$this->row}:{$labelTo}{$this->row}");
        $this->sheet->getStyle("A{$this->row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $cell = "{$amountCol}{$this->row}";
        $this->sheet->setCellValue($cell, $formula);
        $this->sheet->getStyle($cell)->getNumberFormat()->setFormatCode(self::NUMBER);
        $this->sheet->setCellValue("{$this->last}{$this->row}", $currency);
        $range = "A{$this->row}:{$this->last}{$this->row}";
        $this->sheet->getStyle($range)->getFont()->setBold(true)->setSize($grand ? 12 : 10);
        if ($grand) {
            $this->fill($range, self::HEAD_FILL);
            $this->sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
            $this->sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        }
        $this->row++;

        return $cell;
    }

    private function words(float $amount, string $currency): void
    {
        if (! function_exists('numberToWordsWithCurrency')) {
            return;
        }
        $this->sheet->setCellValue("A{$this->row}", 'Amount in words: '.numberToWordsWithCurrency($amount).' '.$currency);
        $this->sheet->mergeCells("A{$this->row}:{$this->last}{$this->row}");
        $this->sheet->getStyle("A{$this->row}")->getFont()->setItalic(true)->setSize(9);
        $this->sheet->getStyle("A{$this->row}")->getAlignment()->setWrapText(true);
        $this->row += 2;
    }

    /** Order description(s): one note per order (keyed by order number when several). */
    private function notes(string $title, $notes): void
    {
        $notes = collect($notes)->filter(fn ($text) => trim((string) $text) !== '');
        if ($notes->isEmpty()) {
            return;
        }
        $many = $notes->count() > 1;
        $text = $notes->map(fn ($note, $key) => ($many ? $key.': ' : '').trim((string) $note))->implode("\n");
        $this->textBlock($title, $text);
    }

    private function textBlock(string $title, string $text): void
    {
        $this->sheet->setCellValue("A{$this->row}", $title);
        $this->sheet->getStyle("A{$this->row}")->getFont()->setBold(true);
        $this->row++;
        $cell = "A{$this->row}";
        $this->sheet->setCellValue($cell, $text);
        $this->sheet->mergeCells("{$cell}:{$this->last}{$this->row}");
        $this->sheet->getStyle($cell)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $lines = max(1, substr_count($text, "\n") + 1 + intdiv(mb_strlen($text), 110));
        $this->sheet->getRowDimension($this->row)->setRowHeight(min(400, 14 * $lines));
        $this->sheet->getStyle($cell.":{$this->last}{$this->row}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('C9D1DC');
        $this->row += 2;
    }

    private function bankDetails(?array $bank): void
    {
        if (empty($bank)) {
            return;
        }
        $fields = array_filter([
            'Bank Name' => $bank['bank_name'] ?? null,
            'Account Title' => $bank['account_title'] ?? null,
            'Account Number' => $bank['account'] ?? null,
            'IBAN' => $bank['iban'] ?? null,
            'SWIFT Code' => $bank['swift_code'] ?? null,
            'Branch Code' => $bank['branch_code'] ?? null,
            'Bank Address' => $bank['address'] ?? null,
        ], fn ($value) => trim((string) $value) !== '');
        if (! $fields) {
            return;
        }
        $this->sheet->setCellValue("A{$this->row}", 'Bank Account Details');
        $this->sheet->getStyle("A{$this->row}")->getFont()->setBold(true);
        $this->row++;
        $top = $this->row;
        foreach ($fields as $label => $value) {
            $this->sheet->setCellValue("A{$this->row}", $label);
            $this->sheet->mergeCells("A{$this->row}:B{$this->row}");
            $this->sheet->getStyle("A{$this->row}")->getFont()->setBold(true);
            $this->fill("A{$this->row}", self::LABEL_FILL);
            $this->sheet->setCellValueExplicit("C{$this->row}", (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $this->sheet->mergeCells("C{$this->row}:{$this->last}{$this->row}");
            $this->row++;
        }
        $this->sheet->getStyle("A{$top}:{$this->last}".($this->row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('C9D1DC');
        $this->row++;
    }

    private function certification($company): void
    {
        $name = trim((string) ($company->name ?? ''));
        $this->sheet->setCellValue("A{$this->row}", 'CERTIFIED TO BE TRUE AND CORRECT'.($name !== '' ? ' "'.strtoupper($name).'"' : ''));
        $this->sheet->mergeCells("A{$this->row}:{$this->last}{$this->row}");
        $this->sheet->getStyle("A{$this->row}")->getFont()->setBold(true);
        $this->sheet->getStyle("A{$this->row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->row += 3;
        // Signature line
        $from = Coordinate::stringFromColumnIndex(max(1, $this->columns - 2));
        $this->sheet->getStyle("{$from}{$this->row}:{$this->last}{$this->row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $this->sheet->setCellValue("{$from}{$this->row}", 'Authorized Signature');
        $this->sheet->mergeCells("{$from}{$this->row}:{$this->last}{$this->row}");
        $this->sheet->getStyle("{$from}{$this->row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->sheet->getPageSetup()->setPrintArea("A1:{$this->last}{$this->row}");
    }

    // =========================================================================================
    // Helpers
    // =========================================================================================

    private function autoWidths(array $headers): void
    {
        $widths = ['Sr.' => 6, 'Article No' => 14, 'Order No' => 16, 'Product' => 34, 'Size' => 16, 'QTY' => 11, 'Price' => 14, 'Total' => 17];
        foreach ($headers as $i => $header) {
            $this->sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($widths[$header] ?? 14);
        }
    }

    private function fill(string $range, string $rgb): void
    {
        $this->sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rgb);
    }

    private function qtyText(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 2), '0'), '.');
    }

    /** Local file of the company logo (same choice as printLogoUrl()), if it is an image PhpSpreadsheet can embed. */
    private function logoPath($company): ?string
    {
        foreach ([$company->logo_path ?? null, ! empty($company->logo) ? 'storage/'.$company->logo : null, 'assets/print-logo.png'] as $path) {
            $path = ltrim((string) $path, '/');
            $file = $path !== '' ? public_path($path) : null;
            if ($file && is_file($file) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'gif'], true)) {
                return $file;
            }
        }

        return null;
    }
}
