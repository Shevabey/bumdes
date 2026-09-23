<?php

namespace App\Console\Commands;

use App\Models\KasBumdes;
use App\Models\KasMutasi;
use App\Models\Referral;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReferralVerifyCheck extends Command
{
    protected $signature = 'referral:verify-check';

    protected $description = 'Memverifikasi referral pending setelah batas aktivitas 15 hari';

    public function handle(): int
    {
        $cair = 0;
        $gagal = 0;
        $checkedAt = Carbon::now();

        Referral::query()
            ->where('status', 'pending')
            ->whereNotNull('batas_verifikasi')
            ->where('batas_verifikasi', '<', $checkedAt)
            ->orderBy('id_referral')
            ->get()
            ->each(function (Referral $referral) use ($checkedAt, &$cair, &$gagal): void {
                $hasActivity = Transaksi::query()
                    ->whereBetween('tanggal', [
                        $referral->tanggal_redeem->toDateString(),
                        $checkedAt->toDateString(),
                    ])
                    ->whereHas('unit', function (Builder $query) use ($referral): void {
                        $query->where('id_bumdes', $referral->id_bumdes_penerima);
                    })
                    ->exists();

                DB::transaction(function () use ($referral, $hasActivity, $checkedAt, &$cair, &$gagal): void {
                    if (! $hasActivity) {
                        $referral->update(['status' => 'gagal']);
                        $gagal++;

                        return;
                    }

                    $kas = KasBumdes::firstOrCreate(
                        ['id_kas' => "KAS-{$referral->id_bumdes_penerima}"],
                        ['id_bumdes' => $referral->id_bumdes_penerima, 'saldo' => 0],
                    );
                    $kas->increment('saldo', 10000);
                    KasMutasi::create([
                        'id_kas' => $kas->id_kas,
                        'tipe' => 'masuk',
                        'jumlah' => 10000,
                        'sumber' => 'referral',
                        'keterangan' => "Pencairan referral {$referral->kode_unik}",
                        'tanggal' => $checkedAt,
                    ]);
                    $referral->update([
                        'status' => 'cair',
                        'tanggal_cair' => $checkedAt,
                    ]);
                    $cair++;
                });
            });

        $this->info("{$cair} referral cair, {$gagal} referral gagal.");

        return self::SUCCESS;
    }
}
