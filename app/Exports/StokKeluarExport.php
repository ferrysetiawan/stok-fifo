<?php

namespace App\Exports;

use App\Models\StokKeluar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StokKeluarExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return StokKeluar::with('bahanBaku')
            ->whereBetween('tanggal_keluar', [$this->startDate, $this->endDate])
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Keluar',
            'Nama Bahan Baku',
            'Satuan',
            'Qty',
            'Harga',
            'Total'
        ];
    }

    public function map($stokKeluar): array
    {
        return [
            $stokKeluar->id,
            $stokKeluar->tanggal_keluar,
            $stokKeluar->bahanBaku->bahan_baku,
            $stokKeluar->bahanBaku->satuan,
            $stokKeluar->jumlah,
            $stokKeluar->bahanBaku->harga,
            $stokKeluar->jumlah * $stokKeluar->bahanBaku->harga,
        ];
    }
}
