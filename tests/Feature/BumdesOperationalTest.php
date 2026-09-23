<?php

namespace Tests\Feature;

use App\Http\Livewire\Operasional\PelangganManager;
use App\Http\Livewire\Operasional\UnitManager;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\Pelanggan;
use App\Models\UnitUsaha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BumdesOperationalTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────── Route Authorization ───────────────────────────────

    public function test_operasional_routes_redirect_guests(): void
    {
        $this->seed();

        $this->get(route('operasional.unit'))->assertRedirect(route('login'));
        $this->get(route('operasional.pelanggan'))->assertRedirect(route('login'));
    }

    public function test_operasional_routes_accessible_by_admin_bumdes(): void
    {
        $this->seed();

        $adminBumdes = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminBumdes);

        $this->get(route('operasional.unit'))->assertOk();
        $this->get(route('operasional.pelanggan'))->assertOk();
    }

    public function test_operasional_routes_accessible_by_super_admin(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $this->get(route('operasional.unit'))->assertOk();
        $this->get(route('operasional.pelanggan'))->assertOk();
    }

    // ─────────────────────────────── UnitManager ───────────────────────────────

    public function test_unit_manager_renders_and_shows_units(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(UnitManager::class)
            ->assertStatus(200)
            ->assertSee('PAMDes')
            ->assertSee('Peternakan');
    }

    public function test_unit_manager_search_filters_results(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(UnitManager::class)
            ->set('search', 'PAMDes')
            ->assertSee('PAMDes')
            ->assertDontSee('Peternakan SDR');
    }

    public function test_unit_manager_filter_by_jenis_unit(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(UnitManager::class)
            ->set('filters.jenis_unit', 'pamdes')
            ->assertSee('PAMDes')
            ->assertDontSee('Peternakan SDR');
    }

    public function test_unit_manager_sort_toggles(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(UnitManager::class)
            ->call('sortBy', 'nama_unit')
            ->assertSet('sortField', 'nama_unit')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'nama_unit')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_unit_manager_create_by_super_admin(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $bumdes = Bumdes::first();

        Livewire::test(UnitManager::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->assertSet('isEdit', false)
            ->set('id_bumdes', $bumdes->id_bumdes)
            ->set('nama_unit', 'Unit Test Baru')
            ->set('jenis_unit', 'custom')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('unit_usaha', ['nama_unit' => 'Unit Test Baru', 'jenis_unit' => 'custom']);
    }

    public function test_unit_manager_create_requires_bumdes(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(UnitManager::class)
            ->call('openCreateModal')
            ->set('nama_unit', 'Unit Tanpa BUMDes')
            ->set('jenis_unit', 'custom')
            ->call('save')
            ->assertHasErrors(['id_bumdes']);
    }

    public function test_unit_manager_edit_by_admin_bumdes_scoped_to_own_bumdes(): void
    {
        $this->seed();

        $adminBumdes = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminBumdes);

        // Find a unit in their BUMDes
        $unit = UnitUsaha::where('id_bumdes', $adminBumdes->id_bumdes)->first();

        Livewire::test(UnitManager::class)
            ->call('openEditModal', $unit->id_unit)
            ->assertSet('showModal', true)
            ->assertSet('isEdit', true)
            ->set('nama_unit', 'Nama Diperbarui')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('unit_usaha', ['id_unit' => $unit->id_unit, 'nama_unit' => 'Nama Diperbarui']);
    }

    public function test_unit_manager_toggle_status(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $unit = UnitUsaha::first();
        $originalStatus = $unit->status_aktif;

        Livewire::test(UnitManager::class)
            ->call('toggleStatus', $unit->id_unit);

        $this->assertDatabaseHas('unit_usaha', [
            'id_unit' => $unit->id_unit,
            'status_aktif' => ! $originalStatus,
        ]);
    }

    public function test_unit_manager_reset_filters(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(UnitManager::class)
            ->set('search', 'PAMDes')
            ->set('filters.jenis_unit', 'pamdes')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filters', ['id_bumdes' => '', 'jenis_unit' => '', 'status_aktif' => '']);
    }

    // ─────────────────────────────── PelangganManager ───────────────────────────────

    public function test_pelanggan_manager_renders_and_shows_pelanggan(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(PelangganManager::class)
            ->assertStatus(200)
            ->assertSee('Pelanggan');
    }

    public function test_pelanggan_manager_search(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $pelanggan = Pelanggan::first();

        Livewire::test(PelangganManager::class)
            ->set('search', $pelanggan->nama)
            ->assertSee($pelanggan->nama);
    }

    public function test_pelanggan_manager_filter_by_status(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(PelangganManager::class)
            ->set('filters.status_aktif', '1')
            ->assertStatus(200);
    }

    public function test_pelanggan_manager_create_by_admin_bumdes(): void
    {
        $this->seed();

        $adminBumdes = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminBumdes);

        $unit = UnitUsaha::where('id_bumdes', $adminBumdes->id_bumdes)->first();

        Livewire::test(PelangganManager::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->set('id_unit', $unit->id_unit)
            ->set('nama', 'Pelanggan Test Baru')
            ->set('kontak', '082200000001')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('pelanggan', ['nama' => 'Pelanggan Test Baru', 'id_unit' => $unit->id_unit]);
    }

    public function test_pelanggan_manager_create_requires_unit(): void
    {
        $this->seed();

        $adminBumdes = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminBumdes);

        Livewire::test(PelangganManager::class)
            ->call('openCreateModal')
            ->set('nama', 'Pelanggan Tanpa Unit')
            ->call('save')
            ->assertHasErrors(['id_unit']);
    }

    public function test_pelanggan_manager_edit(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $pelanggan = Pelanggan::first();

        Livewire::test(PelangganManager::class)
            ->call('openEditModal', $pelanggan->id_pelanggan)
            ->assertSet('showModal', true)
            ->assertSet('isEdit', true)
            ->set('nama', 'Nama Pelanggan Diperbarui')
            ->set('kontak', '081300000099')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('pelanggan', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'nama' => 'Nama Pelanggan Diperbarui',
        ]);
    }

    public function test_pelanggan_manager_toggle_status(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $pelanggan = Pelanggan::first();
        $originalStatus = $pelanggan->status_aktif;

        Livewire::test(PelangganManager::class)
            ->call('toggleStatus', $pelanggan->id_pelanggan);

        $this->assertDatabaseHas('pelanggan', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'status_aktif' => ! $originalStatus,
        ]);
    }

    public function test_pelanggan_manager_admin_bumdes_scoped_to_own_bumdes_units(): void
    {
        $this->seed();

        $adminBumdes = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminBumdes);

        // Their BUMDes units should be in scope
        $ownUnit = UnitUsaha::where('id_bumdes', $adminBumdes->id_bumdes)->first();
        $ownPelanggan = Pelanggan::where('id_unit', $ownUnit->id_unit)->first();

        // Other BUMDes unit
        $otherUnit = UnitUsaha::where('id_bumdes', '!=', $adminBumdes->id_bumdes)->first();
        $otherPelanggan = Pelanggan::where('id_unit', $otherUnit?->id_unit)->first();

        $component = Livewire::test(PelangganManager::class)->assertStatus(200);

        if ($ownPelanggan) {
            $component->assertSee($ownPelanggan->nama);
        }

        if ($otherPelanggan) {
            $component->assertDontSee($otherPelanggan->nama);
        }
    }
}
