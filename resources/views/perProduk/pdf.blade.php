<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Stok Semua Bahan Baku</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 4px;
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @foreach($allStockData as $index => $data)
        <h3>Kartu Stok {{ $data['inventory']->bahanBaku->bahan_baku }}</h3>
        <p>Satuan: {{ $data['inventory']->bahanBaku->satuan }}</p>
        <p>Bulan: {{ \Carbon\Carbon::create($tahun, $bulan)->format('F Y') }}</p>
        <p>Sisa Stok Bulan Lalu: {{ $data['stokAwalBulan'] }}</p>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Sisa Stok</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['dailyStok'] as $key => $stok)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($stok['tanggal'])->format('d-m-Y') }}</td>
                    <td>{{ $stok['masuk'] }}</td>
                    <td>{{ $stok['keluar'] }}</td>
                    <td>{{ $stok['sisa_stok'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p>Sisa Stok Akhir Bulan: {{ end($data['dailyStok'])['sisa_stok'] ?? $data['stokAwalBulan'] }}</p>

        @if(!$loop->last)
            <div class="page-break"></div> <!-- Add page break between each stock card -->
        @endif
    @endforeach
</body>
</html>
