<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataStok extends Model
{
    protected $table = 'data_stok';
    protected $primaryKey = 'id_stok';
    public $incrementing = true;

    protected $fillable = [
        'merk',
        'stok',
        'permintaan',
        'penjualan',
        'kategori_stok',
    ];

    public function prediksi()
    {
        return $this->hasOne(DataPrediksi::class, 'id_stok', 'id_stok');
    }

    public function likelihood()
    {
        return $this->hasMany(DataLikelihood::class, 'id_stok', 'id_stok');
    }

    public function probabilitas()
    {
        return $this->hasMany(DataProbabilitas::class, 'id_stok', 'id_stok');
    }
}