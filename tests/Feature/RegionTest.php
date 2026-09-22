<?php

namespace Tests\Feature;

use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionTest extends TestCase
{
    use RefreshDatabase;

    public function test_region_seeder_builds_minggir_hierarchy(): void
    {
        $this->seed();

        $sendangsari = Region::find('34.04.07.2005');

        $this->assertNotNull($sendangsari);
        $this->assertTrue($sendangsari->is_koordinator);
        $this->assertSame('Minggir', $sendangsari->parent->nama_lengkap);
        $this->assertSame(2, $sendangsari->parent->children()->count());
        $this->assertSame(5, Region::count());
    }
}
