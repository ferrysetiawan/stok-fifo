<?php

namespace App\Exports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SimpleInventoryExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Inventory::with('bahanBaku')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Bahan Baku',
            'Kategori',
            'Satuan',
            'Qty',
            'Harga',
            'Total'
        ];
    }

    public function map($inventory): array
    {
        return [
            $inventory->id,
            $inventory->bahanBaku->bahan_baku,
            $inventory->bahanBaku->kategori->nama,
            $inventory->bahanBaku->satuan,
            $inventory->stok_awal_bulan,
            $inventory->bahanBaku->harga,
            $inventory->stok_awal_bulan * $inventory->bahanBaku->harga,
        ];
    }
}
