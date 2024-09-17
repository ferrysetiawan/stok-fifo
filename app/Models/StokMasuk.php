<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokMasuk extends Model
{
    use HasFactory;
    protected $table = 'stok_masuk';
    protected $fillable = [
        'bahan_baku_id',
        'jumlah',
        'qty',
        'tanggal_masuk',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
