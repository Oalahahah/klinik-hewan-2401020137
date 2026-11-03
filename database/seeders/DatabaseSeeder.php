<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key constraints secara cross-database
        Schema::disableForeignKeyConstraints();
        DB::table('pasien_hewan')->truncate();
        DB::table('jenis_hewan')->truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Data Master: jenis_hewan
        DB::table('jenis_hewan')->insert([
            ['id' => 1, 'nama_jenis' => 'Kucing'],
            ['id' => 2, 'nama_jenis' => 'Anjing'],
        ]);

        // 2. Data Awal Wajib: pasien_hewan
        DB::table('pasien_hewan')->insert([
            [
                'id' => 1,
                'kode_pasien' => 'PH00000001',
                'nama_hewan' => 'Milo',
                'nomor_rekam' => 'RMH-0001',
                'usia_tahun' => 3,
                'jenis_hewan_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'kode_pasien' => 'PH00000002',
                'nama_hewan' => 'Bruno',
                'nomor_rekam' => 'RMH-0002',
                'usia_tahun' => 5,
                'jenis_hewan_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'kode_pasien' => 'PH00000003',
                'nama_hewan' => 'Luna',
                'nomor_rekam' => 'RMH-0003',
                'usia_tahun' => 2,
                'jenis_hewan_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
