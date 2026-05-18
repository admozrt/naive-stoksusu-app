# Panduan Penggunaan — Aplikasi Prediksi Stok Susu (Naive Bayes)

Aplikasi web berbasis Laravel untuk memprediksi kategori stok susu (**Banyak / Sedang / Sedikit**) menggunakan algoritma **Gaussian Naive Bayes** berdasarkan tiga atribut: stok, permintaan, dan penjualan.

---

## 1. Persiapan & Instalasi

### Prasyarat
- PHP ≥ 8.2
- Composer
- MySQL / MariaDB
- Node.js (opsional, untuk Vite)

### Langkah instalasi
```bash
# 1. Clone repository & masuk ke folder
git clone <repo-url>
cd naive-stoksusu-app

# 2. Install dependency
composer install
npm install && npm run build   # opsional

# 3. Siapkan file .env
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env
# DB_DATABASE=naive_stoksusu
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Jalankan migrasi (penting: ini akan membuat semua tabel + kolom is_training)
php artisan migrate

# 6. Buat user pertama
php artisan tinker
>>> \App\Models\User::create([
...   'name' => 'Administrator',
...   'username' => 'admin',
...   'email' => 'admin@example.com',
...   'password' => 'admin123',
... ]);

# 7. Jalankan server
php artisan serve
```

Buka `http://localhost:8000` di browser.

---

## 2. Alur Penggunaan

```
[Login] → [Tambah Data Stok berlabel] → [Training Model] → [Prediksi data baru]
```

### 2.1 Login
1. Buka halaman utama, masukkan **username** dan **password**.
2. Klik **Login** — diarahkan ke Dashboard.
3. Logout via tombol di sidebar kiri.

> Jika lupa password, buat ulang user lewat tinker (lihat langkah 6 di atas).

---

### 2.2 Mengelola Data Stok (Data Training)

Menu **Data Stok** dipakai untuk memasukkan data historis yang sudah memiliki label kategori. Data inilah yang akan dipelajari model saat training.

#### Tambah data
1. Klik tombol **Tambah Data** (pojok kanan atas tabel).
2. Isi form:
   - **Merk** — nama merk susu (contoh: `Indomilk`).
   - **Stok** — jumlah stok saat itu. Gunakan koma untuk desimal (contoh: `50,6`). Mendukung angka negatif.
   - **Permintaan** — jumlah permintaan.
   - **Penjualan** — jumlah penjualan.
   - **Kategori Stok** — label ground truth: **Banyak / Sedang / Sedikit**.
3. Klik **Simpan**.

#### Edit / Hapus data
- Tombol **edit (kuning)** dan **hapus (merah)** di kolom Aksi.

#### Membaca ringkasan di halaman Data Stok
- **Kartu di atas**: jumlah data training & prior `P(C)` per kategori.
- **Banner status**: apakah model sudah dilatih.
- **Tabel Parameter Gaussian**: nilai `μ` (mean) dan `σ` (std-dev) untuk tiap atribut per kategori — inilah yang dipakai prediksi.

#### Aturan penting
- **Minimal 2 data per kategori** sebelum training (supaya std-dev valid).
- Data yang berasal dari hasil prediksi (`is_training = false`) **tidak ikut** dipakai saat training.

#### Cetak laporan
- Klik **Cetak PDF** untuk mengunduh ringkasan statistik dataset.

---

### 2.3 Training Model

1. Pastikan semua data training sudah masuk dan minimal 2 data per kategori.
2. Pada halaman Data Stok, klik tombol hijau **Training Model**.
3. Konfirmasi.
4. Sistem akan:
   - Menghitung **prior** `P(Banyak)`, `P(Sedang)`, `P(Sedikit)`.
   - Menghitung **mean (μ)** dan **standard deviation (σ)** tiap atribut per kategori.
   - Menyimpan ke tabel `data_probabilitas` & `data_likelihood`.
5. Pesan sukses muncul → tabel **Parameter Gaussian** ikut terupdate.

> Lakukan **training ulang** setiap kali ada perubahan data training (tambah/edit/hapus).

---

### 2.4 Melakukan Prediksi

1. Masuk menu **Prediksi**.
2. Isi form **Buat Prediksi Baru**:
   - **Merk** (opsional)
   - **Stok**, **Permintaan**, **Penjualan** (gunakan koma untuk desimal)
3. Klik **Prediksi Sekarang**.

#### Hasil yang ditampilkan
- **Kategori prediksi** + badge warna (Banyak/Sedang/Sedikit).
- **Confidence** dalam persen — seberapa yakin model.
- **Distribusi posterior** — bar tiap kategori (setelah normalisasi, total = 100%).
- **Tabel rincian perhitungan**:
  | Kategori | P(C) | P(stok\|C) | P(permintaan\|C) | P(penjualan\|C) | Posterior ∝ |
  |---|---|---|---|---|---|

  Baris pemenang di-highlight. **Hover** sel likelihood untuk melihat nilai `x, μ, σ` yang dipakai.

#### Rumus yang dipakai
- Teorema Bayes: `P(C|D) = P(D|C) · P(C) / P(D)`
- Asumsi naif: atribut independen → `P(D|C) = P(stok|C) · P(permintaan|C) · P(penjualan|C)`
- Gaussian PDF tiap atribut:

  ```
                       1                ( (x − μ)² )
  P(x|C) =  ───────────────────── · exp(− ────────── )
              σ · √(2π)                    2σ²
  ```
- Posterior dinormalisasi terhadap total semua kategori agar menjumlah 1.

#### Riwayat prediksi
Tabel di bawah form menampilkan semua prediksi yang pernah dilakukan, lengkap dengan tanggal. Bisa dihapus per baris.

> Catatan: hasil prediksi disimpan di `data_stok` dengan flag `is_training = false`, jadi **tidak mencemari** training berikutnya.

---

### 2.5 Riwayat Prediksi

Tabel di bawah form pada halaman **Prediksi** menampilkan semua prediksi yang pernah dilakukan, lengkap dengan tanggal. Setiap baris bisa dihapus dengan tombol merah di kolom Aksi.

> Catatan: hasil prediksi disimpan di `data_stok` dengan flag `is_training = false`, sehingga **tidak ikut** ke training berikutnya saat tombol *Training Model* diklik.

---

## 3. Tips & Troubleshooting

### Semua hasil prediksi selalu satu kategori (mis. selalu "Sedang")
- Cek **distribusi data training**: kalau mayoritas berlabel Sedang, prior dominan dan std-nya lebar → wajar Sedang sering menang.
- Tambahkan **lebih banyak variasi data** pada kategori lain.
- Pastikan sudah **training ulang** setelah data berubah.

### Muncul "Belum ada data training!"
- Klik **Training Model** dulu di halaman Data Stok.

### Muncul "Setiap kategori minimal harus punya 2 data training"
- Tambahkan data hingga setiap kategori (Banyak, Sedang, Sedikit) memiliki ≥ 2 baris.

### Error saat login "Username atau password salah!"
- Pastikan akun ada di tabel `users`.
- Password disimpan ter-hash; **jangan** edit langsung lewat phpMyAdmin tanpa hash. Pakai menu **Pengguna** atau tinker:
  ```php
  $u = \App\Models\User::where('username','admin')->first();
  $u->password = 'passwordbaru'; // auto-hash via cast
  $u->save();
  ```

### Mengulang dataset dari awal
```bash
php artisan migrate:fresh
# lalu buat ulang user pertama lewat tinker
```

---

## 4. Struktur Singkat

| Folder / File | Fungsi |
|---|---|
| `app/Http/Controllers/AuthController.php` | Login & logout |
| `app/Http/Controllers/DataStokController.php` | CRUD data + training |
| `app/Http/Controllers/PrediksiController.php` | Endpoint prediksi & riwayat |
| `app/Models/DataStok.php` | Model data stok (punya scope `training()` & `prediction()`) |
| `app/Models/DataLikelihood.php` | Parameter μ & σ per kategori |
| `app/Models/DataProbabilitas.php` | Prior `P(C)` per kategori |
| `resources/views/admin/...` | Halaman dashboard, data stok, prediksi, pengguna |
| `database/migrations/` | Skema tabel |

---

## 5. Ringkasan Endpoint

| Method | Route | Keterangan |
|---|---|---|
| GET | `/` | Halaman login |
| POST | `/login` | Proses login |
| POST | `/logout` | Logout |
| GET | `/dashboard` | Dashboard statistik |
| GET | `/data-stok` | Daftar data training + parameter Gaussian |
| POST | `/data-stok` | Tambah data |
| PUT | `/data-stok/{id}` | Update data |
| DELETE | `/data-stok/{id}` | Hapus data |
| POST | `/data-stok/training` | Jalankan training |
| GET | `/data-stok/export-pdf` | Cetak PDF |
| GET | `/prediksi` | Halaman prediksi + riwayat |
| POST | `/prediksi/predict` | Lakukan prediksi |
| DELETE | `/prediksi/{id}` | Hapus riwayat prediksi |

---

Selamat menggunakan! Untuk pertanyaan teknis lebih lanjut tentang algoritmanya, lihat penjelasan **Teorema Bayes** & **Gaussian PDF** di bagian 2.4.
