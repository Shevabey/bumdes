<?php

namespace App\Policies;

use App\Models\Akun;
use App\Models\IuranBumdes;

class IuranBumdesPolicy
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

    public function view(Akun $akun, IuranBumdes $iuran): bool
    {
        if ($akun->hasAnyRole(['super_admin', 'pengawas', 'penasihat', 'direktur'])) {
            return true;
        }

        if ($akun->hasAnyRole(['admin_bumdes', 'sekretaris', 'bendahara']) && $akun->id_bumdes === $iuran->id_bumdes) {
            return true;
        }

        // Admin BUMDes Koordinator dapat melihat iuran BUMDes lain dalam kecamatan yang sama
        if ($akun->hasRole('admin_bumdes')) {
            return $this->isCoordinatorInSameDistrict($akun, $iuran);
        }

        return false;
    }

    public function bayar(Akun $akun, IuranBumdes $iuran): bool
    {
        if ($iuran->status !== 'belum_bayar') {
            return false;
        }

        if ($akun->hasRole('super_admin')) {
            return true;
        }

        return $akun->hasAnyRole(['bendahara', 'admin_bumdes'])
            && $akun->id_bumdes === $iuran->id_bumdes;
    }

    public function verifikasi(Akun $akun, IuranBumdes $iuran): bool
    {
        if ($iuran->status !== 'menunggu_verifikasi') {
            return false;
        }

        if ($akun->hasRole('super_admin')) {
            return true;
        }

        if ($akun->hasRole('admin_bumdes')) {
            return $this->isCoordinatorInSameDistrict($akun, $iuran);
        }

        return false;
    }

    public function generate(Akun $akun): bool
    {
        return $akun->hasRole('super_admin');
    }

    private function isCoordinatorInSameDistrict(Akun $akun, IuranBumdes $iuran): bool
    {
        $akun->loadMissing('bumdes.kelurahan');
        $iuran->loadMissing('bumdes.kelurahan');

        $actorKelurahan = $akun->bumdes?->kelurahan;
        $targetKelurahan = $iuran->bumdes?->kelurahan;

        $isCoordinator = (bool) ($actorKelurahan?->is_koordinator ?? false);
        $sameDistrict = $actorKelurahan?->parent_id !== null
            && $actorKelurahan->parent_id === $targetKelurahan?->parent_id;

        return $isCoordinator && $sameDistrict;
    }
}
