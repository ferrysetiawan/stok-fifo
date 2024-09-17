<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyStockHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'bahan_baku_id',
        'stok_awal_bulan',
        'stok_akhir_bulan',
        'bulan'
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
