@extends('layouts.global')

@section('title', 'Simple Inventory')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between">
        <h1>Halaman Simple Inventory</h1>
        <div class="d-flex">
            <select id="bulan" class="form-control mr-2">
                <option value="">Pilih Bulan</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                @endfor
            </select>
            <select id="tahun" class="form-control mr-2">
                <option value="">Pilih Tahun</option>
                @for ($i = now()->year; $i >= 2000; $i--)
                    <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
            <button id="filterButton" class="btn btn-success px-4">Filter</button>
        </div>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Tabel Simple Inventory</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-md" id="table-menu">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Bahan Baku</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
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
            serverSide: true,
            responsive: true,
            searching: true,
            paging: true,
            info: false,
            ordering: false,
            ajax: {
                url: "{{ route('inventory.perProduk') }}",
                data: function (d) {
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
                { data: 'action', name: 'action' }
            ],
        });

        $('#filterButton').on('click', function () {
            table.draw();
        });
    });
</script>
@endsection
