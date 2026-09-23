<?php

namespace Tests\Feature;

use App\Models\Akun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bumdes_can_only_view_owned_bumdes_and_units(): void
    {
        $this->seed();
        $admin = Akun::where('username', 'admin.sendangsari')->firstOrFail();

        $this->actingAs($admin)
            ->get('/panel/bumdes/BMD-SDS-001')
            ->assertOk();
        $this->actingAs($admin)
            ->get('/panel/bumdes/BMD-SDR-001')
            ->assertForbidden();
        $this->actingAs($admin)
            ->get('/panel/unit/UNT-BMD-SDS-001-PAM-01')
            ->assertOk();
        $this->actingAs($admin)
            ->get('/panel/unit/UNT-BMD-SDR-001-PAM-01')
            ->assertForbidden();
    }

    public function test_super_admin_can_view_any_bumdes_and_monitoring_roles_are_scoped(): void
    {
        $this->seed();
        $superAdmin = Akun::where('username', 'superadmin')->firstOrFail();
        $pengawas = Akun::where('username', 'pengawas1')->firstOrFail();
        $admin = Akun::where('username', 'admin.sendangsari')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/panel/bumdes/BMD-SDR-001')
            ->assertOk();
        $this->actingAs($pengawas)->get('/monitoring')->assertOk();
        $this->actingAs($admin)->get('/monitoring')->assertForbidden();
    }
}
