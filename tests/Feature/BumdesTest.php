<?php

namespace Tests\Feature;

use App\Models\Bumdes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BumdesTest extends TestCase
{
    use RefreshDatabase;

    public function test_bumdes_seeder_creates_phase_one_bumdes_with_region_relations(): void
    {
        $this->seed();

        $sendangsari = Bumdes::find('BMD-SDS-001');
        $sendangrejo = Bumdes::find('BMD-SDR-001');

        $this->assertNotNull($sendangsari);
        $this->assertNotNull($sendangrejo);
        $this->assertTrue($sendangsari->status_aktif);
        $this->assertSame('Sendangsari', $sendangsari->kelurahan->nama_lengkap);
        $this->assertSame('Sendangrejo', $sendangrejo->kelurahan->nama_lengkap);
        $this->assertSame(2, Bumdes::count());
    }
}
