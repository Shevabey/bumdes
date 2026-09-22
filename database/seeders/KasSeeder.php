<?php

namespace Database\Seeders;

use App\Models\Bumdes;
use App\Models\KasBumdes;
use App\Models\KasMutasi;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KasSeeder extends Seeder
{
    public function run(): void
    {
        $mutationDate = Carbon::create(2026, 9, 12, 10, 0);
        $data = [
            'BMD-SDS-001' => [
                'saldo' => 250000,
                'mutations' => [
                    ['id_mutasi' => 1, 'tipe' => 'masuk', 'jumlah' => 300000, 'sumber' => 'lainnya', 'keterangan' => 'Saldo awal development'],
                    ['id_mutasi' => 2, 'tipe' => 'keluar', 'jumlah' => 50000, 'sumber' => 'iuran', 'keterangan' => 'Pembayaran iuran September 2026'],
                ],
            ],
            'BMD-SDR-001' => [
                'saldo' => 150000,
                'mutations' => [
                    ['id_mutasi' => 3, 'tipe' => 'masuk', 'jumlah' => 150000, 'sumber' => 'lainnya', 'keterangan' => 'Saldo awal development'],
                ],
            ],
        ];

        foreach (Bumdes::query()->orderBy('id_bumdes')->get() as $bumdes) {
            $kas = KasBumdes::updateOrCreate(
                ['id_kas' => "KAS-{$bumdes->id_bumdes}"],
                [
                    'id_bumdes' => $bumdes->id_bumdes,
                    'saldo' => $data[$bumdes->id_bumdes]['saldo'],
                ],
            );

            foreach ($data[$bumdes->id_bumdes]['mutations'] as $mutation) {
                KasMutasi::updateOrCreate(
                    ['id_mutasi' => $mutation['id_mutasi']],
                    [...$mutation, 'id_kas' => $kas->id_kas, 'tanggal' => $mutationDate],
                );
            }
        }
    }
}
