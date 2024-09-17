<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokKeluar extends Model
{
    use HasFactory;
    protected $table = 'stok_keluar';
    protected $fillable = [
        'bahan_baku_id',
        'jumlah',
        'tanggal_keluar',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
