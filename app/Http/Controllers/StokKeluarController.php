<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StokKeluarExport;
use App\Models\Inventory;
use App\Models\Kategori;
use App\Models\StokKeluar;
use App\Models\StokMasuk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StokKeluarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StokKeluar::with('bahanBaku');

            $currentMonth = $request->bulan ?: now()->month;
            $currentYear = $request->tahun ?: now()->year;

            // Filter berdasarkan bulan dan tahun
            $query->whereMonth('tanggal_keluar', $currentMonth)
                ->whereYear('tanggal_keluar', $currentYear);

            // Filter berdasarkan nama bahan baku
            if ($request->nama_bahan_baku) {
                $query->whereHas('bahanBaku', function ($q) use ($request) {
                    $q->where('bahan_baku', 'like', '%' . $request->nama_bahan_baku . '%');
                });
            }

            $data = $query->get();
            return datatables()->of($data)
                ->addColumn('bahan_baku', function ($row) {
                    return $row->bahanBaku ? $row->bahanBaku->bahan_baku : '-';
                })
                ->make(true);
        }

        return view('stokKeluar.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $query = StokKeluar::with('bahanBaku');

        // Filter berdasarkan tanggal jika tanggal diberikan
        if ($request->has('tanggal')) {
            $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');
            $query->whereDate('tanggal_keluar', $tanggal);
        }

        if ($request->ajax()) {
            $data = $query->get();
            return datatables()->of($data)
                ->addColumn('bahan_baku', function ($row) {
                    return $row->bahanBaku ? $row->bahanBaku->bahan_baku : '-';
                })
                ->make(true);
        }

        $kategori = Kategori::all();
        return view('stokKeluar.create', compact('kategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validasi request
            $validatedData = $request->validate([
                'tanggal_keluar' => 'required|date',
                'bahanBaku' => 'required|exists:bahan_baku,id', // Sesuaikan dengan model dan nama tabel Bahan Baku yang Anda gunakan
                'jumlah' => 'required|numeric',
            ]);
        } catch (ValidationException $e) {
            // Tangani error validasi
            $errors = $e->validator->errors()->toArray();

            return response()->json(['errors' => $errors], 422);
        }

        $jumlahKeluar = $validatedData['jumlah'];
        $bahanBakuId = $validatedData['bahanBaku'];
        $tanggalKeluar = $validatedData['tanggal_keluar'];

        // Dapatkan stok total dari StokMasuk yang sesuai dengan tanggal
        $totalStokTersedia = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->where('jumlah', '>', 0)
            ->sum('jumlah'); // Menghitung total stok yang tersedia

        // Cek apakah stok mencukupi
        if ($jumlahKeluar > $totalStokTersedia) {
            // Jika stok tidak cukup, kembalikan pesan error
            return response()->json(['error' => 'Stok tidak mencukupi.'], 422);
        }

        // Ambil stok masuk yang tersedia dengan urutan berdasarkan tanggal_masuk
        $bahanBakuMasuks = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->where('jumlah', '>', 0)
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        // Cek apakah ada stok masuk yang lebih baru dari tanggal keluar
        foreach ($bahanBakuMasuks as $bahanBakuMasuk) {
            if ($bahanBakuMasuk->tanggal_masuk > $tanggalKeluar) {
                // Jika ada stok masuk yang tanggalnya lebih besar dari tanggal keluar
                return response()->json(['error' => 'Stok keluar tidak bisa dilakukan karena stok masuk di tanggal yang lebih baru.'], 422);
            }
        }

        // Lanjutkan proses jika tidak ada masalah dengan tanggal
        foreach ($bahanBakuMasuks as $bahanBakuMasuk) {
            if ($jumlahKeluar <= 0) {
                break;
            }

            if ($bahanBakuMasuk->jumlah >= $jumlahKeluar) {
                // Kurangi jumlah di stok masuk
                $bahanBakuMasuk->jumlah -= $jumlahKeluar;
                $bahanBakuMasuk->save();
                $jumlahKeluar = 0;
            } else {
                // Kurangi jumlah stok keluar dan set stok masuk ke 0
                $jumlahKeluar -= $bahanBakuMasuk->jumlah;
                $bahanBakuMasuk->jumlah = 0;
                $bahanBakuMasuk->save();
            }
        }

        // Simpan hanya sekali di StokKeluar setelah semua stok dikurangi
        $pengeluaran = StokKeluar::create([
            'tanggal_keluar' => $validatedData['tanggal_keluar'],
            'bahan_baku_id' => $validatedData['bahanBaku'],
            'jumlah' => $validatedData['jumlah'],
        ]);

        // Perbarui stok di inventory
        $inventory = Inventory::where('bahan_baku_id', $bahanBakuId)->first();
        $inventory->stok = $inventory->bahanBakuMasuks->sum('jumlah');
        $inventory->save();

        return response()->json(['message' => 'Stok berhasil disimpan', 'data' => $pengeluaran]);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $stokKeluar = StokKeluar::findOrFail($id);
        return view('stokKeluar.edit', compact('stokKeluar'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1',
        ]);

        $bahanBakuKeluar = StokKeluar::findOrFail($id);
        $jumlahBaru = $request->jumlah;
        $jumlahLama = $bahanBakuKeluar->jumlah;
        $bahanBakuId = $bahanBakuKeluar->bahan_baku_id;
        $tanggalKeluar = $bahanBakuKeluar->tanggal_keluar;

        if ($jumlahBaru == $jumlahLama) {
            return redirect()->route('stok_keluar.index')
                ->with('info', 'Tidak ada perubahan pada jumlah.');
        }

        // Kembalikan stok lama ke inventory
        $this->restoreInventory($bahanBakuId, $jumlahLama);

        // Cek apakah stok cukup untuk jumlah baru sebelum menguranginya
        if (!$this->checkAvailableStock($bahanBakuId, $jumlahBaru, $tanggalKeluar)) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi atau tidak sesuai dengan tanggal stok masuk.');
        }

        // Mengurangi stok baru dari inventory
        if (!$this->reduceInventory($bahanBakuId, $jumlahBaru, $tanggalKeluar)) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi.');
        }

        // Update jumlah stok keluar
        $bahanBakuKeluar->update(['jumlah' => $jumlahBaru]);

        // Perbarui stok di inventory
        $this->updateInventory($bahanBakuId);

        return redirect()->route('stok_keluar.index')
            ->with('success', 'Bahan Baku Keluar berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stokKeluar = StokKeluar::findOrFail($id);
        // $jumlahLama = $stokKeluar->jumlah;
        // $bahanBakuId = $stokKeluar->bahan_baku_id;
        // $this->restoreInventory($bahanBakuId, $jumlahLama);
        $stokKeluar->delete();
        if ($stokKeluar) {
            return response()->json([
                'status' => 'success'
            ]);
        } else {
            return response()->json([
                'status' => 'error'
            ]);
        }
    }


    private function restoreInventory($bahanBakuId, $jumlah)
    {
        $bahanBakuMasuks = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        foreach ($bahanBakuMasuks as $bahanBakuMasuk) {
            if ($jumlah <= 0) {
                break;
            }

            $availableSpace = $bahanBakuMasuk->jumlah_asli - $bahanBakuMasuk->jumlah;

            if ($availableSpace > 0) {
                $restoreAmount = min($availableSpace, $jumlah);
                $bahanBakuMasuk->jumlah += $restoreAmount;
                $bahanBakuMasuk->save();
                $jumlah -= $restoreAmount;
            }
        }

        $this->updateInventory($bahanBakuId);
    }




    private function reduceInventory($bahanBakuId, $jumlah, $tanggalKeluar)
    {
        $bahanBakuMasuks = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->where('tanggal_masuk', '<=', $tanggalKeluar) // Hanya stok masuk sebelum atau sama dengan tanggal keluar
            ->where('jumlah', '>', 0)
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        foreach ($bahanBakuMasuks as $bahanBakuMasuk) {
            if ($jumlah <= 0) {
                break;
            }

            if ($bahanBakuMasuk->jumlah >= $jumlah) {
                $bahanBakuMasuk->jumlah -= $jumlah;
                $bahanBakuMasuk->save();
                $jumlah = 0;
            } else {
                $jumlah -= $bahanBakuMasuk->jumlah;
                $bahanBakuMasuk->jumlah = 0;
                $bahanBakuMasuk->save();
            }
        }

        if ($jumlah > 0) {
            return false; // Stok tidak mencukupi
        }

        return true;
    }

    private function checkAvailableStock($bahanBakuId, $jumlah, $tanggalKeluar)
    {
        $totalStokTersedia = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->where('tanggal_masuk', '<=', $tanggalKeluar) // Hanya stok masuk sebelum atau sama dengan tanggal keluar
            ->where('jumlah', '>', 0)
            ->sum('jumlah');

        return $jumlah <= $totalStokTersedia;
    }

    private function updateInventory($bahanBakuId)
    {
        $inventory = Inventory::where('bahan_baku_id', $bahanBakuId)->first();
        $inventory->stok = StokMasuk::where('bahan_baku_id', $bahanBakuId)->sum('jumlah');
        $inventory->save();
    }


    public function exportExcel(Request $request)
    {
        $month = $request->input('start_date');
        $year = $request->input('end_date');

        // dd($month, $year);

        $fileName = 'stok_keluar_' . $month . '_' . $year . '_' . now()->format('His') . '.xlsx';

        return Excel::download(new StokKeluarExport($month, $year), $fileName);
    }

}
