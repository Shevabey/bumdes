<?php

namespace App\Policies;

use App\Models\Akun;
use App\Models\Transaksi;

class TransaksiPolicy
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
        ]);
    }

    public function view(Akun $akun, Transaksi $transaksi): bool
    {
        if ($akun->hasAnyRole(['super_admin', 'pengawas', 'penasihat', 'direktur'])) {
            return true;
        }

        if ($akun->hasRole('admin_unit')) {
            return $akun->id_unit === $transaksi->id_unit;
        }

        // admin_bumdes, sekretaris, bendahara — scope BUMDes
        return $transaksi->unit?->id_bumdes === $akun->id_bumdes;
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

    public function update(Akun $akun, Transaksi $transaksi): bool
    {
        if ($akun->hasRole('super_admin')) {
            return true;
        }

        if ($akun->hasRole('admin_unit')) {
            return $akun->id_unit === $transaksi->id_unit;
        }

        return $akun->hasAnyRole(['admin_bumdes', 'bendahara', 'sekretaris'])
            && $transaksi->unit?->id_bumdes === $akun->id_bumdes;
    }

    public function delete(Akun $akun, Transaksi $transaksi): bool
    {
        if ($akun->hasRole('super_admin')) {
            return true;
        }

        return $akun->hasAnyRole(['admin_bumdes', 'bendahara'])
            && $transaksi->unit?->id_bumdes === $akun->id_bumdes;
    }
}
