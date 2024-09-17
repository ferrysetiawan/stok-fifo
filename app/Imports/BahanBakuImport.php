<?php

namespace App\Imports;

use App\Models\BahanBaku;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BahanBakuImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new BahanBaku([
            'kategori_id' => $row['kategori_id'],
            'bahan_baku' => $row['bahan_baku'],
            'satuan' => $row['satuan'],
            'harga' => $row['harga']
        ]);
    }
}
