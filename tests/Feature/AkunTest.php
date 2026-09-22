<?php

namespace Tests\Feature;

use App\Models\Akun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AkunTest extends TestCase
{
    use RefreshDatabase;

    public function test_akun_seeder_creates_roles_and_supports_username_authentication(): void
    {
        $this->seed();

        $admin = Akun::where('username', 'admin.pamdes.sds')->first();

        $this->assertNotNull($admin);
        $this->assertSame('password_hash', $admin->getAuthPasswordName());
        $this->assertTrue($admin->hasRole('admin_unit'));
        $this->assertSame('PAMDes SDS', $admin->unit->nama_unit);
        $this->assertSame(9, Role::count());
        $this->assertTrue(Auth::attempt(['username' => 'superadmin', 'password' => 'password']));
        $this->assertSame('AKN-000001', Auth::user()->getAuthIdentifier());
    }
}
