<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->string('id_transaksi', 35)->primary();
            $table->string('id_unit', 30);
            $table->enum('tipe', ['input', 'output']);
            $table->decimal('jumlah', 15, 2);
            $table->json('detail');
            $table->date('tanggal');
            $table->string('dicatat_oleh', 30);
            $table->timestamps();

            $table->foreign('id_unit')
                ->references('id_unit')
                ->on('unit_usaha')
                ->restrictOnDelete();
            $table->foreign('dicatat_oleh')
                ->references('id_akun')
                ->on('akun')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
