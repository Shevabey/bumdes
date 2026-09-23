<?php

namespace App\Console\Commands;

use App\Services\IuranService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateIuranBulanan extends Command
{
    protected $signature = 'iuran:generate-bulanan {--bulan= : Periode YYYY-MM, default bulan berjalan}';

    protected $description = 'Generate iuran bulanan untuk seluruh BUMDes aktif';

    public function handle(IuranService $iuranService): int
    {
        $bulan = $this->option('bulan');
        $periode = $bulan === null
            ? Carbon::now()
            : Carbon::createFromFormat('!Y-m', $bulan);
        $created = $iuranService->generateBulanan($periode);

        $this->info("{$created} iuran bulanan dibuat untuk periode {$periode->format('Y-m')}.");

        return self::SUCCESS;
    }
}
