<?php

namespace App\Console\Commands;

use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ReferralExpireCheck extends Command
{
    protected $signature = 'referral:expire-check';

    protected $description = 'Menandai kode referral aktif yang melewati masa berlaku';

    public function handle(): int
    {
        $expired = 0;
        $generated = 0;

        Referral::query()
            ->where('status', 'aktif')
            ->where('tanggal_expired', '<', Carbon::now())
            ->orderBy('id_referral')
            ->get()
            ->each(function (Referral $referral) use (&$expired, &$generated): void {
                $referral->update(['status' => 'kedaluwarsa']);
                $expired++;

                if (! Referral::query()
                    ->where('id_bumdes_pengaju', $referral->id_bumdes_pengaju)
                    ->where('status', 'aktif')
                    ->exists()) {
                    $code = $this->uniqueCode();
                    Referral::create([
                        'id_referral' => "REF-{$referral->id_bumdes_pengaju}-{$code}",
                        'id_bumdes_pengaju' => $referral->id_bumdes_pengaju,
                        'kode_unik' => $code,
                        'tanggal_generate' => Carbon::now(),
                        'tanggal_expired' => Carbon::now()->addDays(5),
                        'status' => 'aktif',
                    ]);
                    $generated++;
                }
            });

        $this->info("{$expired} referral kedaluwarsa, {$generated} kode baru dibuat.");

        return self::SUCCESS;
    }

    private function uniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (Referral::where('kode_unik', $code)->exists());

        return $code;
    }
}
