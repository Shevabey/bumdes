<?php

namespace Tests\Feature;

use App\Models\Bumdes;
use App\Models\IuranBumdes;
use App\Services\IuranService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IuranServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_generates_iuran_for_active_bumdes_only_and_is_idempotent(): void
    {
        $this->seed();
        Bumdes::where('id_bumdes', 'BMD-SDR-001')->update(['status_aktif' => false]);
        $service = new IuranService;
        $periode = Carbon::create(2026, 10, 1);

        $this->assertSame(1, $service->generateBulanan($periode));
        $this->assertSame(0, $service->generateBulanan($periode));
        $this->assertDatabaseHas('iuran_bumdes', [
            'id_iuran' => 'IUR-BMD-SDS-001-2026-10',
            'status' => 'belum_bayar',
            'jumlah' => 50000,
        ]);
        $this->assertDatabaseMissing('iuran_bumdes', [
            'id_iuran' => 'IUR-BMD-SDR-001-2026-10',
        ]);
    }

    public function test_command_generates_monthly_iuran_and_can_be_replayed(): void
    {
        $this->seed();

        $this->artisan('iuran:generate-bulanan', ['--bulan' => '2026-11'])
            ->expectsOutput('2 iuran bulanan dibuat untuk periode 2026-11.')
            ->assertExitCode(0);
        $this->artisan('iuran:generate-bulanan', ['--bulan' => '2026-11'])
            ->expectsOutput('0 iuran bulanan dibuat untuk periode 2026-11.')
            ->assertExitCode(0);

        $this->assertSame(2, IuranBumdes::where('bulan_tahun', '2026-11')->count());
    }
}
