@extends('layouts.global')

@section('title')
Tambah Stok Masuk
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2-bootstrap4.css') }}">
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Halaman Tambah Stok Masuk</h1>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-12 col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Form Stok Masuk</h3>

                    </div>
                    <div class="">
                        <div class="card-body">
                            <form id="pembelianForm" action="{{ route('stok_masuk.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="tanggal_masuk">Tanggal Masuk</label>
                                    <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror"
                                        name="tanggal_masuk" id="tanggal_masuk" value="{{ old('tanggal_masuk') }}">
                                    @error('tanggal_masuk')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-10">
                                        <div class="form-group">
                                            <label>Bahan Baku</label>
                                            <select class="form-control select2" id="bahanBaku"></select>
                                        </div>
                                    </div>

                                    <div class="col-2" style="padding-left: 0 !important">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary mt-4 px-3 py-2"
                                                data-toggle="modal" data-target="#bahanBakuModal">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="jumlah">Jumlah</label>
                                    <input type="text" class="form-control @error('jumlah') is-invalid @enderror"
                                        name="jumlah" id="jumlah" value="{{ old('jumlah') }}">
                                    @error('jumlah')
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
            <div class="col-12 col-md-7">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Data Stok Masuk</h3>
                        <form class="form-inline" id="search-form">
                            <div class="form-group">
                                <input type="date" class="form-control" name="tanggal" id="tanggalan">
                            </div>
                            <button class="btn btn-info ml-2" type="submit">Cari</button>
                        </form>
                    </div>
                    <div class="">
                        <div class="row">
                            <div class="card-body data-pembelian">
                                <table class="table table-pembelian table-bordered table-hover" id="table-pembelian">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Bahan Baku</th>
                                            <th>Jumlah</th>
                                            <th>Tgl. Masuk</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="modal fade" id="bahanBakuModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Tambah Bahan Baku</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="bahanBakuForm" action="{{ route('bahan.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_bahan_baku">Bahan Baku</label>
                            <input type="text" class="form-control @error('nama_bahan_baku') is-invalid @enderror"
                                placeholder="Masukkan Bahan Baku" value="{{ old('nama_bahan_baku') }}"
                                name="nama_bahan_baku" id="nama_bahan_baku">
                            @error('nama_bahan_baku')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="satuan">Satuan</label>
                            <input id="satuan" type="text" class="form-control @error('satuan') is-invalid @enderror"
                                placeholder="Masukkan Satuan" value="{{ old('satuan') }}" name="satuan">
                            @error('satuan')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="kategori_id">Kategori</label>
                            <select name="kategori_id" id=""
                                class="form-control @error('kategori_id') is-invalid @enderror" id="kategori_id">
                                @foreach ($kategori as $item)
                                <option value="{{ $item->id }}" {{ old('kategori_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}</option>
                                @endforeach
                            </select>
                            @error('kategori_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="harga">Harga</label>
                            <input id="harga" type="number" class="form-control @error('harga') is-invalid @enderror"
                                placeholder="Masukkan harga" value="{{ old('harga') }}" name="harga">
                            @error('harga')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="{{ asset('assets/modules/select2/dist/js/select2.full.min.js') }}"></script>
<script>
    $(function () {
        var table = $('#table-pembelian').DataTable({
            processing: false,
            serverSide: true,
            responsive: true,
            searching: false,
            paging: false,
            info: false,
            ordering: false,
            ajax: {
                url: "{{ route('stok_masuk.create') }}",
                data: function (d) {
                    d.tanggal = $('#tanggalan').val();
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1; // Menampilkan nomor urut (dimulai dari 1)
                    }
                },
                { data: 'bahan_baku', name:'bahan_baku' },
                { data: 'jumlah', name:'jumlah' },
                { data: 'tanggal_masuk', name:'tanggal_masuk' },
            ]
        });

        $('#search-form').on('submit', function (e) {
            e.preventDefault();
            console.log("Tanggal yang dipilih:", $('#tanggalan').val()); // Tambahkan log untuk memeriksa tanggal yang dipilih
            table.ajax.reload();
        });

        $('#tanggalan').on('change', function () {
            table.ajax.reload();
        });
    });

    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Function to handle AJAX errors
        function handleAjaxError(xhr) {
            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;
                // Display validation errors
                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        var errorMessage = errors[key][0];
                        $('#' + key).addClass('is-invalid');
                        $('#' + key).after('<div class="invalid-feedback">' + errorMessage + '</div>');
                    }
                }
            } else {
                console.error(xhr.responseText);
            }
        }

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
            var satuan = data.satuan; // Ambil satuan dari data yang dipilih

            // Tampilkan satuan di UI
            $("#satuanBahanBaku").text(satuan);
        });

        $("#bahanBakuForm").submit(function (e) {
            e.preventDefault();

            // Serialize form data
            var formData = $(this).serialize();

            // Clear previous styles and error messages
            $(".form-control").removeClass('is-invalid');
            $(".invalid-feedback").remove();

            // AJAX request
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'BERHASIL!',
                        text: 'DATA BERHASIL DISIMPAN!',
                        showConfirmButton: false,
                        timer: 3000
                    }).then(function () {
                        $("#nama_bahan_baku").val(' ');
                        $("#satuan").val(' ');
                        $("#harga").val(' ');
                        $("#bahanBakuModal").modal('hide');
                    });
                },
                error: function (xhr, status, error) {
                    handleAjaxError(xhr);
                }
            });
        });

        $("#pembelianForm").submit(function (e) {
            e.preventDefault();

            // Serialize form data
            var formData = $(this).serialize();
            var bahanBakuId = $("#bahanBaku").val();
            formData += "&bahanBaku=" + bahanBakuId;

            // Clear previous styles and error messages
            $(".form-control").removeClass('is-invalid');
            $(".invalid-feedback").remove();

            // AJAX request
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'BERHASIL!',
                        text: 'DATA BERHASIL DISIMPAN!',
                        showConfirmButton: false,
                        timer: 3000
                    }).then(function () {
                        // $(".data-pembelian").load(location.href +
                        //     ' .data-pembelian')
                        var dataTable = $('#table-pembelian').DataTable();
                        dataTable.ajax.reload();
                        $('#jumlah').val('');
                        $('#bahanBaku').val('').trigger('change');
                    });
                },
                error: function (xhr, status, error) {
                    handleAjaxError(xhr);
                }
            });
        });
    });

</script>
@endsection
