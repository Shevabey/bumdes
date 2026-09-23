<?php

namespace App\Services;

use App\Models\Bumdes;
use App\Models\IuranBumdes;
use Carbon\Carbon;

class IuranService
{
    public function generateBulanan(?Carbon $bulan = null): int
    {
        $periode = ($bulan ?? Carbon::now())->startOfMonth();
        $created = 0;

        Bumdes::query()
            ->where('status_aktif', true)
            ->orderBy('id_bumdes')
            ->each(function (Bumdes $bumdes) use ($periode, &$created): void {
                $iuran = IuranBumdes::firstOrCreate(
                    [
                        'id_iuran' => sprintf('IUR-%s-%s', $bumdes->id_bumdes, $periode->format('Y-m')),
                    ],
                    [
                        'id_bumdes' => $bumdes->id_bumdes,
                        'bulan_tahun' => $periode->format('Y-m'),
                        'jumlah' => 50000,
                        'status' => 'belum_bayar',
                    ],
                );

                if ($iuran->wasRecentlyCreated) {
                    $created++;
                }
            });

        return $created;
    }
}
