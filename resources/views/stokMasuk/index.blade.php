@extends('layouts.global')

@section('title')
    Stok Masuk
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2-bootstrap4.css') }}">
@endsection

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between">
        <div class="d-flex">
            <input type="text" id="nama_bahan_baku" class="form-control mr-2" placeholder="Nama Bahan Baku">
            <select id="bulan" class="form-control mr-2">
                <option value="">Pilih Bulan</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ now()->month == $i ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                @endfor
            </select>
            <select id="tahun" class="form-control mr-2">
                <option value="">Pilih Tahun</option>
                @for ($i = now()->year; $i >= 2000; $i--)
                    <option value="{{ $i }}" {{ now()->year == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
            <button id="filterButton" class="btn btn-success px-4">Filter</button>
            <button id="downloadButton" class="btn btn-info px-4 ml-2">Download</button>
            {{-- <form action="{{ route('stok-masuk.import') }}" method="POST" enctype="multipart/form-data" class="form-inline">
                @csrf

                <input type="file" name="file" required>
                <button type="submit">Import Stok Masuk</button>
            </form> --}}
        </div>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Tabel Stok Masuk</h3>
                        <a href="{{ route('stok_masuk.create') }}" class="btn btn-light">
                            Tambah Data
                        </a>
                    </div>
                    <div class="">
                        <div class="card-body">
                            <table class="table table-bordered table-md" id="table-stokmasuk">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Bahan Baku</th>
                                        <th>Jumlah</th>
                                        <th>Sisa</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // $('#filterButton').click(function() {
            //     table.ajax.reload();
            // });

            var table = $('#table-stokmasuk').DataTable({
                // processing: true,
                serverSide: true,
                responsive: true,
                searching: false,
                paging: true,
                info: false,
                ordering: false,
                ajax: {
                    url: "{{ route('stok_masuk.index') }}",
                    data: function (d) {
                        d.bulan = $('#bulan').val();
                        d.tahun = $('#tahun').val();
                        d.nama_bahan_baku = $('#nama_bahan_baku').val();
                    }
                },
                columns: [
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1; // Menampilkan nomor urut
                        }
                    },
                    { data: 'bahan_baku', name: 'bahan_baku_id' },
                    { data: 'qty', name: 'qty' },
                    { data: 'jumlah', name: 'jumlah' },
                    { data: 'tanggal_masuk', name: 'tanggal_masuk' },
                    {
                        data: null,
                        render: function (data) {
                            return '<a class="btn btn-warning mr-1" data-toggle="tooltip" data-placement="bottom" title="ubah" href="{{ url('stok_masuk') }}/' + data.id + '/edit"><i class="fas fa-pencil-alt"></i></a>' +
                                '<button class="btn btn-danger" data-toggle="tooltip" data-placement="bottom" title="hapus" onclick="destroy(' + data.id + ')" id="' + data.id + '"><i class="fas fa-trash"></i></button>';
                        }
                    }
                ]
            });

            $('#filterButton').click(function() {
                table.ajax.reload();
            });

            $('#downloadButton').click(function () {
                var bulan = $('#bulan').val();
                var tahun = $('#tahun').val();
                var start_date = tahun + '-' + bulan + '-01';
                var end_date = tahun + '-' + bulan + '-' + new Date(tahun, bulan, 0).getDate(); // Mendapatkan jumlah hari dalam bulan

                // Redirect untuk download
                window.location.href = "{{ route('stok_masuk.export') }}?start_date=" + bulan + "&end_date=" + tahun;
            });
        });



        function destroy(id) {
            var id = id;
            var token = $("meta[name='csrf-token']").attr("content");

            Swal.fire({
                title: 'APAKAH KAMU YAKIN ?',
                text: "INGIN MENGHAPUS DATA INI!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'BATAL',
                confirmButtonText: 'YA, HAPUS!',
            }).then((result) => {
                if (result.isConfirmed) {
                    //ajax delete
                    jQuery.ajax({
                        url: `stok_masuk/${id}`,
                        data: {
                            "id": id,
                            "_token": token
                        },
                        type: 'DELETE',
                        success: function (response) {
                            if (response.status == "success") {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'BERHASIL!',
                                    text: 'DATA BERHASIL DIHAPUS!',
                                    showConfirmButton: false,
                                    timer: 3000
                                }).then(function () {
                                    var dataTable = $('#table-stokmasuk').DataTable();
                                    dataTable.ajax.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'GAGAL!',
                                    text: 'DATA GAGAL DIHAPUS!',
                                    showConfirmButton: false,
                                    timer: 3000
                                }).then(function () {
                                    var dataTable = $('#table-stokmasuk').DataTable();
                                    dataTable.ajax.reload();
                                });
                            }
                        }
                    });
                }
            })
        }
    </script>
@endsection
