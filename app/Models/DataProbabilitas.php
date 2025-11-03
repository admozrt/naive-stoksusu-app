<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataProbabilitas extends Model
{
    protected $table = 'data_probabilitas';
    protected $primaryKey = 'id_probabilitas';
    public $incrementing = true;

    protected $fillable = [
        'id_stok',
        'kategori',
        'probability',
    ];

    public function dataStok()
    {
        return $this->belongsTo(DataStok::class, 'id_stok', 'id_stok');
    }
}