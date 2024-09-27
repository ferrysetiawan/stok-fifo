@extends('layouts.global')

@section('title', 'Inventory Card')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between">
        <h1>Halaman Kartu Bahan Baku</h1>
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
            <a id="cetakKartuButton" href="{{ route('inventory.downloadAllPdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-primary ml-2">Cetak Kartu</a>

        </div>
    </div>
    <div class="section-body">
        <div class="row my-5">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h3 class="text-light">Tabel Kartu Bahan Baku</h3>
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

         // Fungsi untuk update URL di tombol cetak PDF
          // Fungsi untuk update URL di tombol cetak PDF
        function updateCetakKartuUrl() {
            var bulan = $('#bulan').val();
            var tahun = $('#tahun').val();

            if (bulan && tahun) {
                // Buat URL dengan format yang benar (tanpa :bulan dan :tahun)
                var newUrl = "{{ route('inventory.downloadAllPdf') }}?bulan=" + bulan + "&tahun=" + tahun;

                // Set href baru di tombol cetak kartu
                $('#cetakKartuButton').attr('href', newUrl);
            }
        }

        // Update tabel dan URL tombol cetak saat tombol filter diklik
        $('#filterButton').on('click', function () {
            table.draw(); // Update DataTables berdasarkan filter
            updateCetakKartuUrl(); // Update href tombol cetak PDF
        });

        // Update href tombol cetak PDF saat bulan atau tahun diubah
        $('#bulan, #tahun').on('change', function () {
            updateCetakKartuUrl(); // Setiap kali bulan/tahun berubah, perbarui href
        });
    });
</script>
@endsection
