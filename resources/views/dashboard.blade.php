@extends('layouts.global')
@section('title')
Dashboard
@endsection
@section('style')
    <style>
        .card.card-statistic-1 .card-body {
            font-size: 16px !important;
        }
    </style>
@endsection
@section('content')
<section class="section">
    <div class="section-header">
        <h1>Halaman Dashboard</h1>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                    <i class="fas fa-box"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Stok Awal</h4>
                    </div>
                    <div class="card-body">
                        {{ formatRupiah($grandInitialStockValue) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-money-check"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Belanja</h4>
                    </div>
                    <div class="card-body">
                       {{ formatRupiah($grandTotalValue) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success">
                    <i class="far fa-money-bill-alt"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Stok Akhir</h4>
                    </div>
                    <div class="card-body">
                        {{ formatRupiah($grandUsedValue) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-list"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Cost Bulanan</h4>
                    </div>
                    <div class="card-body">
                        {{ formatRupiah($costBulanan) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
