@extends('layouts.global')

@section('title')
Tambah Stok Keluar
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2-bootstrap4.css') }}">
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Halaman Tambah Stok Keluar</h1>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-12 col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Form Stok Keluar</h3>

                    </div>
                    <div class="">
                        <div class="card-body">
                            <form id="pembelianForm" action="{{ route('stok_keluar.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="tanggal_keluar">Tanggal Keluar</label>
                                    <input type="date" class="form-control @error('tanggal_keluar') is-invalid @enderror"
                                        name="tanggal_keluar" id="tanggal_keluar" value="{{ old('tanggal_keluar') }}">
                                    @error('tanggal_keluar')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Bahan Baku</label>
                                            <select class="form-control select2" id="bahanBaku"></select>
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
                        <h3 class="text-light">Data Stok Keluar</h3>
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
                                            <th>Tgl. Keluar</th>
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
                url: "{{ route('stok_keluar.create') }}",
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
                { data: 'tanggal_keluar', name:'tanggal_keluar' },
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
