<?php

namespace App\Exports;

use App\Models\StokMasuk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Support\Responsable;

class StokPembalianExport implements FromCollection, WithHeadings
{
    private $startDate;
    private $endDate;
    private $fileName = 'laporan_stok_masuk.xlsx';

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $no = 1;

        return StokMasuk::with('bahanBaku')
            ->whereBetween('tanggal_masuk', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($item) use (&$no) {
                return [
                    'No' => $no++,
                    'Nama Bahan / Merk Bahan' => $item->bahanBaku->bahan_baku ?? '' . ' ' . ($item->bahanBaku->merek ?? ''),
                    'Jumlah' => $item->qty . ' ' . ($item->bahanBaku->satuan ?? ''),
                    'Waktu Pembelian' => $item->tanggal_masuk,
                    'Penanggung Jawab' => 'AAN', // bisa disesuaiin
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Bahan / Merk Bahan',
            'Jumlah',
            'Waktu Pembelian',
            'Penanggung Jawab'
        ];
    }
}
