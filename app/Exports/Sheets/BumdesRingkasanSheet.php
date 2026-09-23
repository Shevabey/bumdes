<?php

namespace App\Exports\Sheets;

use App\Models\UnitUsaha;
use App\Services\ReportService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BumdesRingkasanSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(
        public string $idBumdes,
        public ?string $periode = null,
        public Carbon|string|null $dariTanggal = null,
    ) {}

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $reportService = new ReportService;
        $units = UnitUsaha::query()
            ->where('id_bumdes', $this->idBumdes)
            ->orderBy('id_unit')
            ->get();

        $rows = [];
        foreach ($units as $unit) {
            $summary = $reportService->untungRugiUnit($unit->id_unit, $this->periode, $this->dariTanggal);
            $rows[] = [
                $unit->id_unit,
                $unit->nama_unit,
                strtoupper(str_replace('_', ' ', $unit->jenis_unit)),
                $summary['total_input'],
                $summary['total_output'],
                $summary['untung_rugi'],
            ];
        }

        $bumdesSummary = $reportService->untungRugiBumdes($this->idBumdes, $this->periode, $this->dariTanggal);
        $rows[] = [
            'TOTAL BUMDES',
            '-',
            '-',
            $bumdesSummary['total_input'],
            $bumdesSummary['total_output'],
            $bumdesSummary['untung_rugi'],
        ];

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'ID Unit',
            'Nama Unit',
            'Jenis Usaha',
            'Total Input (Rp)',
            'Total Output (Rp)',
            'Untung / Rugi (Rp)',
        ];
    }

    public function title(): string
    {
        return 'Ringkasan Untung-Rugi';
    }
}
