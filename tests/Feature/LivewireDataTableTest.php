<?php

namespace Tests\Feature;

use App\Http\Livewire\Shared\DataTable;
use App\Models\Akun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireDataTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_table_mounts_and_renders_cleanly(): void
    {
        Livewire::test(DataTable::class)
            ->assertStatus(200)
            ->assertSee('Cari data...')
            ->assertSee('Tidak ada data ditemukan.')
            ->assertSet('search', '')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_data_table_search_and_reset_filters(): void
    {
        Livewire::test(DataTable::class)
            ->set('search', 'Sendangsari')
            ->assertSet('search', 'Sendangsari')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filters', []);
    }

    public function test_data_table_sorting_toggles_asc_and_desc(): void
    {
        Livewire::test(DataTable::class)
            ->call('sortBy', 'nama')
            ->assertSet('sortField', 'nama')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'nama')
            ->assertSet('sortDirection', 'desc')
            ->call('sortBy', 'tanggal')
            ->assertSet('sortField', 'tanggal')
            ->assertSet('sortDirection', 'asc');
    }

    public function test_status_badge_component_renders_expected_variants(): void
    {
        $lunasHtml = Blade::render('<x-status-badge status="lunas" />');
        $this->assertStringContainsString('Lunas', $lunasHtml);
        $this->assertStringContainsString('bg-emerald-50', $lunasHtml);

        $menungguHtml = Blade::render('<x-status-badge status="menunggu_verifikasi" />');
        $this->assertStringContainsString('Menunggu Verifikasi', $menungguHtml);
        $this->assertStringContainsString('bg-amber-50', $menungguHtml);

        $belumBayarHtml = Blade::render('<x-status-badge status="belum_bayar" />');
        $this->assertStringContainsString('Belum Bayar', $belumBayarHtml);
        $this->assertStringContainsString('bg-orange-50', $belumBayarHtml);

        $ditolakHtml = Blade::render('<x-status-badge status="ditolak" />');
        $this->assertStringContainsString('Ditolak', $ditolakHtml);
        $this->assertStringContainsString('bg-rose-50', $ditolakHtml);

        $cairHtml = Blade::render('<x-status-badge status="cair" />');
        $this->assertStringContainsString('Cair', $cairHtml);

        $pendingHtml = Blade::render('<x-status-badge status="pending" />');
        $this->assertStringContainsString('Pending', $pendingHtml);
    }

    public function test_panel_layout_renders_navigation_based_on_user_role(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $superadminView = Blade::render(
            '<x-layouts.panel title="Test Super Admin"><div>Konten Dashboard</div></x-layouts.panel>'
        );
        $this->assertStringContainsString('Super Admin', $superadminView);
        $this->assertStringContainsString('Wilayah (Region)', $superadminView);
        $this->assertStringContainsString('BUMDes', $superadminView);
        $this->assertStringContainsString('Konten Dashboard', $superadminView);

        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $this->actingAs($bendahara);

        $bendaharaView = Blade::render(
            '<x-layouts.panel title="Test Bendahara"><div>Konten Bendahara</div></x-layouts.panel>'
        );
        $this->assertStringContainsString('Operasional BUMDes', $bendaharaView);
        $this->assertStringContainsString('Iuran BUMDes', $bendaharaView);
        $this->assertStringNotContainsString('Super Admin', $bendaharaView);

        $pengawas = Akun::where('username', 'pengawas1')->firstOrFail();
        $this->actingAs($pengawas);

        $pengawasView = Blade::render(
            '<x-layouts.panel title="Test Pengawas"><div>Konten Pengawas</div></x-layouts.panel>'
        );
        $this->assertStringContainsString('Monitoring & Evaluasi', $pengawasView);
        $this->assertStringContainsString('Dashboard Nasional', $pengawasView);
        $this->assertStringNotContainsString('Super Admin', $pengawasView);
    }
}
