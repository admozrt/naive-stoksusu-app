@extends('layouts.app')

@section('title', 'Data Stok')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0 text-gray-800">Data Stok Susu</h1>
        <p class="text-muted">Kelola data stok susu untuk training model</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Data Stok</h6>
        <div>
            <button class="btn btn-success btn-sm" id="btnTraining">
                <i class="fas fa-cogs"></i> Training Model
            </button>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Minggu</th>
                        <th>Stok</th>
                        <th>Permintaan</th>
                        <th>Penjualan</th>
                        <th>Kategori</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataStok as $index => $data)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $data->minggu }}</td>
                        <td>{{ $data->stok }}</td>
                        <td>{{ $data->permintaan }}</td>
                        <td>{{ $data->penjualan }}</td>
                        <td>
                            @if($data->kategori_stok == 'Banyak')
                                <span class="badge bg-success">{{ $data->kategori_stok }}</span>
                            @elseif($data->kategori_stok == 'Sedang')
                                <span class="badge bg-warning">{{ $data->kategori_stok }}</span>
                            @else
                                <span class="badge bg-danger">{{ $data->kategori_stok }}</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit" 
                                    data-id="{{ $data->id_stok }}"
                                    data-minggu="{{ $data->minggu }}"
                                    data-stok="{{ $data->stok }}"
                                    data-permintaan="{{ $data->permintaan }}"
                                    data-penjualan="{{ $data->penjualan }}"
                                    data-kategori="{{ $data->kategori_stok }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $data->id_stok }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Data Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="minggu" class="form-label">Minggu</label>
                        <input type="number" class="form-control" id="minggu" name="minggu" required>
                    </div>
                    <div class="mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" required>
                    </div>
                    <div class="mb-3">
                        <label for="permintaan" class="form-label">Permintaan</label>
                        <input type="number" class="form-control" id="permintaan" name="permintaan" required>
                    </div>
                    <div class="mb-3">
                        <label for="penjualan" class="form-label">Penjualan</label>
                        <input type="number" class="form-control" id="penjualan" name="penjualan" required>
                    </div>
                    <div class="mb-3">
                        <label for="kategori_stok" class="form-label">Kategori Stok</label>
                        <select class="form-select" id="kategori_stok" name="kategori_stok" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Banyak">Banyak</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Sedikit">Sedikit</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Data Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit">
                <input type="hidden" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_minggu" class="form-label">Minggu</label>
                        <input type="number" class="form-control" id="edit_minggu" name="minggu" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="edit_stok" name="stok" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_permintaan" class="form-label">Permintaan</label>
                        <input type="number" class="form-control" id="edit_permintaan" name="permintaan" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_penjualan" class="form-label">Penjualan</label>
                        <input type="number" class="form-control" id="edit_penjualan" name="penjualan" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_kategori_stok" class="form-label">Kategori Stok</label>
                        <select class="form-select" id="edit_kategori_stok" name="kategori_stok" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Banyak">Banyak</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Sedikit">Sedikit</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Tambah CSRF token untuk semua AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Tambah Data
    $('#formTambah').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Sedang menyimpan data',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("data-stok.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.close();
                if(response.success) {
                    $('#modalTambah').modal('hide');
                    $('#formTambah')[0].reset();
                    showToast('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('error', response.message || 'Gagal menambahkan data!');
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMessage = 'Gagal menambahkan data!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    errorMessage = 'Data yang dimasukkan tidak valid';
                }
                showToast('error', errorMessage);
            }
        });
    });

    // Edit Data
    $('.btn-edit').on('click', function() {
        const id = $(this).data('id');
        const minggu = $(this).data('minggu');
        const stok = $(this).data('stok');
        const permintaan = $(this).data('permintaan');
        const penjualan = $(this).data('penjualan');
        const kategori = $(this).data('kategori');

        $('#edit_id').val(id);
        $('#edit_minggu').val(minggu);
        $('#edit_stok').val(stok);
        $('#edit_permintaan').val(permintaan);
        $('#edit_penjualan').val(penjualan);
        $('#edit_kategori_stok').val(kategori);

        $('#modalEdit').modal('show');
    });

    $('#formEdit').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit_id').val();
        
        // Show loading
        Swal.fire({
            title: 'Memperbarui...',
            text: 'Sedang mengupdate data',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("data-stok.update", ":id") }}'.replace(':id', id),
            type: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                Swal.close();
                if(response.success) {
                    $('#modalEdit').modal('hide');
                    showToast('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('error', response.message || 'Gagal mengupdate data!');
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMessage = 'Gagal mengupdate data!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    errorMessage = 'Data yang dimasukkan tidak valid';
                }
                showToast('error', errorMessage);
            }
        });
    });

    // Delete Data
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Sedang menghapus data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("data-stok.destroy", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        Swal.close();
                        if(response.success) {
                            showToast('success', response.message);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast('error', response.message || 'Gagal menghapus data!');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        let errorMessage = 'Gagal menghapus data!';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showToast('error', errorMessage);
                    }
                });
            }
        });
    });

    // Training Model
    $('#btnTraining').on('click', function() {
        Swal.fire({
            title: 'Training Model?',
            text: "Proses ini akan melatih model dengan data yang ada.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lakukan Training!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang melakukan training model',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("data-stok.training") }}',
                    type: 'POST',
                    success: function(response) {
                        Swal.close();
                        if(response.success) {
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message || 'Gagal melakukan training!');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        let errorMessage = 'Gagal melakukan training!';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showToast('error', errorMessage);
                    }
                });
            }
        });
    });
});
</script>
@endsection