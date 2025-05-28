@extends('layouts.global')

@section('title')
    Simple Inventory
@endsection

@section('style')
<style>
    .font-weight-bold {
       font-weight: bold;
   }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between">
        <h1>Halaman Simple Inventory</h1>
        @if ($isStartOfMonth)
            <form action="{{ route('inventory.update-stok-awal-bulan') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Update Stok Awal Bulan</button>
            </form>
        @endif
         <!-- Tombol Export PDF -->
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Tabel Simple Inventory</h3>
                        <span>
                            <a href="{{ route('inventory.simple-export-pdf') }}" class="btn btn-danger">Export PDF</a>
                            <a href="{{ route('inventory.simple-export-excel') }}" class="btn btn-warning">Export Excel</a>
                            <a href="{{ route('stok.export.form') }}" class="btn btn-warning">Export masuk & keluar</a>
                            <a href="{{ route('stok.exportpembelian.form') }}" class="btn btn-warning">Export pembelian</a>
                        </span>
                    </div>
                    <div class="">
                        <div class="card-body">
                            <table class="table table-bordered table-md" id="table-menu">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Bahan Baku</th>
                                        <th>Stok awal</th>
                                        <th>Stok Saat ini</th>
                                        <th>Stok Akhir</th>
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
            var table = $('#table-menu').DataTable({
                // processing: true,
                serverSide: true,
                responsive: true,
                searching: true,
                paging: true,
                info: false,
                ordering: false,
                ajax: {
                    url: "{{ route('inventory.index') }}",
                },
                columns: [
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1; // Menampilkan nomor urut
                        }
                    },
                    { data: 'bahan_baku', name: 'bahan_baku' },
                    { data: 'stok_awal_bulan', name:'stok_awal_bulan' },
                    { data: 'stok_satuan', name:'stok_satuan' },
                    { data: 'stok_akhir_bulan', name:'stok_akhir_bulan' },
                ],
                createdRow: function (row, data, dataIndex) {
                    $('td:eq(2)', row).addClass('font-weight-bold'); // jumlah column
                    $('td:eq(3)', row).addClass('font-weight-bold'); // jumlah column
                }
            });
        });
    </script>
@endsection
