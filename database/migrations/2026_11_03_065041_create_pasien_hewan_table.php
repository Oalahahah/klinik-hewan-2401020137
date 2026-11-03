<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pasien_hewan', function (Blueprint $table) {
            $table->id();
            $table->char('kode_pasien', 10)->unique();
            $table->string('nama_hewan', 100);
            $table->string('nomor_rekam', 30)->unique();
            $table->unsignedTinyInteger('usia_tahun');
            $table->foreignId('jenis_hewan_id')
                ->constrained('jenis_hewan')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->timestamps();
        });

        // Constraint CHECK usia_tahun BETWEEN 0 AND 30 untuk MySQL / MariaDB
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE pasien_hewan ADD CONSTRAINT chk_usia CHECK (usia_tahun BETWEEN 0 AND 30)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasien_hewan');
    }
};
