<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral', function (Blueprint $table) {
            $table->string('id_referral', 40)->primary();
            $table->string('id_bumdes_pengaju', 30);
            $table->string('id_bumdes_penerima', 30)->nullable();
            $table->string('kode_unik', 10)->unique();
            $table->dateTime('tanggal_generate');
            $table->dateTime('tanggal_expired');
            $table->enum('status', ['aktif', 'terpakai', 'kedaluwarsa', 'pending', 'cair', 'gagal']);
            $table->dateTime('tanggal_redeem')->nullable();
            $table->dateTime('batas_verifikasi')->nullable();
            $table->dateTime('tanggal_cair')->nullable();
            $table->timestamps();

            $table->foreign('id_bumdes_pengaju')
                ->references('id_bumdes')
                ->on('bumdes')
                ->restrictOnDelete();
            $table->foreign('id_bumdes_penerima')
                ->references('id_bumdes')
                ->on('bumdes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral');
    }
};
