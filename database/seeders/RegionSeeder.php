<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            [
                'id_region' => '34',
                'jenis_wilayah' => 'provinsi',
                'nama_lengkap' => 'Daerah Istimewa Yogyakarta',
                'parent_id' => null,
                'is_koordinator' => false,
            ],
            [
                'id_region' => '34.04',
                'jenis_wilayah' => 'kabupaten_kota',
                'nama_lengkap' => 'Kabupaten Sleman',
                'parent_id' => '34',
                'is_koordinator' => false,
            ],
            [
                'id_region' => '34.04.07',
                'jenis_wilayah' => 'kecamatan',
                'nama_lengkap' => 'Minggir',
                'parent_id' => '34.04',
                'is_koordinator' => false,
            ],
            [
                'id_region' => '34.04.07.2004',
                'jenis_wilayah' => 'kelurahan_desa',
                'nama_lengkap' => 'Sendangrejo',
                'parent_id' => '34.04.07',
                'is_koordinator' => false,
            ],
            [
                'id_region' => '34.04.07.2005',
                'jenis_wilayah' => 'kelurahan_desa',
                'nama_lengkap' => 'Sendangsari',
                'parent_id' => '34.04.07',
                'is_koordinator' => true,
            ],
        ];

        foreach ($regions as $region) {
            Region::updateOrCreate(
                ['id_region' => $region['id_region']],
                $region,
            );
        }
    }
}
