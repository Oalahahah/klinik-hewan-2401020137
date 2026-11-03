# Dokumentasi GitHub Issue

**Repository:** `klinik-hewan-2401020137`  
**Mata Kuliah:** Praktikum Pemrograman Web (INF11197) - Paket 2 Tipe D  
**NIM:** 2401020137  

---

## Issue #1: [BUG/ENHANCEMENT] Penanganan Duplikasi Nomor Rekam Medis pada Form Update & Penegakan Constraint CHECK Usia Pasien

### 1. Masalah yang Ditemukan (Problem Statement)
Saat melakukan pembaruan (update) data pasien hewan:
1. Jika pengguna memperbarui atribut seperti `nama_hewan` atau `usia_tahun` tanpa mengganti `kode_pasien` atau `nomor_rekam`, validasi `unique` standar menolak data dengan pesan bahwa nomor rekam medis atau kode pasien sudah terdaftar. Hal ini terjadi karena validator memeriksa keberadaan nilai tersebut pada seluruh baris tabel tanpa mengecualikan ID pasien yang sedang diedit.
2. Diperlukan penegakan integritas data ganda: baik di level aplikasi (validasi Laravel `between:0,30`) maupun level basis data (MySQL DDL Constraint `CHECK (usia_tahun BETWEEN 0 AND 30)`) serta constraint relasi `ON UPDATE CASCADE ON DELETE RESTRICT`.

### 2. Langkah Pemeriksaan (Investigation Steps)
1. Memeriksa alur pada controller `PasienHewanController::update` dan mencatat rule validasi yang diterapkan.
2. Melakukan pengujian simulasi HTTP PUT dengan data yang sama pada `kode_pasien` dan `nomor_rekam`.
3. Memeriksa DDL tabel `pasien_hewan` pada MySQL melalui `SHOW CREATE TABLE pasien_hewan` untuk memastikan constraint integritas referensial dan CHECK constraint terdefinisi dengan benar.

### 3. Langkah Perbaikan (Resolution / Implementation)
1. **Pembaruan Rule Validasi pada Controller**:
   Menggunakan `Illuminate\Validation\Rule::unique('pasien_hewan', 'kode_pasien')->ignore($id)` dan `Rule::unique('pasien_hewan', 'nomor_rekam')->ignore($id)`. Dengan ini, record milik pasien yang bersangkutan diabaikan saat pengecekan keunikan nilai.
2. **Penambahan Sanitasi Input**:
   Menambahkan `trim(strip_tags(...))` pada parameter `kode_pasien`, `nama_hewan`, dan `nomor_rekam` sebelum validasi.
3. **Penyempurnaan Migrasi dan DDL**:
   Menerapkan constraint `chk_usia` secara eksplisit pada basis data:
   ```sql
   ALTER TABLE pasien_hewan ADD CONSTRAINT chk_usia CHECK (usia_tahun BETWEEN 0 AND 30);
   ```
   Serta foreign key constraint:
   ```sql
   CONSTRAINT fk_pasien_jenis FOREIGN KEY (jenis_hewan_id) REFERENCES jenis_hewan(id) ON UPDATE CASCADE ON DELETE RESTRICT;
   ```
4. **Penerapan Prepared Statement & Try-Catch**:
   Semua eksekusi query menggunakan `$pdo->prepare()` dan `$stmt->execute()`, dibungkus dalam blok `try-catch (\Throwable $e)` yang mencatat ke `report($e)`.

### 4. Hasil Uji Ulang (Verification & Re-test)
1. **Uji Kasus Update Normal**: Memperbarui nama hewan tanpa mengganti nomor rekam medis -> **BERHASIL** (status 302 redirect, feedback sukses muncul, data terbarui).
2. **Uji Kasus Konflik Update**: Memperbarui pasien ID 1 dengan kode/nomor rekam milik pasien ID 2 -> **BERHASIL DITOLAK** (validation error muncul, perubahan dibatalkan).
3. **Uji Kasus Batas Usia**: Menginput usia < 0 atau > 30 -> **BERHASIL DITOLAK** oleh validasi dan basis data.
4. **Automated Testing**: Seluruh 12 test cases pada `tests/Feature/PasienHewanTest.php` lulus 100% (`OK (12 tests, 66 assertions)`).
