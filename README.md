# Sistem Pasien Klinik Hewan (Paket 2 - Tipe D)

Aplikasi Web CRUD Pengelolaan Data Pasien Berdasarkan Jenis Hewan pada Klinik Hewan.  
Dikembangkan untuk memenuhi Tugas **Studi Kasus Praktikum Pemrograman Web (INF11197) - Modul 1 sampai Modul 7**.

---

## 📌 Identitas Proyek
- **Mata Kuliah**: Praktikum Pemrograman Web (INF11197)
- **Paket Soal**: Paket 2 - Tipe D
- **Domain**: Pasien Klinik Hewan
- **Nama Basis Data**: `klinik_hewan_2401020137`
- **NIM**: 2401020137
- **Route Utama**: `/pasien-hewan`
- **Teknologi**: Laravel 12/13, PHP 8.3+, MySQL 8.x / MariaDB, PDO Prepared Statements, Blade HTML Sederhana, Git & GitHub.

---

## ⚙️ Persyaratan Sistem & Lingkungan
1. PHP >= 8.3 dengan ekstensi `pdo`, `pdo_mysql`, `mbstring`, `openssl`
2. Composer >= 2.x
3. MySQL >= 8.0 atau MariaDB >= 10.5
4. Git

---

## 🚀 Panduan Instalasi dan Menjalankan Proyek

1. **Clone Repository**:
   ```bash
   git clone <URL_REPOSITORY_ANDA>
   cd klinik-hewan-2401020137
   ```

2. **Instal Dependensi**:
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**:
   Salin file `.env.example` menjadi `.env`:
   ```bash
   copy .env.example .env
   ```
   Pastikan konfigurasi database di file `.env` sudah sesuai:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=klinik_hewan_2401020137
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Migrasi dan Seeding Basis Data**:
   Jalankan migrasi sekaligus seeding 3 data awal wajib dan tabel master:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Alternatif menggunakan berkas SQL langsung:*
   ```bash
   mysql -u root -p klinik_hewan_2401020137 < klinik_hewan_2401020137.sql
   ```

6. **Jalankan Web Server**:
   ```bash
   php artisan serve
   ```
   Buka peramban (browser) di alamat: `http://127.0.0.1:8000` (otomatis redirect ke `/pasien-hewan`).

---

## 🗄️ Spesifikasi Skema Basis Data

### 1. Tabel Master: `jenis_hewan`
| Kolom | Tipe Data | Ketentuan |
|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY |
| `nama_jenis` | VARCHAR(100) | NOT NULL, UNIQUE |

### 2. Tabel Utama: `pasien_hewan`
| Kolom | Tipe Data | Ketentuan |
|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY |
| `kode_pasien` | CHAR(10) | NOT NULL, UNIQUE (tepat 10 karakter alfanumerik) |
| `nama_hewan` | VARCHAR(100) | NOT NULL (2–100 karakter) |
| `nomor_rekam` | VARCHAR(30) | NOT NULL, UNIQUE (5–30 karakter) |
| `usia_tahun` | TINYINT UNSIGNED | NOT NULL, CHECK (`usia_tahun` BETWEEN 0 AND 30) |
| `jenis_hewan_id` | BIGINT UNSIGNED | NOT NULL, FK `REFERENCES jenis_hewan(id)` ON UPDATE CASCADE ON DELETE RESTRICT |
| `created_at` | TIMESTAMP | NULL, DEFAULT NULL |
| `updated_at` | TIMESTAMP | NULL, DEFAULT NULL |

### Data Awal Wajib
| Kode Pasien | Nama Hewan | No. Rekam Medis | Usia (Tahun) | Jenis Hewan |
|---|---|---|---|---|
| `PH00000001` | Milo | RMH-0001 | 3 | Kucing |
| `PH00000002` | Bruno | RMH-0002 | 5 | Anjing |
| `PH00000003` | Luna | RMH-0003 | 2 | Kucing |

---

## 🛣️ Daftar Rute Aplikasi (Routes)
| Method | URI | Controller Action | Keterangan |
|---|---|---|---|
| `GET` | `/` | Closure | Redirect ke `/pasien-hewan` |
| `GET` | `/pasien-hewan` | `PasienHewanController@index` | Menampilkan tabel pasien, form tambah, form pencarian, dan filter |
| `POST` | `/pasien-hewan` | `PasienHewanController@store` | Menyimpan data pasien baru dengan validasi & sanitasi |
| `GET` | `/pasien-hewan/{id}/edit` | `PasienHewanController@edit` | Menampilkan form edit data pasien yang dipilih |
| `PUT` | `/pasien-hewan/{id}` | `PasienHewanController@update` | Memperbarui data pasien dengan method spoofing `@method('PUT')` |
| `DELETE` | `/pasien-hewan/{id}` | `PasienHewanController@destroy` | Menghapus pasien dengan method spoofing `@method('DELETE')` |

---

## 🛡️ Aspek Keamanan & Implementasi Teknis

1. **Akses Database dengan PDO & Prepared Statement**:
   - Seluruh operasi query (SELECT, INSERT, UPDATE, DELETE) dieksekusi melalui instance PDO koneksi Laravel (`DB::connection()->getPdo()`) dengan metode `$stmt->prepare()` dan `$stmt->execute()`.
   - Tidak ada penggabungan (concatenation) string variabel input pengguna secara langsung ke dalam sintaks SQL untuk mencegah **SQL Injection**.

2. **Error Handling Terstruktur**:
   - Seluruh akses database dan pemrosesan data dibungkus blok `try-catch (\Throwable $e)`.
   - Kesalahan dicatat secara internal menggunakan `report($e)`.
   - Pengguna menerima respons ramah dan aman tanpa membocorkan rincian sensitif SQL atau error stack trace.

3. **Sanitasi dan Validasi Input**:
   - Input teks disanitasi menggunakan `trim()` dan `strip_tags()`.
   - Validasi ketat sisi server:
     - `kode_pasien`: tepat 10 alfanumerik (`alpha_num`, `size:10`), unik di tabel `pasien_hewan`.
     - `nama_hewan`: `min:2`, `max:100`.
     - `nomor_rekam`: `min:5`, `max:30`, unik di tabel `pasien_hewan`.
     - `usia_tahun`: `integer`, `between:0,30`.
     - `jenis_hewan_id`: `required`, `exists:jenis_hewan,id`.
   - Menggunakan token perlindungan CSRF (`@csrf`), method spoofing untuk PUT/DELETE, serta retensi nilai lama form dengan `old()`.

4. **Fungsi Logika `statusUsiaHewan()`**:
   - Didefinisikan di `app/helpers.php` dan terdaftar pada composer autoload.
   - Mengembalikan `'Dewasa'` jika usia $\ge 3$ tahun, dan `'Muda'` jika usia $< 3$ tahun.
   - Ditampilkan pada tampilan Blade menggunakan struktur kontrol perulangan (`@foreach`) dan percabangan (`@if` / `@else`).

5. **Tampilan HTML Sederhana Murni**:
   - Menggunakan Blade murni tanpa framework CSS/JS (tanpa Bootstrap, Tailwind, atau pustaka eksternal lainnya) sesuai instruksi teknis studi kasus.

---

## 🧪 10 Skenario Pengujian Wajib (Feature Testing)

Semua pengujian telah diuji dan lulus otomatis menggunakan PHPUnit:

| No | Fitur / Skenario Uji | Kriteria Keberhasilan | Status Uji |
|---|---|---|---|
| 1 | **Read** | Tiga data awal dan nama tabel master tampil pada halaman indeks | **LULUS** (`PASS`) |
| 2 | **Create valid** | Data baru tersimpan dan feedback keberhasilan tampil | **LULUS** (`PASS`) |
| 3 | **Create tidak valid** | Pesan kesalahan tampil dan input lama dipertahankan | **LULUS** (`PASS`) |
| 4 | **Duplikat** | Kode atau identitas unik yang sama ditolak tanpa merusak data | **LULUS** (`PASS`) |
| 5 | **Update** | Data lama tampil pada form edit dan perubahan tersimpan | **LULUS** (`PASS`) |
| 6 | **Konflik Update** | Nilai unik milik data lain ditolak | **LULUS** (`PASS`) |
| 7 | **Delete** | Data terpilih terhapus melalui form DELETE dengan CSRF | **LULUS** (`PASS`) |
| 8 | **Pencarian** | Kata kunci mencari 3 kolom (`kode_pasien`, `nama_hewan`, `nomor_rekam`) | **LULUS** (`PASS`) |
| 9 | **Filter** | Hasil dapat dibatasi berdasarkan tabel master (`jenis_hewan_id`) | **LULUS** (`PASS`) |
| 10 | **Error handling** | Kesalahan sistem dicatat via `report()`, respons aman ke pengguna | **LULUS** (`PASS`) |

Jalankan pengujian otomatis kapan saja:
```bash
php artisan test
```

---

## 📝 Ringkasan GitHub Issue

- **Issue**: Issue #1 - "Penanganan Duplikasi Nomor Rekam Medis & Integrasi Constraint CHECK Usia Pasien Hewan"
- **Masalah**: Kegagalan validasi duplikat pada form update saat mengubah field selain kode unik, serta kebutuhan penegakan constraint usia 0–30 tahun.
- **Langkah Pemeriksaan**: Memeriksa skema basis data MySQL dan rule validasi `unique` pada controller.
- **Perbaikan**: Menambahkan `Rule::unique()->ignore($id)` pada operasi update dan menambahkan constraint CHECK `chk_usia` pada tabel `pasien_hewan`.
- **Hasil Uji Ulang**: Uji coba unit & fitur berhasil (12 tests, 66 assertions passed). Rincian lengkap tersedia pada berkas `GITHUB_ISSUE.md`.
