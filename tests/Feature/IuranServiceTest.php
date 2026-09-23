<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\IuranBumdes;
use App\Models\KasBumdes;
use App\Models\KasMutasi;
use App\Services\IuranService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class IuranServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_generates_iuran_for_active_bumdes_only_and_is_idempotent(): void
    {
        $this->seed();
        Bumdes::where('id_bumdes', 'BMD-SDR-001')->update(['status_aktif' => false]);
        $service = new IuranService;
        $periode = Carbon::create(2026, 10, 1);

        $this->assertSame(1, $service->generateBulanan($periode));
        $this->assertSame(0, $service->generateBulanan($periode));
        $this->assertDatabaseHas('iuran_bumdes', [
            'id_iuran' => 'IUR-BMD-SDS-001-2026-10',
            'status' => 'belum_bayar',
            'jumlah' => 50000,
        ]);
        $this->assertDatabaseMissing('iuran_bumdes', [
            'id_iuran' => 'IUR-BMD-SDR-001-2026-10',
        ]);
    }

    public function test_command_generates_monthly_iuran_and_can_be_replayed(): void
    {
        $this->seed();

        $this->artisan('iuran:generate-bulanan', ['--bulan' => '2026-11'])
            ->expectsOutput('2 iuran bulanan dibuat untuk periode 2026-11.')
            ->assertExitCode(0);
        $this->artisan('iuran:generate-bulanan', ['--bulan' => '2026-11'])
            ->expectsOutput('0 iuran bulanan dibuat untuk periode 2026-11.')
            ->assertExitCode(0);

        $this->assertSame(2, IuranBumdes::where('bulan_tahun', '2026-11')->count());
    }

    public function test_bendahara_can_pay_iuran_using_cash_with_mutation_recorded(): void
    {
        $this->seed();
        $service = new IuranService;
        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $payDate = Carbon::create(2026, 10, 5, 8, 30);

        $iuran = IuranBumdes::create([
            'id_iuran' => 'IUR-BMD-SDS-001-2026-10',
            'id_bumdes' => 'BMD-SDS-001',
            'bulan_tahun' => '2026-10',
            'jumlah' => 50000,
            'status' => 'belum_bayar',
        ]);

        $kas = KasBumdes::where('id_bumdes', 'BMD-SDS-001')->firstOrFail();
        $saldoAwal = (float) $kas->saldo;

        $paid = $service->bayar($iuran, $bendahara, [
            'sumber_dana' => 'kas',
            'metode_bayar' => 'tunai',
        ], $payDate);

        $this->assertSame('menunggu_verifikasi', $paid->status);
        $this->assertSame('kas', $paid->sumber_dana);
        $this->assertSame('tunai', $paid->metode_bayar);
        $this->assertSame('2026-10-05 08:30:00', $paid->tanggal_bayar->toDateTimeString());

        $kas->refresh();
        $this->assertSame($saldoAwal - 50000, (float) $kas->saldo);

        $mutation = KasMutasi::where('id_kas', $kas->id_kas)
            ->where('sumber', 'iuran')
            ->where('keterangan', 'Pembayaran iuran 2026-10')
            ->first();

        $this->assertNotNull($mutation);
        $this->assertSame('keluar', $mutation->tipe);
        $this->assertSame(50000.0, (float) $mutation->jumlah);
    }

    public function test_payment_from_cash_rejects_when_balance_insufficient(): void
    {
        $this->seed();
        $service = new IuranService;
        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();

        $kas = KasBumdes::where('id_bumdes', 'BMD-SDS-001')->firstOrFail();
        $kas->update(['saldo' => 10000]);

        $iuran = IuranBumdes::create([
            'id_iuran' => 'IUR-BMD-SDS-001-2026-10',
            'id_bumdes' => 'BMD-SDS-001',
            'bulan_tahun' => '2026-10',
            'jumlah' => 50000,
            'status' => 'belum_bayar',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Saldo kas BUMDes tidak mencukupi');

        $service->bayar($iuran, $bendahara, [
            'sumber_dana' => 'kas',
            'metode_bayar' => 'tunai',
        ]);
    }

    public function test_payment_via_transfer_requires_proof_url(): void
    {
        $this->seed();
        $service = new IuranService;
        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();

        $iuran = IuranBumdes::create([
            'id_iuran' => 'IUR-BMD-SDS-001-2026-10',
            'id_bumdes' => 'BMD-SDS-001',
            'bulan_tahun' => '2026-10',
            'jumlah' => 50000,
            'status' => 'belum_bayar',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bukti pembayaran wajib diunggah');

        $service->bayar($iuran, $bendahara, [
            'sumber_dana' => 'luar_kas',
            'metode_bayar' => 'transfer',
            'bukti_pembayaran_url' => '',
        ]);
    }

    public function test_payment_rejects_cross_bumdes_actor_or_invalid_status(): void
    {
        $this->seed();
        $service = new IuranService;
        $bendaharaSds = Akun::where('username', 'bendahara.sds')->firstOrFail();

        $iuranSdr = IuranBumdes::create([
            'id_iuran' => 'IUR-BMD-SDR-001-2026-10',
            'id_bumdes' => 'BMD-SDR-001',
            'bulan_tahun' => '2026-10',
            'jumlah' => 50000,
            'status' => 'belum_bayar',
        ]);

        $this->expectException(AuthorizationException::class);

        $service->bayar($iuranSdr, $bendaharaSds, [
            'sumber_dana' => 'luar_kas',
            'metode_bayar' => 'tunai',
        ]);
    }

    public function test_coordinator_admin_bumdes_can_approve_iuran_in_same_district(): void
    {
        $this->seed();
        $service = new IuranService;
        $coordinator = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $verifyDate = Carbon::create(2026, 10, 6, 14, 0);

        $iuranWaiting = IuranBumdes::where('id_bumdes', 'BMD-SDR-001')
            ->where('status', 'menunggu_verifikasi')
            ->firstOrFail();

        $verified = $service->verifikasi($iuranWaiting, $coordinator, true, $verifyDate);

        $this->assertSame('lunas', $verified->status);
        $this->assertSame($coordinator->id_akun, $verified->diverifikasi_oleh);
        $this->assertSame('2026-10-06 14:00:00', $verified->tanggal_verifikasi->toDateTimeString());
    }

    public function test_coordinator_rejection_returns_status_and_refunds_cash_if_paid_from_cash(): void
    {
        $this->seed();
        $service = new IuranService;
        $coordinator = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $rejectDate = Carbon::create(2026, 10, 7, 16, 0);

        $kas = KasBumdes::where('id_bumdes', 'BMD-SDS-001')->firstOrFail();
        $saldoAwal = (float) $kas->saldo;

        $iuranCash = IuranBumdes::create([
            'id_iuran' => 'IUR-BMD-SDS-001-2026-10',
            'id_bumdes' => 'BMD-SDS-001',
            'bulan_tahun' => '2026-10',
            'jumlah' => 50000,
            'status' => 'menunggu_verifikasi',
            'sumber_dana' => 'kas',
            'metode_bayar' => 'transfer',
            'bukti_pembayaran_url' => 'contributions/proof-invalid.jpg',
            'tanggal_bayar' => '2026-10-06 10:00:00',
        ]);

        $rejected = $service->verifikasi($iuranCash, $coordinator, false, $rejectDate);

        $this->assertSame('belum_bayar', $rejected->status);
        $this->assertNull($rejected->sumber_dana);
        $this->assertNull($rejected->metode_bayar);
        $this->assertNull($rejected->bukti_pembayaran_url);
        $this->assertSame($coordinator->id_akun, $rejected->diverifikasi_oleh);
        $this->assertSame('2026-10-07 16:00:00', $rejected->tanggal_verifikasi->toDateTimeString());

        $kas->refresh();
        $this->assertSame($saldoAwal + 50000, (float) $kas->saldo);

        $refundMutation = KasMutasi::where('id_kas', $kas->id_kas)
            ->where('tipe', 'masuk')
            ->where('keterangan', 'Pengembalian dana iuran 2026-10 (ditolak)')
            ->first();

        $this->assertNotNull($refundMutation);
        $this->assertSame(50000.0, (float) $refundMutation->jumlah);
    }

    public function test_non_coordinator_cannot_verify_iuran(): void
    {
        $this->seed();
        $service = new IuranService;
        $nonCoordinator = Akun::where('username', 'admin.sendangrejo')->firstOrFail();

        $iuranWaiting = IuranBumdes::where('status', 'menunggu_verifikasi')->firstOrFail();

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Hanya Admin BUMDes Koordinator');

        $service->verifikasi($iuranWaiting, $nonCoordinator, true);
    }
}
