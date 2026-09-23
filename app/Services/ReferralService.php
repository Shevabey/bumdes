<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\KasBumdes;
use App\Models\KasMutasi;
use App\Models\Referral;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ReferralService
{
    public function generateOrGetActive(Bumdes|string $bumdes, Akun $actor, ?Carbon $now = null): Referral
    {
        $idBumdes = $bumdes instanceof Bumdes ? $bumdes->id_bumdes : $bumdes;
        $this->authorizeAdminBumdes($actor, $idBumdes);

        $currentTime = $now ?? Carbon::now();

        $active = Referral::query()
            ->where('id_bumdes_pengaju', $idBumdes)
            ->where('status', 'aktif')
            ->first();

        if ($active !== null) {
            if ($active->tanggal_expired > $currentTime) {
                return $active;
            }

            $active->update(['status' => 'kedaluwarsa']);
        }

        return $this->createNewActiveCode($idBumdes, $currentTime);
    }

    public function redeem(string $kodeUnik, Bumdes|string $bumdesPenerima, Akun $actor, ?Carbon $now = null): Referral
    {
        $idPenerima = $bumdesPenerima instanceof Bumdes ? $bumdesPenerima->id_bumdes : $bumdesPenerima;
        $this->authorizeAdminBumdes($actor, $idPenerima);

        $redeemTime = $now ?? Carbon::now();

        $referral = Referral::query()
            ->where('kode_unik', $kodeUnik)
            ->first();

        if ($referral === null) {
            throw new InvalidArgumentException('Kode referral tidak ditemukan.');
        }

        if ($referral->status !== 'aktif' || $referral->tanggal_expired <= $redeemTime) {
            if ($referral->status === 'aktif' && $referral->tanggal_expired <= $redeemTime) {
                $referral->update(['status' => 'kedaluwarsa']);
            }

            throw new InvalidArgumentException('Kode referral tidak valid atau sudah kedaluwarsa.');
        }

        if ($referral->id_bumdes_pengaju === $idPenerima) {
            throw new InvalidArgumentException('BUMDes tidak dapat menggunakan kode referral milik sendiri.');
        }

        $alreadyUsed = Referral::query()
            ->where('id_bumdes_penerima', $idPenerima)
            ->whereIn('status', ['pending', 'cair'])
            ->exists();

        if ($alreadyUsed) {
            throw new InvalidArgumentException('BUMDes penerima sudah pernah menggunakan kode referral.');
        }

        return DB::transaction(function () use ($referral, $idPenerima, $redeemTime): Referral {
            $referral->update([
                'status' => 'pending',
                'id_bumdes_penerima' => $idPenerima,
                'tanggal_redeem' => $redeemTime,
                'batas_verifikasi' => $redeemTime->copy()->addDays(15),
            ]);

            $hasActive = Referral::query()
                ->where('id_bumdes_pengaju', $referral->id_bumdes_pengaju)
                ->where('status', 'aktif')
                ->where('tanggal_expired', '>', $redeemTime)
                ->exists();

            if (! $hasActive) {
                $this->createNewActiveCode($referral->id_bumdes_pengaju, $redeemTime);
            }

            return $referral->refresh();
        });
    }

    public function prosesPencairan(Referral $referral, ?Carbon $now = null): Referral
    {
        if ($referral->status !== 'pending') {
            throw new InvalidArgumentException('Hanya referral berstatus pending yang dapat diproses pencairannya.');
        }

        $checkedAt = $now ?? Carbon::now();

        $hasActivity = Transaksi::query()
            ->whereBetween('tanggal', [
                $referral->tanggal_redeem->toDateString(),
                $checkedAt->toDateString(),
            ])
            ->whereHas('unit', function (Builder $query) use ($referral): void {
                $query->where('id_bumdes', $referral->id_bumdes_penerima);
            })
            ->exists();

        return DB::transaction(function () use ($referral, $hasActivity, $checkedAt): Referral {
            if ($hasActivity) {
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
            } else {
                $referral->update([
                    'status' => 'gagal',
                ]);
            }

            return $referral->refresh();
        });
    }

    private function createNewActiveCode(string $idBumdes, Carbon $now): Referral
    {
        $code = $this->uniqueCode();

        return Referral::create([
            'id_referral' => "REF-{$idBumdes}-{$code}",
            'id_bumdes_pengaju' => $idBumdes,
            'id_bumdes_penerima' => null,
            'kode_unik' => $code,
            'tanggal_generate' => $now,
            'tanggal_expired' => $now->copy()->addDays(5),
            'status' => 'aktif',
            'tanggal_redeem' => null,
            'batas_verifikasi' => null,
            'tanggal_cair' => null,
        ]);
    }

    private function uniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (Referral::where('kode_unik', $code)->exists());

        return $code;
    }

    private function authorizeAdminBumdes(Akun $actor, string $idBumdes): void
    {
        if ($actor->hasRole('super_admin')) {
            return;
        }

        if ($actor->hasRole('admin_bumdes') && $actor->id_bumdes === $idBumdes) {
            return;
        }

        throw new AuthorizationException('Akun tidak berwenang mengelola referral BUMDes ini.');
    }
}
