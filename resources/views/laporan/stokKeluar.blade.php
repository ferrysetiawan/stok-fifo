@extends('layouts.global')

@section('title')
    Laporan Stok Keluar
@endsection

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between">
        <h1>Halaman Laporan Stok Keluar</h1>
        <form method="GET" class="d-flex" action="{{ url('/laporan-stok-keluar') }}">
            <select name="bulan" id="bulan" class="form-control mr-2">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $m == $bulan ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endforeach
            </select>
            <input type="number" class="form-control mr-2" name="tahun" id="tahun" value="{{ $tahun }}" min="2000" max="{{ \Carbon\Carbon::now()->year }}">

            <button type="submit" class="btn btn-success">Tampilkan</button>
        </form>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">
                            Laporan Stok Keluar Bulan {{ DateTime::createFromFormat('!m', $bulan)->format('F') }} {{ $tahun }}
                        </h3>
                    </div>
                    <div class="">
                        <div class="card-body">
                            @if($stokMasuk->isEmpty())
                                <p>Data tidak ditemukan untuk bulan yang dipilih.</p>
                            @else
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Bahan Baku</th>
                                            <th>Total Qty</th>
                                            <th>Satuan</th>
                                            <th>Kategori</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stokMasuk as $index => $stok)
                                            @php
                                                $bahanBaku = $bahanBakuList->find($stok->bahan_baku_id);
                                                $nomorUrut = ($stokMasuk->currentPage() - 1) * $stokMasuk->perPage() + $index + 1;
                                            @endphp
                                            <tr>
                                                <td>{{ $nomorUrut }}</td> <!-- Nomor urut -->
                                                <td>{{ $bahanBaku->bahan_baku }}</td> <!-- Nama bahan baku -->
                                                <td>{{ $stok->total_qty }}</td> <!-- Total qty -->
                                                <td>{{ $bahanBaku->satuan }}</td>
                                                <td>{{ $bahanBaku->kategori->nama }}</td> <!-- Nama kategori -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="float-right">
                                    {{ $stokMasuk->appends(['bulan' => $bulan, 'tahun' => $tahun])->links('pagination::bootstrap-4') }}
                                </div>
                                <!-- Tampilkan pagination dan tambahkan query string -->
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
