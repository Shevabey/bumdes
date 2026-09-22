<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iuran_bumdes', function (Blueprint $table) {
            $table->string('id_iuran', 40)->primary();
            $table->string('id_bumdes', 30);
            $table->string('bulan_tahun', 7);
            $table->decimal('jumlah', 15, 2)->default(50000);
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas']);
            $table->enum('sumber_dana', ['kas', 'luar_kas'])->nullable();
            $table->enum('metode_bayar', ['transfer', 'tunai'])->nullable();
            $table->string('bukti_pembayaran_url', 255)->nullable();
            $table->dateTime('tanggal_bayar')->nullable();
            $table->string('diverifikasi_oleh', 30)->nullable();
            $table->dateTime('tanggal_verifikasi')->nullable();
            $table->timestamps();

            $table->unique(['id_bumdes', 'bulan_tahun']);
            $table->foreign('id_bumdes')
                ->references('id_bumdes')
                ->on('bumdes')
                ->restrictOnDelete();
            $table->foreign('diverifikasi_oleh')
                ->references('id_akun')
                ->on('akun')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iuran_bumdes');
    }
};
