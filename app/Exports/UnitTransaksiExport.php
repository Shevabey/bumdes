<?php

namespace App\Exports;

use App\Models\Transaksi;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class UnitTransaksiExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        public string $idUnit,
        public ?string $periode = null,
        public Carbon|string|null $dariTanggal = null,
    ) {}

    public function query(): Builder
    {
        $query = Transaksi::query()
            ->with(['unit', 'pencatat'])
            ->where('id_unit', $this->idUnit)
            ->orderBy('tanggal', 'asc')
            ->orderBy('id_transaksi', 'asc');

        (new ReportService)->applyPeriode($query, $this->periode, $this->dariTanggal);

        return $query;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Tanggal',
            'Tipe',
            'Jumlah',
            'Keterangan',
            'Dicatat Oleh',
            'Detail',
        ];
    }

    /**
     * @param  Transaksi  $row
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        $keterangan = $row->detail['keterangan']
            ?? $row->detail['deskripsi']
            ?? $row->detail['sumber']
            ?? '-';

        return [
            $row->id_transaksi,
            $row->tanggal instanceof Carbon ? $row->tanggal->format('Y-m-d') : (string) $row->tanggal,
            strtoupper($row->tipe),
            (float) $row->jumlah,
            $keterangan,
            $row->pencatat?->nama ?? $row->dicatat_oleh,
            ! empty($row->detail) ? json_encode($row->detail, JSON_UNESCAPED_UNICODE) : '-',
        ];
    }

    public function title(): string
    {
        return substr('Unit '.$this->idUnit, 0, 31);
    }
}
