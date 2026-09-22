<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->string('id_tagihan', 35)->primary();
            $table->string('id_pelanggan', 40);
            $table->string('id_unit', 30);
            $table->decimal('jumlah', 15, 2);
            $table->date('jatuh_tempo');
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'ditolak']);
            $table->enum('metode', ['tunai', 'transfer'])->nullable();
            $table->string('bukti_transfer_url', 255)->nullable();
            $table->string('diverifikasi_oleh', 30)->nullable();
            $table->dateTime('tanggal_verifikasi')->nullable();
            $table->timestamps();

            $table->foreign('id_pelanggan')
                ->references('id_pelanggan')
                ->on('pelanggan')
                ->restrictOnDelete();
            $table->foreign('id_unit')
                ->references('id_unit')
                ->on('unit_usaha')
                ->restrictOnDelete();
            $table->foreign('diverifikasi_oleh')
                ->references('id_akun')
                ->on('akun')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
