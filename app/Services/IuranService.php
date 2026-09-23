<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\IuranBumdes;
use App\Models\KasBumdes;
use App\Models\KasMutasi;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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

    public function bayar(IuranBumdes $iuran, Akun $actor, array $data, ?Carbon $tanggalBayar = null): IuranBumdes
    {
        $this->authorizePaymentActor($actor, $iuran);

        if ($iuran->status !== 'belum_bayar') {
            throw new InvalidArgumentException('Hanya iuran berstatus belum bayar yang dapat dibayarkan.');
        }

        $sumberDana = $data['sumber_dana'] ?? null;
        if (! in_array($sumberDana, ['kas', 'luar_kas'], true)) {
            throw new InvalidArgumentException('Sumber dana iuran tidak valid. Pilih kas atau luar_kas.');
        }

        $metodeBayar = $data['metode_bayar'] ?? null;
        if (! in_array($metodeBayar, ['transfer', 'tunai'], true)) {
            throw new InvalidArgumentException('Metode bayar iuran tidak valid. Pilih transfer atau tunai.');
        }

        $buktiPembayaranUrl = $data['bukti_pembayaran_url'] ?? null;
        if ($metodeBayar === 'transfer' && (empty($buktiPembayaranUrl) || trim((string) $buktiPembayaranUrl) === '')) {
            throw new InvalidArgumentException('Bukti pembayaran wajib diunggah untuk metode transfer.');
        }

        return DB::transaction(function () use ($iuran, $sumberDana, $metodeBayar, $buktiPembayaranUrl, $tanggalBayar): IuranBumdes {
            $paymentTime = $tanggalBayar ?? Carbon::now();

            if ($sumberDana === 'kas') {
                $kas = KasBumdes::firstOrCreate(
                    ['id_kas' => "KAS-{$iuran->id_bumdes}"],
                    ['id_bumdes' => $iuran->id_bumdes, 'saldo' => 0],
                );

                if ((float) $kas->saldo < (float) $iuran->jumlah) {
                    throw new InvalidArgumentException('Saldo kas BUMDes tidak mencukupi untuk pembayaran iuran.');
                }

                $kas->decrement('saldo', $iuran->jumlah);

                KasMutasi::create([
                    'id_kas' => $kas->id_kas,
                    'tipe' => 'keluar',
                    'jumlah' => $iuran->jumlah,
                    'sumber' => 'iuran',
                    'keterangan' => "Pembayaran iuran {$iuran->bulan_tahun}",
                    'tanggal' => $paymentTime,
                ]);
            }

            $iuran->update([
                'status' => 'menunggu_verifikasi',
                'sumber_dana' => $sumberDana,
                'metode_bayar' => $metodeBayar,
                'bukti_pembayaran_url' => $buktiPembayaranUrl,
                'tanggal_bayar' => $paymentTime->toDateTimeString(),
                'diverifikasi_oleh' => null,
                'tanggal_verifikasi' => null,
            ]);

            return $iuran->refresh();
        });
    }

    public function verifikasi(IuranBumdes $iuran, Akun $actor, bool $approve, ?Carbon $tanggalVerifikasi = null): IuranBumdes
    {
        $this->authorizeVerificationActor($actor, $iuran);

        if ($iuran->status !== 'menunggu_verifikasi') {
            throw new InvalidArgumentException('Hanya iuran berstatus menunggu verifikasi yang dapat diverifikasi.');
        }

        return DB::transaction(function () use ($iuran, $actor, $approve, $tanggalVerifikasi): IuranBumdes {
            $verificationTime = $tanggalVerifikasi ?? Carbon::now();

            if ($approve) {
                $iuran->update([
                    'status' => 'lunas',
                    'diverifikasi_oleh' => $actor->id_akun,
                    'tanggal_verifikasi' => $verificationTime->toDateTimeString(),
                ]);
            } else {
                if ($iuran->sumber_dana === 'kas') {
                    $kas = KasBumdes::where('id_bumdes', $iuran->id_bumdes)->first();
                    if ($kas) {
                        $kas->increment('saldo', $iuran->jumlah);
                        KasMutasi::create([
                            'id_kas' => $kas->id_kas,
                            'tipe' => 'masuk',
                            'jumlah' => $iuran->jumlah,
                            'sumber' => 'iuran',
                            'keterangan' => "Pengembalian dana iuran {$iuran->bulan_tahun} (ditolak)",
                            'tanggal' => $verificationTime,
                        ]);
                    }
                }

                $iuran->update([
                    'status' => 'belum_bayar',
                    'sumber_dana' => null,
                    'metode_bayar' => null,
                    'bukti_pembayaran_url' => null,
                    'tanggal_bayar' => null,
                    'diverifikasi_oleh' => $actor->id_akun,
                    'tanggal_verifikasi' => $verificationTime->toDateTimeString(),
                ]);
            }

            return $iuran->refresh();
        });
    }

    private function authorizePaymentActor(Akun $actor, IuranBumdes $iuran): void
    {
        if ($actor->hasRole('super_admin')) {
            return;
        }

        if (($actor->hasRole('bendahara') || $actor->hasRole('admin_bumdes')) && $actor->id_bumdes === $iuran->id_bumdes) {
            return;
        }

        throw new AuthorizationException('Akun tidak berwenang membayar iuran BUMDes ini.');
    }

    private function authorizeVerificationActor(Akun $actor, IuranBumdes $iuran): void
    {
        if ($actor->hasRole('super_admin')) {
            return;
        }

        if ($actor->hasRole('admin_bumdes')) {
            $actor->loadMissing('bumdes.kelurahan');
            $iuran->loadMissing('bumdes.kelurahan');

            $actorKelurahan = $actor->bumdes?->kelurahan;
            $targetKelurahan = $iuran->bumdes?->kelurahan;

            $isCoordinator = (bool) ($actorKelurahan?->is_koordinator ?? false);
            $sameDistrict = $actorKelurahan?->parent_id !== null
                && $actorKelurahan->parent_id === $targetKelurahan?->parent_id;

            if ($isCoordinator && $sameDistrict) {
                return;
            }
        }

        throw new AuthorizationException('Hanya Admin BUMDes Koordinator di kecamatan yang sama yang dapat memverifikasi iuran.');
    }
}
