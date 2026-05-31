<?php

namespace App\Exports;

use App\Models\ProductExcel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;


use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;


class PriceListExport implements FromCollection, WithHeadings, WithStyles, WithEvents, ShouldAutoSize, WithCustomStartCell, WithDrawings
{
    private $categoryRows = [];
    private $headingRow = 10;
    private $shop = null;

    public function collection()
    {
        $data = DB::table('mark_up_product as mup')
            ->join('products as p', 'mup.product_id', '=', 'p.id')
            ->join('category as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('brand as br', 'p.brand_id', '=', 'br.id')
            ->select(
                'c.category_name',
                'br.brand_name',
                'p.product_name',
                'mup.new_price',
                'p.quantity',
                'p.packaging',
                'p.variation',
                'p.weight',
                'mup.business_type'
            )
            ->selectRaw("
                CASE 
                    WHEN mup.business_type = 'WHOLESALE' 
                    THEN p.stock 
                    ELSE p.stock_pc 
                END as stock
            ")
            ->where('mup.status', 1)
            ->where('p.disabled', 0)
            ->orderByRaw('c.ordering IS NULL, c.ordering = 0, c.ordering ASC')
            ->orderBy('c.category_name', 'asc')
            ->orderBy('br.brand_name', 'asc')
            ->orderBy('p.product_name', 'asc')
            ->get()
            ->groupBy('category_name');

        $rows = collect();
        $rowNumber = $this->headingRow + 1;

        foreach ($data as $categoryName => $items) {
            $rows->push([
                $categoryName,
                '',
                '',
                '',
                '',
            ]);

            $this->categoryRows[] = $rowNumber;
            $rowNumber++;

            foreach ($items as $item) {
                $rows->push([
                    $item->business_type == 'WHOLESALE'
                        ? $item->product_name . ' ' . $item->packaging
                        : $item->product_name,

                    $item->brand_name ?? '',

                    number_format($item->new_price, 2),

                    $item->business_type == 'WHOLESALE'
                        ? $item->quantity . ' x ' . ($item->weight / $item->quantity) . $item->variation
                        : ($item->weight / $item->quantity) . $item->variation,

                    $item->stock == 0 ? 'OUT OF STOCK' : $item->stock,
                ]);

                $rowNumber++;
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Brand Name',
            'Price',
            'Quantity',
            'Stock',
        ];
    }

    public function startCell(): string
    {
        return "A{$this->headingRow}";
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('MDR Logo');
        $drawing->setDescription('MDR Logo');
        $drawing->setPath(public_path('img/MDR_LOGO.jpg'));
        $drawing->setHeight(150);
        $drawing->setCoordinates('B1');
        $drawing->setOffsetX(30);
        $drawing->setOffsetY(5);

        return $drawing;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            $this->headingRow => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F2937'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $shop = $this->getActiveShop();

                $sheet->mergeCells('A1:E4');
                $sheet->mergeCells('A5:E5');
                $sheet->mergeCells('A6:E6');
                $sheet->mergeCells('A7:E7');
                $sheet->mergeCells('A8:E8');

                $sheet->getRowDimension(1)->setRowHeight(35);
                $sheet->getRowDimension(2)->setRowHeight(35);
                $sheet->getRowDimension(3)->setRowHeight(35);
                $sheet->getRowDimension(4)->setRowHeight(35);
                $sheet->getRowDimension(5)->setRowHeight(24);
                $sheet->getRowDimension(6)->setRowHeight(19);
                $sheet->getRowDimension(7)->setRowHeight(30);
                $sheet->getRowDimension(8)->setRowHeight(22);
                $sheet->getRowDimension(9)->setRowHeight(8);

                $sheet->setCellValue('A5', $shop ? $shop->shop_name : '');
                $sheet->setCellValue('A6', 'Contact Number: ' . ($shop ? $shop->contact_number : ''));
                $sheet->setCellValue('A7', 'Address: ' . ($shop ? $shop->address : ''));
                $sheet->setCellValue('A8', 'Updated Price List as of ' . now()->format('F d, Y'));

                $sheet->getStyle('A1:E9')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFFFF'],
                    ],
                ]);

                $sheet->getStyle('A5:E5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'color' => ['rgb' => '7F1D1D'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A6:E7')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => '111827'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getStyle('A8:E8')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'italic' => true,
                        'size' => 12,
                        'color' => ['rgb' => '800000'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEE2E2'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A1:E9')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension($this->headingRow)->setRowHeight(22);

                $sheet->freezePane('A' . ($this->headingRow + 1));

                $sheet->getStyle('A:E')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('C:C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('E:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$this->headingRow}:E{$this->headingRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle("A{$this->headingRow}:E{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);

                foreach ($this->categoryRows as $row) {
                    $sheet->mergeCells("A{$row}:E{$row}");

                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'FFFFFF'],
                            'size' => 12,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '800000'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                        ],
                    ]);
                }

                for ($row = $this->headingRow + 1; $row <= $highestRow; $row++) {
                    $stock = $sheet->getCell("E{$row}")->getValue();

                    if ($stock === 'OUT OF STOCK') {
                        $sheet->getStyle("E{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'DC2626'],
                            ],
                        ]);
                    }
                }
            },
        ];
    }

    private function getActiveShop()
    {
        if ($this->shop === null) {
            $this->shop = DB::table('shop')
                ->select('shop_name', 'shop_type_id', 'contact_number', 'address', 'status')
                ->where('status', 1)
                ->first();
        }

        return $this->shop;
    }
}
