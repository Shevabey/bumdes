<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\IuranBumdes;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IuranBumdesSeeder extends Seeder
{
    public function run(): void
    {
        $coordinator = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $paymentDate = Carbon::create(2026, 9, 10, 10, 0);
        $verificationDate = Carbon::create(2026, 9, 11, 10, 0);

        foreach (Bumdes::query()->orderBy('id_bumdes')->get() as $bumdes) {
            $isCoordinator = $bumdes->id_bumdes === 'BMD-SDS-001';

            IuranBumdes::updateOrCreate(
                [
                    'id_iuran' => "IUR-{$bumdes->id_bumdes}-2026-09",
                ],
                [
                    'id_bumdes' => $bumdes->id_bumdes,
                    'bulan_tahun' => '2026-09',
                    'jumlah' => 50000,
                    'status' => $isCoordinator ? 'lunas' : 'menunggu_verifikasi',
                    'sumber_dana' => $isCoordinator ? 'kas' : 'luar_kas',
                    'metode_bayar' => $isCoordinator ? 'tunai' : 'transfer',
                    'bukti_pembayaran_url' => $isCoordinator ? null : 'contributions/development-proof-001.jpg',
                    'tanggal_bayar' => $paymentDate,
                    'diverifikasi_oleh' => $isCoordinator ? $coordinator->id_akun : null,
                    'tanggal_verifikasi' => $isCoordinator ? $verificationDate : null,
                ],
            );
        }
    }
}
