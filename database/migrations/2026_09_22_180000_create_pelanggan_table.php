<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->string('id_pelanggan', 40)->primary();
            $table->string('id_unit', 30);
            $table->string('nama', 150);
            $table->string('kontak', 50);
            $table->string('id_akun', 30)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->foreign('id_unit')
                ->references('id_unit')
                ->on('unit_usaha')
                ->restrictOnDelete();
            $table->foreign('id_akun')
                ->references('id_akun')
                ->on('akun')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
