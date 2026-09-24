<?php

namespace Tests\Feature;

use App\Http\Livewire\Monitoring\DashboardNasional;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\KasBumdes;
use App\Models\Transaksi;
use App\Models\UnitUsaha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardNasionalTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────── Route Authorization ───────────────────────────────

    public function test_dashboard_routes_redirect_guests(): void
    {
        $this->seed();

        $this->get(route('monitoring.dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_routes_reject_unauthorized_roles(): void
    {
        $this->seed();

        $unauthorized = [
            'admin.sendangsari' => 'admin_bumdes',
            'sekretaris.sds' => 'sekretaris',
            'bendahara.sds' => 'bendahara',
            'admin.pamdes.sds' => 'admin_unit',
            'pengguna.sds' => 'pengguna',
        ];

        foreach ($unauthorized as $username => $role) {
            $akun = Akun::where('username', $username)->firstOrFail();
            $this->actingAs($akun);
            $response = $this->get(route('monitoring.dashboard'));
            $response->assertStatus(403, "Role {$role} seharusnya mendapat 403.");
        }
    }

    public function test_dashboard_routes_accessible_by_authorized_roles(): void
    {
        $this->seed();

        // Super Admin
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);
        $this->get(route('monitoring.dashboard'))->assertOk();

        // Pengawas
        $pengawas = Akun::where('username', 'pengawas1')->firstOrFail();
        $this->actingAs($pengawas);
        $this->get(route('monitoring.dashboard'))->assertOk();

        // Penasihat
        $penasihat = Akun::where('username', 'penasihat1')->firstOrFail();
        $this->actingAs($penasihat);
        $this->get(route('monitoring.dashboard'))->assertOk();

        // Direktur
        $direktur = Akun::where('username', 'direktur1')->firstOrFail();
        $this->actingAs($direktur);
        $this->get(route('monitoring.dashboard'))->assertOk();
    }

    // ─────────────────────────────── Livewire DashboardNasional ───────────────────────────────

    public function test_dashboard_renders_and_shows_bumdes_rows(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();

        Livewire::actingAs($superadmin)
            ->test(DashboardNasional::class)
            ->assertOk()
            ->assertSee('Dashboard Monitoring Nasional')
            ->assertSee('Sendangsari')
            ->assertSee('Sendangrejo')
            ->assertSee('BUMDes Aktif')
            ->assertSee('Menunggak Iuran')
            ->assertSee('Unit Tanpa Aktivitas')
            ->assertSee('Verifikasi Tertunda');
    }

    public function test_dashboard_shows_national_financial_summary(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();

        Livewire::actingAs($superadmin)
            ->test(DashboardNasional::class)
            ->assertOk()
            ->assertSee('Saldo Kas Nasional')
            ->assertSee('Total Omzet')
            ->assertSee('Total Pengeluaran')
            ->assertSee('Laba / Rugi Bersih Nasional');
    }

    public function test_dashboard_national_indicators_calculation(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();

        $component = Livewire::actingAs($superadmin)
            ->test(DashboardNasional::class);

        /** @var DashboardNasional $instance */
        $instance = $component->instance();
        $indikator = $instance->indikatorNasional();

        // Seeder membuat 2 BUMDes aktif (Sendangsari & Sendangrejo)
        $this->assertGreaterThanOrEqual(2, $indikator['total_bumdes_aktif']);
        $this->assertIsInt($indikator['bumdes_menunggak_iuran']);
        $this->assertIsInt($indikator['unit_tanpa_aktivitas']);
        $this->assertIsInt($indikator['verifikasi_tertunda']);
    }

    public function test_dashboard_national_financial_aggregation(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();

        $component = Livewire::actingAs($superadmin)
            ->test(DashboardNasional::class);

        /** @var DashboardNasional $instance */
        $instance = $component->instance();
        $keuangan = $instance->keuanganNasional();

        $this->assertArrayHasKey('kas_nasional', $keuangan);
        $this->assertArrayHasKey('total_input', $keuangan);
        $this->assertArrayHasKey('total_output', $keuangan);
        $this->assertArrayHasKey('untung_rugi', $keuangan);

        // Kas nasional harus >= 0
        $this->assertGreaterThanOrEqual(0, $keuangan['kas_nasional']);

        // total_input = sum 'input' transaksi bulanan, seeder memuat transaksi
        $expectedInput = (float) Transaksi::where('tipe', 'input')
            ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('jumlah');

        $this->assertEqualsWithDelta($expectedInput, $keuangan['total_input'], 0.01);
    }

    public function test_dashboard_bumdes_rows_contain_kas_and_untung_rugi(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();

        $component = Livewire::actingAs($superadmin)
            ->test(DashboardNasional::class);

        $rows = $component->viewData('bumdesRows');

        $this->assertNotEmpty($rows);

        $sdRow = collect($rows->items())->firstWhere('id_bumdes', 'BMD-SDS-001');
        $this->assertNotNull($sdRow);
        $this->assertArrayHasKey('kas', $sdRow);
        $this->assertArrayHasKey('total_input', $sdRow);
        $this->assertArrayHasKey('total_output', $sdRow);
        $this->assertArrayHasKey('untung_rugi', $sdRow);
        $this->assertArrayHasKey('status_iuran', $sdRow);
        $this->assertArrayHasKey('units_count', $sdRow);

        // kas Sendangsari seeder = Rp 10.000 (dari ReferralSeeder pencairan)
        $kasSeeder = (float) KasBumdes::where('id_bumdes', 'BMD-SDS-001')->value('saldo');
        $this->assertEqualsWithDelta($kasSeeder, $sdRow['kas'], 0.01);
    }

    public function test_dashboard_search_filters_bumdes(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();

        Livewire::actingAs($superadmin)
            ->test(DashboardNasional::class)
            ->set('search', 'Sendangsari')
            ->assertSee('Sendangsari')
            ->assertDontSee('Sendangrejo');
    }

    public function test_dashboard_filter_periode_changes_financial_data(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();

        $componentBulanan = Livewire::actingAs($superadmin)
            ->test(DashboardNasional::class)
            ->set('filters.periode', 'bulanan');

        /** @var DashboardNasional $instBulanan */
        $instBulanan = $componentBulanan->instance();
        $keuanganBulanan = $instBulanan->keuanganNasional();

        $componentSemua = Livewire::actingAs($superadmin)
            ->test(DashboardNasional::class)
            ->set('filters.periode', 'semua');

        /** @var DashboardNasional $instSemua */
        $instSemua = $componentSemua->instance();
        $keuanganSemua = $instSemua->keuanganNasional();

        // Periode 'semua' input harus >= bulan ini
        $this->assertGreaterThanOrEqual($keuanganBulanan['total_input'], $keuanganSemua['total_input']);
    }

    public function test_dashboard_unit_detail_modal(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();

        Livewire::actingAs($superadmin)
            ->test(DashboardNasional::class)
            ->call('showUnitDetail', 'BMD-SDS-001', 'BUMDes Sendangsari')
            ->assertSet('showUnitModal', true)
            ->assertSet('selectedBumdesId', 'BMD-SDS-001')
            ->assertSet('selectedBumdesName', 'BUMDes Sendangsari')
            ->assertSee('Rincian Unit Usaha')
            ->call('closeUnitModal')
            ->assertSet('showUnitModal', false)
            ->assertSet('selectedBumdesId', null);
    }

    public function test_dashboard_unit_modal_shows_per_unit_breakdown(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();

        $component = Livewire::actingAs($superadmin)
            ->test(DashboardNasional::class)
            ->call('showUnitDetail', 'BMD-SDS-001', 'BUMDes Sendangsari');

        $units = $component->get('selectedBumdesUnits');

        // Seeder membuat 5 unit usaha untuk Sendangsari
        $this->assertCount(
            UnitUsaha::where('id_bumdes', 'BMD-SDS-001')->count(),
            $units
        );

        foreach ($units as $unit) {
            $this->assertArrayHasKey('id_unit', $unit);
            $this->assertArrayHasKey('nama_unit', $unit);
            $this->assertArrayHasKey('total_input', $unit);
            $this->assertArrayHasKey('total_output', $unit);
            $this->assertArrayHasKey('untung_rugi', $unit);
        }
    }

    public function test_dashboard_reset_filters(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();

        Livewire::actingAs($superadmin)
            ->test(DashboardNasional::class)
            ->set('search', 'Sendangsari')
            ->set('filters.status_iuran', 'lunas')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filters.status_iuran', '')
            ->assertSet('filters.periode', 'bulanan');
    }
}
