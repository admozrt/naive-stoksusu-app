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
        $dataStok = DataStok::orderBy('id_stok', 'desc')->get();
        return view('admin.data-stok.index', compact('dataStok'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'merk' => 'required|string|max:100',
            'stok' => 'required|integer',
            'permintaan' => 'required|integer',
            'penjualan' => 'required|integer',
            'kategori_stok' => 'required|in:Banyak,Sedikit,Sedang',
        ]);

        try {
            DataStok::create($request->all());
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
            'stok' => 'required|integer',
            'permintaan' => 'required|integer',
            'penjualan' => 'required|integer',
            'kategori_stok' => 'required|in:Banyak,Sedikit,Sedang',
        ]);

        try {
            $dataStok = DataStok::findOrFail($id);
            $dataStok->update($request->all());
            
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
        $dataStok = DataStok::all();
        $totalData = $dataStok->count();

        if ($totalData == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data untuk training!'
            ], 400);
        }

        // DB::beginTransaction();

        try {
            // Hapus data likelihood dan probabilitas lama
            DataLikelihood::query()->truncate();
            DataProbabilitas::query()->truncate();

            // Kategori yang ada
            $kategori = ['Banyak', 'Sedikit', 'Sedang'];

            // Hitung prior probability untuk setiap kategori
            $priorProbabilities = [];
            foreach ($kategori as $kat) {
                $countKategori = DataStok::where('kategori_stok', $kat)->count();
                $priorProbabilities[$kat] = $countKategori / $totalData;
            }

            // Simpan probabilitas prior
            foreach ($dataStok as $data) {
                foreach ($kategori as $kat) {
                    DataProbabilitas::create([
                        'id_stok' => $data->id_stok,
                        'kategori' => $kat,
                        'probability' => $priorProbabilities[$kat],
                    ]);
                }
            }

            // Hitung likelihood untuk setiap kategori
            foreach ($kategori as $kat) {
                $dataByKategori = DataStok::where('kategori_stok', $kat)->get();
                $countKategori = $dataByKategori->count();

                if ($countKategori > 0) {
                    // Hitung mean untuk setiap atribut
                    $meanStok = $dataByKategori->avg('stok');
                    $meanPermintaan = $dataByKategori->avg('permintaan');
                    $meanPenjualan = $dataByKategori->avg('penjualan');

                    // Hitung standard deviation
                    $stdStok = $this->calculateStdDev($dataByKategori->pluck('stok')->toArray(), $meanStok);
                    $stdPermintaan = $this->calculateStdDev($dataByKategori->pluck('permintaan')->toArray(), $meanPermintaan);
                    $stdPenjualan = $this->calculateStdDev($dataByKategori->pluck('penjualan')->toArray(), $meanPenjualan);

                    // Simpan likelihood untuk semua data
                    foreach ($dataStok as $data) {
                        DataLikelihood::create([
                            'id_stok' => $data->id_stok,
                            'kategori' => $kat,
                            'stok_li' => $meanStok,
                            'permintaan_li' => $meanPermintaan,
                            'penjualan_li' => $meanPenjualan,
                            'stok_std' => $stdStok,
                            'permintaan_std' => $stdPermintaan,
                            'penjualan_std' => $stdPenjualan,
                        ]);
                    }
                } else {
                    // Jika tidak ada data untuk kategori tertentu, set nilai default
                    foreach ($dataStok as $data) {
                        DataLikelihood::create([
                            'id_stok' => $data->id_stok,
                            'kategori' => $kat,
                            'stok_li' => 0,
                            'permintaan_li' => 0,
                            'penjualan_li' => 0,
                            'stok_std' => 1,
                            'permintaan_std' => 1,
                            'penjualan_std' => 1,
                        ]);
                    }
                }
            }

            // DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Training model berhasil! Data likelihood dan probabilitas telah dihitung.'
            ]);

        } catch (\Exception $e) {
            // // Hanya rollback jika transaction masih aktif
            // if (DB::transactionLevel() > 0) {
            //     DB::rollback();
            // }
            
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
        $dataStok = DataStok::orderBy('id_stok', 'asc')->get();

        // Hitung statistik per kategori
        $kategori = ['Banyak', 'Sedikit', 'Sedang'];
        $statistik = [];

        foreach ($kategori as $kat) {
            $dataByKategori = DataStok::where('kategori_stok', $kat)->get();
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
}