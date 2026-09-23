<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Feedback;
use App\Models\UnitUsaha;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $pengawas = Akun::where('username', 'pengawas1')->firstOrFail();
        $direktur = Akun::where('username', 'direktur1')->firstOrFail();
        $superAdmin = Akun::where('username', 'superadmin')->firstOrFail();
        $pamdes = UnitUsaha::where('id_unit', 'UNT-BMD-SDS-001-PAM-01')->firstOrFail();

        $feedback = [
            [
                'id_feedback' => 'FB-000001',
                'dari_id_akun' => $pengawas->id_akun,
                'ke_id_bumdes' => 'BMD-SDS-001',
                'ke_id_unit' => null,
                'isi_catatan' => 'Mohon lengkapi ringkasan aktivitas bulanan BUMDes.',
                'status_tindak_lanjut' => 'belum',
            ],
            [
                'id_feedback' => 'FB-000002',
                'dari_id_akun' => $direktur->id_akun,
                'ke_id_bumdes' => 'BMD-SDS-001',
                'ke_id_unit' => $pamdes->id_unit,
                'isi_catatan' => 'Pastikan pencatatan transaksi PAMDes dilakukan setiap hari.',
                'status_tindak_lanjut' => 'sedang',
            ],
            [
                'id_feedback' => 'FB-000003',
                'dari_id_akun' => $superAdmin->id_akun,
                'ke_id_bumdes' => 'BMD-SDR-001',
                'ke_id_unit' => null,
                'isi_catatan' => 'Data setup awal BUMDes sudah ditinjau.',
                'status_tindak_lanjut' => 'selesai',
            ],
        ];

        foreach ($feedback as $index => $data) {
            Feedback::updateOrCreate(
                ['id_feedback' => $data['id_feedback']],
                [...$data, 'tanggal' => Carbon::create(2026, 9, 23)->addDays($index)],
            );
        }
    }
}
