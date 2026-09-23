<?php

namespace App\Policies;

use App\Models\Akun;
use App\Models\Tagihan;

class TagihanPolicy
{
    public function viewAny(Akun $akun): bool
    {
        return $akun->hasAnyRole([
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
    }

    public function view(Akun $akun, Tagihan $tagihan): bool
    {
        if ($akun->hasAnyRole(['super_admin', 'pengawas', 'penasihat', 'direktur'])) {
            return true;
        }

        if ($akun->hasRole('admin_unit')) {
            return $akun->id_unit === $tagihan->id_unit;
        }

        if ($akun->hasAnyRole(['admin_bumdes', 'sekretaris', 'bendahara'])) {
            return $tagihan->unit?->id_bumdes === $akun->id_bumdes;
        }

        if ($akun->hasRole('pengguna')) {
            return $tagihan->id_pelanggan === $akun->pelanggan?->id_pelanggan;
        }

        return false;
    }

    public function create(Akun $akun): bool
    {
        return $akun->hasAnyRole([
            'super_admin',
            'admin_bumdes',
            'sekretaris',
            'bendahara',
            'admin_unit',
        ]);
    }

    public function update(Akun $akun, Tagihan $tagihan): bool
    {
        // Hanya dapat diubah jika belum lunas
        if (in_array($tagihan->status, ['lunas', 'menunggu_verifikasi'], true)) {
            return false;
        }

        if ($akun->hasRole('super_admin')) {
            return true;
        }

        if ($akun->hasRole('admin_unit')) {
            return $akun->id_unit === $tagihan->id_unit;
        }

        return $akun->hasAnyRole(['admin_bumdes', 'sekretaris', 'bendahara'])
            && $tagihan->unit?->id_bumdes === $akun->id_bumdes;
    }

    public function bayarTunai(Akun $akun, Tagihan $tagihan): bool
    {
        if ($akun->hasRole('super_admin')) {
            return true;
        }

        if ($akun->hasRole('admin_unit')) {
            return $akun->id_unit === $tagihan->id_unit;
        }

        return $akun->hasAnyRole(['sekretaris', 'bendahara'])
            && $tagihan->unit?->id_bumdes === $akun->id_bumdes;
    }

    public function verifikasiTransfer(Akun $akun, Tagihan $tagihan): bool
    {
        if ($akun->hasRole('super_admin')) {
            return true;
        }

        return $akun->hasRole('admin_unit') && $akun->id_unit === $tagihan->id_unit;
    }
}
