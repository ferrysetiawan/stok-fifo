<?php

namespace App\Http\Controllers;

use App\Imports\BahanBakuImport;
use App\Models\BahanBaku;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class BahanBakuController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new BahanBakuImport, $request->file('file'));

        return response()->json(['message' => 'Data Imported Successfully']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $kategori = Kategori::all();
        if ($request->ajax()) {
            $data = BahanBaku::with('kategori')
                            ->orderBy('bahan_baku', 'asc')
                            ->get();
            return datatables()->of($data)->make(true);
        }
        return view('bahanBaku.index', compact('kategori'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'harga' => 'required',
                'bahan_baku' => 'required|unique:bahan_baku,bahan_baku',
                'kategori_id' => 'required',
                'satuan' => 'required'
            ],[
                'required' => ':attribute harus diisi',
                'unique' => ':attribute sudah ada',
            ]);
        } catch (ValidationException $e) {
            // Tangani error validasi
            $errors = $e->validator->errors()->toArray();

            return response()->json(['errors' => $errors], 422);
        }

        $menu = BahanBaku::create([
            'harga' => $validatedData['harga'],
            'bahan_baku' => $validatedData['bahan_baku'],
            'kategori_id' => $validatedData['kategori_id'],
            'satuan' => $validatedData['satuan'],
        ]);

        return response()->json(['message' => 'Menu berhasil disimpan', 'data' => $menu]);
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
        $bahanBaku = BahanBaku::find($id);
        return response()->json($bahanBaku);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $menu = BahanBaku::findOrFail($id);
        try {
            $validatedData = $request->validate([
                'bahan_baku' => [
                    'required',
                    Rule::unique('bahan_baku')->ignore($menu->id)->where(function ($query) use ($request) {
                        return $query->where('bahan_baku', $request->bahan_baku);
                    }),
                ],
                'harga' => 'required',
                'satuan' => 'required',
                'kategori_id' => 'required',
            ],[
                'required' => ':attribute harus diisi',
                'unique' => ':attribute sudah ada',
            ], [
                'harga' => 'Harga',
                'bahan_baku' => 'Bahan Baku',
                'kategori_id' => 'Kategori',
            ]);
        } catch (ValidationException $e) {
            // Tangani error validasi
            $errors = $e->validator->errors()->toArray();

            return response()->json(['errors' => $errors], 422);
        }

        $menu->update([
            'bahan_baku' => $validatedData['bahan_baku'],
            'harga' => $validatedData['harga'],
            'satuan' => $validatedData['satuan'],
            'kategori_id' => $validatedData['kategori_id'],
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $menu = BahanBaku::findOrFail($id);
        $menu->delete();
        if ($menu) {
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
