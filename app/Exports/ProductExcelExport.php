<?php

namespace App\Exports;

use App\Models\ProductExcel;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ProductExcelExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = DB::table('category as c')
            ->join('products as p', 'c.id', '=', 'p.category_id')
            ->join('brand as b', 'b.id', '=', 'p.brand_id')
            ->select(
                'p.id',
                'c.category_name',
                'b.brand_name',
                'p.product_name',
                'p.price',
                'p.quantity',
                'p.packaging',
                'p.stock',
                'p.stock_pc',
            )        
            ->where('p.disabled', 0)
             ->where('p.stock', '>', 0)
            ->orderBy('c.category_name', 'asc')
            ->get();

        return $data;
    }

    /**
     * Define Excel header row
     */
    public function headings(): array
    {
        return [
            'ID',
            'Category Name',
            'Brand Name',
            'Product Name',
            'Price',
            'Quantity',
            'Packaging',
            'Stock',
            'Stock (PC)',
        ];
    }
}
