<?php

namespace App\Policies;

use App\Models\Akun;
use App\Models\Bumdes;

class BumdesPolicy
{
    public function view(Akun $akun, Bumdes $bumdes): bool
    {
        if ($akun->hasAnyRole(['super_admin', 'pengawas', 'penasihat', 'direktur'])) {
            return true;
        }

        return $akun->id_bumdes === $bumdes->id_bumdes;
    }

    public function update(Akun $akun, Bumdes $bumdes): bool
    {
        return $akun->hasRole('super_admin');
    }

    public function toggleStatus(Akun $akun, Bumdes $bumdes): bool
    {
        return $akun->hasRole('super_admin');
    }
}
