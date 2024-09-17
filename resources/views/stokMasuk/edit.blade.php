@extends('layouts.global')

@section('title')
Ubah Stok Masuk
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2-bootstrap4.css') }}">
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Halaman Ubah Stok Masuk</h1>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Form Ubah Stok Masuk</h3>

                    </div>
                    <div class="">
                        <div class="card-body">
                            <form action="{{ route('stok_masuk.update', $stokMasuk->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="field_name">Tanggal Masuk</label>
                                    <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror"
                                        name="tanggal_masuk"
                                        value="{{ old('tanggal_masuk', $stokMasuk->tanggal_masuk) }}">
                                    @error('tanggal_masuk')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Bahan Baku</label>
                                    <select name="bahan_baku_id" class="form-control select2" id="bahanBaku">
                                        <option value="{{ $stokMasuk->bahanBaku->id }}">{{ $stokMasuk->bahanBaku->bahan_baku }}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="field_name">Qty</label>
                                    <input type="number" class="form-control @error('jumlah') is-invalid @enderror"
                                        name="jumlah"
                                        value="{{ old('jumlah', $stokMasuk->qty) }}">
                                    @error('jumlah')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <button class="btn btn-primary btn-block" type="submit">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
<script src="{{ asset('assets/modules/select2/dist/js/select2.full.min.js') }}"></script>
<script>
    $(document).ready(function () {
        $("#bahanBaku").select2({
            ajax: {
                url: '/stok/bahanbaku',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term, // Parameter pencarian
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.id,
                                text: item.bahan_baku
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 1, // Jumlah karakter minimal sebelum pencarian dimulai
            placeholder: 'Pilih Bahan Baku',
            escapeMarkup: function (markup) {
                return markup;
            } // Mengizinkan markup HTML pada hasil pencarian
        });
    });

</script>
@endsection
