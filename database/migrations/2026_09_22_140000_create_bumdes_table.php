<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bumdes', function (Blueprint $table) {
            $table->string('id_bumdes', 30)->primary();
            $table->string('id_kelurahan', 20);
            $table->string('nama_bumdes', 150);
            $table->boolean('status_aktif')->default(true);
            $table->date('tanggal_berdiri')->nullable();
            $table->timestamps();

            $table->foreign('id_kelurahan')
                ->references('id_region')
                ->on('region')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bumdes');
    }
};
