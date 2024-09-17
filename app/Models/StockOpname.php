<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;
    protected $table = 'stock_opname';
    protected $fillable = [
        'bahan_baku_id',
         'stok_awal',
         'penerimaan',
         'pengeluaran',
         'stok_akhir',
         'stok_fisik',
         'selisih',
         'tanggal_opname'
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
