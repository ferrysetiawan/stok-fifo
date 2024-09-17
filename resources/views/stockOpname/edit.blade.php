@extends('layouts.global')

@section('title')
    Edit Stock Opname
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Stock Opname</h1>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary">
                        <h3 class="text-light">Form Edit Stock Opname</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('stockOpname.update', $stockOpname->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="bahan_baku">Nama Bahan Baku</label>
                                <input type="text" id="bahan_baku" class="form-control" value="{{ $stockOpname->bahanBaku->bahan_baku }}" readonly>
                            </div>

                            <div class="form-group">
                                <label for="stok_fisik">Stok Fisik</label>
                                <input type="number" id="stok_fisik" name="stok_fisik" class="form-control" min="0" value="{{ old('stok_fisik', $stockOpname->stok_fisik) }}" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
