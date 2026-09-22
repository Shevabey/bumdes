<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_usaha', function (Blueprint $table) {
            $table->string('id_unit', 30)->primary();
            $table->string('id_bumdes', 30);
            $table->enum('jenis_unit', ['pamdes', 'peternakan', 'mitra_tani', 'sewa_mobil', 'sampah', 'custom']);
            $table->string('nama_unit', 150);
            $table->json('skema_field');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->foreign('id_bumdes')
                ->references('id_bumdes')
                ->on('bumdes')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_usaha');
    }
};
