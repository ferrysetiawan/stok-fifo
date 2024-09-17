@extends('layouts.global')

@section('title')
    Inventory History
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
        <h1>Halaman Inventory History</h1>
        @if ($isSpecificDates)
        <form id="updateStokAkhirBulanForm" action="{{ route('inventory.update-stok-akhir-bulan') }}" method="POST">
            @csrf
            <input type="hidden" name="isStartOfMonth" id="isStartOfMonth" value="false">
            <button type="submit" class="btn btn-primary">Update Stok Akhir Bulan</button>
        </form>
        @endif
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Tabel Inventory History</h3>
                        <div class="d-flex">
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
                            <button id="exportPdfButton" class="btn btn-danger ml-2 px-4">Export PDF</button>
                        </div>
                    </div>
                    <div class="">
                        <div class="card-body">
                            <table class="table table-bordered table-md" id="table-menu">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Bahan Baku</th>
                                        <th>Stok Awal Bulan</th>
                                        <th>Total Rupiah Awal</th>
                                        <th>Stok Akhir Bulan</th>
                                        <th>Total Rupiah Akhir</th>
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
        $('#exportPdfButton').click(function() {
            var bulan = $('#bulan').val();
            var tahun = $('#tahun').val();

            var exportUrl = '{{ route('inventory.export-pdf') }}' + '?bulan=' + bulan + '&tahun=' + tahun;
            window.location.href = exportUrl;
        });
        $(document).ready(function () {
            var table = $('#table-menu').DataTable({
                serverSide: true,
                responsive: true,
                searching: true,
                paging: true,
                info: false,
                ordering: false,
                ajax: {
                    url: "{{ route('inventory.history') }}",
                    data: function(d) {
                        d.bulan = $('#bulan').val();
                        d.tahun = $('#tahun').val();
                    }
                },
                columns: [
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1; // Menampilkan nomor urut
                        }
                    },
                    { data: 'bahan_baku', name: 'bahan_baku' },
                    { data: 'stok_awal_bulan', name: 'stok_awal_bulan' },
                    { data: 'total_rupiah_awal', name: 'total_rupiah_awal' },
                    { data: 'stok_akhir_bulan', name: 'stok_akhir_bulan' },
                    { data: 'total_rupiah_akhir', name: 'total_rupiah_akhir' },
                ],
                createdRow: function (row, data, dataIndex) {
                    $('td:eq(2)', row).addClass('font-weight-bold'); // Menambahkan kelas font-weight-bold pada kolom stok awal
                }
            });

            $('#filterButton').click(function() {
                table.ajax.reload();
            });

            $('#updateStokAkhirBulanForm').submit(function(e) {
                var isStartOfMonth = (new Date()).getDate() === 1;
                $('#isStartOfMonth').val(isStartOfMonth ? 'true' : 'false');
            });
        });
    </script>
@endsection
