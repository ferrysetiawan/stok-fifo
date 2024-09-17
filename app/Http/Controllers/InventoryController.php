<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::now();
        // dd($today);
        $isStartOfMonth = $today->day === 4;
        if ($request->ajax()) {
            $data = Inventory::with('bahanBaku')
                ->get();
            return datatables()->of($data)
                ->addColumn('bahan_baku', function ($row) {
                    return $row->bahanBaku ? $row->bahanBaku->bahan_baku : '-';
                })
                ->addColumn('stok_satuan', function ($row) {
                    return $row->stok . ' ' . ($row->bahanBaku ? $row->bahanBaku->satuan : '-');
                })
                ->make(true);
        }
        return view('simpleInventory.index', compact('isStartOfMonth'));
    }

    public function perProduk(Request $request)
    {
        // Ambil bulan dan tahun dari request atau set ke bulan dan tahun saat ini sebagai default
        $bulan = $request->input('bulan', now()->month); // Default ke bulan sekarang jika tidak ada input
        $tahun = $request->input('tahun', now()->year);  // Default ke tahun sekarang jika tidak ada input

        if ($request->ajax()) {
            // Inisialisasi query untuk Inventory
            $query = Inventory::with('bahanBaku');

            // Filter data berdasarkan bulan dan tahun
            if ($bulan && $tahun) {
                $startDate = Carbon::create($tahun, $bulan)->startOfMonth();
                $endDate = Carbon::create($tahun, $bulan)->endOfMonth();

                // Filter dengan bahanBakuMasuks (stok masuk) dan stokKeluar (stok keluar) berdasarkan tanggal
                $query->where(function ($query) use ($startDate, $endDate) {
                    $query->whereHas('bahanBakuMasuks', function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->whereBetween('tanggal_masuk', [$startDate, $endDate]);
                    })
                        ->orWhereHas('stokKeluar', function ($subQuery) use ($startDate, $endDate) {
                            $subQuery->whereBetween('tanggal_keluar', [$startDate, $endDate]);
                        });
                });
            }

            // Ambil data yang sesuai dengan filter
            $data = $query->get();

            // Kembalikan data dalam format DataTables
            return datatables()->of($data)
                ->addColumn('bahan_baku', function ($row) {
                    return $row->bahanBaku ? $row->bahanBaku->bahan_baku : '-'; // Sesuaikan dengan kolom nama bahan baku
                })
                ->addColumn('action', function ($row) use ($bulan, $tahun) {
                    // Tambahkan query parameter bulan dan tahun ke URL detail
                    return '<a class="btn btn-warning mr-1" href="' . route('inventory.show', ['id' => $row->id, 'bulan' => $bulan, 'tahun' => $tahun]) . '">Show</a>';
                })
                ->make(true);
        }

        // Jika bukan permintaan AJAX, tampilkan halaman utama dengan filter bulan dan tahun
        return view('perProduk.index', compact('bulan', 'tahun'));
    }



    public function show($id, Request $request)
    {
        // Cari inventory yang sesuai
        $inventory = Inventory::findOrFail($id);
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        // Tentukan awal dan akhir bulan yang dipilih
        $startDate = Carbon::create($tahun, $bulan)->startOfMonth();
        $endDate = Carbon::create($tahun, $bulan)->endOfMonth();

        // Ambil stok awal bulan dari tabel monthly_stock_histories berdasarkan bulan dan tahun yang difilter
        $stokAwalBulanRecord = DB::table('monthly_stock_histories')
            ->where('bahan_baku_id', $inventory->bahan_baku_id)
            ->whereYear('bulan', $tahun)
            ->whereMonth('bulan', $bulan)
            ->first();

        // Jika tidak ditemukan stok awal bulan, set ke 0
        $stokAwalBulan = $stokAwalBulanRecord ? $stokAwalBulanRecord->stok_awal_bulan : 0;

        // Ambil stok masuk dalam rentang waktu yang dipilih
        $stokMasuk = DB::table('stok_masuk')
            ->where('bahan_baku_id', $id)
            ->whereBetween('tanggal_masuk', [$startDate, $endDate])
            ->select('tanggal_masuk as tanggal', 'jumlah as masuk')
            ->get();

        // Ambil stok keluar dalam rentang waktu yang dipilih
        $stokKeluar = DB::table('stok_keluar')
            ->where('bahan_baku_id', $id)
            ->whereBetween('tanggal_keluar', [$startDate, $endDate])
            ->select('tanggal_keluar as tanggal', 'jumlah as keluar')
            ->get();

        // Gabungkan stok masuk dan keluar berdasarkan tanggal yang sama
        $stokGabungan = $stokMasuk->concat($stokKeluar)
            ->groupBy('tanggal')
            ->map(function ($item, $key) {
                $masuk = $item->whereNotNull('masuk')->sum('masuk');
                $keluar = $item->whereNotNull('keluar')->sum('keluar');
                return [
                    'tanggal' => $key,
                    'masuk' => $masuk,
                    'keluar' => $keluar,
                ];
            })->sortBy('tanggal');

        // Inisialisasi sisa stok dengan stok awal bulan
        $sisaStok = $stokAwalBulan;
        $dailyStok = [];

        // Hitung sisa stok untuk setiap tanggal berdasarkan stok masuk dan keluar
        foreach ($stokGabungan as $stok) {
            $sisaStok = $sisaStok + $stok['masuk'] - $stok['keluar'];
            $dailyStok[] = [
                'tanggal' => $stok['tanggal'],
                'masuk' => $stok['masuk'],
                'keluar' => $stok['keluar'],
                'sisa_stok' => $sisaStok
            ];
        }

        // Tampilkan data pada view
        return view('perProduk.show', [
            'inventory' => $inventory,
            'dailyStok' => $dailyStok,
            'stokAwalBulan' => $stokAwalBulan,
            'bulan' => $bulan,
            'tahun' => $tahun
        ]);
    }



    public function exportSimpleInventoryPDF(Request $request)
    {
        // Ambil data inventory
        $data = Inventory::with('bahanBaku')->get();

        // Kirim data ke view khusus PDF
        $pdf = PDF::loadView('simpleInventory.pdf', compact('data'));

        // Set ukuran dan orientasi kertas
        $pdf->setPaper('A4', 'landscape');

        // Download file PDF
        return $pdf->download('simple-inventory.pdf');
    }

    public function indexs(Request $request, $category_id = null)
    {
        if ($request->ajax()) {
            $inventories = Inventory::with('bahanBaku', 'bahanBakuMasuks')
                ->whereHas('bahanBaku', function ($query) use ($category_id) {
                    if ($category_id) {
                        $query->where('kategori_id', $category_id);
                    }
                })
                ->get();

            $data = [];
            $counter = 1;
            $seenBahanBaku = [];

            foreach ($inventories as $inventory) {
                $firstRow = true;
                foreach ($inventory->bahanBakuMasuks as $bahanBakuMasuk) {
                    $bahanBakuName = $inventory->bahanBaku->bahan_baku;
                    $satuan = $inventory->bahanBaku->satuan;

                    if (!isset($seenBahanBaku[$bahanBakuName])) {
                        $seenBahanBaku[$bahanBakuName] = $counter++;
                    }

                    if ($bahanBakuMasuk->jumlah == 0) {
                        continue;
                    }

                    $data[] = [
                        'no' => $firstRow ? $seenBahanBaku[$bahanBakuName] : '',
                        'nama_bahan_baku' => $firstRow ? $bahanBakuName : '',
                        'tanggal_masuk' => $bahanBakuMasuk->tanggal_masuk,
                        'jumlah' => $bahanBakuMasuk->jumlah . ' ' . $satuan,
                        'total' => $firstRow ? $inventory->total_stok . ' ' . $satuan : '',
                    ];
                    $firstRow = false;
                }
            }

            return response()->json(['data' => $data]);
        }

        return view('inventory.index', ['category_id' => $category_id]);
    }

    public function updateStokAkhirBulan(Request $request)
    {
        if ($request->has('isStartOfMonth') && $request->input('isStartOfMonth') === 'true') {
            $date = now()->subMonth(); // If it's the start of the month, use the previous month
        } else {
            $date = now(); // Otherwise, use the current month
        }

        Inventory::updateStokAkhirBulan($date);
        return redirect()->route('inventory.history')
            ->with('success', 'Stok akhir bulan berhasil diperbarui.');
    }

    public function updateStokAwalBulan()
    {
        Inventory::updateStokAwalBulan();
        return redirect()->route('inventory.index')
            ->with('success', 'Stok awal bulan berhasil diperbarui.');
    }
}
