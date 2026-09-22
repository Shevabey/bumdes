<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_bumdes', function (Blueprint $table) {
            $table->string('id_kas', 35)->primary();
            $table->string('id_bumdes', 30)->unique();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_bumdes')
                ->references('id_bumdes')
                ->on('bumdes')
                ->restrictOnDelete();
        });

        Schema::create('kas_mutasi', function (Blueprint $table) {
            $table->id('id_mutasi');
            $table->string('id_kas', 35);
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->decimal('jumlah', 15, 2);
            $table->enum('sumber', ['referral', 'iuran', 'lainnya']);
            $table->string('keterangan', 255);
            $table->dateTime('tanggal');
            $table->timestamps();

            $table->foreign('id_kas')
                ->references('id_kas')
                ->on('kas_bumdes')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_mutasi');
        Schema::dropIfExists('kas_bumdes');
    }
};
