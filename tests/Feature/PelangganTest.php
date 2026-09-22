<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelanggan_seeder_creates_one_customer_per_unit_and_portal_relation(): void
    {
        $this->seed();

        $customer = Pelanggan::where('id_akun', 'AKN-000010')->first();
        $portalAccount = Akun::find('AKN-000010');

        $this->assertCount(10, Pelanggan::all());
        $this->assertNotNull($customer);
        $this->assertSame('pengguna.sds', $customer->akun->username);
        $this->assertSame($customer->id_pelanggan, $portalAccount->pelanggan->id_pelanggan);
        $this->assertSame('PAMDes SDS', $customer->unit->nama_unit);
        $this->assertTrue($customer->status_aktif);
    }
}
