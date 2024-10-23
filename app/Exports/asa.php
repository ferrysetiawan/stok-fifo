<?php

namespace App\Exports;

use App\Models\StokKeluar;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StokKeluarExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $month;
    protected $year;
    protected $stockReports;

    /**
     * Constructor untuk menerima bulan dan tahun filter.
     */
    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;

        // Ambil data stok keluar berdasarkan bulan dan tahun
        $this->stockReports = StokKeluar::with('bahanBaku.kategori')
            ->whereMonth('tanggal_keluar', $this->month)
            ->whereYear('tanggal_keluar', $this->year)
            ->orderBy('tanggal_keluar')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->tanggal_keluar)->format('d F Y'); // Mengelompokkan berdasarkan tanggal
            });
    }

    /**
     * Mengembalikan koleksi stok keluar untuk di-export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->stockReports->flatten(1); // Flatten data agar bisa digunakan oleh Excel
    }

    /**
     * Kosongkan heading default karena heading ditangani per tanggal.
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * Mapping data stok keluar ke dalam format Excel.
     *
     * @param mixed $stokKeluar
     * @return array
     */
    public function map($stokKeluar): array
    {
        // Hitung total harga (jumlah * harga satuan)
        $totalHarga = $stokKeluar->jumlah * $stokKeluar->bahanBaku->harga;

        // Tentukan kolom yang akan diisi berdasarkan kategori bahan baku
        $kategori = $stokKeluar->bahanBaku->kategori->nama;

        // Default semua kolom ke 0
        $dapur = 0;
        $bar = 0;
        $operasional = 0;

        // Set kolom sesuai kategori
        if ($kategori === 'Dapur') {
            $dapur = $totalHarga;
        } elseif ($kategori === 'Bar') {
            $bar = $totalHarga;
        } elseif ($kategori === 'Operasional') {
            $operasional = $totalHarga;
        }

        // Return data format Excel
        return [
            $stokKeluar->id,  // NO
            $stokKeluar->bahanBaku->bahan_baku,  // NAMA BARANG
            $stokKeluar->bahanBaku->satuan,  // UNIT
            $stokKeluar->jumlah,  // QTY
            $stokKeluar->bahanBaku->harga,  // HARGA SATUAN
            $dapur,  // Kolom DAPUR
            $bar,  // Kolom BAR
            $operasional,  // Kolom OPERASIONAL
            $totalHarga,  // TOTAL
        ];
    }

    /**
     * Styling untuk worksheet (misalnya untuk heading tanggal dan header kolom).
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $currentRow = 1; // Mulai dari baris 1

        // Atur lebar kolom
        $sheet->getColumnDimension('A')->setWidth(5);  // Kolom No
        $sheet->getColumnDimension('B')->setWidth(25); // Kolom Nama Barang
        $sheet->getColumnDimension('C')->setWidth(10); // Kolom Unit
        $sheet->getColumnDimension('D')->setWidth(10); // Kolom Qty
        $sheet->getColumnDimension('E')->setWidth(15); // Kolom Harga Satuan
        $sheet->getColumnDimension('F')->setWidth(15); // Kolom Dapur
        $sheet->getColumnDimension('G')->setWidth(15); // Kolom Bar
        $sheet->getColumnDimension('H')->setWidth(15); // Kolom Operasional
        $sheet->getColumnDimension('I')->setWidth(15); // Kolom Total

        foreach ($this->stockReports as $date => $stokItems) {
            // Tambahkan heading tanggal di setiap kelompok tanggal
            $sheet->setCellValue("A{$currentRow}", $date); // Tanggal di kolom A
            $sheet->mergeCells("A{$currentRow}:I{$currentRow}"); // Gabungkan dari kolom A sampai I
            $styles[$currentRow] = [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => 'center'], // Rata tengah
            ];

            $currentRow++; // Pindah ke row berikutnya untuk header barang

            // Tambahkan header kolom
            $sheet->setCellValue("A{$currentRow}", 'No');
            $sheet->mergeCells("A{$currentRow}:A" . ($currentRow + 1));
            $sheet->setCellValue("B{$currentRow}", 'Nama Barang');
            $sheet->mergeCells("B{$currentRow}:B" . ($currentRow + 1));
            $sheet->setCellValue("C{$currentRow}", 'Unit');
            $sheet->mergeCells("C{$currentRow}:C" . ($currentRow + 1));
            $sheet->setCellValue("D{$currentRow}", 'Qty');
            $sheet->mergeCells("D{$currentRow}:D" . ($currentRow + 1));
            $sheet->setCellValue("E{$currentRow}", 'Harga Satuan');
            $sheet->mergeCells("E{$currentRow}:E" . ($currentRow + 1));
            $sheet->mergeCells("F{$currentRow}:H{$currentRow}"); // Merge kolom Dapur, Bar, Operasional
            $sheet->setCellValue("F{$currentRow}", 'Total Harga Berdasarkan Kategori');
            $sheet->setCellValue("F" . ($currentRow + 1), 'Dapur');
            $sheet->setCellValue("G" . ($currentRow + 1), 'Bar');
            $sheet->setCellValue("H" . ($currentRow + 1), 'Operasional');
            $sheet->mergeCells("I{$currentRow}:I" . ($currentRow + 1));
            $sheet->setCellValue("I{$currentRow}", 'Total');

            // Tambahkan style untuk header kolom
            $sheet->getStyle("A{$currentRow}:I" . ($currentRow + 1))->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'], // Rata tengah secara horizontal dan vertikal
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFF00'], // Warna background kuning
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

            $currentRow += 2; // Pindah ke row berikutnya untuk data barang, setelah header selesai (increment dengan 2)

            $subtotalDapur = 0;
            $subtotalBar = 0;
            $subtotalOperasional = 0;
            $subtotalTotal = 0;

            // Iterasi data barang untuk setiap tanggal
            foreach ($stokItems as $stokKeluar) {
                // Map data barang ke Excel
                $sheet->fromArray($this->map($stokKeluar), null, "A{$currentRow}");

                // Update subtotal berdasarkan kategori
                $totalHarga = $stokKeluar->jumlah * $stokKeluar->bahanBaku->harga;
                $kategori = $stokKeluar->bahanBaku->kategori->nama;

                if ($kategori === 'Dapur') {
                    $subtotalDapur += $totalHarga;
                } elseif ($kategori === 'Bar') {
                    $subtotalBar += $totalHarga;
                } elseif ($kategori === 'Operasional') {
                    $subtotalOperasional += $totalHarga;
                }

                $subtotalTotal += $totalHarga;

                // Tambahkan border untuk setiap baris data
                $styles[$currentRow] = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];

                $currentRow++; // Baris untuk setiap data barang
            }

            // Tambahkan baris subtotal
            $sheet->setCellValue("A{$currentRow}", 'Subtotal');
            $sheet->mergeCells("A{$currentRow}:E{$currentRow}"); // Gabungkan dari kolom A ke E untuk 'Subtotal'
            $sheet->setCellValue("F{$currentRow}", $subtotalDapur);
            $sheet->setCellValue("G{$currentRow}", $subtotalBar);
            $sheet->setCellValue("H{$currentRow}", $subtotalOperasional);
            $sheet->setCellValue("I{$currentRow}", $subtotalTotal);

            // Style untuk subtotal
            $styles[$currentRow] = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'right'], // Subtotal rata kanan
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];

            $currentRow++; // Tambahkan satu baris kosong setelah subtotal
        }

        return $styles;
    }

}
