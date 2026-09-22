<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('region', function (Blueprint $table) {
            $table->string('id_region', 20)->primary();
            $table->enum('jenis_wilayah', ['provinsi', 'kabupaten_kota', 'kecamatan', 'kelurahan_desa']);
            $table->string('nama_lengkap', 150);
            $table->string('parent_id', 20)->nullable();
            $table->boolean('is_koordinator')->default(false);
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id_region')
                ->on('region')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region');
    }
};
