<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;
    protected $table = 'bahan_baku';
    protected $fillable = [
        'kategori_id',
        'bahan_baku',
        'satuan',
        'harga'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function stokMasuk()
    {
        return $this->hasMany(StokMasuk::class);
    }
}
