<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Inventory;
use App\Models\Kategori;
use App\Models\StokMasuk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StokMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StokMasuk::with('bahanBaku');
            $currentMonth = $request->bulan ?: now()->month;
            $currentYear = $request->tahun ?: now()->year;

            // Filter by month and year
            $query->whereMonth('tanggal_masuk', $currentMonth)->whereYear('tanggal_masuk', $currentYear);
            $data = $query->get();
            return datatables()->of($data)
                ->addColumn('bahan_baku', function ($row) {
                    return $row->bahanBaku ? $row->bahanBaku->bahan_baku : '-';
                })
                ->make(true);
        }
        return view('stokMasuk.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $query = StokMasuk::with('bahanBaku');

        // Filter berdasarkan tanggal jika tanggal diberikan
        if ($request->has('tanggal')) {
            $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');
            $query->whereDate('tanggal_masuk', $tanggal);
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
        return view('stokMasuk.create', compact('kategori'));
    }


    public function bahanBaku(Request $request)
    {
        $searchTerm = $request->input('search');

        $query = BahanBaku::query();

        if ($searchTerm) {
            $query->where('bahan_baku', 'like', '%' . $searchTerm . '%');
        }

        $data = $query->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validasi request
            $validatedData = $request->validate([
                'tanggal_masuk' => 'required|date',
                'bahanBaku' => 'required|exists:bahan_baku,id', // Sesuaikan dengan model dan nama tabel Bahan Baku yang Anda gunakan
                'jumlah' => 'required|numeric',
            ]);
        } catch (ValidationException $e) {
            // Tangani error validasi
            $errors = $e->validator->errors()->toArray();

            return response()->json(['errors' => $errors], 422);
        }

        // Simpan data ke dalam database
        $pembelian = StokMasuk::create([
            'tanggal_masuk' => $validatedData['tanggal_masuk'],
            'bahan_baku_id' => $validatedData['bahanBaku'],
            'jumlah' => $validatedData['jumlah'],
            'qty' => $validatedData['jumlah'],
        ]);

        $inventory = Inventory::firstOrCreate(
            ['bahan_baku_id' => $validatedData['bahanBaku']],
            ['stok' => 0],
        );
        $inventory->stok += $validatedData['jumlah'];
        $inventory->save();

        // Optional: return response success
        return response()->json(['message' => 'Stok berhasil disimpan', 'data' => $pembelian]);
    }

    public function bahanBakuStore(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama_bahan_baku' => 'required',
                'satuan' => 'required',
                'kategori_id' => 'required',
            ],[
                'required' => ':attribute harus diisi',
            ]);
        } catch (ValidationException $e) {
            // Tangani error validasi
            $errors = $e->validator->errors()->toArray();

            return response()->json(['errors' => $errors], 422);
        }

        $bahanBaku = BahanBaku::create([
            'bahan_baku' => $request->nama_bahan_baku,
            'satuan' => $request->satuan,
            'kategori_id' => $request->kategori_id,
            'harga' => $request->harga ?? 0,
        ]);

        return response()->json(['message' => 'Bahan baku berhasil disimpan', 'data' => $bahanBaku]);
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
        $stokMasuk = StokMasuk::find($id);
        return view('stokMasuk.edit', compact('stokMasuk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $stokMasuk = StokMasuk::findOrFail($id);
        try {
            $validatedData = $request->validate([
                'bahan_baku_id' => 'required',
                'tanggal_masuk' => 'required',
                'jumlah' => 'required|numeric',
            ], [
                'required' => ':attribute harus diisi',
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        $qtyLama = $stokMasuk->qty; // Get the old qty
        $jumlahLama = $stokMasuk->jumlah;

        $stokMasuk->update([
            'bahan_baku_id' => $validatedData['bahan_baku_id'],
            'tanggal_masuk' => $validatedData['tanggal_masuk'],
            'qty' => $validatedData['jumlah'],
            'jumlah' => $jumlahLama + ($validatedData['jumlah'] - $qtyLama),
        ]);

        $selisihQty = $validatedData['jumlah'] - $qtyLama;
        $inventory = Inventory::where('bahan_baku_id', $validatedData['bahan_baku_id'])->first();
        $inventory->stok += $selisihQty;
        $inventory->save();

        return redirect()->route('stok_masuk.index')->with('success', 'Stok masuk berhasil diperbarui');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stokMasuk = StokMasuk::findOrFail($id);
        $inventory = Inventory::where('bahan_baku_id', $stokMasuk->bahan_baku_id)->first();
        $inventory->stok -= $stokMasuk->jumlah;
        $inventory->save();
        $stokMasuk->delete();
        if ($stokMasuk) {
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
