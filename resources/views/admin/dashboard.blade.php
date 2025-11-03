@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <p class="text-muted">Sistem Prediksi Stok Susu Menggunakan Naive Bayes</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row">
    <!-- Total Data -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Data Stok
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalData }}</div>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-database fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Prediksi -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Prediksi
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPrediksi }}</div>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kategori Banyak -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Stok Banyak
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kategoriBanyak }}</div>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-arrow-up fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kategori Sedikit -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Stok Sedikit
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kategoriSedikit }}</div>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-arrow-down fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kategori Distribution -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Distribusi Kategori Stok</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-circle text-success"></i> Banyak</span>
                        <span class="fw-bold">{{ $kategoriBanyak }}</span>
                    </div>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $totalData > 0 ? ($kategoriBanyak/$totalData)*100 : 0 }}%">
                            {{ $totalData > 0 ? number_format(($kategoriBanyak/$totalData)*100, 1) : 0 }}%
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-circle text-warning"></i> Sedang</span>
                        <span class="fw-bold">{{ $kategoriSedang }}</span>
                    </div>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-warning" role="progressbar" 
                             style="width: {{ $totalData > 0 ? ($kategoriSedang/$totalData)*100 : 0 }}%">
                            {{ $totalData > 0 ? number_format(($kategoriSedang/$totalData)*100, 1) : 0 }}%
                        </div>
                    </div>
                </div>

                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-circle text-danger"></i> Sedikit</span>
                        <span class="fw-bold">{{ $kategoriSedikit }}</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-danger" role="progressbar" 
                             style="width: {{ $totalData > 0 ? ($kategoriSedikit/$totalData)*100 : 0 }}%">
                            {{ $totalData > 0 ? number_format(($kategoriSedikit/$totalData)*100, 1) : 0 }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Tentang Sistem</h6>
            </div>
            <div class="card-body">
                <h5 class="mb-3">Algoritma Naive Bayes</h5>
                <p class="text-justify">
                    Sistem ini menggunakan algoritma Naive Bayes untuk memprediksi kategori stok susu
                    berdasarkan data historis penjualan, permintaan, dan stok yang tersedia.
                </p>
                
                <h6 class="mt-4 mb-2">Fitur Sistem:</h6>
                <ul>
                    <li>Manajemen data stok susu</li>
                    <li>Training model Naive Bayes</li>
                    <li>Prediksi kategori stok (Banyak, Sedang, Sedikit)</li>
                    <li>Analisis dan evaluasi model</li>
                    <li>Laporan hasil prediksi</li>
                </ul>

                <div class="alert alert-info mt-3" role="alert">
                    <i class="fas fa-info-circle"></i>
                    <strong>Tips:</strong> Lakukan training model terlebih dahulu sebelum melakukan prediksi!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection