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
            'stok' => 'required|numeric',
            'permintaan' => 'required|numeric',
            'penjualan' => 'required|numeric',
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

            // Konversi koma ke titik untuk desimal
            $stok = $this->convertToDecimal($request->stok);
            $permintaan = $this->convertToDecimal($request->permintaan);
            $penjualan = $this->convertToDecimal($request->penjualan);

            $kategori = ['Banyak', 'Sedikit', 'Sedang'];
            $probabilities = [];

            foreach ($kategori as $kat) {
                // Ambil data likelihood untuk kategori ini
                $likelihood = DataLikelihood::where('kategori', $kat)->first();
                
                // Ambil prior probability
                $prior = DataProbabilitas::where('kategori', $kat)->first();
                
                if ($likelihood && $prior) {
                    // Gaussian Naive Bayes: P(x|C) = (1 / (sqrt(2π) * σ)) * exp(-((x-μ)² / (2σ²)))
                    $probStok = $this->gaussianProbability($stok, $likelihood->stok_li, $likelihood->stok_std);
                    $probPermintaan = $this->gaussianProbability($permintaan, $likelihood->permintaan_li, $likelihood->permintaan_std);
                    $probPenjualan = $this->gaussianProbability($penjualan, $likelihood->penjualan_li, $likelihood->penjualan_std);

                    // Posterior ∝ P(C) * P(stok|C) * P(permintaan|C) * P(penjualan|C)
                    $posterior = $prior->probability * $probStok * $probPermintaan * $probPenjualan;

                    $probabilities[$kat] = $posterior;
                }
            }

            // Normalisasi agar total probabilitas = 1 (P(D) sebagai evidence)
            $totalProb = array_sum($probabilities);
            if ($totalProb > 0) {
                foreach ($probabilities as $k => $v) {
                    $probabilities[$k] = $v / $totalProb;
                }
            }

            // Cari kategori dengan probability tertinggi
            arsort($probabilities);
            $hasilPrediksi = array_key_first($probabilities);

            // Simpan ke database
            DB::beginTransaction();
            
            // Simpan data stok terlebih dahulu (ditandai sebagai hasil prediksi
            // agar tidak ikut diproses ulang saat training berikutnya)
            $dataStok = DataStok::create([
                'merk' => $request->merk ?: 'Prediksi-' . date('YmdHis'),
                'stok' => $stok,
                'permintaan' => $permintaan,
                'penjualan' => $penjualan,
                'kategori_stok' => $hasilPrediksi,
                'is_training' => false,
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

    private function gaussianProbability($x, $mean, $stdDev)
    {
        // Hindari pembagian dengan nol bila standard deviation = 0
        if ($stdDev == 0) {
            $stdDev = 1e-6;
        }

        $exponent = exp(-pow($x - $mean, 2) / (2 * pow($stdDev, 2)));
        return (1 / (sqrt(2 * pi()) * $stdDev)) * $exponent;
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

    /**
     * Konversi format angka dengan koma menjadi titik untuk desimal
     * Mendukung angka negatif
     */
    private function convertToDecimal($value)
    {
        // Jika sudah berupa angka, return langsung
        if (is_numeric($value)) {
            return $value;
        }

        // Hapus spasi dan ubah koma menjadi titik
        $value = str_replace(' ', '', $value);
        $value = str_replace(',', '.', $value);

        return floatval($value);
    }
}