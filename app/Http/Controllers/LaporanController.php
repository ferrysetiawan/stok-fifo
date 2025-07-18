<?php

namespace App\Http\Controllers;

use App\Exports\StokExport;
use App\Exports\StokPembalianExport;
use App\Exports\StokPembelianExport;
use App\Models\BahanBaku;
use App\Models\StokKeluar;
use App\Models\StokMasuk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function laporanStokMasuk(Request $request)
    {
        // Ambil bulan dan tahun dari request, jika tidak ada gunakan bulan dan tahun saat ini
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);

        // Query untuk mendapatkan total qty stok masuk per bahan baku untuk bulan dan tahun yang dipilih
        $stokMasuk = StokMasuk::with(['bahanBaku.kategori'])
            ->selectRaw('bahan_baku_id, SUM(qty) as total_qty')
            ->whereMonth('tanggal_masuk', $bulan)
            ->whereYear('tanggal_masuk', $tahun)
            ->groupBy('bahan_baku_id')
            ->paginate(10)
            ->appends(['bulan' => $bulan, 'tahun' => $tahun]); // Menambahkan parameter bulan dan tahun ke pagination

        // Mengambil data bahan baku untuk menampilkan nama dalam tampilan
        $bahanBakuList = BahanBaku::whereIn('id', $stokMasuk->pluck('bahan_baku_id'))->get();

        return view('laporan.stokMasuk', compact('stokMasuk', 'bulan', 'tahun', 'bahanBakuList'));
    }

    public function laporanStokKeluar(Request $request)
    {
        // Ambil bulan dan tahun dari request, jika tidak ada gunakan bulan dan tahun saat ini
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);

        // Query untuk mendapatkan total qty stok masuk per bahan baku untuk bulan dan tahun yang dipilih
        $stokMasuk = StokKeluar::with(['bahanBaku.kategori'])
            ->selectRaw('bahan_baku_id, SUM(jumlah) as total_qty')
            ->whereMonth('tanggal_keluar', $bulan)
            ->whereYear('tanggal_keluar', $tahun)
            ->groupBy('bahan_baku_id')
            ->paginate(10)
            ->appends(['bulan' => $bulan, 'tahun' => $tahun]); // Menambahkan parameter bulan dan tahun ke pagination

        // Mengambil data bahan baku untuk menampilkan nama dalam tampilan
        $bahanBakuList = BahanBaku::whereIn('id', $stokMasuk->pluck('bahan_baku_id'))->get();

        return view('laporan.stokKeluar', compact('stokMasuk', 'bulan', 'tahun', 'bahanBakuList'));
    }

    public function exportForm()
    {
        return view('stok.export');
    }

    public function exportPembelianForm()
    {
        return view('stok.export-pembelian');
    }

    // Proses export Excel
    public function exportExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        return Excel::download(new StokExport($request->start_date, $request->end_date), 'laporan_stok.xlsx');
    }



    public function exportStokMasuk(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        return Excel::download(new StokPembalianExport($request->start_date, $request->end_date), 'laporan_stok_masuk.xlsx');
    }
}
