@extends('layouts.app')

@section('title', 'Prediksi Stok')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0 text-gray-800">Prediksi Stok Susu</h1>
        <p class="text-muted">Hasil prediksi menggunakan algoritma Naive Bayes</p>
    </div>
</div>

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
            <h5 class="mb-3">Hasil Prediksi:</h5>
            <div class="alert alert-success" role="alert">
                <h5 class="alert-heading"><i class="fas fa-check-circle"></i> Prediksi Berhasil!</h5>
                <hr>
                <p class="mb-0">
                    Kategori Stok: <strong id="kategoriHasil" class="fs-4"></strong>
                </p>
            </div>

            <div class="card">
                <div class="card-header">
                    <strong>Detail Probabilitas</strong>
                </div>
                <div class="card-body">
                    <div id="detailProbabilitas"></div>
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
                    
                    // Tampilkan detail probabilitas
                    let probHtml = '<table class="table table-sm">';
                    probHtml += '<tr><th>Kategori</th><th>Probabilitas</th></tr>';
                    $.each(response.data.probabilities, function(kategori, prob) {
                        probHtml += '<tr><td>' + kategori + '</td><td>' + prob.toFixed(6) + '</td></tr>';
                    });
                    probHtml += '</table>';
                    $('#detailProbabilitas').html(probHtml);
                    
                    $('#hasilPrediksi').slideDown();
                    
                    // Reload after 2 seconds
                    setTimeout(() => location.reload(), 2000);
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