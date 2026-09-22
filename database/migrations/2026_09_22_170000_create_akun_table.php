<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun', function (Blueprint $table) {
            $table->string('id_akun', 30)->primary();
            $table->string('nama', 150);
            $table->string('username', 100)->unique();
            $table->string('password_hash', 255);
            $table->enum('role', [
                'super_admin',
                'pengawas',
                'penasihat',
                'direktur',
                'admin_bumdes',
                'sekretaris',
                'bendahara',
                'admin_unit',
                'pengguna',
            ]);
            $table->string('id_bumdes', 30)->nullable();
            $table->string('id_unit', 30)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->foreign('id_bumdes')
                ->references('id_bumdes')
                ->on('bumdes')
                ->restrictOnDelete();
            $table->foreign('id_unit')
                ->references('id_unit')
                ->on('unit_usaha')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akun');
    }
};
