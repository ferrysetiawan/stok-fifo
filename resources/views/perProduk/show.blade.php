@extends('layouts.global')

@section('title', 'Detail Kartu Stok')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Kartu Stok</h1>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h5>KARTU STOK LARAS GARDEN RESTO</h5>
                                <p>Nama Barang: {{ $inventory->bahanBaku->bahan_baku }}</p>
                                <p>Bulan: {{ \Carbon\Carbon::createFromDate($tahun, $bulan)->format('F Y') }}</p>
                                <p>Sisa Stock Bulan Lalu: {{ $stokAwalBulan }}</p>
                            </div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Keluar</th>
                                    <th>Sisa Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyStok as $index => $stok)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($stok['tanggal'])->format('d-m-Y') }}</td>
                                        <td>{{ $stok['masuk'] }}</td>
                                        <td>{{ $stok['keluar'] }}</td>
                                        <td>{{ $stok['sisa_stok'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <p>Sisa Stok Akhir Bulan: {{ end($dailyStok)['sisa_stok'] ?? 0 }}</p>

                        <a href="{{ route('inventory.perProduk', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
