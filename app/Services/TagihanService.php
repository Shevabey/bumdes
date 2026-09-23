<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;

class TagihanService
{
    public function bayarTunai(Tagihan $tagihan, Akun $actor, ?Carbon $tanggalVerifikasi = null): Tagihan
    {
        $tagihan->loadMissing('unit');
        $this->authorizeCashPayment($actor, $tagihan);

        if (! in_array($tagihan->status, ['belum_bayar', 'ditolak'], true)) {
            throw new InvalidArgumentException('Tagihan hanya dapat dibayar tunai dari status belum bayar atau ditolak.');
        }

        $tagihan->update([
            'status' => 'lunas',
            'metode' => 'tunai',
            'bukti_transfer_url' => null,
            'diverifikasi_oleh' => $actor->id_akun,
            'tanggal_verifikasi' => ($tanggalVerifikasi ?? Carbon::now())->toDateTimeString(),
        ]);

        return $tagihan->refresh();
    }

    public function uploadBuktiTransfer(Tagihan $tagihan, Akun $actor, string $buktiTransferUrl): Tagihan
    {
        $tagihan->loadMissing('pelanggan');
        $this->authorizeProofUpload($actor, $tagihan);

        if (! in_array($tagihan->status, ['belum_bayar', 'ditolak'], true)) {
            throw new InvalidArgumentException('Bukti transfer hanya dapat diunggah dari status belum bayar atau ditolak.');
        }

        if (trim($buktiTransferUrl) === '') {
            throw new InvalidArgumentException('URL bukti transfer wajib diisi.');
        }

        $tagihan->update([
            'status' => 'menunggu_verifikasi',
            'metode' => 'transfer',
            'bukti_transfer_url' => $buktiTransferUrl,
            'diverifikasi_oleh' => null,
            'tanggal_verifikasi' => null,
        ]);

        return $tagihan->refresh();
    }

    public function verifikasiTransfer(Tagihan $tagihan, Akun $actor, bool $approve, ?Carbon $tanggalVerifikasi = null): Tagihan
    {
        $tagihan->loadMissing('unit');
        $this->authorizeTransferVerification($actor, $tagihan);

        if ($tagihan->status !== 'menunggu_verifikasi') {
            throw new InvalidArgumentException('Hanya tagihan menunggu verifikasi yang dapat diverifikasi.');
        }

        if ($tagihan->metode !== 'transfer' || $tagihan->bukti_transfer_url === null) {
            throw new InvalidArgumentException('Tagihan transfer wajib memiliki bukti pembayaran.');
        }

        $tagihan->update([
            'status' => $approve ? 'lunas' : 'ditolak',
            'diverifikasi_oleh' => $actor->id_akun,
            'tanggal_verifikasi' => ($tanggalVerifikasi ?? Carbon::now())->toDateTimeString(),
        ]);

        return $tagihan->refresh();
    }

    private function authorizeCashPayment(Akun $actor, Tagihan $tagihan): void
    {
        if ($actor->hasRole('super_admin')) {
            return;
        }

        if ($actor->hasRole('admin_unit') && $actor->id_unit === $tagihan->id_unit) {
            return;
        }

        if (($actor->hasRole('sekretaris') || $actor->hasRole('bendahara')) && $actor->id_bumdes === $tagihan->unit->id_bumdes) {
            return;
        }

        throw new AuthorizationException('Akun tidak berwenang mencatat pembayaran tunai tagihan ini.');
    }

    private function authorizeProofUpload(Akun $actor, Tagihan $tagihan): void
    {
        if ($actor->hasRole('pengguna') && $actor->pelanggan?->id_pelanggan === $tagihan->id_pelanggan) {
            return;
        }

        throw new AuthorizationException('Akun tidak berwenang mengunggah bukti transfer tagihan ini.');
    }

    private function authorizeTransferVerification(Akun $actor, Tagihan $tagihan): void
    {
        if ($actor->hasRole('admin_unit') && $actor->id_unit === $tagihan->id_unit) {
            return;
        }

        throw new AuthorizationException('Hanya Admin Unit terkait yang dapat memverifikasi bukti transfer tagihan.');
    }
}
