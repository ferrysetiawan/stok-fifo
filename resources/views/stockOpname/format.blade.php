<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Format Stock Opname</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h4 { margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @foreach($groupedByKategori as $kategori => $stockOpnames)
        <h3>Nama Kategori: {{ $kategori }}</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Bahan Baku</th>
                    <th>Stok Fisik</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                    $currentDate = null;
                @endphp
                @foreach($stockOpnames as $stockOpname)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $stockOpname->bahanBaku->bahan_baku }}</td>
                        <td></td>
                        <td>{{ $stockOpname->bahanBaku->satuan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{-- Uncomment if you need page breaks --}}
        <div class="page-break"></div>
    @endforeach
    <script>window.print()</script>
</body>
</html>
