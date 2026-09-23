<?php

namespace Tests\Feature;

use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_service_aggregates_unit_and_bumdes_profit(): void
    {
        $this->seed();
        $service = new ReportService;

        $unitReport = $service->untungRugiUnit('UNT-BMD-SDS-001-PAM-01');
        $bumdesReport = $service->untungRugiBumdes('BMD-SDS-001', 'bulanan');

        $this->assertSame(100000.0, $unitReport['total_input']);
        $this->assertSame(40000.0, $unitReport['total_output']);
        $this->assertSame(60000.0, $unitReport['untung_rugi']);
        $this->assertSame(500000.0, $bumdesReport['total_input']);
        $this->assertSame(200000.0, $bumdesReport['total_output']);
        $this->assertSame(300000.0, $bumdesReport['untung_rugi']);
    }

    public function test_report_service_supports_weekly_period_and_rejects_unknown_period(): void
    {
        $this->seed();
        $service = new ReportService;

        $weeklyReport = $service->untungRugiUnit('UNT-BMD-SDS-001-PAM-01', 'mingguan');

        $this->assertSame(60000.0, $weeklyReport['untung_rugi']);
        $this->expectException(InvalidArgumentException::class);
        $service->untungRugiUnit('UNT-BMD-SDS-001-PAM-01', 'harian');
    }
}
