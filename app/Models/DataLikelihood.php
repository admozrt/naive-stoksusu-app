<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataLikelihood extends Model
{
    protected $table = 'data_likelihood';
    protected $primaryKey = 'id_likelihood';
    public $incrementing = true;

    protected $fillable = [
        'id_stok',
        'kategori',
        'stok_li',
        'permintaan_li',
        'penjualan_li',
        'stok_std',
        'permintaan_std',
        'penjualan_std',
    ];

    public function dataStok()
    {
        return $this->belongsTo(DataStok::class, 'id_stok', 'id_stok');
    }
}