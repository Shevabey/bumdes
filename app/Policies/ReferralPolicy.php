<?php

namespace App\Policies;

use App\Models\Akun;
use App\Models\Referral;

class ReferralPolicy
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
        ]);
    }

    public function view(Akun $akun, Referral $referral): bool
    {
        if ($akun->hasAnyRole(['super_admin', 'pengawas', 'penasihat', 'direktur'])) {
            return true;
        }

        if ($akun->hasAnyRole(['admin_bumdes', 'sekretaris', 'bendahara']) && $akun->id_bumdes !== null) {
            return $akun->id_bumdes === $referral->id_bumdes_pengaju
                || $akun->id_bumdes === $referral->id_bumdes_penerima;
        }

        return false;
    }

    public function generate(Akun $akun): bool
    {
        if ($akun->hasRole('super_admin')) {
            return true;
        }

        return $akun->hasRole('admin_bumdes') && ! empty($akun->id_bumdes);
    }

    public function redeem(Akun $akun): bool
    {
        if ($akun->hasRole('super_admin')) {
            return true;
        }

        return $akun->hasRole('admin_bumdes') && ! empty($akun->id_bumdes);
    }
}
