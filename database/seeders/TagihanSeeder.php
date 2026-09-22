<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TagihanSeeder extends Seeder
{
    public function run(): void
    {
        $verifier = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $dueDate = Carbon::create(2026, 9, 30);
        $customers = Pelanggan::query()->orderBy('id_pelanggan')->get();

        foreach ($customers as $index => $customer) {
            $status = match ($index) {
                0 => 'lunas',
                1 => 'menunggu_verifikasi',
                default => 'belum_bayar',
            };
            $method = $index === 0 ? 'tunai' : ($index === 1 ? 'transfer' : null);

            Tagihan::updateOrCreate(
                ['id_tagihan' => sprintf('TAG-%s-%06d', $dueDate->format('Ymd'), $index + 1)],
                [
                    'id_pelanggan' => $customer->id_pelanggan,
                    'id_unit' => $customer->id_unit,
                    'jumlah' => 25000,
                    'jatuh_tempo' => $dueDate->toDateString(),
                    'status' => $status,
                    'metode' => $method,
                    'bukti_transfer_url' => $method === 'transfer' ? 'payments/development-proof-0002.jpg' : null,
                    'diverifikasi_oleh' => $status === 'lunas' ? $verifier->id_akun : null,
                    'tanggal_verifikasi' => $status === 'lunas' ? $dueDate->copy()->subDay() : null,
                ],
            );
        }
    }
}
