@extends('layouts.global')

@section('title')
    Create Stock Opname
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2-bootstrap4.css') }}">
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Create Stock Opname</h1>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Form Stok Opname</h3>

                    </div>
                    <div class="">
                        <div class="card-body">
                            <form id="pembelianForm" action="{{ route('stockOpname.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="tanggal_opname">Tanggal Opname</label>
                                    <input type="date" class="form-control @error('tanggal_opname') is-invalid @enderror"
                                        name="tanggal_opname" id="tanggal_opname" value="{{ old('tanggal_opname') }}">
                                    @error('tanggal_opname')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <input type="hidden" name="bahan_baku_id" id="BahanBakuId">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Bahan Baku</label>
                                            <select class="form-control select2 @error('bahan_baku_id') is-invalid @enderror" id="bahanBaku"></select>
                                            @error('bahan_baku_id')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="stok_fisik">Stok Fisik</label>
                                    <input type="text" class="form-control @error('stok_fisik') is-invalid @enderror"
                                        name="stok_fisik" id="stok_fisik" value="{{ old('stok_fisik') }}">
                                    @error('stok_fisik')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="satuanBahanBaku">Satuan</label>
                                    <p id="satuanBahanBaku" class="form-control-plaintext">Pilih bahan baku untuk melihat satuan</p>
                                </div>
                                    <button type="submit" class="btn btn-info btn-block">Simpan</button>
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
                                text: item.bahan_baku,
                                satuan: item.satuan
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

        $("#bahanBaku").on('select2:select', function (e) {
            var data = e.params.data;
            var satuan = data.satuan;
            var bahan_baku_id = data.id;

            // Tampilkan satuan di UI
            $("#satuanBahanBaku").text(satuan);
            $("#BahanBakuId").val(bahan_baku_id);
        });
        })
    </script>
@endsection
