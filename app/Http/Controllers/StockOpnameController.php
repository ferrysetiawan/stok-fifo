<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessStockOpname;
use App\Models\BahanBaku;
use App\Models\Inventory;
use App\Models\StockOpname;
use App\Models\StokKeluar;
use App\Models\StokMasuk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StockOpname::with('bahanBaku');

            $currentMonth = $request->bulan ?: now()->month;
            $currentYear = $request->tahun ?: now()->year;
            $query->whereMonth('tanggal_opname', $currentMonth)->whereYear('tanggal_opname', $currentYear);

            $data = $query->get();
            return datatables()->of($data)
                ->addColumn('bahan_baku', function ($row) {
                    return $row->bahanBaku ? $row->bahanBaku->bahan_baku : '-';
                })
                ->addColumn('kategori', function ($row) {
                    return $row->bahanBaku ? $row->bahanBaku->kategori->nama : '-';
                })
                ->addColumn('satuan', function ($row) {
                    return $row->bahanBaku ? $row->bahanBaku->satuan : '-';
                })
                ->make(true);
        }
        return view('stockOpname.index');
    }

    public function create()
    {
        $bahanBakus = Inventory::paginate(1);
        return view('stockOpname.create', compact('bahanBakus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'opnames' => 'required|array',
            'opnames.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'opnames.*.stok_fisik' => 'required|integer|min:0',
        ]);

        // Mengirimkan data ke job queue
        Queue::push(new ProcessStockOpname($request->opnames));

        return redirect()->route('stockOpname.create')->with('success', 'Stock Opname berhasil dikirim untuk diproses.');
    }

    public function edit($id)
    {
        $stockOpname = StockOpname::findOrFail($id);
        $bahanBakus = Inventory::with('bahanBaku')->get(); // Ambil bahan baku untuk dropdown
        return view('stockOpname.edit', compact('stockOpname', 'bahanBakus'));
    }

    // Memproses pembaruan stok opname
    public function update(Request $request, $id)
    {
        $request->validate([
            'stok_fisik' => 'required|numeric|min:0',
        ]);

        $stockOpname = StockOpname::findOrFail($id);
        $tanggalOpname = Carbon::parse($stockOpname->tanggal_opname);
        $startOfMonth = $tanggalOpname->copy()->startOfMonth();
        $endOfMonth = $tanggalOpname->copy()->endOfMonth();

        DB::transaction(function () use ($request, $stockOpname, $startOfMonth, $endOfMonth) {
            $inventory = Inventory::where('bahan_baku_id', $stockOpname->bahan_baku_id)->first();

            $stokAwal = $inventory->stok_awal_bulan;

            $penerimaan = StokMasuk::where('bahan_baku_id', $stockOpname->bahan_baku_id)
                ->whereBetween('tanggal_masuk', [$startOfMonth, $endOfMonth])
                ->sum('qty');

            $pengeluaran = StokKeluar::where('bahan_baku_id', $stockOpname->bahan_baku_id)
                ->whereBetween('tanggal_keluar', [$startOfMonth, $endOfMonth])
                ->sum('jumlah');

            $stokAkhir = $stokAwal + $penerimaan - $pengeluaran;
            $stokFisik = $request->stok_fisik;
            $selisih = $stokFisik - $stokAkhir;
            $selisihKeluar = $selisih;

            // Update stok opname
            $stockOpname->update([
                'stok_awal' => $stokAwal,
                // 'penerimaan' => $penerimaan,
                // 'pengeluaran' => $pengeluaran,
                'stok_akhir' => $stokAkhir,
                'stok_fisik' => $stokFisik,
                'selisih' => $selisih,
            ]);

            // Update inventory
            $inventory->stok = $stokFisik;
            $inventory->save();


            $this->adjustStockMasuk($stockOpname->bahan_baku_id, $stokFisik, $selisihKeluar);
        });

        return redirect()->route('stockOpname.index')->with('success', 'Stock Opname berhasil diperbarui.');
    }

    private function adjustStockMasuk($bahanBakuId, $stokFisik, $selisihKeluar)
    {
        // Ambil semua stok masuk yang terkait dengan bahan baku yang dipilih
        $stokMasukItems = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->orderBy('tanggal_masuk', 'asc')
            ->get();


        $totalStokMasuk = $stokMasukItems->sum('jumlah');
        $selisih = $stokFisik - $totalStokMasuk;

        //dd(compact('stokMasukItems','stokFisik', 'totalStokMasuk', 'selisih'));

        // Jika stok awal dan stok masuk 0, cari 1 entri terakhir dengan jumlah 0
        if ($totalStokMasuk == 0 && $stokFisik > 0) {
            $stokMasukTerakhir = StokMasuk::where('bahan_baku_id', $bahanBakuId)
                ->where('jumlah', 0) // Cari stok masuk yang jumlahnya 0
                ->orderBy('tanggal_masuk', 'desc')
                ->first(); // Ambil hanya satu entri

            if ($stokMasukTerakhir) {
                // Tambah stok masuk terakhir yang 0
                $stokMasukTerakhir->jumlah = min($selisih, 2); // Tambah maksimal 2 atau selisih jika lebih kecil
                $selisih -= $stokMasukTerakhir->jumlah;
                $stokMasukTerakhir->save();
            }
        }

        // Penanganan stok masuk (jika ada stok masuk yang lebih dari 0 dan selisih > 0)
        foreach ($stokMasukItems as $stokMasuk) {
            if ($selisih == 0) {
                break;
            }

            if ($stokMasuk->jumlah > 0) {
                // Jika stok masuk lebih dari 0, tambahkan selisih ke stok tersebut
                $stokMasuk->jumlah += $selisih;
                $selisih = 0; // Setelah selisih diterapkan, set ke 0
                $stokMasuk->save();
            }
        }

        // Jika selisih masih negatif (stok fisik lebih kecil dari total stok masuk), buat stok keluar
        if ($selisihKeluar < 0) {
            $this->adjustStokKeluar($bahanBakuId, abs($selisihKeluar));
        }
    }


    private function adjustStokKeluar($bahanBakuId, $jumlah)
    {
        $stokKeluarItem = StokKeluar::where('bahan_baku_id', $bahanBakuId)
            ->orderBy('tanggal_keluar', 'asc')
            ->first();

        if ($stokKeluarItem) {
            // Jika stok keluar sudah ada, perbarui jumlahnya
            $stokKeluarItem->jumlah = $jumlah;

            // Jika stok keluar menjadi 0, hapus stok keluar
            if ($stokKeluarItem->jumlah == 0) {
                $stokKeluarItem->delete();
            } else {
                $stokKeluarItem->save();
            }
        } else {
            // Jika stok keluar belum ada, buat stok keluar baru
            StokKeluar::create([
                'bahan_baku_id' => $bahanBakuId,
                'jumlah' => $jumlah,
                'tanggal_keluar' => Carbon::now(),
            ]);
        }
    }

    public function format()
    {
        $inventories = Inventory::with(['bahanBaku.kategori'])->get();
        $groupedByKategori = $inventories->groupBy('bahanBaku.kategori.nama');
        return view('stockOpname.format', compact('groupedByKategori'));
    }
}
