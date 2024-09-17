<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory History PDF</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Inventory History - {{ \Carbon\Carbon::createFromDate(null, $bulan)->format('F') }} {{ $tahun }}</h1>

    <table>
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
        <tbody>
            @foreach ($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->bahanBaku ? $item->bahanBaku->bahan_baku : '-' }}</td>
                    <td>{{ $item->stok_awal_bulan }} {{ $item->bahanBaku ? $item->bahanBaku->satuan : '-' }}</td>
                    <td>{{ formatRupiah($item->stok_awal_bulan * $item->bahanBaku->harga) }}</td>
                    <td>{{ $item->stok_akhir_bulan }} {{ $item->bahanBaku ? $item->bahanBaku->satuan : '-' }}</td>
                    <td>{{ formatRupiah($item->stok_akhir_bulan * $item->bahanBaku->harga) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
