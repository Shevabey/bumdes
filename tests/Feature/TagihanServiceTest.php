<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Services\TagihanService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TagihanServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sekretaris_bendahara_and_admin_unit_can_record_cash_payment_in_owned_scope(): void
    {
        $this->seed();
        $service = new TagihanService;
        $verifiedAt = Carbon::create(2026, 10, 2, 9, 30);
        $sekretaris = Akun::where('username', 'sekretaris.sds')->firstOrFail();
        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();

        $sekretarisBill = $this->createBill('TAG-20261001-100001', 'UNT-BMD-SDS-001-PTN-01');
        $bendaharaBill = $this->createBill('TAG-20261001-100002', 'UNT-BMD-SDS-001-MTN-01');
        $adminUnitBill = $this->createBill('TAG-20261001-100003', 'UNT-BMD-SDS-001-PAM-01');

        $this->assertSame('lunas', $service->bayarTunai($sekretarisBill, $sekretaris, $verifiedAt)->status);
        $this->assertSame('lunas', $service->bayarTunai($bendaharaBill, $bendahara, $verifiedAt)->status);
        $paidByAdminUnit = $service->bayarTunai($adminUnitBill, $adminUnit, $verifiedAt);

        $this->assertSame('tunai', $paidByAdminUnit->metode);
        $this->assertSame($adminUnit->id_akun, $paidByAdminUnit->diverifikasi_oleh);
        $this->assertSame('2026-10-02 09:30:00', $paidByAdminUnit->tanggal_verifikasi->toDateTimeString());
    }

    public function test_cash_payment_rejects_cross_scope_and_invalid_status(): void
    {
        $this->seed();
        $service = new TagihanService;
        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $crossUnitBill = $this->createBill('TAG-20261001-100004', 'UNT-BMD-SDS-001-PTN-01');

        $this->expectException(AuthorizationException::class);

        $service->bayarTunai($crossUnitBill, $adminUnit, Carbon::create(2026, 10, 2, 10));
    }

    public function test_cash_payment_rejects_bill_waiting_for_transfer_verification(): void
    {
        $this->seed();
        $service = new TagihanService;
        $sekretaris = Akun::where('username', 'sekretaris.sds')->firstOrFail();
        $bill = $this->createBill('TAG-20261001-100005', 'UNT-BMD-SDS-001-PTN-01', [
            'status' => 'menunggu_verifikasi',
            'metode' => 'transfer',
            'bukti_transfer_url' => 'payments/waiting-proof.jpg',
        ]);

        $this->expectException(InvalidArgumentException::class);

        $service->bayarTunai($bill, $sekretaris, Carbon::create(2026, 10, 2, 10));
    }

    public function test_pengguna_can_upload_transfer_proof_for_owned_bill_only(): void
    {
        $this->seed();
        $service = new TagihanService;
        $pengguna = Akun::where('username', 'pengguna.sds')->firstOrFail();
        $ownedBill = $this->createBill('TAG-20261001-100006', 'UNT-BMD-SDS-001-PAM-01', [
            'id_pelanggan' => $pengguna->pelanggan->id_pelanggan,
        ]);

        $updated = $service->uploadBuktiTransfer($ownedBill, $pengguna, 'payments/portal-proof.jpg');

        $this->assertSame('menunggu_verifikasi', $updated->status);
        $this->assertSame('transfer', $updated->metode);
        $this->assertSame('payments/portal-proof.jpg', $updated->bukti_transfer_url);
        $this->assertNull($updated->diverifikasi_oleh);
        $this->assertNull($updated->tanggal_verifikasi);

        $otherBill = $this->createBill('TAG-20261001-100007', 'UNT-BMD-SDS-001-PTN-01');

        $this->expectException(AuthorizationException::class);

        $service->uploadBuktiTransfer($otherBill, $pengguna, 'payments/wrong-proof.jpg');
    }

    public function test_admin_unit_can_approve_or_reject_transfer_proof_in_owned_unit(): void
    {
        $this->seed();
        $service = new TagihanService;
        $verifiedAt = Carbon::create(2026, 10, 3, 11, 15);
        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $approvedBill = $this->createBill('TAG-20261001-100008', 'UNT-BMD-SDS-001-PAM-01', [
            'status' => 'menunggu_verifikasi',
            'metode' => 'transfer',
            'bukti_transfer_url' => 'payments/approve-proof.jpg',
        ]);
        $rejectedBill = $this->createBill('TAG-20261001-100009', 'UNT-BMD-SDS-001-PAM-01', [
            'status' => 'menunggu_verifikasi',
            'metode' => 'transfer',
            'bukti_transfer_url' => 'payments/reject-proof.jpg',
        ]);

        $approved = $service->verifikasiTransfer($approvedBill, $adminUnit, true, $verifiedAt);
        $rejected = $service->verifikasiTransfer($rejectedBill, $adminUnit, false, $verifiedAt);

        $this->assertSame('lunas', $approved->status);
        $this->assertSame('ditolak', $rejected->status);
        $this->assertSame($adminUnit->id_akun, $approved->diverifikasi_oleh);
        $this->assertSame($adminUnit->id_akun, $rejected->diverifikasi_oleh);
        $this->assertSame('2026-10-03 11:15:00', $approved->tanggal_verifikasi->toDateTimeString());
    }

    public function test_transfer_verification_requires_admin_unit_scope(): void
    {
        $this->seed();
        $service = new TagihanService;
        $sekretaris = Akun::where('username', 'sekretaris.sds')->firstOrFail();
        $waitingBill = $this->createBill('TAG-20261001-100010', 'UNT-BMD-SDS-001-PAM-01', [
            'status' => 'menunggu_verifikasi',
            'metode' => 'transfer',
            'bukti_transfer_url' => 'payments/waiting-proof.jpg',
        ]);

        $this->expectException(AuthorizationException::class);

        $service->verifikasiTransfer($waitingBill, $sekretaris, true, Carbon::create(2026, 10, 3, 12));
    }

    public function test_transfer_verification_requires_waiting_status(): void
    {
        $this->seed();
        $service = new TagihanService;
        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $this->expectException(InvalidArgumentException::class);

        $service->verifikasiTransfer(
            $this->createBill('TAG-20261001-100011', 'UNT-BMD-SDS-001-PAM-01'),
            $adminUnit,
            true,
            Carbon::create(2026, 10, 3, 12),
        );
    }

    private function createBill(string $idTagihan, string $idUnit, array $overrides = []): Tagihan
    {
        $pelanggan = isset($overrides['id_pelanggan'])
            ? Pelanggan::where('id_pelanggan', $overrides['id_pelanggan'])->firstOrFail()
            : Pelanggan::where('id_unit', $idUnit)->firstOrFail();

        return Tagihan::create([
            'id_tagihan' => $idTagihan,
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'id_unit' => $idUnit,
            'jumlah' => 25000,
            'jatuh_tempo' => '2026-10-31',
            'status' => 'belum_bayar',
            'metode' => null,
            'bukti_transfer_url' => null,
            'diverifikasi_oleh' => null,
            'tanggal_verifikasi' => null,
            ...$overrides,
        ]);
    }
}
