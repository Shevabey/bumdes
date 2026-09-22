<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Pelanggan;
use App\Models\UnitUsaha;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $portalAccount = Akun::where('username', 'pengguna.sds')->firstOrFail();

        foreach (UnitUsaha::query()->orderBy('id_unit')->get() as $unit) {
            $id = "PLG-{$unit->id_unit}-0001";

            Pelanggan::updateOrCreate(
                ['id_pelanggan' => $id],
                [
                    'id_unit' => $unit->id_unit,
                    'nama' => "Pelanggan {$unit->nama_unit}",
                    'kontak' => '081200000000',
                    'id_akun' => $unit->id_unit === 'UNT-BMD-SDS-001-PAM-01'
                        ? $portalAccount->id_akun
                        : null,
                    'status_aktif' => true,
                ],
            );
        }
    }
}
