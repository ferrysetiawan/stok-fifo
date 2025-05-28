<?php

namespace App\Exports;

use App\Models\StokMasuk;
use App\Models\StokKeluar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Support\Responsable;

class StokExport implements FromCollection, WithHeadings
{
    private $startDate;
    private $endDate;
    private $fileName = 'laporan_stok.xlsx';

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $no = 1;

        $masuk = StokMasuk::with('bahanBaku')
            ->whereBetween('tanggal_masuk', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($item) use (&$no) {
                return [
                    'No' => $no++,
                    'Nama Bahan' => $item->bahanBaku->bahan_baku ?? '',
                    // 'Nama Produk' => $item->bahanBaku->produk ?? '',
                    // 'Merek dan Produsen' => $item->bahanBaku->merek ?? '',
                    'Tanggal Masuk' => $item->tanggal_masuk,
                    'Tanggal Keluar' => '',
                    'Jumlah' => $item->qty . ' ' . ($item->bahanBaku->satuan ?? ''),
                    'Penanggung Jawab' => 'Sar bini',
                    'tanggal_sort' => $item->tanggal_masuk . '-1',
                ];
            });

        $keluar = StokKeluar::with('bahanBaku')
            ->whereBetween('tanggal_keluar', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($item) use (&$no) {
                return [
                    'No' => $no++,
                    'Nama Bahan' => $item->bahanBaku->bahan_baku ?? '',
                    // 'Nama Produk' => $item->bahanBaku->produk ?? '',
                    // 'Merek dan Produsen' => $item->bahanBaku->merek ?? '',
                    'Tanggal Masuk' => '',
                    'Tanggal Keluar' => $item->tanggal_keluar,
                    'Jumlah' => $item->jumlah . ' ' . ($item->bahanBaku->satuan ?? ''),
                    'Penanggung Jawab' => 'Sar bini',
                    'tanggal_sort' => $item->tanggal_keluar . '-2',
                ];
            });

        // Gabung & urutkan
        $data = $masuk->merge($keluar)
            ->sortBy('tanggal_sort')
            ->values()
            ->map(function ($item) {
                unset($item['tanggal_sort']);
                return $item;
            });

        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Bahan',
            'Tanggal Masuk',
            'Tanggal Keluar',
            'Jumlah',
            'Penanggung Jawab'
        ];
    }
}
