<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Kategori::select('id','nama')
                            ->orderBy('nama', 'asc')
                            ->get();
            return datatables()->of($data)->make(true);
        }
        return view('kategori.index');
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
                'nama' => 'required',
            ],[
                'required' => ':attribute harus diisi',
                'unique' => ':attribute sudah ada',
            ]);
        } catch (ValidationException $e) {
            // Tangani error validasi
            $errors = $e->validator->errors()->toArray();

            return response()->json(['errors' => $errors], 422);
        }

        $kategori = Kategori::create([
            'nama' => $validatedData['nama'],
        ]);

        return response()->json(['message' => 'kategori berhasil disimpan', 'data' => $kategori]);
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
        $kategori = Kategori::find($id);
        return response()->json($kategori);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $kategori = Kategori::findOrFail($id);
        try {
            $validatedData = $request->validate([
                'nama' => 'required',
            ],[
                'required' => ':attribute harus diisi',
                'unique' => ':attribute sudah ada',
            ], [
                'nama' => 'Nama Kategori',
                'no_urut' => 'No Urut',
            ]);
        } catch (ValidationException $e) {
            // Tangani error validasi
            $errors = $e->validator->errors()->toArray();

            return response()->json(['errors' => $errors], 422);
        }

        $kategori = Kategori::findOrFail($id);
        $kategori->update([
            'nama' => $validatedData['nama'],
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();
        if ($kategori) {
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
