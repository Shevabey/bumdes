<?php

namespace Tests\Feature;

use App\Http\Livewire\Operasional\TransaksiManager;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\Transaksi;
use App\Models\UnitUsaha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BumdesTransaksiTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────── Route Authorization ───────────────────────────────

    public function test_transaksi_routes_redirect_guests(): void
    {
        $this->seed();

        $this->get(route('operasional.transaksi'))->assertRedirect(route('login'));
    }

    public function test_transaksi_routes_accessible_by_authorized_roles(): void
    {
        $this->seed();

        // 1. Super Admin
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);
        $this->get(route('operasional.transaksi'))->assertOk();

        // 2. Admin BUMDes
        $adminBumdes = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminBumdes);
        $this->get(route('operasional.transaksi'))->assertOk();

        // 3. Bendahara
        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $this->actingAs($bendahara);
        $this->get(route('operasional.transaksi'))->assertOk();

        // 4. Admin Unit PAMDes
        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $this->actingAs($adminUnit);
        $this->get(route('operasional.transaksi'))->assertOk();
    }

    // ─────────────────────────────── TransaksiManager Livewire ───────────────────────────────

    public function test_transaksi_manager_renders_and_shows_records(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TransaksiManager::class)
            ->assertStatus(200)
            ->assertSee('Pemasukan')
            ->assertSee('Pengeluaran')
            ->assertSee('Total Pemasukan (Input)');
    }

    public function test_transaksi_manager_search(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $firstTrx = Transaksi::firstOrFail();

        Livewire::test(TransaksiManager::class)
            ->set('search', $firstTrx->id_transaksi)
            ->assertSee($firstTrx->id_transaksi);
    }

    public function test_transaksi_manager_filter_by_tipe(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TransaksiManager::class)
            ->set('filters.tipe', 'input')
            ->assertStatus(200);

        Livewire::test(TransaksiManager::class)
            ->set('filters.tipe', 'output')
            ->assertStatus(200);
    }

    public function test_transaksi_manager_filter_by_unit(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $unit = UnitUsaha::firstOrFail();

        Livewire::test(TransaksiManager::class)
            ->set('filters.id_unit', $unit->id_unit)
            ->assertStatus(200);
    }

    public function test_transaksi_manager_filter_by_date_range(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TransaksiManager::class)
            ->set('filters.tanggal_mulai', '2026-09-01')
            ->set('filters.tanggal_akhir', '2026-09-30')
            ->assertStatus(200);
    }

    public function test_transaksi_manager_stats_calculation(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $component = Livewire::test(TransaksiManager::class);

        $expectedInput = (float) Transaksi::where('tipe', 'input')->sum('jumlah');
        $expectedOutput = (float) Transaksi::where('tipe', 'output')->sum('jumlah');
        $expectedNet = $expectedInput - $expectedOutput;

        $stats = $component->get('stats');
        $this->assertEquals($expectedInput, $stats['totalInput']);
        $this->assertEquals($expectedOutput, $stats['totalOutput']);
        $this->assertEquals($expectedNet, $stats['saldoBersih']);
    }

    public function test_transaksi_manager_create_input_by_admin_unit(): void
    {
        $this->seed();

        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $this->actingAs($adminUnit);

        Livewire::test(TransaksiManager::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->assertSet('isEdit', false)
            ->assertSet('id_unit', $adminUnit->id_unit)
            ->set('tipe', 'input')
            ->set('jumlah', 150000)
            ->set('tanggal', '2026-09-24')
            ->set('keterangan', 'Pemasukan Retribusi Air')
            ->set('kategori', 'Retribusi')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('transaksi', [
            'id_unit' => $adminUnit->id_unit,
            'tipe' => 'input',
            'jumlah' => 150000,
            'dicatat_oleh' => $adminUnit->id_akun,
        ]);
    }

    public function test_transaksi_manager_create_output_by_bendahara(): void
    {
        $this->seed();

        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $this->actingAs($bendahara);

        $unit = UnitUsaha::where('id_bumdes', $bendahara->id_bumdes)->firstOrFail();

        Livewire::test(TransaksiManager::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->set('id_unit', $unit->id_unit)
            ->set('tipe', 'output')
            ->set('jumlah', 75000)
            ->set('tanggal', '2026-09-24')
            ->set('keterangan', 'Beli Pipa Paralon')
            ->set('kategori', 'Pemeliharaan')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('transaksi', [
            'id_unit' => $unit->id_unit,
            'tipe' => 'output',
            'jumlah' => 75000,
            'dicatat_oleh' => $bendahara->id_akun,
        ]);
    }

    public function test_transaksi_manager_create_validation_errors(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TransaksiManager::class)
            ->call('openCreateModal')
            ->set('id_unit', '')
            ->set('jumlah', '')
            ->set('keterangan', '')
            ->call('save')
            ->assertHasErrors(['id_unit', 'jumlah', 'keterangan']);
    }

    public function test_transaksi_manager_edit_transaksi(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $trx = Transaksi::firstOrFail();

        Livewire::test(TransaksiManager::class)
            ->call('openEditModal', $trx->id_transaksi)
            ->assertSet('showModal', true)
            ->assertSet('isEdit', true)
            ->set('jumlah', 125000)
            ->set('keterangan', 'Keterangan Hasil Edit')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('transaksi', [
            'id_transaksi' => $trx->id_transaksi,
            'jumlah' => 125000,
        ]);
    }

    public function test_transaksi_manager_view_detail_modal(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $trx = Transaksi::firstOrFail();

        Livewire::test(TransaksiManager::class)
            ->call('openDetailModal', $trx->id_transaksi)
            ->assertSet('showDetailModal', true)
            ->assertSet('detailId', $trx->id_transaksi)
            ->assertSee($trx->id_transaksi)
            ->call('closeDetailModal')
            ->assertSet('showDetailModal', false)
            ->assertSet('detailId', null);
    }

    public function test_transaksi_manager_sorting(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TransaksiManager::class)
            ->call('sortBy', 'jumlah')
            ->assertSet('sortField', 'jumlah')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'jumlah')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_transaksi_manager_reset_filters(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TransaksiManager::class)
            ->set('search', 'TRX-')
            ->set('filters.tipe', 'input')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filters.tipe', '');
    }

    public function test_transaksi_manager_admin_unit_scope_isolation(): void
    {
        $this->seed();

        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $this->actingAs($adminUnit);

        // Own unit transaksi
        $ownTrx = Transaksi::where('id_unit', $adminUnit->id_unit)->first();

        // Other unit transaksi
        $otherTrx = Transaksi::where('id_unit', '!=', $adminUnit->id_unit)->first();

        $component = Livewire::test(TransaksiManager::class)->assertStatus(200);

        if ($ownTrx) {
            $component->assertSee($ownTrx->id_transaksi);
        }

        if ($otherTrx) {
            $component->assertDontSee($otherTrx->id_transaksi);
        }
    }
}
