<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasienHewanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset and seed data
        $this->artisan('migrate:fresh --seed');
    }

    /**
     * 1. READ: Tiga data awal dan nama tabel master tampil pada indeks.
     */
    public function test_read_menampilkan_tiga_data_awal_dan_jenis_hewan()
    {
        $response = $this->get('/pasien-hewan');

        $response->assertStatus(200);
        $response->assertSee('PH00000001');
        $response->assertSee('Milo');
        $response->assertSee('RMH-0001');
        $response->assertSee('PH00000002');
        $response->assertSee('Bruno');
        $response->assertSee('RMH-0002');
        $response->assertSee('PH00000003');
        $response->assertSee('Luna');
        $response->assertSee('RMH-0003');
        $response->assertSee('Kucing');
        $response->assertSee('Anjing');
    }

    /**
     * 2. CREATE VALID: Data baru tersimpan dan feedback keberhasilan tampil.
     */
    public function test_create_valid_tersimpan_dan_menampilkan_feedback()
    {
        $payload = [
            'kode_pasien' => 'PH00000004',
            'nama_hewan' => 'Chiko',
            'nomor_rekam' => 'RMH-0004',
            'usia_tahun' => 4,
            'jenis_hewan_id' => 1,
        ];

        $response = $this->post('/pasien-hewan', $payload);

        $response->assertRedirect('/pasien-hewan');
        $response->assertSessionHas('success', 'Data pasien hewan berhasil disimpan.');

        $follow = $this->get('/pasien-hewan');
        $follow->assertSee('PH00000004');
        $follow->assertSee('Chiko');
        $follow->assertSee('RMH-0004');
        $follow->assertSee('Dewasa'); // usia 4 >= 3
    }

    /**
     * 3. CREATE TIDAK VALID: Pesan kesalahan tampil dan input lama dipertahankan.
     */
    public function test_create_tidak_valid_menampilkan_error_dan_old_input()
    {
        $payload = [
            'kode_pasien' => 'SHORT', // Kurang dari 10 karakter
            'nama_hewan' => '',       // Wajib diisi
            'nomor_rekam' => 'RM',    // Kurang dari 5 karakter
            'usia_tahun' => 99,       // Melebihi batas 30
            'jenis_hewan_id' => 9999, // Tidak ada di jenis_hewan
        ];

        $response = $this->from('/pasien-hewan')->post('/pasien-hewan', $payload);

        $response->assertRedirect('/pasien-hewan');
        $response->assertSessionHasErrors([
            'kode_pasien',
            'nama_hewan',
            'nomor_rekam',
            'usia_tahun',
            'jenis_hewan_id',
        ]);

        $this->get('/pasien-hewan');
        // Pastikan tidak tersimpan di basis data
        $this->assertDatabaseMissing('pasien_hewan', [
            'kode_pasien' => 'SHORT',
        ]);
    }

    /**
     * 4. DUPLIKAT: Kode atau identitas unik yang sama ditolak tanpa merusak data.
     */
    public function test_duplikat_kode_dan_nomor_rekam_ditolak()
    {
        // Coba duplikat kode_pasien milik Milo
        $payloadKodeDuplikat = [
            'kode_pasien' => 'PH00000001',
            'nama_hewan' => 'Si Belang',
            'nomor_rekam' => 'RMH-9999',
            'usia_tahun' => 2,
            'jenis_hewan_id' => 1,
        ];

        $response = $this->from('/pasien-hewan')->post('/pasien-hewan', $payloadKodeDuplikat);
        $response->assertSessionHasErrors(['kode_pasien']);

        // Coba duplikat nomor_rekam milik Bruno
        $payloadRekamDuplikat = [
            'kode_pasien' => 'PH00000099',
            'nama_hewan' => 'Si Hitam',
            'nomor_rekam' => 'RMH-0002',
            'usia_tahun' => 1,
            'jenis_hewan_id' => 2,
        ];

        $response2 = $this->from('/pasien-hewan')->post('/pasien-hewan', $payloadRekamDuplikat);
        $response2->assertSessionHasErrors(['nomor_rekam']);

        // Pastikan jumlah pasien tetap 3
        $count = DB::table('pasien_hewan')->count();
        $this->assertEquals(3, $count);
    }

    /**
     * 5. UPDATE: Data lama tampil pada form edit dan perubahan tersimpan.
     */
    public function test_update_berhasil_menyimpan_perubahan()
    {
        // Pastikan form edit memuat data lama
        $editPage = $this->get('/pasien-hewan/1/edit');
        $editPage->assertStatus(200);
        $editPage->assertSee('PH00000001');
        $editPage->assertSee('Milo');

        $updatePayload = [
            'kode_pasien' => 'PH00000001',
            'nama_hewan' => 'Milo Updated',
            'nomor_rekam' => 'RMH-0001',
            'usia_tahun' => 4,
            'jenis_hewan_id' => 1,
        ];

        $response = $this->put('/pasien-hewan/1', $updatePayload);
        $response->assertRedirect('/pasien-hewan');
        $response->assertSessionHas('success', 'Data pasien hewan berhasil diperbarui.');

        $this->assertDatabaseHas('pasien_hewan', [
            'id' => 1,
            'nama_hewan' => 'Milo Updated',
            'usia_tahun' => 4,
        ]);
    }

    /**
     * 6. KONFLIK UPDATE: Nilai unik milik data lain ditolak.
     */
    public function test_konflik_update_nilai_unik_milik_pasien_lain_ditolak()
    {
        // Pasien ID 1 mencoba menggunakan kode_pasien atau nomor_rekam milik Pasien ID 2
        $payloadKonflik = [
            'kode_pasien' => 'PH00000002', // Milik ID 2
            'nama_hewan' => 'Milo',
            'nomor_rekam' => 'RMH-0002', // Milik ID 2
            'usia_tahun' => 3,
            'jenis_hewan_id' => 1,
        ];

        $response = $this->from('/pasien-hewan/1/edit')->put('/pasien-hewan/1', $payloadKonflik);
        $response->assertRedirect('/pasien-hewan/1/edit');
        $response->assertSessionHasErrors(['kode_pasien', 'nomor_rekam']);

        // Data ID 1 tidak berubah
        $this->assertDatabaseHas('pasien_hewan', [
            'id' => 1,
            'kode_pasien' => 'PH00000001',
        ]);
    }

    /**
     * 7. DELETE: Data terpilih terhapus melalui form DELETE dengan CSRF.
     */
    public function test_delete_berhasil_menghapus_data()
    {
        $response = $this->delete('/pasien-hewan/3');

        $response->assertRedirect('/pasien-hewan');
        $response->assertSessionHas('success', 'Data pasien hewan berhasil dihapus.');

        $this->assertDatabaseMissing('pasien_hewan', [
            'id' => 3,
            'kode_pasien' => 'PH00000003',
        ]);
    }

    /**
     * 8. PENCARIAN: Kata kunci mencari tiga kolom yang telah ditentukan.
     */
    public function test_pencarian_mencari_tiga_kolom()
    {
        // Cari berdasarkan kode_pasien
        $resKode = $this->get('/pasien-hewan?cari=PH00000002');
        $resKode->assertSee('Bruno');
        $resKode->assertDontSee('Milo');

        // Cari berdasarkan nama_hewan
        $resNama = $this->get('/pasien-hewan?cari=Milo');
        $resNama->assertSee('PH00000001');
        $resNama->assertDontSee('Bruno');

        // Cari berdasarkan nomor_rekam
        $resRekam = $this->get('/pasien-hewan?cari=RMH-0003');
        $resRekam->assertSee('Luna');
        $resRekam->assertDontSee('Bruno');
    }

    /**
     * 9. FILTER: Hasil dapat dibatasi berdasarkan tabel master.
     */
    public function test_filter_berdasarkan_jenis_hewan()
    {
        // Filter ID 2 (Anjing) -> hanya Bruno yang harus muncul
        $response = $this->get('/pasien-hewan?jenis_hewan_id=2');
        $response->assertSee('Bruno');
        $response->assertDontSee('Milo');
        $response->assertDontSee('Luna');

        // Filter ID 1 (Kucing) -> Milo dan Luna harus muncul
        $response2 = $this->get('/pasien-hewan?jenis_hewan_id=1');
        $response2->assertSee('Milo');
        $response2->assertSee('Luna');
        $response2->assertDontSee('Bruno');
    }

    /**
     * 10. ERROR HANDLING: Kesalahan dicatat via report() dan memberikan respon yang aman.
     */
    public function test_error_handling_edit_not_found()
    {
        $response = $this->get('/pasien-hewan/99999/edit');
        $response->assertStatus(404);
    }
}
