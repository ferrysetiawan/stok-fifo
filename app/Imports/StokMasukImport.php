<?php

namespace App\Imports;

use App\Models\StokMasuk;
use App\Models\Inventory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;

class StokMasukImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                // Skip baris pertama kalau itu header
                if ($index === 0) continue;

                $bahanBakuId = $row[0];
                $jumlah = $row[1];
                $tanggalMasuk = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2]);

                // Simpan ke stok_masuk
                StokMasuk::create([
                    'bahan_baku_id' => $bahanBakuId,
                    'jumlah' => $jumlah,
                    'qty' => $jumlah,
                    'tanggal_masuk' => $tanggalMasuk,
                ]);

                // Update atau create inventory
                $inventory = Inventory::firstOrNew(['bahan_baku_id' => $bahanBakuId]);
                $inventory->stok = ($inventory->stok ?? 0) + $jumlah;
                $inventory->save();
            }
        });
    }
}
