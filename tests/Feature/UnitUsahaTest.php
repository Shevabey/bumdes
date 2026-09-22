<?php

namespace Tests\Feature;

use App\Models\Bumdes;
use App\Models\UnitUsaha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitUsahaTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_usaha_seeder_creates_five_standard_units_per_bumdes(): void
    {
        $this->seed();

        $sendangsari = Bumdes::find('BMD-SDS-001');
        $units = UnitUsaha::where('id_bumdes', 'BMD-SDS-001')->orderBy('jenis_unit')->get();

        $this->assertNotNull($sendangsari);
        $this->assertCount(5, $units);
        $this->assertSame(10, UnitUsaha::count());
        $this->assertSame(5, $sendangsari->units()->count());
        $this->assertSame([], $units->first()->skema_field);
        $this->assertTrue($units->every(fn(UnitUsaha $unit): bool => $unit->status_aktif));
    }
}
