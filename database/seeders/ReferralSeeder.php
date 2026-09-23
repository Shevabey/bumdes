<?php

namespace Database\Seeders;

use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReferralSeeder extends Seeder
{
    public function run(): void
    {
        $referrals = [
            [
                'id_referral' => 'REF-BMD-SDS-001-ACT001',
                'id_bumdes_pengaju' => 'BMD-SDS-001',
                'id_bumdes_penerima' => null,
                'kode_unik' => 'ACT001',
                'tanggal_generate' => Carbon::create(2026, 9, 20),
                'tanggal_expired' => Carbon::create(2026, 9, 25),
                'status' => 'aktif',
                'tanggal_redeem' => null,
                'batas_verifikasi' => null,
                'tanggal_cair' => null,
            ],
            [
                'id_referral' => 'REF-BMD-SDS-001-PND001',
                'id_bumdes_pengaju' => 'BMD-SDS-001',
                'id_bumdes_penerima' => 'BMD-SDR-001',
                'kode_unik' => 'PND001',
                'tanggal_generate' => Carbon::create(2026, 9, 15),
                'tanggal_expired' => Carbon::create(2026, 9, 20),
                'status' => 'pending',
                'tanggal_redeem' => Carbon::create(2026, 9, 18),
                'batas_verifikasi' => Carbon::create(2026, 10, 3),
                'tanggal_cair' => null,
            ],
            [
                'id_referral' => 'REF-BMD-SDR-001-CLR001',
                'id_bumdes_pengaju' => 'BMD-SDR-001',
                'id_bumdes_penerima' => 'BMD-SDS-001',
                'kode_unik' => 'CLR001',
                'tanggal_generate' => Carbon::create(2026, 8, 1),
                'tanggal_expired' => Carbon::create(2026, 8, 6),
                'status' => 'cair',
                'tanggal_redeem' => Carbon::create(2026, 8, 3),
                'batas_verifikasi' => Carbon::create(2026, 8, 18),
                'tanggal_cair' => Carbon::create(2026, 8, 10),
            ],
            [
                'id_referral' => 'REF-BMD-SDR-001-FAI001',
                'id_bumdes_pengaju' => 'BMD-SDR-001',
                'id_bumdes_penerima' => 'BMD-SDS-001',
                'kode_unik' => 'FAI001',
                'tanggal_generate' => Carbon::create(2026, 7, 1),
                'tanggal_expired' => Carbon::create(2026, 7, 6),
                'status' => 'gagal',
                'tanggal_redeem' => Carbon::create(2026, 7, 3),
                'batas_verifikasi' => Carbon::create(2026, 7, 18),
                'tanggal_cair' => null,
            ],
        ];

        foreach ($referrals as $referral) {
            Referral::updateOrCreate(
                ['id_referral' => $referral['id_referral']],
                $referral,
            );
        }
    }
}
