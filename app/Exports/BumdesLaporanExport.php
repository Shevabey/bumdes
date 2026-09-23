<?php

namespace App\Exports;

use App\Exports\Sheets\BumdesRingkasanSheet;
use App\Exports\Sheets\BumdesTransaksiSheet;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BumdesLaporanExport implements WithMultipleSheets
{
    public function __construct(
        public string $idBumdes,
        public ?string $periode = null,
        public Carbon|string|null $dariTanggal = null,
    ) {}

    /**
     * @return array<int, mixed>
     */
    public function sheets(): array
    {
        return [
            new BumdesRingkasanSheet($this->idBumdes, $this->periode, $this->dariTanggal),
            new BumdesTransaksiSheet($this->idBumdes, $this->periode, $this->dariTanggal),
        ];
    }
}
