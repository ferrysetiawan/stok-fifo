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

        // Dapatkan stok total dari StokMasuk yang sesuai dengan syarat tanggal
        $totalStokTersedia = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->where('jumlah', '>', 0)
            ->where('tanggal_masuk', '<', $tanggalKeluar) // Hanya ambil stok sebelum tanggal keluar
            ->sum('jumlah'); // Menghitung total stok yang tersedia

        // Cek apakah stok mencukupi
        if ($jumlahKeluar > $totalStokTersedia) {
            // Jika stok tidak cukup, kembalikan pesan error
            return response()->json(['error' => 'Stok tidak mencukupi.'], 422);
        }

        // Ambil stok masuk yang tersedia dengan urutan berdasarkan tanggal_masuk
        $bahanBakuMasuks = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->where('jumlah', '>', 0)
            ->where('tanggal_masuk', '<', $tanggalKeluar) // Hanya ambil stok sebelum tanggal keluar
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        // Proses pengurangan stok secara FIFO
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
        $inventory->stok = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->sum('jumlah'); // Hitung ulang jumlah stok total
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validasi input jumlah
        $request->validate([
            'jumlah' => 'required|integer|min:1',
        ]);

        // Ambil data stok keluar berdasarkan ID
        $bahanBakuKeluar = StokKeluar::findOrFail($id);
        $jumlahBaru = $request->jumlah;
        $jumlahLama = $bahanBakuKeluar->jumlah;
        $bahanBakuId = $bahanBakuKeluar->bahan_baku_id;
        $tanggalKeluar = $bahanBakuKeluar->tanggal_keluar;

        // Jika jumlah baru sama dengan jumlah lama, tidak perlu update
        if ($jumlahBaru == $jumlahLama) {
            return redirect()->route('stok_keluar.index')
                ->with('info', 'Tidak ada perubahan pada jumlah.');
        }

        // Kembalikan stok lama ke inventory sebelum update
        $this->restoreInventory($bahanBakuId, $jumlahLama);

        // Cek apakah stok cukup untuk jumlah baru
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

    private function restoreInventory($bahanBakuId, $jumlah)
    {
        // Mengembalikan stok lama ke inventory, FIFO
        $bahanBakuMasuks = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        foreach ($bahanBakuMasuks as $bahanBakuMasuk) {
            if ($jumlah <= 0) {
                break;
            }

            // Hitung ruang yang tersedia untuk mengembalikan stok tanpa melebihi jumlah asli
            $availableSpace = $bahanBakuMasuk->jumlah_asli - $bahanBakuMasuk->jumlah;

            if ($availableSpace > 0) {
                // Kembalikan stok sesuai jumlah ruang yang tersedia
                $restoreAmount = min($availableSpace, $jumlah);
                $bahanBakuMasuk->jumlah += $restoreAmount;
                $bahanBakuMasuk->save();
                $jumlah -= $restoreAmount;
            }
        }

        // Perbarui inventory setelah stok dikembalikan
        $this->updateInventory($bahanBakuId);
    }

    private function reduceInventory($bahanBakuId, $jumlah, $tanggalKeluar)
    {
        // Kurangi stok dari inventory, FIFO dan hanya ambil stok sebelum atau sama dengan tanggal keluar
        $bahanBakuMasuks = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->where('tanggal_masuk', '<=', $tanggalKeluar)
            ->where('jumlah', '>', 0)
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        foreach ($bahanBakuMasuks as $bahanBakuMasuk) {
            if ($jumlah <= 0) {
                break;
            }

            if ($bahanBakuMasuk->jumlah >= $jumlah) {
                // Kurangi jumlah yang sesuai dari stok masuk
                $bahanBakuMasuk->jumlah -= $jumlah;
                $bahanBakuMasuk->save();
                $jumlah = 0;
            } else {
                // Jika jumlah stok tidak cukup, kurangi stok keluar dan kosongkan stok ini
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
        // Hitung stok yang tersedia dengan kondisi FIFO dan tanggal stok masuk sesuai
        $totalStokTersedia = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->where('tanggal_masuk', '<=', $tanggalKeluar) // Stok sebelum atau sama dengan tanggal keluar
            ->where('jumlah', '>', 0)
            ->sum('jumlah');

        // Pastikan stok cukup untuk jumlah yang dibutuhkan
        return $jumlah <= $totalStokTersedia;
    }

    private function updateInventory($bahanBakuId)
    {
        // Perbarui jumlah stok di inventory setelah perubahan
        $inventory = Inventory::where('bahan_baku_id', $bahanBakuId)->first();
        $inventory->stok = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->sum('jumlah'); // Hitung ulang jumlah stok total
        $inventory->save();
    }

    public function exportExcel(Request $request)
    {
        $month = $request->input('start_date');
        $year = $request->input('end_date');

        //dd($month, $year);

        $fileName = 'stok_keluar_' . $month . '_' . $year . '_' . now()->format('His') . '.xlsx';

        return Excel::download(new StokKeluarExport($month, $year), $fileName);
    }

}
