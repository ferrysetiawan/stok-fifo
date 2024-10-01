<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StokKeluarExport implements FromArray, WithStyles, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $currentRow = 1; // Baris dimulai dari 1

                foreach ($this->data as $index => $row) {
                    // Jika row 'nama_barang' mengandung teks 'Tanggal:'
                    if (stripos($row['nama_barang'], 'Tanggal:') !== false) {
                        // Merge dari kolom A sampai F untuk header tanggal
                        $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
                        $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFFFC000'], // Warna header tanggal
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            ],
                        ]);
                    }

                    // Jika row 'nama_barang' adalah 'Jumlah' (subtotal)
                    if ($row['nama_barang'] === 'Jumlah') {
                        // Merge dari kolom A sampai E untuk subtotal
                        $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
                        $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFFFFF00'], // Warna subtotal
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                            ],
                        ]);
                    }

                    // Pindah ke baris berikutnya
                    $currentRow++;
                }

                // Mengatur lebar kolom
                $sheet->getColumnDimension('A')->setWidth(5);   // No
                $sheet->getColumnDimension('B')->setWidth(25);  // Nama Barang
                $sheet->getColumnDimension('C')->setWidth(10);  // Unit
                $sheet->getColumnDimension('D')->setWidth(10);  // Qty
                $sheet->getColumnDimension('E')->setWidth(15);  // Harga Satuan
                $sheet->getColumnDimension('F')->setWidth(20);  // Total

                // Mengatur border untuk seluruh tabel
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A1:F{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Mengatur format untuk kolom harga satuan dan total
                $sheet->getStyle("E1:F{$highestRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0.00');
            },
        ];
    }

    // Tidak perlu mengatur styles di sini, karena sudah ditangani di AfterSheet
    public function styles(Worksheet $sheet)
    {
        return [];
    }
}
