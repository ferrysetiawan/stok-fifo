<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $table = 'inventory';
    protected $fillable = [
        'bahan_baku_id',
        'stok',
        'stok_awal_bulan',
        'stok_akhir_bulan',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function bahanBakuMasuks()
    {
        return $this->hasMany(StokMasuk::class, 'bahan_baku_id', 'bahan_baku_id');
    }

    public function stokKeluar()
    {
        return $this->hasMany(StokKeluar::class, 'bahan_baku_id', 'bahan_baku_id');
    }

    public function getTotalStokAttribute()
    {
        return $this->bahanBakuMasuks->sum('jumlah');
    }

    public static function updateStokAkhirBulan(Carbon $date)
    {
        $inventories = self::all();
        foreach ($inventories as $inventory) {
            $stokMasukBulanIni = $inventory->stok;

            $stokAkhirBulan = $stokMasukBulanIni;

            // Simpan riwayat stok akhir bulan
            \App\Models\MonthlyStockHistory::create([
                'bahan_baku_id' => $inventory->bahan_baku_id,
                'stok_awal_bulan' => $inventory->stok_awal_bulan,
                'stok_akhir_bulan' => $stokAkhirBulan,
                'bulan' => $date->startOfMonth()->toDateString(),
            ]);

            $inventory->stok_akhir_bulan = $stokAkhirBulan;
            $inventory->save();
        }
    }

    public static function updateStokAwalBulan()
    {
        $inventories = self::all();
        foreach ($inventories as $inventory) {
            $inventory->stok_awal_bulan = $inventory->stok;
            $inventory->save();
        }
    }
}

