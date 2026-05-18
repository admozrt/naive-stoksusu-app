@extends('layouts.app')

@section('title', 'Prediksi Stok')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0 text-gray-800">Prediksi Stok Susu</h1>
        <p class="text-muted">Hasil prediksi menggunakan algoritma Gaussian Naive Bayes</p>
    </div>
</div>

<!-- Ringkasan -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card primary h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-xs text-uppercase text-primary fw-bold mb-1">Total Prediksi</div>
                    <div class="h4 mb-0 fw-bold">{{ $statistik['total'] }}</div>
                </div>
                <div class="text-primary"><i class="fas fa-chart-line fa-2x opacity-50"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card success h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-xs text-uppercase text-success fw-bold mb-1">Hasil Banyak</div>
                    <div class="h4 mb-0 fw-bold">{{ $statistik['banyak'] }}</div>
                </div>
                <div class="text-success"><i class="fas fa-arrow-up fa-2x opacity-50"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card warning h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-xs text-uppercase text-warning fw-bold mb-1">Hasil Sedang</div>
                    <div class="h4 mb-0 fw-bold">{{ $statistik['sedang'] }}</div>
                </div>
                <div class="text-warning"><i class="fas fa-equals fa-2x opacity-50"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card danger h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-xs text-uppercase text-danger fw-bold mb-1">Hasil Sedikit</div>
                    <div class="h4 mb-0 fw-bold">{{ $statistik['sedikit'] }}</div>
                </div>
                <div class="text-danger"><i class="fas fa-arrow-down fa-2x opacity-50"></i></div>
            </div>
        </div>
    </div>
</div>

@if(!$statistik['sudah_training'])
<div class="alert alert-warning d-flex align-items-center" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <div>Model belum dilatih. Tambahkan data di menu <strong>Data Stok</strong>, lalu klik <em>Training Model</em> sebelum melakukan prediksi.</div>
</div>
@endif

<div class="card mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line"></i> Buat Prediksi Baru</h6>
    </div>
    <div class="card-body">
        <form id="formPrediksi">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="pred_merk" class="form-label">Merk</label>
                        <input type="text" class="form-control" id="pred_merk" name="merk" placeholder="Contoh: Indomilk" maxlength="100">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="pred_stok" class="form-label">Stok</label>
                        <input type="text" class="form-control" id="pred_stok" name="stok" placeholder="Contoh: 50,6 atau -10,5" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="pred_permintaan" class="form-label">Permintaan</label>
                        <input type="text" class="form-control" id="pred_permintaan" name="permintaan" placeholder="Contoh: 100,75 atau -5" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="pred_penjualan" class="form-label">Penjualan</label>
                        <input type="text" class="form-control" id="pred_penjualan" name="penjualan" placeholder="Contoh: 75,25 atau -3,5" required>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calculator"></i> Prediksi Sekarang
                </button>
            </div>
        </form>

        <!-- Hasil Prediksi -->
        <div id="hasilPrediksi" class="mt-4" style="display: none;">
            <hr>
            <h5 class="mb-3"><i class="fas fa-check-circle text-success"></i> Hasil Prediksi</h5>

            <div class="alert alert-success" role="alert">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        Kategori prediksi:
                        <strong id="kategoriHasil" class="fs-3 ms-2"></strong>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Confidence</small>
                        <strong id="confidenceHasil" class="fs-5"></strong>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-5 mb-3">
                    <div class="card h-100">
                        <div class="card-header"><strong>Distribusi Probabilitas Posterior</strong></div>
                        <div class="card-body" id="distribusiPosterior"></div>
                    </div>
                </div>
                <div class="col-md-7 mb-3">
                    <div class="card h-100">
                        <div class="card-header"><strong>Rincian Perhitungan Naive Bayes</strong></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0 text-center align-middle" id="tabelDetail">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Kategori</th>
                                            <th rowspan="2">P(C)</th>
                                            <th colspan="3">Likelihood P(xᵢ|C)</th>
                                            <th rowspan="2">Posterior ∝</th>
                                        </tr>
                                        <tr>
                                            <th>Stok</th><th>Permintaan</th><th>Penjualan</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">
                                Rumus: <em>P(C|D) ∝ P(C) · P(stok|C) · P(permintaan|C) · P(penjualan|C)</em>
                                — dengan likelihood pakai Gaussian PDF
                                <em>P(x|C) = (1/(σ√2π)) · exp(−(x−μ)²/(2σ²))</em>.
                                Posterior dinormalisasi terhadap total agar menjumlah 1.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Riwayat Prediksi</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>ID Stok</th>
                        <th>Merk</th>
                        <th>Stok</th>
                        <th>Permintaan</th>
                        <th>Penjualan</th>
                        <th>Hasil Prediksi</th>
                        <th>Tanggal</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prediksi as $index => $p)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $p->id_stok }}</td>
                        <td>{{ $p->dataStok->merk }}</td>
                        <td>{{ str_replace('.', ',', $p->dataStok->stok) }}</td>
                        <td>{{ str_replace('.', ',', $p->dataStok->permintaan) }}</td>
                        <td>{{ str_replace('.', ',', $p->dataStok->penjualan) }}</td>
                        <td>
                            @if($p->prediksi == 'Banyak')
                                <span class="badge bg-success">{{ $p->prediksi }}</span>
                            @elseif($p->prediksi == 'Sedang')
                                <span class="badge bg-warning">{{ $p->prediksi }}</span>
                            @else
                                <span class="badge bg-danger">{{ $p->prediksi }}</span>
                            @endif
                        </td>
                        <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <button class="btn btn-sm btn-danger btn-delete-prediksi" data-id="{{ $p->id_prediksi }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Belum ada data prediksi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Validasi input angka dengan koma dan negatif
    function validateNumericInput(input) {
        // Hanya izinkan angka, koma, minus, dan titik
        input.value = input.value.replace(/[^0-9,\-\.]/g, '');

        // Pastikan minus hanya di awal
        if (input.value.indexOf('-') > 0) {
            input.value = input.value.replace(/-/g, '');
        }

        // Pastikan hanya satu koma atau titik
        const commaCount = (input.value.match(/,/g) || []).length;
        const dotCount = (input.value.match(/\./g) || []).length;

        if (commaCount > 1) {
            input.value = input.value.replace(/,([^,]*)$/, '$1');
        }

        if (dotCount > 1) {
            input.value = input.value.replace(/\.([^\.]*)$/, '$1');
        }
    }

    // Terapkan validasi pada input prediksi
    $('#pred_stok, #pred_permintaan, #pred_penjualan').on('input', function() {
        validateNumericInput(this);
    });

    // Form Prediksi
    $('#formPrediksi').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading
        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang melakukan prediksi',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("prediksi.predict") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.close();
                
                if(response.success) {
                    showToast('success', response.message);
                    
                    // Tampilkan hasil
                    $('#kategoriHasil').text(response.data.prediksi);
                    
                    // Set badge color
                    let badgeClass = 'badge ';
                    if(response.data.prediksi === 'Banyak') {
                        badgeClass += 'bg-success';
                    } else if(response.data.prediksi === 'Sedang') {
                        badgeClass += 'bg-warning';
                    } else {
                        badgeClass += 'bg-danger';
                    }
                    $('#kategoriHasil').attr('class', badgeClass + ' fs-4');
                    
                    const probs = response.data.probabilities;
                    const detail = response.data.detail || {};
                    const winner = response.data.prediksi;

                    // Confidence (probabilitas yang menang setelah normalisasi)
                    const confidence = probs[winner] ?? 0;
                    $('#confidenceHasil').text((confidence * 100).toFixed(2) + '%');

                    // Distribusi posterior - progress bar tiap kategori
                    const colorMap = { 'Banyak': 'bg-success', 'Sedang': 'bg-warning', 'Sedikit': 'bg-danger' };
                    let distHtml = '';
                    $.each(probs, function(kat, p) {
                        const pct = (p * 100).toFixed(2);
                        const color = colorMap[kat] || 'bg-primary';
                        distHtml += '<div class="mb-2">'
                            + '<div class="d-flex justify-content-between"><strong>' + kat + '</strong>'
                            + '<span>' + pct + '%</span></div>'
                            + '<div class="progress" style="height: 18px;">'
                            + '<div class="progress-bar ' + color + '" role="progressbar" style="width:' + pct + '%"></div>'
                            + '</div></div>';
                    });
                    $('#distribusiPosterior').html(distHtml);

                    // Rincian perhitungan (prior, likelihood, posterior unnormalized)
                    let rowsHtml = '';
                    const fmt = (v) => {
                        if (v === 0) return '0';
                        const abs = Math.abs(v);
                        if (abs !== 0 && (abs < 0.0001 || abs > 9999)) return v.toExponential(3);
                        return v.toFixed(6);
                    };
                    $.each(detail, function(kat, d) {
                        const highlight = (kat === winner) ? ' class="table-success fw-bold"' : '';
                        rowsHtml += '<tr' + highlight + '>'
                            + '<td>' + kat + '</td>'
                            + '<td>' + d.prior.toFixed(4) + '</td>'
                            + '<td title="x=' + d.stok.x + ', μ=' + d.stok.mean + ', σ=' + d.stok.std + '">'
                                + fmt(d.stok.likelihood) + '</td>'
                            + '<td title="x=' + d.permintaan.x + ', μ=' + d.permintaan.mean + ', σ=' + d.permintaan.std + '">'
                                + fmt(d.permintaan.likelihood) + '</td>'
                            + '<td title="x=' + d.penjualan.x + ', μ=' + d.penjualan.mean + ', σ=' + d.penjualan.std + '">'
                                + fmt(d.penjualan.likelihood) + '</td>'
                            + '<td>' + fmt(d.posterior_raw) + '</td>'
                            + '</tr>';
                    });
                    $('#tabelDetail tbody').html(rowsHtml);

                    $('#hasilPrediksi').slideDown();

                    // Reload setelah 4 detik agar user sempat baca rincian
                    setTimeout(() => location.reload(), 4000);
                }
            },
            error: function(xhr) {
                Swal.close();
                const message = xhr.responseJSON?.message || 'Gagal melakukan prediksi!';
                showToast('error', message);
            }
        });
    });

    // Delete Prediksi
    $('.btn-delete-prediksi').on('click', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data prediksi akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("prediksi.destroy", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if(response.success) {
                            showToast('success', response.message);
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', 'Gagal menghapus data!');
                    }
                });
            }
        });
    });
});
</script>
@endsection