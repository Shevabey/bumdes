<?php

namespace Tests\Feature;

use App\Http\Livewire\Operasional\IuranPanel;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\IuranBumdes;
use App\Models\KasBumdes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class BumdesIuranTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────── Route Authorization ───────────────────────────────

    public function test_iuran_routes_redirect_guests(): void
    {
        $this->seed();

        $this->get(route('operasional.iuran'))->assertRedirect(route('login'));
        $this->get(route('bumdes.iuran'))->assertRedirect(route('login'));
    }

    public function test_iuran_routes_accessible_by_authorized_roles(): void
    {
        $this->seed();

        // 1. Super Admin
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);
        $this->get(route('operasional.iuran'))->assertOk();

        // 2. Admin BUMDes Koordinator (Sendangsari)
        $adminKoordinator = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminKoordinator);
        $this->get(route('operasional.iuran'))->assertOk();

        // 3. Admin BUMDes Non-koordinator (Sendangrejo)
        $adminBumdes = Akun::where('username', 'admin.sendangrejo')->firstOrFail();
        $this->actingAs($adminBumdes);
        $this->get(route('operasional.iuran'))->assertOk();

        // 4. Bendahara
        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $this->actingAs($bendahara);
        $this->get(route('operasional.iuran'))->assertOk();

        // 5. Sekretaris
        $sekretaris = Akun::where('username', 'sekretaris.sds')->firstOrFail();
        $this->actingAs($sekretaris);
        $this->get(route('operasional.iuran'))->assertOk();

        // Test alias bumdes.iuran redirect
        $this->get(route('bumdes.iuran'))->assertRedirect(route('operasional.iuran'));
    }

    // ─────────────────────────────── Livewire IuranPanel ───────────────────────────────

    public function test_iuran_panel_renders_and_shows_records(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(IuranPanel::class)
            ->assertStatus(200)
            ->assertSee('Manajemen Iuran BUMDes')
            ->assertSee('Belum Bayar')
            ->assertSee('Menunggu Verifikasi')
            ->assertSee('Lunas');
    }

    public function test_iuran_panel_search_and_filters(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $iuran = IuranBumdes::firstOrFail();

        // Search by ID
        Livewire::test(IuranPanel::class)
            ->set('search', $iuran->id_iuran)
            ->assertSee($iuran->id_iuran);

        // Filter status
        Livewire::test(IuranPanel::class)
            ->set('filters.status', 'belum_bayar')
            ->assertStatus(200);

        // Filter bulan_tahun
        Livewire::test(IuranPanel::class)
            ->set('filters.bulan_tahun', $iuran->bulan_tahun)
            ->assertSee($iuran->bulan_tahun);
    }

    public function test_iuran_panel_stats_calculation(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $component = Livewire::test(IuranPanel::class);

        $expectedBelumBayar = IuranBumdes::where('status', 'belum_bayar')->count();
        $expectedNominalBelumBayar = (float) IuranBumdes::where('status', 'belum_bayar')->sum('jumlah');
        $expectedTotalNominal = (float) IuranBumdes::sum('jumlah');

        $stats = $component->get('stats');
        $this->assertEquals($expectedBelumBayar, $stats['countBelumBayar']);
        $this->assertEquals($expectedNominalBelumBayar, $stats['nominalBelumBayar']);
        $this->assertEquals($expectedTotalNominal, $stats['totalNominal']);
    }

    public function test_iuran_panel_scoping_coordinator_sees_district_vs_regular_sees_own(): void
    {
        $this->seed();

        // Coordinator: admin.sendangsari
        $adminKoordinator = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminKoordinator);

        // Sendangsari dan Sendangrejo keduanya ada di Kec. Minggir
        $componentKoordinator = Livewire::test(IuranPanel::class);
        $componentKoordinator->assertSee('IUR-BMD-SDS-001-2026-09');
        $componentKoordinator->assertSee('IUR-BMD-SDR-001-2026-09');

        // Non-coordinator: bendahara.sds (hanya BMD-SDS-001)
        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $this->actingAs($bendahara);

        $componentBendahara = Livewire::test(IuranPanel::class);
        $componentBendahara->assertSee('IUR-BMD-SDS-001-2026-09');
        $componentBendahara->assertDontSee('IUR-BMD-SDR-001-2026-09');
    }

    public function test_iuran_panel_bendahara_can_pay_iuran_via_transfer(): void
    {
        $this->seed();

        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $this->actingAs($bendahara);

        $iuran = IuranBumdes::where('id_bumdes', $bendahara->id_bumdes)->firstOrFail();
        $iuran->update([
            'status' => 'belum_bayar',
            'sumber_dana' => null,
            'metode_bayar' => null,
            'bukti_pembayaran_url' => null,
            'tanggal_bayar' => null,
        ]);

        Livewire::test(IuranPanel::class)
            ->call('openBayarModal', $iuran->id_iuran)
            ->assertSet('showBayarModal', true)
            ->assertSet('bayarIuranId', $iuran->id_iuran)
            ->set('sumber_dana', 'luar_kas')
            ->set('metode_bayar', 'transfer')
            ->set('bukti_pembayaran_url', 'https://example.com/bukti-transfer-iuran.png')
            ->call('prosesBayar')
            ->assertSet('showBayarModal', false)
            ->assertHasNoErrors();

        $iuran->refresh();
        $this->assertEquals('menunggu_verifikasi', $iuran->status);
        $this->assertEquals('luar_kas', $iuran->sumber_dana);
        $this->assertEquals('transfer', $iuran->metode_bayar);
        $this->assertEquals('https://example.com/bukti-transfer-iuran.png', $iuran->bukti_pembayaran_url);
        $this->assertNotNull($iuran->tanggal_bayar);
    }

    public function test_iuran_panel_pay_via_kas_deducts_balance_and_records_mutasi(): void
    {
        $this->seed();

        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $this->actingAs($bendahara);

        // Buat saldo kas BUMDes mencukupi
        $kas = KasBumdes::firstOrCreate(
            ['id_kas' => "KAS-{$bendahara->id_bumdes}"],
            ['id_bumdes' => $bendahara->id_bumdes, 'saldo' => 100000]
        );
        $kas->update(['saldo' => 100000]);

        $iuran = IuranBumdes::where('id_bumdes', $bendahara->id_bumdes)->firstOrFail();
        $iuran->update([
            'status' => 'belum_bayar',
            'sumber_dana' => null,
            'metode_bayar' => null,
            'bukti_pembayaran_url' => null,
            'tanggal_bayar' => null,
        ]);

        Livewire::test(IuranPanel::class)
            ->call('openBayarModal', $iuran->id_iuran)
            ->set('sumber_dana', 'kas')
            ->set('metode_bayar', 'tunai')
            ->call('prosesBayar')
            ->assertHasNoErrors();

        $iuran->refresh();
        $this->assertEquals('menunggu_verifikasi', $iuran->status);
        $this->assertEquals('kas', $iuran->sumber_dana);

        $kas->refresh();
        $this->assertEquals(50000, (float) $kas->saldo);

        $this->assertDatabaseHas('kas_mutasi', [
            'id_kas' => $kas->id_kas,
            'tipe' => 'keluar',
            'sumber' => 'iuran',
            'jumlah' => 50000,
        ]);
    }

    public function test_iuran_panel_transfer_payment_requires_proof(): void
    {
        $this->seed();

        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $this->actingAs($bendahara);

        $iuran = IuranBumdes::where('id_bumdes', $bendahara->id_bumdes)->firstOrFail();
        $iuran->update([
            'status' => 'belum_bayar',
            'sumber_dana' => null,
            'metode_bayar' => null,
            'bukti_pembayaran_url' => null,
            'tanggal_bayar' => null,
        ]);

        Livewire::test(IuranPanel::class)
            ->call('openBayarModal', $iuran->id_iuran)
            ->set('sumber_dana', 'luar_kas')
            ->set('metode_bayar', 'transfer')
            ->set('bukti_pembayaran_url', '')
            ->call('prosesBayar')
            ->assertHasErrors(['bukti_pembayaran_url']);

        $iuran->refresh();
        $this->assertEquals('belum_bayar', $iuran->status);
    }

    public function test_iuran_panel_coordinator_can_verify_and_approve_iuran(): void
    {
        $this->seed();

        $adminKoordinator = Akun::where('username', 'admin.sendangsari')->firstOrFail();

        // Buat iuran berstatus menunggu_verifikasi untuk Sendangrejo (dalam kecamatan Minggir yang sama)
        $iuran = IuranBumdes::where('id_bumdes', 'BMD-SDR-001')->firstOrFail();
        $iuran->update([
            'status' => 'menunggu_verifikasi',
            'sumber_dana' => 'luar_kas',
            'metode_bayar' => 'transfer',
            'bukti_pembayaran_url' => 'https://example.com/bukti-sdr.jpg',
            'tanggal_bayar' => Carbon::now(),
        ]);

        $this->actingAs($adminKoordinator);

        Livewire::test(IuranPanel::class)
            ->call('openVerifikasiModal', $iuran->id_iuran)
            ->assertSet('showVerifikasiModal', true)
            ->assertSet('verifikasiIuranId', $iuran->id_iuran)
            ->call('prosesVerifikasi', true)
            ->assertSet('showVerifikasiModal', false);

        $iuran->refresh();
        $this->assertEquals('lunas', $iuran->status);
        $this->assertEquals($adminKoordinator->id_akun, $iuran->diverifikasi_oleh);
        $this->assertNotNull($iuran->tanggal_verifikasi);
    }

    public function test_iuran_panel_coordinator_can_reject_and_refund_cash_iuran(): void
    {
        $this->seed();

        $adminKoordinator = Akun::where('username', 'admin.sendangsari')->firstOrFail();

        $bumdesSdr = Bumdes::where('id_bumdes', 'BMD-SDR-001')->firstOrFail();
        $kasSdr = KasBumdes::firstOrCreate(
            ['id_kas' => "KAS-{$bumdesSdr->id_bumdes}"],
            ['id_bumdes' => $bumdesSdr->id_bumdes, 'saldo' => 50000]
        );
        $kasSdr->update(['saldo' => 50000]);

        $iuran = IuranBumdes::where('id_bumdes', 'BMD-SDR-001')->firstOrFail();
        $iuran->update([
            'status' => 'menunggu_verifikasi',
            'sumber_dana' => 'kas',
            'metode_bayar' => 'tunai',
            'tanggal_bayar' => Carbon::now(),
        ]);

        $this->actingAs($adminKoordinator);

        Livewire::test(IuranPanel::class)
            ->call('openVerifikasiModal', $iuran->id_iuran)
            ->call('prosesVerifikasi', false)
            ->assertSet('showVerifikasiModal', false);

        $iuran->refresh();
        $this->assertEquals('belum_bayar', $iuran->status);
        $this->assertNull($iuran->sumber_dana);
        $this->assertNull($iuran->metode_bayar);

        // Refund kas masuk
        $kasSdr->refresh();
        $this->assertEquals(100000, (float) $kasSdr->saldo);
        $this->assertDatabaseHas('kas_mutasi', [
            'id_kas' => $kasSdr->id_kas,
            'tipe' => 'masuk',
            'sumber' => 'iuran',
            'jumlah' => 50000,
        ]);
    }

    public function test_iuran_panel_non_coordinator_cannot_verify_iuran(): void
    {
        $this->seed();

        // Non-coordinator: admin.sendangrejo
        $adminNonKoordinator = Akun::where('username', 'admin.sendangrejo')->firstOrFail();
        $this->actingAs($adminNonKoordinator);

        $iuran = IuranBumdes::where('id_bumdes', 'BMD-SDS-001')->firstOrFail();
        $iuran->update([
            'status' => 'menunggu_verifikasi',
            'sumber_dana' => 'luar_kas',
            'metode_bayar' => 'transfer',
            'bukti_pembayaran_url' => 'https://example.com/bukti.jpg',
            'tanggal_bayar' => Carbon::now(),
        ]);

        Livewire::test(IuranPanel::class)
            ->call('openVerifikasiModal', $iuran->id_iuran)
            ->assertForbidden();
    }

    public function test_iuran_panel_superadmin_can_generate_bulanan(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(IuranPanel::class)
            ->call('generateBulanan')
            ->assertStatus(200);
    }

    public function test_iuran_panel_detail_modal(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $iuran = IuranBumdes::firstOrFail();

        Livewire::test(IuranPanel::class)
            ->call('openDetailModal', $iuran->id_iuran)
            ->assertSet('showDetailModal', true)
            ->assertSet('detailId', $iuran->id_iuran)
            ->call('closeDetailModal')
            ->assertSet('showDetailModal', false)
            ->assertSet('detailId', null);
    }
}
