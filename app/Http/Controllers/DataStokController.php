<?php

namespace App\Http\Controllers;

use App\Models\DataStok;
use App\Models\DataPrediksi;
use App\Models\DataLikelihood;
use App\Models\DataProbabilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DataStokController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $dataStok = DataStok::training()->orderBy('id_stok', 'desc')->get();
        return view('admin.data-stok.index', compact('dataStok'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'merk' => 'required|string|max:100',
            'stok' => 'required|numeric',
            'permintaan' => 'required|numeric',
            'penjualan' => 'required|numeric',
            'kategori_stok' => 'required|in:Banyak,Sedikit,Sedang',
        ]);

        try {
            // Konversi koma ke titik untuk desimal
            $data = $request->all();
            $data['stok'] = $this->convertToDecimal($request->stok);
            $data['permintaan'] = $this->convertToDecimal($request->permintaan);
            $data['penjualan'] = $this->convertToDecimal($request->penjualan);
            $data['is_training'] = true;

            DataStok::create($data);
            return response()->json([
                'success' => true,
                'message' => 'Data stok berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data stok!'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'merk' => 'required|string|max:100',
            'stok' => 'required|numeric',
            'permintaan' => 'required|numeric',
            'penjualan' => 'required|numeric',
            'kategori_stok' => 'required|in:Banyak,Sedikit,Sedang',
        ]);

        try {
            // Konversi koma ke titik untuk desimal
            $data = $request->all();
            $data['stok'] = $this->convertToDecimal($request->stok);
            $data['permintaan'] = $this->convertToDecimal($request->permintaan);
            $data['penjualan'] = $this->convertToDecimal($request->penjualan);

            $dataStok = DataStok::findOrFail($id);
            $dataStok->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data stok berhasil diupdate!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data stok!'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $dataStok = DataStok::findOrFail($id);
            $dataStok->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Data stok berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data stok!'
            ], 500);
        }
    }

    public function training()
    {
        $dataStok = DataStok::training()->get();
        $totalData = $dataStok->count();

        if ($totalData == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data training! Tambahkan data stok berlabel terlebih dahulu.'
            ], 400);
        }

        $kategori = ['Banyak', 'Sedikit', 'Sedang'];

        // Validasi: setiap kategori minimal punya 2 data agar std-dev (n-1) valid
        $kekurangan = [];
        foreach ($kategori as $kat) {
            $count = $dataStok->where('kategori_stok', $kat)->count();
            if ($count < 2) {
                $kekurangan[] = "$kat ($count data)";
            }
        }
        if (!empty($kekurangan)) {
            return response()->json([
                'success' => false,
                'message' => 'Setiap kategori minimal harus punya 2 data training. Kekurangan: '
                    . implode(', ', $kekurangan),
            ], 400);
        }

        DB::beginTransaction();
        try {
            DataLikelihood::query()->delete();
            DataProbabilitas::query()->delete();

            foreach ($kategori as $kat) {
                $dataByKategori = $dataStok->where('kategori_stok', $kat);
                $countKategori = $dataByKategori->count();

                // Prior: P(C) = jumlah data kategori C / total data training
                DataProbabilitas::create([
                    'kategori' => $kat,
                    'probability' => $countKategori / $totalData,
                ]);

                // Likelihood: mean & sample std-dev tiap atribut
                $stoks = $dataByKategori->pluck('stok')->map('floatval')->toArray();
                $permintaans = $dataByKategori->pluck('permintaan')->map('floatval')->toArray();
                $penjualans = $dataByKategori->pluck('penjualan')->map('floatval')->toArray();

                $meanStok = array_sum($stoks) / $countKategori;
                $meanPermintaan = array_sum($permintaans) / $countKategori;
                $meanPenjualan = array_sum($penjualans) / $countKategori;

                DataLikelihood::create([
                    'kategori' => $kat,
                    'stok_li' => $meanStok,
                    'permintaan_li' => $meanPermintaan,
                    'penjualan_li' => $meanPenjualan,
                    'stok_std' => $this->calculateStdDev($stoks, $meanStok),
                    'permintaan_std' => $this->calculateStdDev($permintaans, $meanPermintaan),
                    'penjualan_std' => $this->calculateStdDev($penjualans, $meanPenjualan),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Training berhasil! $totalData data training diproses.",
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Training error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan training: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateStdDev($data, $mean)
    {
        $count = count($data);
        if ($count <= 1) return 1; // Avoid division by zero

        $variance = 0;
        foreach ($data as $value) {
            $variance += pow($value - $mean, 2);
        }
        $variance /= ($count - 1);
        
        return sqrt($variance);
    }

    private function gaussianProbability($x, $mean, $stdDev)
    {
        if ($stdDev == 0) $stdDev = 1; // Avoid division by zero

        $exponent = exp(-pow($x - $mean, 2) / (2 * pow($stdDev, 2)));
        return (1 / (sqrt(2 * pi()) * $stdDev)) * $exponent;
    }

    public function exportPdf()
    {
        $dataStok = DataStok::training()->orderBy('id_stok', 'asc')->get();

        // Hitung statistik per kategori
        $kategori = ['Banyak', 'Sedikit', 'Sedang'];
        $statistik = [];

        foreach ($kategori as $kat) {
            $dataByKategori = $dataStok->where('kategori_stok', $kat);
            $count = $dataByKategori->count();

            if ($count > 0) {
                $statistik[$kat] = [
                    'count' => $count,
                    'prior_probability' => $count / $dataStok->count(),
                    'mean_stok' => round($dataByKategori->avg('stok'), 2),
                    'mean_permintaan' => round($dataByKategori->avg('permintaan'), 2),
                    'mean_penjualan' => round($dataByKategori->avg('penjualan'), 2),
                    'std_stok' => round($this->calculateStdDev($dataByKategori->pluck('stok')->toArray(), $dataByKategori->avg('stok')), 2),
                    'std_permintaan' => round($this->calculateStdDev($dataByKategori->pluck('permintaan')->toArray(), $dataByKategori->avg('permintaan')), 2),
                    'std_penjualan' => round($this->calculateStdDev($dataByKategori->pluck('penjualan')->toArray(), $dataByKategori->avg('penjualan')), 2),
                ];
            }
        }

        $pdf = Pdf::loadView('admin.data-stok.pdf', [
            'dataStok' => $dataStok,
            'statistik' => $statistik,
            'totalData' => $dataStok->count(),
        ]);

        return $pdf->download('laporan-data-stok-' . date('Y-m-d') . '.pdf');
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