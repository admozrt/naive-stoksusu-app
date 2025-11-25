<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Stok Susu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        .info-box {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .info-box p {
            margin: 3px 0;
        }

        h2 {
            font-size: 14px;
            margin: 15px 0 10px 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table thead {
            background-color: #4e73df;
            color: white;
        }

        table thead th {
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }

        table tbody td {
            padding: 6px 5px;
            border-bottom: 1px solid #e3e6f0;
            font-size: 10px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fc;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }

        .badge-success {
            background-color: #1cc88a;
        }

        .badge-warning {
            background-color: #f6c23e;
        }

        .badge-danger {
            background-color: #e74a3b;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .stats-item {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e3e6f0;
            text-align: center;
        }

        .stats-item h3 {
            font-size: 12px;
            margin-bottom: 5px;
            color: #5a5c69;
        }

        .stats-item p {
            font-size: 10px;
            margin: 2px 0;
        }

        .stats-item strong {
            color: #333;
            font-size: 11px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .calculation-section {
            background-color: #fff3cd;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
        }

        .calculation-section h3 {
            font-size: 12px;
            margin-bottom: 8px;
            color: #856404;
        }

        .calculation-section p {
            font-size: 10px;
            margin: 3px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DATA STOK SUSU</h1>
        <p>Sistem Prediksi Stok Menggunakan Algoritma Naive Bayes</p>
        <p>Tanggal: {{ date('d F Y') }}</p>
    </div>

    <div class="info-box">
        <p><strong>Total Data:</strong> {{ $totalData }} record</p>
        <p><strong>Tanggal Cetak:</strong> {{ date('d F Y H:i:s') }}</p>
    </div>

    <h2>Data Stok Susu</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Merk</th>
                <th style="width: 15%;">Stok</th>
                <th style="width: 15%;">Permintaan</th>
                <th style="width: 15%;">Penjualan</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 15%;">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataStok as $index => $data)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $data->merk }}</td>
                <td>{{ $data->stok }}</td>
                <td>{{ $data->permintaan }}</td>
                <td>{{ $data->penjualan }}</td>
                <td>
                    @if($data->kategori_stok == 'Banyak')
                        <span class="badge badge-success">{{ $data->kategori_stok }}</span>
                    @elseif($data->kategori_stok == 'Sedang')
                        <span class="badge badge-warning">{{ $data->kategori_stok }}</span>
                    @else
                        <span class="badge badge-danger">{{ $data->kategori_stok }}</span>
                    @endif
                </td>
                <td>{{ $data->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($statistik) > 0)
    <h2>Perhitungan Naive Bayes</h2>

    <div class="calculation-section">
        <h3>1. Prior Probability (P(Kategori))</h3>
        <p>Prior Probability dihitung dengan rumus: <strong>P(Kategori) = Jumlah Data Kategori / Total Data</strong></p>
        @foreach($statistik as $kategori => $stat)
        <p><strong>P({{ $kategori }}):</strong> {{ $stat['count'] }} / {{ $totalData }} = {{ number_format($stat['prior_probability'], 4) }}</p>
        @endforeach
    </div>

    <h2>Statistik Per Kategori</h2>
    @foreach($statistik as $kategori => $stat)
    <div style="page-break-inside: avoid; margin-bottom: 15px;">
        <h3 style="font-size: 12px; margin-bottom: 8px;">Kategori: {{ $kategori }} ({{ $stat['count'] }} data)</h3>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Mean (μ)</th>
                    <th>Standard Deviasi (σ)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Stok</strong></td>
                    <td>{{ $stat['mean_stok'] }}</td>
                    <td>{{ $stat['std_stok'] }}</td>
                </tr>
                <tr>
                    <td><strong>Permintaan</strong></td>
                    <td>{{ $stat['mean_permintaan'] }}</td>
                    <td>{{ $stat['std_permintaan'] }}</td>
                </tr>
                <tr>
                    <td><strong>Penjualan</strong></td>
                    <td>{{ $stat['mean_penjualan'] }}</td>
                    <td>{{ $stat['std_penjualan'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="calculation-section">
        <h3>2. Likelihood (Gaussian Probability)</h3>
        <p>Likelihood dihitung menggunakan distribusi Gaussian (Normal):</p>
        <p><strong>P(X|Kategori) = (1 / (√(2π) × σ)) × e^(-(x-μ)² / (2σ²))</strong></p>
        <p>Dimana:</p>
        <p>• x = nilai atribut yang akan diprediksi</p>
        <p>• μ (mu) = mean/rata-rata dari atribut pada kategori tertentu</p>
        <p>• σ (sigma) = standard deviasi dari atribut pada kategori tertentu</p>
    </div>

    <div class="calculation-section">
        <h3>3. Posterior Probability</h3>
        <p>Untuk menghitung prediksi kategori, dihitung dengan rumus:</p>
        <p><strong>P(Kategori|Data) = P(Kategori) × P(Stok|Kategori) × P(Permintaan|Kategori) × P(Penjualan|Kategori)</strong></p>
        <p>Kategori dengan nilai Posterior Probability tertinggi adalah hasil prediksi.</p>
    </div>
    @endif

    <div class="footer">
        <p>Sistem Prediksi Stok Susu - Naive Bayes</p>
        <p>Dicetak pada: {{ date('d F Y H:i:s') }}</p>
    </div>
</body>
</html>
