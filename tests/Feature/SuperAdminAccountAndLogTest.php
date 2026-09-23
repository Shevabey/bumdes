<?php

namespace Tests\Feature;

use App\Http\Livewire\SuperAdmin\AkunManager;
use App\Http\Livewire\SuperAdmin\LogAktivitasViewer;
use App\Models\Akun;
use App\Models\Bumdes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class SuperAdminAccountAndLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_account_and_log_routes_authorization(): void
    {
        $this->seed();

        // 1. Guest redirected to login
        $this->get(route('super-admin.akun'))->assertRedirect(route('login'));
        $this->get(route('super-admin.log'))->assertRedirect(route('login'));

        // 2. Non-super admin forbidden (403)
        $adminBumdes = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminBumdes);
        $this->get(route('super-admin.akun'))->assertForbidden();
        $this->get(route('super-admin.log'))->assertForbidden();

        // 3. Super admin authorized (200)
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);
        $this->get(route('super-admin.akun'))->assertOk();
        $this->get(route('super-admin.log'))->assertOk();
    }

    public function test_akun_manager_mount_and_search_filter(): void
    {
        $this->seed();
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(AkunManager::class)
            ->assertStatus(200)
            ->assertSee('superadmin')
            ->assertSee('admin.sendangsari')
            // Search username
            ->set('search', 'bendahara.sds')
            ->assertSee('bendahara.sds')
            ->assertDontSee('admin.sendangsari')
            // Reset search & filter role
            ->set('search', '')
            ->set('filters.role', 'super_admin')
            ->assertSee('superadmin')
            ->assertDontSee('bendahara.sds')
            // Filter status
            ->set('filters.role', '')
            ->set('filters.status_aktif', '1')
            ->assertSee('superadmin');
    }

    public function test_akun_manager_create_account(): void
    {
        $this->seed();
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);
        $bumdes = Bumdes::firstOrFail();

        Livewire::test(AkunManager::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->assertSet('isEdit', false)
            ->set('nama', 'Staf Sekretariat Baru')
            ->set('username', 'sekretaris.baru')
            ->set('password', 'secret123')
            ->set('role', 'sekretaris')
            ->set('id_bumdes', $bumdes->id_bumdes)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false)
            ->assertSee('sekretaris.baru');

        $this->assertDatabaseHas('akun', [
            'username' => 'sekretaris.baru',
            'nama' => 'Staf Sekretariat Baru',
            'id_bumdes' => $bumdes->id_bumdes,
        ]);

        $created = Akun::where('username', 'sekretaris.baru')->firstOrFail();
        $this->assertTrue($created->hasRole('sekretaris'));
        $this->assertTrue(Hash::check('secret123', $created->password_hash));
    }

    public function test_akun_manager_validation_errors(): void
    {
        $this->seed();
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(AkunManager::class)
            ->call('openCreateModal')
            ->set('nama', '')
            ->set('username', 'superadmin') // duplicate username
            ->set('password', '123') // min 6
            ->call('save')
            ->assertHasErrors(['nama', 'username', 'password']);
    }

    public function test_akun_manager_edit_and_toggle_status(): void
    {
        $this->seed();
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $targetAkun = Akun::where('username', 'admin.sendangsari')->firstOrFail();

        // 1. Edit Profil
        Livewire::test(AkunManager::class)
            ->call('openEditModal', $targetAkun->id_akun)
            ->assertSet('isEdit', true)
            ->assertSet('nama', $targetAkun->nama)
            ->set('nama', 'Admin Sendangsari Diperbarui')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('akun', [
            'id_akun' => $targetAkun->id_akun,
            'nama' => 'Admin Sendangsari Diperbarui',
        ]);

        // 2. Toggle Status Nonaktif
        Livewire::test(AkunManager::class)
            ->call('toggleStatus', $targetAkun->id_akun)
            ->assertSee('dinonaktifkan');

        $this->assertFalse($targetAkun->fresh()->status_aktif);

        // 3. Toggle Status Aktif Kembali
        Livewire::test(AkunManager::class)
            ->call('toggleStatus', $targetAkun->id_akun)
            ->assertSee('diaktifkan');

        $this->assertTrue($targetAkun->fresh()->status_aktif);

        // 4. Cannot toggle own superadmin account
        Livewire::test(AkunManager::class)
            ->call('toggleStatus', $superadmin->id_akun)
            ->assertSee('Tidak dapat menonaktifkan akun sendiri');
    }

    public function test_akun_manager_reset_password(): void
    {
        $this->seed();
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $targetAkun = Akun::where('username', 'admin.sendangsari')->firstOrFail();

        Livewire::test(AkunManager::class)
            ->call('openResetPasswordModal', $targetAkun->id_akun)
            ->assertSet('showResetPasswordModal', true)
            ->set('new_password', 'passwordBaru2026')
            ->call('resetPassword')
            ->assertHasNoErrors()
            ->assertSet('showResetPasswordModal', false)
            ->assertSee('berhasil direset');

        $this->assertTrue(Hash::check('passwordBaru2026', $targetAkun->fresh()->password_hash));
    }

    public function test_log_aktivitas_viewer_mount_search_filter_and_detail(): void
    {
        $this->seed();
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        // Log some activity via Spatie
        activity('audit_test')
            ->causedBy($superadmin)
            ->event('created')
            ->withProperties(['ip' => '127.0.0.1', 'test_key' => 'nilai_rahasia'])
            ->log('Uji coba pencatatan aktivitas sistem');

        activity('keamanan')
            ->causedBy($superadmin)
            ->event('login')
            ->withProperties(['status' => 'berhasil'])
            ->log('Login berhasil superadmin');

        $testActivity = Activity::where('log_name', 'audit_test')->firstOrFail();

        Livewire::test(LogAktivitasViewer::class)
            ->assertStatus(200)
            ->assertSee('Log Audit Aktivitas Sistem')
            ->assertSee('Uji coba pencatatan aktivitas sistem')
            ->assertSee('audit_test')
            // Search
            ->set('search', 'nilai_rahasia') // or search description
            ->set('search', 'Uji coba pencatatan')
            ->assertSee('Uji coba pencatatan aktivitas sistem')
            // Filter by log_name
            ->set('search', '')
            ->set('filters.log_name', 'audit_test')
            ->assertSee('Uji coba pencatatan aktivitas sistem')
            ->assertDontSee('Login berhasil superadmin')
            // Filter by event
            ->set('filters.log_name', '')
            ->set('filters.event', 'login')
            ->assertSee('Login berhasil superadmin')
            ->assertDontSee('Uji coba pencatatan aktivitas sistem')
            // Detail modal
            ->call('showDetail', $testActivity->id)
            ->assertSet('showDetailModal', true)
            ->assertSee('nilai_rahasia')
            ->call('closeDetailModal')
            ->assertSet('showDetailModal', false);
    }
}
