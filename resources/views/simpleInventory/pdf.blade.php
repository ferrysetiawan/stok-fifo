<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Inventory PDF</title>
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
    <h1>Simple Inventory</h1>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Bahan Baku</th>
                <th>Stok Awal Bulan</th>
                <th>Stok Saat Ini</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->bahanBaku ? $item->bahanBaku->bahan_baku : '-' }}</td>
                    <td>{{ $item->stok_awal_bulan . ' ' . ($item->bahanBaku ? $item->bahanBaku->satuan : '-') }}</td>
                    <td>{{ $item->stok . ' ' . ($item->bahanBaku ? $item->bahanBaku->satuan : '-') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
