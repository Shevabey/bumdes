<?php

namespace Tests\Feature;

use App\Exports\BumdesLaporanExport;
use App\Exports\Sheets\BumdesRingkasanSheet;
use App\Exports\Sheets\BumdesTransaksiSheet;
use App\Exports\UnitTransaksiExport;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_transaksi_export_structure_and_headings(): void
    {
        $this->seed();

        $export = new UnitTransaksiExport('UNT-BMD-SDS-001-PAM-01', 'bulanan', Carbon::create(2026, 9, 22));

        $this->assertSame('Unit UNT-BMD-SDS-001-PAM-01', $export->title());
        $this->assertSame([
            'ID Transaksi',
            'Tanggal',
            'Tipe',
            'Jumlah',
            'Keterangan',
            'Dicatat Oleh',
            'Detail',
        ], $export->headings());

        $transactions = $export->query()->get();
        $this->assertCount(2, $transactions);

        /** @var Transaksi $firstTrx */
        $firstTrx = $transactions->first();
        $mapped = $export->map($firstTrx);

        $this->assertSame($firstTrx->id_transaksi, $mapped[0]);
        $this->assertSame('2026-09-22', $mapped[1]);
        $this->assertSame('INPUT', $mapped[2]);
        $this->assertSame(100000.0, $mapped[3]);
        $this->assertSame('data seed development', $mapped[4]);
        $this->assertNotEmpty($mapped[5]);
        $this->assertStringContainsString('pamdes', $mapped[6]);
    }

    public function test_unit_transaksi_export_filters_by_period_and_date(): void
    {
        $this->seed();

        $weeklyExport = new UnitTransaksiExport('UNT-BMD-SDS-001-PAM-01', 'mingguan', Carbon::create(2026, 9, 22));
        $this->assertCount(2, $weeklyExport->query()->get());

        $emptyExport = new UnitTransaksiExport('UNT-BMD-SDS-001-PAM-01', 'bulanan', Carbon::create(2025, 1, 1));
        $this->assertCount(0, $emptyExport->query()->get());
    }

    public function test_bumdes_laporan_export_multi_sheet_and_content(): void
    {
        $this->seed();

        $export = new BumdesLaporanExport('BMD-SDS-001', 'bulanan', Carbon::create(2026, 9, 22));
        $sheets = $export->sheets();

        $this->assertCount(2, $sheets);
        $this->assertInstanceOf(BumdesRingkasanSheet::class, $sheets[0]);
        $this->assertInstanceOf(BumdesTransaksiSheet::class, $sheets[1]);

        // Sheet 1: Ringkasan
        /** @var BumdesRingkasanSheet $ringkasanSheet */
        $ringkasanSheet = $sheets[0];
        $this->assertSame('Ringkasan Untung-Rugi', $ringkasanSheet->title());
        $this->assertSame([
            'ID Unit',
            'Nama Unit',
            'Jenis Usaha',
            'Total Input (Rp)',
            'Total Output (Rp)',
            'Untung / Rugi (Rp)',
        ], $ringkasanSheet->headings());

        $rows = $ringkasanSheet->array();
        // 5 units in Sendangsari + 1 total row = 6 rows
        $this->assertCount(6, $rows);

        $totalRow = end($rows);
        $this->assertSame('TOTAL BUMDES', $totalRow[0]);
        $this->assertSame(500000.0, $totalRow[3]);
        $this->assertSame(200000.0, $totalRow[4]);
        $this->assertSame(300000.0, $totalRow[5]);

        // Sheet 2: Detail Transaksi
        /** @var BumdesTransaksiSheet $transaksiSheet */
        $transaksiSheet = $sheets[1];
        $this->assertSame('Detail Transaksi', $transaksiSheet->title());
        $this->assertSame([
            'ID Transaksi',
            'ID Unit',
            'Nama Unit',
            'Tanggal',
            'Tipe',
            'Jumlah',
            'Keterangan',
            'Dicatat Oleh',
            'Detail',
        ], $transaksiSheet->headings());

        $bumdesTrx = $transaksiSheet->query()->get();
        // 5 units * 2 transactions = 10 transactions
        $this->assertCount(10, $bumdesTrx);

        $mapped = $transaksiSheet->map($bumdesTrx->first());
        $this->assertSame('UNT-BMD-SDS-001-MTN-01', $mapped[1]);
        $this->assertSame(100000.0, $mapped[5]);
    }

    public function test_excel_download_can_be_faked(): void
    {
        Excel::fake();

        $unitExport = new UnitTransaksiExport('UNT-BMD-SDS-001-PAM-01', 'mingguan');
        Excel::download($unitExport, 'unit-report.xlsx');
        Excel::assertDownloaded('unit-report.xlsx');

        $bumdesExport = new BumdesLaporanExport('BMD-SDS-001', 'bulanan');
        Excel::download($bumdesExport, 'bumdes-report.xlsx');
        Excel::assertDownloaded('bumdes-report.xlsx');
    }

    public function test_export_rejects_invalid_period(): void
    {
        $export = new UnitTransaksiExport('UNT-BMD-SDS-001-PAM-01', 'harian');

        $this->expectException(InvalidArgumentException::class);
        $export->query();
    }
}
