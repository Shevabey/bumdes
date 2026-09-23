<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\KasBumdes;
use App\Models\KasMutasi;
use App\Models\Referral;
use App\Services\ReferralService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ReferralServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bumdes_can_generate_or_get_active_referral_code(): void
    {
        $this->seed();
        $service = new ReferralService;
        $adminSds = Akun::where('username', 'admin.sendangsari')->firstOrFail();

        $active = $service->generateOrGetActive('BMD-SDS-001', $adminSds, Carbon::create(2026, 9, 21));

        $this->assertSame('ACT001', $active->kode_unik);
        $this->assertSame('aktif', $active->status);
        $this->assertSame('BMD-SDS-001', $active->id_bumdes_pengaju);
    }

    public function test_generate_code_rejects_cross_bumdes_actor(): void
    {
        $this->seed();
        $service = new ReferralService;
        $adminSdr = Akun::where('username', 'admin.sendangrejo')->firstOrFail();

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Akun tidak berwenang');

        $service->generateOrGetActive('BMD-SDS-001', $adminSdr, Carbon::create(2026, 9, 21));
    }

    public function test_expired_active_code_is_replaced_on_generate(): void
    {
        $this->seed();
        $service = new ReferralService;
        $adminSds = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $afterExpiry = Carbon::create(2026, 9, 26, 10, 0);

        $newActive = $service->generateOrGetActive('BMD-SDS-001', $adminSds, $afterExpiry);

        $this->assertNotSame('ACT001', $newActive->kode_unik);
        $this->assertSame('aktif', $newActive->status);
        $this->assertSame('2026-10-01 10:00:00', $newActive->tanggal_expired->toDateTimeString());

        $oldActive = Referral::where('kode_unik', 'ACT001')->firstOrFail();
        $this->assertSame('kedaluwarsa', $oldActive->status);
    }

    public function test_recipient_admin_bumdes_can_redeem_valid_code(): void
    {
        $this->seed();
        $service = new ReferralService;
        $adminSdr = Akun::where('username', 'admin.sendangrejo')->firstOrFail();
        $redeemTime = Carbon::create(2026, 9, 21, 11, 0);

        // Reset status pending existing di seeder agar BUMDes SDR bisa redeem baru
        Referral::where('kode_unik', 'PND001')->update(['status' => 'gagal']);

        $redeemed = $service->redeem('ACT001', 'BMD-SDR-001', $adminSdr, $redeemTime);

        $this->assertSame('pending', $redeemed->status);
        $this->assertSame('BMD-SDR-001', $redeemed->id_bumdes_penerima);
        $this->assertSame('2026-09-21 11:00:00', $redeemed->tanggal_redeem->toDateTimeString());
        $this->assertSame('2026-10-06 11:00:00', $redeemed->batas_verifikasi->toDateTimeString());

        // Pengaju (Sendangsari) otomatis mendapatkan kode aktif baru
        $replacementActive = Referral::where('id_bumdes_pengaju', 'BMD-SDS-001')
            ->where('status', 'aktif')
            ->first();

        $this->assertNotNull($replacementActive);
        $this->assertNotSame('ACT001', $replacementActive->kode_unik);
    }

    public function test_redeem_rejects_self_referral_or_expired_or_invalid_code(): void
    {
        $this->seed();
        $service = new ReferralService;
        $adminSds = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $adminSdr = Akun::where('username', 'admin.sendangrejo')->firstOrFail();

        // 1. Self referral
        try {
            $service->redeem('ACT001', 'BMD-SDS-001', $adminSds, Carbon::create(2026, 9, 21));
            $this->fail('Harus gagal karena self referral.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('milik sendiri', $e->getMessage());
        }

        // 2. Kode tidak ditemukan
        try {
            $service->redeem('INVALD', 'BMD-SDR-001', $adminSdr, Carbon::create(2026, 9, 21));
            $this->fail('Harus gagal karena kode tidak ditemukan.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('tidak ditemukan', $e->getMessage());
        }

        // 3. Kode kedaluwarsa
        try {
            $service->redeem('ACT001', 'BMD-SDR-001', $adminSdr, Carbon::create(2026, 9, 26));
            $this->fail('Harus gagal karena kode kedaluwarsa.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('kedaluwarsa', $e->getMessage());
        }
    }

    public function test_redeem_rejects_recipient_that_already_used_referral(): void
    {
        $this->seed();
        $service = new ReferralService;
        $adminSdr = Akun::where('username', 'admin.sendangrejo')->firstOrFail();

        // Di seeder, BMD-SDR-001 sudah terikat ke PND001 (status pending)
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sudah pernah menggunakan kode referral');

        $service->redeem('ACT001', 'BMD-SDR-001', $adminSdr, Carbon::create(2026, 9, 21));
    }

    public function test_process_pencairan_cashes_referral_with_activity_and_credits_kas(): void
    {
        $this->seed();
        $service = new ReferralService;
        $pending = Referral::where('kode_unik', 'PND001')->firstOrFail();
        $checkTime = Carbon::create(2026, 10, 4, 10, 0);

        $kas = KasBumdes::where('id_bumdes', 'BMD-SDR-001')->firstOrFail();
        $initialBalance = (float) $kas->saldo;

        $processed = $service->prosesPencairan($pending, $checkTime);

        $this->assertSame('cair', $processed->status);
        $this->assertSame('2026-10-04 10:00:00', $processed->tanggal_cair->toDateTimeString());

        $kas->refresh();
        $this->assertSame($initialBalance + 10000, (float) $kas->saldo);

        $mutation = KasMutasi::where('id_kas', $kas->id_kas)
            ->where('sumber', 'referral')
            ->where('keterangan', 'Pencairan referral PND001')
            ->first();

        $this->assertNotNull($mutation);
        $this->assertSame('masuk', $mutation->tipe);
        $this->assertSame(10000.0, (float) $mutation->jumlah);
    }

    public function test_process_pencairan_fails_referral_without_activity(): void
    {
        $this->seed();
        $service = new ReferralService;
        $checkTime = Carbon::create(2026, 10, 4, 10, 0);

        // Buat referral pending di periode masa depan tanpa ada transaksi
        $pendingWithoutActivity = Referral::create([
            'id_referral' => 'REF-BMD-SDS-001-NOACT1',
            'id_bumdes_pengaju' => 'BMD-SDS-001',
            'id_bumdes_penerima' => 'BMD-SDR-001',
            'kode_unik' => 'NOACT1',
            'tanggal_generate' => Carbon::create(2026, 11, 1),
            'tanggal_expired' => Carbon::create(2026, 11, 6),
            'status' => 'pending',
            'tanggal_redeem' => Carbon::create(2026, 11, 2),
            'batas_verifikasi' => Carbon::create(2026, 11, 17),
        ]);

        $kas = KasBumdes::where('id_bumdes', 'BMD-SDR-001')->firstOrFail();
        $initialBalance = (float) $kas->saldo;

        $processed = $service->prosesPencairan($pendingWithoutActivity, Carbon::create(2026, 11, 18));

        $this->assertSame('gagal', $processed->status);
        $this->assertNull($processed->tanggal_cair);

        $kas->refresh();
        $this->assertSame($initialBalance, (float) $kas->saldo);
    }
}
