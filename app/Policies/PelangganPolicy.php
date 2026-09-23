<?php

namespace App\Policies;

use App\Models\Akun;
use App\Models\Pelanggan;

class PelangganPolicy
{
    public function view(Akun $akun, Pelanggan $pelanggan): bool
    {
        if ($akun->hasAnyRole(['super_admin', 'pengawas', 'penasihat', 'direktur'])) {
            return true;
        }

        if ($akun->hasRole('admin_unit')) {
            return $akun->id_unit === $pelanggan->id_unit;
        }

        // admin_bumdes, sekretaris, bendahara — scope BUMDes via unit
        return $pelanggan->unit?->id_bumdes === $akun->id_bumdes;
    }

    public function create(Akun $akun): bool
    {
        return $akun->hasAnyRole(['super_admin', 'admin_bumdes', 'sekretaris', 'admin_unit']);
    }

    public function update(Akun $akun, Pelanggan $pelanggan): bool
    {
        if ($akun->hasRole('super_admin')) {
            return true;
        }

        if ($akun->hasRole('admin_unit')) {
            return $akun->id_unit === $pelanggan->id_unit;
        }

        return $akun->hasAnyRole(['admin_bumdes', 'sekretaris'])
            && $pelanggan->unit?->id_bumdes === $akun->id_bumdes;
    }

    public function toggleStatus(Akun $akun, Pelanggan $pelanggan): bool
    {
        if ($akun->hasRole('super_admin')) {
            return true;
        }

        if ($akun->hasRole('admin_unit')) {
            return $akun->id_unit === $pelanggan->id_unit;
        }

        return $akun->hasAnyRole(['admin_bumdes', 'sekretaris'])
            && $pelanggan->unit?->id_bumdes === $akun->id_bumdes;
    }
}
