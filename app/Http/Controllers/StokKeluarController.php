<?php

namespace App\Http\Controllers;

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
            $query->whereMonth('tanggal_keluar', $currentMonth)->whereYear('tanggal_keluar', $currentYear);

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

        $bahanBakuMasuks = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->where('jumlah', '>', 0)
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        foreach ($bahanBakuMasuks as $bahanBakuMasuk) {
            if ($jumlahKeluar <= 0) {
                break;
            }

            if ($bahanBakuMasuk->jumlah >= $jumlahKeluar) {
                $bahanBakuMasuk->jumlah -= $jumlahKeluar;
                $bahanBakuMasuk->save();

                // Simpan data ke dalam database
                $pengeluaran = StokKeluar::create([
                    'tanggal_keluar' => $validatedData['tanggal_keluar'],
                    'bahan_baku_id' => $validatedData['bahanBaku'],
                    'jumlah' => $validatedData['jumlah'],
                ]);

                $jumlahKeluar = 0;
            } else {
                $jumlahKeluar -= $bahanBakuMasuk->jumlah;

                // Simpan data ke dalam database
                $pengeluaran = StokKeluar::create([
                    'tanggal_keluar' => $validatedData['tanggal_keluar'],
                    'bahan_baku_id' => $validatedData['bahanBaku'],
                    'jumlah' => $validatedData['jumlah'],
                ]);

                $bahanBakuMasuk->jumlah = 0;
                $bahanBakuMasuk->save();
            }
        }

        if ($jumlahKeluar > 0) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi.');
        }

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

        if ($jumlahBaru == $jumlahLama) {
            return redirect()->route('stok_keluar.index')
                ->with('info', 'Tidak ada perubahan pada jumlah.');
        }

        // Mengembalikan stok lama ke inventory
        $this->restoreInventory($bahanBakuId, $jumlahLama);

        // Mengurangi stok baru dari inventory
        if (!$this->reduceInventory($bahanBakuId, $jumlahBaru)) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi.');
        }

        $bahanBakuKeluar->update(['jumlah' => $jumlahBaru]);

        // Perbarui stok di inventory
        $this->updateInventory($bahanBakuId);

        return redirect()->route('stok_keluar.index')
            ->with('success', 'Bahan Baku Keluar berhasil diperbarui.');
    }


    private function restoreInventory($bahanBakuId, $jumlah)
    {
        $bahanBakuMasuks = StokMasuk::where('bahan_baku_id', $bahanBakuId)
            ->where('jumlah', '>', 0)
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        foreach ($bahanBakuMasuks as $bahanBakuMasuk) {
            if ($jumlah <= 0) {
                break;
            }

            $remainingSpace = $bahanBakuMasuk->jumlah;

            if ($remainingSpace > 0) {
                $bahanBakuMasuk->jumlah += $jumlah;
                $bahanBakuMasuk->save();
                $jumlah = 0;
            }
        }

        $this->updateInventory($bahanBakuId);
    }



    private function reduceInventory($bahanBakuId, $jumlah)
    {
        $bahanBakuMasuks = StokMasuk::where('bahan_baku_id', $bahanBakuId)
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


    private function updateInventory($bahanBakuId)
    {
        $inventory = Inventory::where('bahan_baku_id', $bahanBakuId)->first();
        $inventory->stok = StokMasuk::where('bahan_baku_id', $bahanBakuId)->sum('jumlah');
        $inventory->save();
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stokKeluar = StokKeluar::findOrFail($id);
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
}
