<?php

namespace Tests\Feature;

use App\Http\Livewire\Operasional\ReferralPanel;
use App\Models\Akun;
use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BumdesReferralTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────── Route Authorization ───────────────────────────────

    public function test_referral_routes_redirect_guests(): void
    {
        $this->seed();

        $this->get(route('operasional.referral'))->assertRedirect(route('login'));
        $this->get(route('bumdes.referral'))->assertRedirect(route('login'));
    }

    public function test_referral_routes_accessible_by_authorized_roles(): void
    {
        $this->seed();

        // 1. Super Admin
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);
        $this->get(route('operasional.referral'))->assertOk();

        // 2. Admin BUMDes Sendangsari
        $adminSds = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminSds);
        $this->get(route('operasional.referral'))->assertOk();

        // 3. Admin BUMDes Sendangrejo
        $adminSdr = Akun::where('username', 'admin.sendangrejo')->firstOrFail();
        $this->actingAs($adminSdr);
        $this->get(route('operasional.referral'))->assertOk();

        // 4. Bendahara
        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $this->actingAs($bendahara);
        $this->get(route('operasional.referral'))->assertOk();

        // 5. Sekretaris
        $sekretaris = Akun::where('username', 'sekretaris.sds')->firstOrFail();
        $this->actingAs($sekretaris);
        $this->get(route('operasional.referral'))->assertOk();

        // Test alias bumdes.referral redirect
        $this->get(route('bumdes.referral'))->assertRedirect(route('operasional.referral'));
    }

    // ─────────────────────────────── Livewire ReferralPanel ───────────────────────────────

    public function test_referral_panel_renders_and_shows_records(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(ReferralPanel::class)
            ->assertStatus(200)
            ->assertSee('Program Referral BUMDes')
            ->assertSee('Kode Aktif')
            ->assertSee('Pending')
            ->assertSee('Berhasil Cair')
            ->assertSee('ACT001');
    }

    public function test_referral_panel_search_and_filters(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        // Search kode unik
        Livewire::test(ReferralPanel::class)
            ->set('search', 'ACT001')
            ->assertSee('ACT001')
            ->assertDontSee('CLR001');

        // Filter status
        Livewire::test(ReferralPanel::class)
            ->set('filters.status', 'cair')
            ->assertSee('CLR001')
            ->assertDontSee('FAI001');
    }

    public function test_referral_panel_stats_calculation(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $component = Livewire::test(ReferralPanel::class);

        $expectedAktif = Referral::where('status', 'aktif')->count();
        $expectedPending = Referral::where('status', 'pending')->count();
        $expectedCair = Referral::where('status', 'cair')->count();
        $expectedInsentif = $expectedCair * 10000;

        $stats = $component->get('stats');
        $this->assertEquals($expectedAktif, $stats['countAktif']);
        $this->assertEquals($expectedPending, $stats['countPending']);
        $this->assertEquals($expectedCair, $stats['countCair']);
        $this->assertEquals($expectedInsentif, $stats['totalInsentifCair']);
    }

    public function test_referral_panel_scoping_bumdes_sees_own_sent_and_received(): void
    {
        $this->seed();

        // Buat referral terisolasi antar BUMDes lain jika ada, atau uji scoping Sendangrejo
        $adminSdr = Akun::where('username', 'admin.sendangrejo')->firstOrFail();
        $this->actingAs($adminSdr);

        // Sendangrejo terlibat dalam PND001 (sebagai penerima), CLR001 (sebagai pengaju), FAI001 (sebagai pengaju)
        // Tetapi ACT001 adalah kode aktif Sendangsari tanpa penerima, sehingga Sendangrejo TIDAK melihat ACT001 di tabel miliknya
        $component = Livewire::test(ReferralPanel::class);
        $component->assertSee('PND001');
        $component->assertSee('CLR001');
        $component->assertDontSee('REF-BMD-SDS-001-ACT001');

        // Filter relasi diajukan
        $component->set('filters.tipe_relasi', 'diajukan')
            ->assertSee('CLR001')
            ->assertDontSee('PND001');

        // Filter relasi diterima
        $component->set('filters.tipe_relasi', 'diterima')
            ->assertSee('PND001')
            ->assertDontSee('CLR001');
    }

    public function test_referral_panel_admin_bumdes_can_generate_active_code(): void
    {
        $this->seed();

        $adminSds = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminSds);

        // Sendangsari sudah punya ACT001 aktif, generateActiveCode akan memuat kode aktif tersebut
        Livewire::test(ReferralPanel::class)
            ->call('generateActiveCode')
            ->assertSee('ACT001');

        // Jika kode kedaluwarsa, generateActiveCode akan membuat kode baru
        Referral::where('kode_unik', 'ACT001')->update([
            'tanggal_expired' => Carbon::now()->subDay(),
        ]);

        Livewire::test(ReferralPanel::class)
            ->call('generateActiveCode')
            ->assertStatus(200);

        $newActive = Referral::where('id_bumdes_pengaju', 'BMD-SDS-001')
            ->where('status', 'aktif')
            ->first();

        $this->assertNotNull($newActive);
        $this->assertNotEquals('ACT001', $newActive->kode_unik);
    }

    public function test_referral_panel_admin_bumdes_can_redeem_valid_code(): void
    {
        $this->seed();

        $adminSdr = Akun::where('username', 'admin.sendangrejo')->firstOrFail();
        $this->actingAs($adminSdr);

        // Reset status pending existing di seeder agar BUMDes SDR bisa redeem baru
        Referral::where('kode_unik', 'PND001')->update(['status' => 'gagal']);

        Livewire::test(ReferralPanel::class)
            ->set('inputKodeRedeem', 'ACT001')
            ->call('redeemCode')
            ->assertHasNoErrors()
            ->assertSet('inputKodeRedeem', '');

        $referral = Referral::where('kode_unik', 'ACT001')->firstOrFail();
        $this->assertEquals('pending', $referral->status);
        $this->assertEquals('BMD-SDR-001', $referral->id_bumdes_penerima);
    }

    public function test_referral_panel_redeem_rejects_self_referral(): void
    {
        $this->seed();

        $adminSds = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminSds);

        // Sendangsari mencoba redeem kode ACT001 miliknya sendiri
        Livewire::test(ReferralPanel::class)
            ->set('inputKodeRedeem', 'ACT001')
            ->call('redeemCode')
            ->assertHasErrors(['inputKodeRedeem']);

        $referral = Referral::where('kode_unik', 'ACT001')->firstOrFail();
        $this->assertEquals('aktif', $referral->status);
    }

    public function test_referral_panel_redeem_rejects_invalid_or_expired_code(): void
    {
        $this->seed();

        $adminSdr = Akun::where('username', 'admin.sendangrejo')->firstOrFail();
        $this->actingAs($adminSdr);

        Livewire::test(ReferralPanel::class)
            ->set('inputKodeRedeem', 'KODEXX')
            ->call('redeemCode')
            ->assertHasErrors(['inputKodeRedeem']);
    }

    public function test_referral_panel_detail_modal(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $referral = Referral::firstOrFail();

        Livewire::test(ReferralPanel::class)
            ->call('openDetailModal', $referral->id_referral)
            ->assertSet('showDetailModal', true)
            ->assertSet('detailId', $referral->id_referral)
            ->assertSee($referral->kode_unik)
            ->call('closeDetailModal')
            ->assertSet('showDetailModal', false)
            ->assertSet('detailId', null);
    }
}
