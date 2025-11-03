<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPrediksi extends Model
{
    protected $table = 'data_prediksi';
    protected $primaryKey = 'id_prediksi';
    public $incrementing = true;

    protected $fillable = [
        'id_stok',
        'prediksi',
    ];

    public function dataStok()
    {
        return $this->belongsTo(DataStok::class, 'id_stok', 'id_stok');
    }
}