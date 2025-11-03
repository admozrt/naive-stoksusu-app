<?php

namespace App\Http\Controllers;

use App\Models\DataStok;
use App\Models\DataPrediksi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $totalData = DataStok::count();
        $totalPrediksi = DataPrediksi::count();
        $kategoriBanyak = DataStok::where('kategori_stok', 'Banyak')->count();
        $kategoriSedikit = DataStok::where('kategori_stok', 'Sedikit')->count();
        $kategoriSedang = DataStok::where('kategori_stok', 'Sedang')->count();

        return view('admin.dashboard', compact(
            'totalData',
            'totalPrediksi',
            'kategoriBanyak',
            'kategoriSedikit',
            'kategoriSedang'
        ));
    }
}