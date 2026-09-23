<?php

namespace App\Policies;

use App\Models\Akun;
use App\Models\UnitUsaha;

class UnitUsahaPolicy
{
    public function view(Akun $akun, UnitUsaha $unit): bool
    {
        if ($akun->hasAnyRole(['super_admin', 'pengawas', 'penasihat', 'direktur'])) {
            return true;
        }

        if ($akun->hasRole('admin_unit')) {
            return $akun->id_unit === $unit->id_unit;
        }

        return $akun->id_bumdes === $unit->id_bumdes;
    }

    public function create(Akun $akun): bool
    {
        return $akun->hasAnyRole(['super_admin', 'admin_bumdes']);
    }

    public function update(Akun $akun, UnitUsaha $unit): bool
    {
        return $akun->hasRole('super_admin')
            || ($akun->hasRole('admin_bumdes') && $akun->id_bumdes === $unit->id_bumdes);
    }

    public function toggleStatus(Akun $akun, UnitUsaha $unit): bool
    {
        return $akun->hasRole('super_admin')
            || ($akun->hasRole('admin_bumdes') && $akun->id_bumdes === $unit->id_bumdes);
    }
}
