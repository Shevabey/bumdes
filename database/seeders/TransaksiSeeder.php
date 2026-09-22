<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Transaksi;
use App\Models\UnitUsaha;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $adminSendangrejo = Akun::where('username', 'admin.sendangrejo')->firstOrFail();
        $transactionDate = Carbon::create(2026, 9, 22);
        $transactionNumber = 1;

        foreach (UnitUsaha::query()->orderBy('id_unit')->get() as $unit) {
            $recorder = str($unit->id_bumdes)->contains('SDS')
                ? $bendahara
                : $adminSendangrejo;

            foreach ([['tipe' => 'input', 'jumlah' => 100000], ['tipe' => 'output', 'jumlah' => 40000]] as $entry) {
                $id = sprintf('TRX-%s-%06d', $transactionDate->format('Ymd'), $transactionNumber++);

                Transaksi::updateOrCreate(
                    ['id_transaksi' => $id],
                    [
                        'id_unit' => $unit->id_unit,
                        'tipe' => $entry['tipe'],
                        'jumlah' => $entry['jumlah'],
                        'detail' => [
                            'sumber' => 'data seed development',
                            'jenis_unit' => $unit->jenis_unit,
                        ],
                        'tanggal' => $transactionDate->toDateString(),
                        'dicatat_oleh' => $recorder->id_akun,
                    ],
                );
            }
        }
    }
}
