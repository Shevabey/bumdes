<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->string('id_feedback', 20)->primary();
            $table->string('dari_id_akun', 30);
            $table->string('ke_id_bumdes', 30);
            $table->string('ke_id_unit', 30)->nullable();
            $table->text('isi_catatan');
            $table->enum('status_tindak_lanjut', ['belum', 'sedang', 'selesai'])->default('belum');
            $table->dateTime('tanggal');
            $table->timestamps();

            $table->foreign('dari_id_akun')
                ->references('id_akun')
                ->on('akun')
                ->restrictOnDelete();
            $table->foreign('ke_id_bumdes')
                ->references('id_bumdes')
                ->on('bumdes')
                ->restrictOnDelete();
            $table->foreign('ke_id_unit')
                ->references('id_unit')
                ->on('unit_usaha')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
