<?php

namespace Database\Seeders;

use App\Models\Bumdes;
use App\Models\UnitUsaha;
use Illuminate\Database\Seeder;

class UnitUsahaSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'PAM', 'type' => 'pamdes', 'name' => 'PAMDes'],
            ['code' => 'PTN', 'type' => 'peternakan', 'name' => 'Peternakan'],
            ['code' => 'MTN', 'type' => 'mitra_tani', 'name' => 'Mitra Tani'],
            ['code' => 'SWM', 'type' => 'sewa_mobil', 'name' => 'Sewa Mobil'],
            ['code' => 'SMH', 'type' => 'sampah', 'name' => 'Sampah'],
        ];

        foreach (Bumdes::query()->orderBy('id_bumdes')->get() as $bumdes) {
            foreach ($types as $type) {
                $shortCode = str($bumdes->id_bumdes)->after('BMD-')->beforeLast('-001');
                $id = "UNT-{$bumdes->id_bumdes}-{$type['code']}-01";

                UnitUsaha::updateOrCreate(
                    ['id_unit' => $id],
                    [
                        'id_bumdes' => $bumdes->id_bumdes,
                        'jenis_unit' => $type['type'],
                        'nama_unit' => "{$type['name']} {$shortCode}",
                        'skema_field' => [],
                        'status_aktif' => true,
                    ],
                );
            }
        }
    }
}
