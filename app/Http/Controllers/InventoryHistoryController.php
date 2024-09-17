<?php

namespace App\Http\Controllers;

use App\Models\MonthlyStockHistory;
// use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use PDF;
use Illuminate\Http\Request;

class InventoryHistoryController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::now();
        $isStartOfMonth = $today->day === 1;
        $isEndOfMonth = $today->isSameDay($today->endOfMonth());
        $isSpecificDates = $today->day == 1 || $today->isLastOfMonth();

        if ($request->ajax()) {
            $query = MonthlyStockHistory::with('bahanBaku');

            // Default to current month and year
            $bulan = $request->bulan ?: now()->month;
            $tahun = $request->tahun ?: now()->year;

            // Filter by month and year
            $query->whereMonth('bulan', $bulan)->whereYear('bulan', $tahun);

            $data = $query->get();

            return datatables()->of($data)
                ->addColumn('bahan_baku', function ($row) {
                    return $row->bahanBaku ? $row->bahanBaku->bahan_baku : '-';
                })
                ->addColumn('stok_awal_bulan', function ($row) {
                    return $row->stok_awal_bulan;
                })
                ->addColumn('total_rupiah_awal', function ($row) {
                    return formatRupiah($row->stok_awal_bulan * $row->bahanBaku->harga);
                })
                ->addColumn('stok_akhir_bulan', function ($row) {
                    return $row->stok_akhir_bulan;
                })
                ->addColumn('total_rupiah_akhir', function ($row) {
                    return formatRupiah($row->stok_akhir_bulan * $row->bahanBaku->harga);
                })
                ->make(true);
        }

        return view('history.index', compact('isStartOfMonth', 'isEndOfMonth', 'isSpecificDates'));
    }

    public function exportPDF(Request $request)
    {
        $bulan = $request->bulan ?: now()->month;
        $tahun = $request->tahun ?: now()->year;

        // Ambil data yang difilter
        $data = MonthlyStockHistory::with('bahanBaku')
            ->whereMonth('bulan', $bulan)
            ->whereYear('bulan', $tahun)
            ->get();

        // Kirim data ke view khusus PDF
        $pdf = PDF::loadView('history.pdf', compact('data', 'bulan', 'tahun'));

        // Set ukuran dan orientasi kertas jika diperlukan
        $pdf->setPaper('A4', 'landscape');

        // Download file PDF
        return $pdf->download('history.pdf');
    }
}
