<?php

namespace App\Services;

use App\Models\Transaksi;
use App\Models\UnitUsaha;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    /**
     * @return array{total_input: float, total_output: float, untung_rugi: float}
     */
    public function untungRugiUnit(string $idUnit, ?string $periode = null): array
    {
        $query = Transaksi::query()->where('id_unit', $idUnit);
        $this->applyPeriode($query, $periode);

        return $this->summarize($query);
    }

    /**
     * @return array{total_input: float, total_output: float, untung_rugi: float}
     */
    public function untungRugiBumdes(string $idBumdes, ?string $periode = null): array
    {
        $unitIds = UnitUsaha::query()
            ->where('id_bumdes', $idBumdes)
            ->pluck('id_unit');
        $query = Transaksi::query()->whereIn('id_unit', $unitIds);
        $this->applyPeriode($query, $periode);

        return $this->summarize($query);
    }

    private function applyPeriode(Builder $query, ?string $periode): void
    {
        if ($periode === null) {
            return;
        }

        $today = Carbon::today();
        $range = match ($periode) {
            'mingguan' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'bulanan' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            default => throw new \InvalidArgumentException('Periode harus berupa mingguan atau bulanan.'),
        };

        $query->whereBetween('tanggal', [$range[0]->toDateString(), $range[1]->toDateString()]);
    }

    /**
     * @return array{total_input: float, total_output: float, untung_rugi: float}
     */
    private function summarize(Builder $query): array
    {
        $totalInput = (float) $query->clone()->where('tipe', 'input')->sum('jumlah');
        $totalOutput = (float) $query->clone()->where('tipe', 'output')->sum('jumlah');

        return [
            'total_input' => $totalInput,
            'total_output' => $totalOutput,
            'untung_rugi' => $totalInput - $totalOutput,
        ];
    }
}
