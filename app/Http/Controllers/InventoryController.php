<?php

namespace App\Http\Controllers;

use App\Exports\SimpleInventoryExport;
use App\Models\BahanBaku;
use App\Models\Inventory;
use App\Models\StokKeluar;
use App\Models\StokMasuk;
use App\Services\InventoryStockService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::now();
        // dd($today);
        $isStartOfMonth = $today->day === 27;
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
                    return '<a class="btn btn-warning mr-1" href="' . route('inventory.show', ['id' => $row->bahanBaku->id, 'bulan' => $bulan, 'tahun' => $tahun]) . '">Show</a>';
                    return '<a class="btn btn-warning mr-1" href="' . route('inventory.show', ['id' => $row->bahanBaku->id, 'bulan' => $bulan, 'tahun' => $tahun]) . '">Show</a>';
                })
                ->make(true);
        }

        // Jika bukan permintaan AJAX, tampilkan halaman utama dengan filter bulan dan tahun
        return view('perProduk.index', compact('bulan', 'tahun'));
    }


    // public function show($id, Request $request)
    // {
    //     // Cari inventory yang sesuai
    //     $inventory = Inventory::where('bahan_baku_id', $id)->first();
    //     // dd($inventory);
    //     $bulan = $request->input('bulan', now()->month);
    //     $tahun = $request->input('tahun', now()->year);

    //     // Tentukan awal dan akhir bulan yang dipilih
    //     $startDate = Carbon::create($tahun, $bulan)->startOfMonth();
    //     $endDate = Carbon::create($tahun, $bulan)->endOfMonth();

    //     // Ambil stok awal bulan dari tabel monthly_stock_histories berdasarkan bulan dan tahun yang difilter
    //     $stokAwalBulanRecord = DB::table('monthly_stock_histories')
    //         ->where('bahan_baku_id', $inventory->bahan_baku_id)
    //         ->whereYear('bulan', $tahun)
    //         ->whereMonth('bulan', $bulan)
    //         ->first();

    //     // Jika stok awal bulan tidak ditemukan, ambil stok awal dari tabel inventory
    //     if ($stokAwalBulanRecord) {
    //         $stokAwalBulan = $stokAwalBulanRecord->stok_awal_bulan;
    //     } else {
    //         // Jika tidak ada di monthly_stock_histories, ambil dari inventory
    //         $stokAwalBulan = $inventory->stok_awal_bulan; // pastikan kolom ini benar
    //     }

    //     // Ambil stok masuk dalam rentang waktu yang dipilih
    //     $stokMasuk = DB::table('stok_masuk')
    //         ->where('bahan_baku_id', $id)
    //         ->whereBetween('tanggal_masuk', [$startDate, $endDate])
    //         ->select('tanggal_masuk as tanggal', 'qty as masuk')
    //         ->get();

    //     // Ambil stok keluar dalam rentang waktu yang dipilih
    //     $stokKeluar = DB::table('stok_keluar')
    //         ->where('bahan_baku_id', $id)
    //         ->whereBetween('tanggal_keluar', [$startDate, $endDate])
    //         ->select('tanggal_keluar as tanggal', 'jumlah as keluar')
    //         ->get();

    //     // Gabungkan stok masuk dan keluar berdasarkan tanggal yang sama
    //     $stokGabungan = $stokMasuk->concat($stokKeluar)
    //         ->groupBy('tanggal')
    //         ->map(function ($item, $key) {
    //             $masuk = $item->whereNotNull('masuk')->sum('masuk');
    //             $keluar = $item->whereNotNull('keluar')->sum('keluar');
    //             return [
    //                 'tanggal' => $key,
    //                 'masuk' => $masuk,
    //                 'keluar' => $keluar,
    //             ];
    //         })->sortBy('tanggal');

    //     // Inisialisasi sisa stok dengan stok awal bulan
    //     $sisaStok = $stokAwalBulan;
    //     $dailyStok = [];

    //     // Hitung sisa stok untuk setiap tanggal berdasarkan stok masuk dan keluar
    //     foreach ($stokGabungan as $stok) {
    //         $sisaStok = $sisaStok + $stok['masuk'] - $stok['keluar'];
    //         $dailyStok[] = [
    //             'tanggal' => $stok['tanggal'],
    //             'masuk' => $stok['masuk'],
    //             'keluar' => $stok['keluar'],
    //             'sisa_stok' => $sisaStok
    //         ];
    //     }

    //     // Tampilkan data pada view
    //     return view('perProduk.show', [
    //         'inventory' => $inventory,
    //         'dailyStok' => $dailyStok,
    //         'stokAwalBulan' => $stokAwalBulan,
    //         'bulan' => $bulan,
    //         'tahun' => $tahun
    //     ]);
    // }

    public function show($id, Request $request)
    {
        // Cari inventory yang sesuai
        $inventory = Inventory::where('bahan_baku_id', $id)->first();

        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        // Tentukan awal dan akhir bulan yang dipilih
        $startDate = Carbon::create($tahun, $bulan)->startOfMonth();
        $endDate = Carbon::create($tahun, $bulan)->endOfMonth();

        // Hitung stok masuk sebelum awal bulan
        $totalStokMasukSebelum = DB::table('stok_masuk')
            ->where('bahan_baku_id', $id)
            ->where('tanggal_masuk', '<', $startDate)
            ->sum('qty');

        // Hitung stok keluar sebelum awal bulan
        $totalStokKeluarSebelum = DB::table('stok_keluar')
            ->where('bahan_baku_id', $id)
            ->where('tanggal_keluar', '<', $startDate)
            ->sum('jumlah');

        // Stok awal bulan hasil hitungan manual
        $stokAwalBulan = $totalStokMasukSebelum - $totalStokKeluarSebelum;

        // Ambil stok masuk dalam rentang waktu yang dipilih
        $stokMasuk = DB::table('stok_masuk')
            ->where('bahan_baku_id', $id)
            ->whereBetween('tanggal_masuk', [$startDate, $endDate])
            ->select('tanggal_masuk as tanggal', 'qty as masuk')
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

        // Hitung sisa stok untuk setiap tanggal
        foreach ($stokGabungan as $stok) {
            $sisaStok = $sisaStok + $stok['masuk'] - $stok['keluar'];
            $dailyStok[] = [
                'tanggal' => $stok['tanggal'],
                'masuk' => $stok['masuk'],
                'keluar' => $stok['keluar'],
                'sisa_stok' => $sisaStok
            ];
        }

        // Tampilkan data ke view
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

    public function exportExcel(Request $request)
    {
        return Excel::download(new SimpleInventoryExport, 'simple-inventory.xlsx');
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
        // if ($request->has('isStartOfMonth') && $request->input('isStartOfMonth') === 'true') {
        //     $date = now()->subMonth(); // If it's the start of the month, use the previous month
        // } else {
        //     $date = now(); // Otherwise, use the current month
        // }
        $date = Carbon::parse('2025-05-01')->subMonth();

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

    public function downloadAllPdf(Request $request)
    {
        // Ambil bulan dan tahun dari request, jika tidak ada set ke bulan dan tahun saat ini
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        // Tentukan awal dan akhir bulan berdasarkan bulan dan tahun yang dipilih
        $startDate = Carbon::create($tahun, $bulan)->startOfMonth(); // Tanggal 1 bulan yang dipilih
        $endDate = Carbon::create($tahun, $bulan)->endOfMonth();     // Tanggal akhir bulan yang dipilih

        // Ambil semua inventory (stok) beserta bahan bakunya
        $inventories = Inventory::with('bahanBaku')->get();

        // Inisialisasi array untuk menampung data stok dari semua bahan baku
        $allStockData = [];

        foreach ($inventories as $inventory) {
            // Ambil stok awal bulan dari tabel monthly_stock_histories
            $stokAwalBulanRecord = DB::table('monthly_stock_histories')
                ->where('bahan_baku_id', $inventory->bahan_baku_id)
                ->whereYear('bulan', $tahun)
                ->whereMonth('bulan', $bulan)
                ->first();

            // Jika stok awal tidak ditemukan, ambil stok awal dari tabel inventory
            $stokAwalBulan = $stokAwalBulanRecord ? $stokAwalBulanRecord->stok_awal_bulan : $inventory->stok_awal_bulan;

            // Ambil stok masuk berdasarkan rentang waktu yang difilter
            $stokMasuk = StokMasuk::where('bahan_baku_id', $inventory->bahan_baku_id)
                ->whereBetween('tanggal_masuk', [$startDate, $endDate]) // Pastikan filter berdasarkan tanggal dari awal hingga akhir bulan
                ->get();

            // Ambil stok keluar berdasarkan rentang waktu yang difilter
            $stokKeluar = StokKeluar::where('bahan_baku_id', $inventory->bahan_baku_id)
                ->whereBetween('tanggal_keluar', [$startDate, $endDate]) // Filter berdasarkan tanggal dari awal hingga akhir bulan
                ->get();

            // Gabungkan stok masuk dan keluar berdasarkan tanggal
            $stokGabungan = $stokMasuk->concat($stokKeluar)
                ->groupBy(function ($item) {
                    // Gabungkan berdasarkan tanggal (misal: 'tanggal_masuk' atau 'tanggal_keluar')
                    return $item->tanggal_masuk ?? $item->tanggal_keluar;
                })
                ->map(function ($item, $key) {
                    // Menghitung total masuk dan keluar per tanggal
                    $masuk = $item->whereNotNull('qty')->sum('qty');   // Pastikan menggunakan 'qty' untuk stok masuk
                    $keluar = $item->whereNotNull('jumlah')->sum('jumlah'); // 'jumlah' untuk stok keluar
                    return [
                        'tanggal' => $key,
                        'masuk' => $masuk,
                        'keluar' => $keluar,
                    ];
                })->sortBy('tanggal');

            // Hitung sisa stok awal
            $sisaStok = $stokAwalBulan;
            $dailyStok = [];

            // Hitung sisa stok harian
            foreach ($stokGabungan as $stok) {
                $sisaStok = $sisaStok + $stok['masuk'] - $stok['keluar'];
                $dailyStok[] = [
                    'tanggal' => $stok['tanggal'],
                    'masuk' => $stok['masuk'],
                    'keluar' => $stok['keluar'],
                    'sisa_stok' => $sisaStok
                ];
            }

            // Simpan data stok harian untuk setiap bahan baku
            $allStockData[] = [
                'inventory' => $inventory,
                'dailyStok' => $dailyStok,
                'stokAwalBulan' => $stokAwalBulan,
            ];
        }

        // Muat view PDF dengan semua data stok
        $pdf = PDF::loadView('perProduk.pdf', [
            'allStockData' => $allStockData,
            'bulan' => $bulan,
            'tahun' => $tahun
        ]);

        // Kembalikan file PDF untuk diunduh
        return $pdf->download('kartu_stok_semua_bahan_baku.pdf');
    }

    public function setStokAkhir()
    {
        $inventories = Inventory::with('bahanBaku')->get();
        $bahanBakus = BahanBaku::all();
        return view('stokKeluar.set_stok_akhir', compact('inventories', 'bahanBakus'));
    }

    public function storeStokAkhir(Request $request)
    {
        $request->validate([
            'bahan_baku_id' => 'required|exists:bahan_baku,id',
            'stok_akhir_bulan' => 'required',
            'bulan' => 'required|date_format:Y-m',
        ]);

        $inventory = Inventory::firstOrCreate(
            ['bahan_baku_id' => $request->bahan_baku_id],
            ['stok' => 0, 'stok_awal_bulan' => 0, 'stok_akhir_bulan' => 0]
        );

        $inventory->stok_akhir_bulan = $request->stok_akhir_bulan;
        $inventory->save();

        $bulan = Carbon::createFromFormat('Y-m', $request->bulan);

        InventoryStockService::generateStokKeluarProdukDenganTarget($inventory, $bulan);

        return redirect()->back()->with('success', 'Target stok akhir disimpan & stok keluar digenerate untuk bulan ' . $bulan->format('F Y') . '!');
    }
}
