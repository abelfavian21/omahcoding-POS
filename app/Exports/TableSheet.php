<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Sheet tabel rapi: judul (baris 1), periode (baris 2), header berwarna (baris 4),
 * data mulai baris 5, border, freeze header, kolom auto-width, baris TOTAL opsional.
 */
class TableSheet extends DefaultValueBinder implements FromArray, WithTitle, WithStyles, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder
{
    /**
     * Teks yang diawali "=" HANYA dianggap rumus pada baris TOTAL (rumus SUM milik kita).
     * Di sel lain (nama pelanggan, produk, catatan) selalu disimpan sebagai teks,
     * sehingga input berbahaya seperti =HYPERLINK(...) tidak dieksekusi saat pemilik membuka Excel.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        $isTotalsRow = $this->totals && $cell->getRow() === 5 + count($this->rows);

        if (is_string($value) && $value !== '' && $value[0] === '=' && ! $isTotalsRow) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function __construct(
        private string $title,
        private string $heading,
        private string $period,
        private array $headers,
        private array $rows,
        private array $formats = [],
        private ?array $totals = null,
    ) {}

    public function array(): array
    {
        $out = [[$this->heading], [$this->period], [], $this->headers];
        foreach ($this->rows as $row) $out[] = $row;
        if ($this->totals) $out[] = $this->totals;

        return $out;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function columnFormats(): array
    {
        return $this->formats;
    }

    public function styles(Worksheet $sheet)
    {
        $last = Coordinate::stringFromColumnIndex(count($this->headers));
        $end = 4 + count($this->rows) + ($this->totals ? 1 : 0);

        $sheet->mergeCells("A1:{$last}1");
        $sheet->mergeCells("A2:{$last}2");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->getColor()->setRGB('64748B');

        $sheet->getStyle("A4:{$last}4")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(22);

        $sheet->getStyle("A4:{$last}{$end}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

        if ($this->totals) {
            $sheet->getStyle("A{$end}:{$last}{$end}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
            ]);
        }

        $sheet->freezePane('A5');

        return [];
    }
}
