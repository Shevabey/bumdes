<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Pelanggan;
use App\Services\AkunService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Tests\TestCase;

class AkunServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_pengguna_account_for_any_registered_pelanggan(): void
    {
        $this->seed();
        $service = new AkunService;
        $creator = Akun::where('username', 'superadmin')->firstOrFail();
        $pelanggan = Pelanggan::where('id_unit', 'UNT-BMD-SDR-001-PAM-01')->firstOrFail();

        $akun = $service->create($creator, [
            'nama' => 'Portal Sendangrejo',
            'username' => 'pengguna.sdr.baru',
            'password' => 'password-baru',
            'role' => 'pengguna',
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);

        $this->assertSame('AKN-000011', $akun->id_akun);
        $this->assertSame('BMD-SDR-001', $akun->id_bumdes);
        $this->assertSame('UNT-BMD-SDR-001-PAM-01', $akun->id_unit);
        $this->assertTrue($akun->hasRole('pengguna'));
        $this->assertSame($akun->id_akun, $pelanggan->refresh()->id_akun);
        $this->assertTrue(Auth::attempt(['username' => 'pengguna.sdr.baru', 'password' => 'password-baru']));
    }

    public function test_admin_bumdes_can_create_internal_account_only_for_own_bumdes(): void
    {
        $this->seed();
        $service = new AkunService;
        $creator = Akun::where('username', 'admin.sendangsari')->firstOrFail();

        $akun = $service->create($creator, [
            'nama' => 'Admin Sampah Sendangsari',
            'username' => 'admin.sampah.sds.baru',
            'password' => 'password-baru',
            'role' => 'admin_unit',
            'id_bumdes' => 'BMD-SDS-001',
            'id_unit' => 'UNT-BMD-SDS-001-SMH-01',
        ]);

        $this->assertSame('admin_unit', $akun->role);
        $this->assertSame('BMD-SDS-001', $akun->id_bumdes);
        $this->assertSame('UNT-BMD-SDS-001-SMH-01', $akun->id_unit);
        $this->assertTrue($akun->hasRole('admin_unit'));

        $this->expectException(AuthorizationException::class);

        $service->create($creator, [
            'nama' => 'Sekretaris Sendangrejo',
            'username' => 'sekretaris.sdr.ditolak',
            'password' => 'password-baru',
            'role' => 'sekretaris',
            'id_bumdes' => 'BMD-SDR-001',
        ]);
    }

    public function test_admin_bumdes_cannot_create_pengguna_account(): void
    {
        $this->seed();
        $service = new AkunService;
        $creator = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $pelanggan = Pelanggan::where('id_unit', 'UNT-BMD-SDS-001-PTN-01')->firstOrFail();

        $this->expectException(AuthorizationException::class);

        $service->create($creator, [
            'nama' => 'Portal Peternakan SDS',
            'username' => 'pengguna.peternakan.sds',
            'password' => 'password-baru',
            'role' => 'pengguna',
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);
    }

    public function test_sekretaris_and_admin_unit_can_only_create_pengguna_in_owned_scope(): void
    {
        $this->seed();
        $service = new AkunService;
        $sekretaris = Akun::where('username', 'sekretaris.sds')->firstOrFail();
        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $pelangganSds = Pelanggan::where('id_unit', 'UNT-BMD-SDS-001-PTN-01')->firstOrFail();
        $pelangganPamdesSds = Pelanggan::create([
            'id_pelanggan' => 'PLG-UNT-BMD-SDS-001-PAM-01-0002',
            'id_unit' => 'UNT-BMD-SDS-001-PAM-01',
            'nama' => 'Pelanggan PAMDes SDS Baru',
            'kontak' => '081233330000',
            'status_aktif' => true,
        ]);

        $akun = $service->create($sekretaris, [
            'nama' => 'Portal Peternakan SDS',
            'username' => 'pengguna.peternakan.sds',
            'password' => 'password-baru',
            'role' => 'pengguna',
            'id_pelanggan' => $pelangganSds->id_pelanggan,
        ]);

        $this->assertSame('BMD-SDS-001', $akun->id_bumdes);
        $this->assertSame($akun->id_akun, $pelangganSds->refresh()->id_akun);

        $akunAdminUnit = $service->create($adminUnit, [
            'nama' => 'Portal PAMDes SDS Baru',
            'username' => 'pengguna.pamdes.sds.baru',
            'password' => 'password-baru',
            'role' => 'pengguna',
            'id_pelanggan' => $pelangganPamdesSds->id_pelanggan,
        ]);

        $this->assertSame('UNT-BMD-SDS-001-PAM-01', $akunAdminUnit->id_unit);
        $this->assertSame($akunAdminUnit->id_akun, $pelangganPamdesSds->refresh()->id_akun);

        $this->expectException(AuthorizationException::class);

        $service->create($adminUnit, [
            'nama' => 'Portal Peternakan SDS Ditolak',
            'username' => 'pengguna.peternakan.sds.ditolak',
            'password' => 'password-baru',
            'role' => 'pengguna',
            'id_pelanggan' => $pelangganSds->id_pelanggan,
        ]);
    }

    public function test_pengguna_account_requires_unlinked_pelanggan(): void
    {
        $this->seed();
        $service = new AkunService;
        $creator = Akun::where('username', 'superadmin')->firstOrFail();
        $linkedPelanggan = Pelanggan::where('id_unit', 'UNT-BMD-SDS-001-PAM-01')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);

        $service->create($creator, [
            'nama' => 'Portal Duplikat',
            'username' => 'pengguna.duplikat',
            'password' => 'password-baru',
            'role' => 'pengguna',
            'id_pelanggan' => $linkedPelanggan->id_pelanggan,
        ]);
    }
}
