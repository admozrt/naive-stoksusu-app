@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Pengguna</h1>
        <p class="text-muted">Kelola akun yang dapat masuk ke aplikasi</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stat-card primary h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-xs text-uppercase text-primary fw-bold mb-1">Total Pengguna</div>
                    <div class="h4 mb-0 fw-bold">{{ $users->count() }}</div>
                </div>
                <div class="text-primary"><i class="fas fa-users fa-2x opacity-50"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-8 mb-3">
        <div class="card stat-card info h-100">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle text-info fa-2x me-3"></i>
                <div>
                    <strong>Catatan:</strong> Password disimpan dalam bentuk hash.
                    Saat mengedit pengguna, kosongkan field password jika tidak ingin mengubahnya.
                    Anda tidak dapat menghapus akun yang sedang login.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-users-cog"></i> Daftar Pengguna</h6>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="fas fa-plus"></i> Tambah Pengguna
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Terdaftar</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $u)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            {{ $u->name }}
                            @if($u->id === auth()->id())
                                <span class="badge bg-info ms-1">Anda</span>
                            @endif
                        </td>
                        <td>{{ $u->username }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit-user"
                                    data-id="{{ $u->id }}"
                                    data-name="{{ $u->name }}"
                                    data-username="{{ $u->username }}"
                                    data-email="{{ $u->email }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            @if($u->id !== auth()->id())
                            <button class="btn btn-sm btn-danger btn-delete-user" data-id="{{ $u->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada pengguna</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Tambah Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambahUser">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" name="password_confirmation" required minlength="6">
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

<!-- Modal Edit User -->
<div class="modal fade" id="modalEditUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit"></i> Edit Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditUser">
                <input type="hidden" id="edit_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="edit_user_name" name="name" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_user_username" name="username" required maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_user_email" name="email" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password baru <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                        <input type="password" class="form-control" id="edit_user_password" name="password" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" name="password_confirmation" minlength="6">
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
    function showError(xhr, fallback) {
        let msg = fallback;
        if (xhr.responseJSON) {
            if (xhr.responseJSON.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            } else if (xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
        }
        showToast('error', msg);
    }

    $('#formTambahUser').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("users.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    $('#modalTambahUser').modal('hide');
                    $('#formTambahUser')[0].reset();
                    showToast('success', res.message);
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showToast('error', res.message || 'Gagal menambahkan pengguna');
                }
            },
            error: function(xhr) { showError(xhr, 'Gagal menambahkan pengguna'); }
        });
    });

    $('.btn-edit-user').on('click', function() {
        $('#edit_user_id').val($(this).data('id'));
        $('#edit_user_name').val($(this).data('name'));
        $('#edit_user_username').val($(this).data('username'));
        $('#edit_user_email').val($(this).data('email'));
        $('#edit_user_password').val('');
        $('#modalEditUser').modal('show');
    });

    $('#formEditUser').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit_user_id').val();
        $.ajax({
            url: '{{ route("users.update", ":id") }}'.replace(':id', id),
            type: 'PUT',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    $('#modalEditUser').modal('hide');
                    showToast('success', res.message);
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showToast('error', res.message || 'Gagal memperbarui pengguna');
                }
            },
            error: function(xhr) { showError(xhr, 'Gagal memperbarui pengguna'); }
        });
    });

    $(document).on('click', '.btn-delete-user', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus pengguna?',
            text: 'Akun ini tidak dapat login lagi setelah dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ route("users.destroy", ":id") }}'.replace(':id', id),
                type: 'DELETE',
                success: function(res) {
                    if (res.success) {
                        showToast('success', res.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('error', res.message || 'Gagal menghapus pengguna');
                    }
                },
                error: function(xhr) { showError(xhr, 'Gagal menghapus pengguna'); }
            });
        });
    });
});
</script>
@endsection
