<?php

namespace Tests\Feature;

use App\Http\Livewire\SuperAdmin\BumdesManager;
use App\Http\Livewire\SuperAdmin\RegionManager;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminManagersTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_routes_authorization(): void
    {
        $this->seed();

        // 1. Guest redirected to login
        $this->get(route('super-admin.region'))->assertRedirect(route('login'));
        $this->get(route('super-admin.bumdes'))->assertRedirect(route('login'));

        // 2. Non-super admin forbidden (403)
        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $this->actingAs($bendahara);
        $this->get(route('super-admin.region'))->assertForbidden();
        $this->get(route('super-admin.bumdes'))->assertForbidden();

        // 3. Super admin authorized (200)
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);
        $this->get(route('super-admin.region'))->assertOk();
        $this->get(route('super-admin.bumdes'))->assertOk();
    }

    public function test_region_manager_crud_and_actions(): void
    {
        $this->seed();
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        // Mount & verify seed regions visible
        Livewire::test(RegionManager::class)
            ->assertStatus(200)
            ->assertSee('Sendangsari')
            ->assertSee('34.04.07.2005')
            // Search
            ->set('search', 'Sendangrejo')
            ->assertSee('Sendangrejo')
            ->assertDontSee('Sendangsari')
            // Filter
            ->set('search', '')
            ->set('filters.jenis_wilayah', 'kecamatan')
            ->assertSee('Minggir')
            // Create Region
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->assertSet('isEdit', false)
            ->set('id_region', '34.04.07.2099')
            ->set('jenis_wilayah', 'kelurahan_desa')
            ->set('nama_lengkap', 'Desa Baru Test')
            ->set('parent_id', '34.04.07')
            ->set('is_koordinator', false)
            ->call('save')
            ->assertSet('showModal', false)
            ->assertSee('berhasil ditambahkan');

        $this->assertDatabaseHas('region', [
            'id_region' => '34.04.07.2099',
            'nama_lengkap' => 'Desa Baru Test',
            'is_koordinator' => false,
        ]);

        // Edit Region
        Livewire::test(RegionManager::class)
            ->call('openEditModal', '34.04.07.2099')
            ->assertSet('showModal', true)
            ->assertSet('isEdit', true)
            ->assertSet('nama_lengkap', 'Desa Baru Test')
            ->set('nama_lengkap', 'Desa Baru Diperbarui')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertSee('berhasil diperbarui');

        $this->assertDatabaseHas('region', [
            'id_region' => '34.04.07.2099',
            'nama_lengkap' => 'Desa Baru Diperbarui',
        ]);

        // Toggle Koordinator
        Livewire::test(RegionManager::class)
            ->call('toggleKoordinator', '34.04.07.2099')
            ->assertSee('Status koordinator wilayah');

        $this->assertTrue((bool) Region::find('34.04.07.2099')->is_koordinator);

        // Delete protection for parent
        Livewire::test(RegionManager::class)
            ->call('delete', '34.04.07')
            ->assertSee('Tidak dapat menghapus wilayah yang masih memiliki sub-wilayah');
        $this->assertDatabaseHas('region', ['id_region' => '34.04.07']);

        // Delete leaf region
        Livewire::test(RegionManager::class)
            ->call('delete', '34.04.07.2099')
            ->assertSee('berhasil dihapus');
        $this->assertDatabaseMissing('region', ['id_region' => '34.04.07.2099']);
    }

    public function test_bumdes_manager_crud_and_toggle_status(): void
    {
        $this->seed();
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        // Mount & verify existing BUMDes
        Livewire::test(BumdesManager::class)
            ->assertStatus(200)
            ->assertSee('BMD-SDS-001')
            ->assertSee('BMD-SDR-001')
            // Search
            ->set('search', 'BMD-SDS-001')
            ->assertSee('Sendangsari')
            ->assertDontSee('BMD-SDR-001')
            // Create BUMDes
            ->set('search', '')
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->assertSet('isEdit', false)
            ->set('id_bumdes', 'BMD-TST-001')
            ->set('id_kelurahan', '34.04.07.2005')
            ->set('nama_bumdes', 'BUMDes Percobaan Baru')
            ->set('tanggal_berdiri', '2026-09-01')
            ->set('status_aktif', true)
            ->call('save')
            ->assertSet('showModal', false)
            ->assertSee('berhasil ditambahkan');

        $this->assertDatabaseHas('bumdes', [
            'id_bumdes' => 'BMD-TST-001',
            'nama_bumdes' => 'BUMDes Percobaan Baru',
            'status_aktif' => true,
        ]);

        // Edit BUMDes
        Livewire::test(BumdesManager::class)
            ->call('openEditModal', 'BMD-TST-001')
            ->assertSet('showModal', true)
            ->assertSet('isEdit', true)
            ->assertSet('nama_bumdes', 'BUMDes Percobaan Baru')
            ->set('nama_bumdes', 'BUMDes Percobaan Diperbarui')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertSee('berhasil diperbarui');

        $this->assertDatabaseHas('bumdes', [
            'id_bumdes' => 'BMD-TST-001',
            'nama_bumdes' => 'BUMDes Percobaan Diperbarui',
        ]);

        // Toggle Status Aktif (FR-08)
        Livewire::test(BumdesManager::class)
            ->call('toggleStatus', 'BMD-TST-001')
            ->assertSee('berhasil dinonaktifkan');

        $this->assertFalse((bool) Bumdes::find('BMD-TST-001')->status_aktif);

        Livewire::test(BumdesManager::class)
            ->call('toggleStatus', 'BMD-TST-001')
            ->assertSee('berhasil diaktifkan');

        $this->assertTrue((bool) Bumdes::find('BMD-TST-001')->status_aktif);
    }
}
