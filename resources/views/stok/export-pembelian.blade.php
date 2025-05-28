@extends('layouts.global')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between">
        <h1>Halaman Export</h1>

        <!-- Tombol Export PDF -->
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">

                    <div class="">
                        <div class="card-body">
                            <form action="{{ route('stok.exportpembelian.excel') }}" method="GET">
                                <div class="row mb-3">
                                    <div class="col-md-5">
                                        <label>Tanggal Mulai</label>
                                        <input type="date" name="start_date" class="form-control" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label>Tanggal Akhir</label>
                                        <input type="date" name="end_date" class="form-control" required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-success w-100">Export Excel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
