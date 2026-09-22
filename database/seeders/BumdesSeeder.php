<?php

namespace Database\Seeders;

use App\Models\Bumdes;
use Illuminate\Database\Seeder;

class BumdesSeeder extends Seeder
{
    public function run(): void
    {
        $bumdes = [
            [
                'id_bumdes' => 'BMD-SDS-001',
                'id_kelurahan' => '34.04.07.2005',
                'nama_bumdes' => 'BUMDes Sendangsari',
                'status_aktif' => true,
                'tanggal_berdiri' => null,
            ],
            [
                'id_bumdes' => 'BMD-SDR-001',
                'id_kelurahan' => '34.04.07.2004',
                'nama_bumdes' => 'BUMDes Sendangrejo',
                'status_aktif' => true,
                'tanggal_berdiri' => null,
            ],
        ];

        foreach ($bumdes as $data) {
            Bumdes::updateOrCreate(
                ['id_bumdes' => $data['id_bumdes']],
                $data,
            );
        }
    }
}