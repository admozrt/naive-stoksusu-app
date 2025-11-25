<?php

namespace App\Http\Controllers;

use App\Models\DataStok;
use App\Models\DataPrediksi;
use App\Models\DataLikelihood;
use App\Models\DataProbabilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrediksiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $prediksi = DataPrediksi::with('dataStok')->orderBy('id_prediksi', 'desc')->get();
        return view('admin.prediksi.index', compact('prediksi'));
    }

    public function create()
    {
        return view('admin.prediksi.create');
    }

    public function predict(Request $request)
    {
        $request->validate([
            'merk' => 'nullable|string|max:100',
            'stok' => 'required|integer',
            'permintaan' => 'required|integer',
            'penjualan' => 'required|integer',
        ]);

        try {
            // Cek apakah sudah ada data training
            $likelihoodCount = DataLikelihood::count();
            if ($likelihoodCount == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada data training! Lakukan training terlebih dahulu.'
                ], 400);
            }

            $stok = $request->stok;
            $permintaan = $request->permintaan;
            $penjualan = $request->penjualan;

            $kategori = ['Banyak', 'Sedikit', 'Sedang'];
            $probabilities = [];

            foreach ($kategori as $kat) {
                // Ambil data likelihood untuk kategori ini
                $likelihood = DataLikelihood::where('kategori', $kat)->first();
                
                // Ambil prior probability
                $prior = DataProbabilitas::where('kategori', $kat)->first();
                
                if ($likelihood && $prior) {
                    // Hitung probability menggunakan Gaussian Naive Bayes (simplified)
                    $probStok = $this->calculateProbability($stok, $likelihood->stok_li);
                    $probPermintaan = $this->calculateProbability($permintaan, $likelihood->permintaan_li);
                    $probPenjualan = $this->calculateProbability($penjualan, $likelihood->penjualan_li);
                    
                    // Hitung posterior probability
                    $posterior = $prior->probability * $probStok * $probPermintaan * $probPenjualan;
                    
                    $probabilities[$kat] = $posterior;
                }
            }

            // Cari kategori dengan probability tertinggi
            arsort($probabilities);
            $hasilPrediksi = array_key_first($probabilities);

            // Simpan ke database
            DB::beginTransaction();
            
            // Simpan data stok terlebih dahulu
            $dataStok = DataStok::create([
                'merk' => $request->merk ?? 'Prediksi-' . date('YmdHis'),
                'stok' => $stok,
                'permintaan' => $permintaan,
                'penjualan' => $penjualan,
                'kategori_stok' => $hasilPrediksi,
            ]);

            // Simpan hasil prediksi
            DataPrediksi::create([
                'id_stok' => $dataStok->id_stok,
                'prediksi' => $hasilPrediksi,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Prediksi berhasil!',
                'data' => [
                    'prediksi' => $hasilPrediksi,
                    'probabilities' => $probabilities,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan prediksi: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateProbability($value, $mean, $stdDev = 10)
    {
        // Simplified probability calculation
        $diff = abs($value - $mean);
        return 1 / (1 + $diff / 100); // Simple probability based on difference
    }

    public function destroy($id)
    {
        try {
            $prediksi = DataPrediksi::findOrFail($id);
            $prediksi->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Data prediksi berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data prediksi!'
            ], 500);
        }
    }
}