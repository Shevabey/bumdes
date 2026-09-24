<?php

namespace App\Http\Livewire\Monitoring;

use App\Http\Livewire\Shared\DataTable;
use App\Models\Bumdes;
use App\Models\IuranBumdes;
use App\Models\KasBumdes;
use App\Models\Tagihan;
use App\Models\Transaksi;
use App\Models\UnitUsaha;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;

class DashboardNasional extends DataTable
{
    public string $sortField = 'nama_bumdes';

    public string $sortDirection = 'asc';

    /**
     * @var array{status_iuran: string, periode: string}
     */
    public array $filters = [
        'status_iuran' => '',
        'periode' => 'bulanan',
    ];

    // Unit detail modal
    public bool $showUnitModal = false;

    public ?string $selectedBumdesId = null;

    public string $selectedBumdesName = '';

    /**
     * @var array<int, array{id_unit: string, nama_unit: string, total_input: float, total_output: float, untung_rugi: float}>
     */
    public array $selectedBumdesUnits = [];

    /** Base query for active BUMDes with optional search & iuran status filter. */
    public function query(): Builder
    {
        $q = Bumdes::query()
            ->with(['kelurahan', 'kas'])
            ->withCount(['units' => fn (Builder $uq) => $uq->where('status_aktif', true)])
            ->where('status_aktif', true);

        if ($this->search !== '') {
            $search = $this->search;
            $q->where(function (Builder $inner) use ($search): void {
                $inner->where('nama_bumdes', 'like', "%{$search}%")
                    ->orWhere('id_bumdes', 'like', "%{$search}%")
                    ->orWhereHas('kelurahan', fn (Builder $kq) => $kq->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        if ($this->filters['status_iuran'] !== '') {
            $bulanIni = now()->format('Y-m');
            $statusIuran = $this->filters['status_iuran'];
            $q->whereHas('iuran', fn (Builder $iq) => $iq
                ->where('bulan_tahun', $bulanIni)
                ->where('status', $statusIuran)
            );
        }

        return $q;
    }

    /** 4 indikator otomatis ringkasan nasional. */
    #[Computed]
    public function indikatorNasional(): array
    {
        $bulanIni = now()->format('Y-m');

        $totalBumdesAktif = Bumdes::query()->where('status_aktif', true)->count();

        $bumdesMenunggak = IuranBumdes::query()
            ->where('bulan_tahun', $bulanIni)
            ->where('status', '!=', 'lunas')
            ->distinct('id_bumdes')
            ->count('id_bumdes');

        // Unit usaha aktif tanpa transaksi dalam 7 hari terakhir
        $unitTanpaAktivitas = UnitUsaha::query()
            ->where('status_aktif', true)
            ->whereDoesntHave('transaksi', fn (Builder $tq) => $tq
                ->where('tanggal', '>=', now()->subDays(7)->toDateString())
            )
            ->count();

        $iuranTertunda = IuranBumdes::query()->where('status', 'menunggu_verifikasi')->count();
        $tagihanTertunda = Tagihan::query()->where('status', 'menunggu_verifikasi')->count();

        return [
            'total_bumdes_aktif' => $totalBumdesAktif,
            'bumdes_menunggak_iuran' => $bumdesMenunggak,
            'unit_tanpa_aktivitas' => $unitTanpaAktivitas,
            'verifikasi_tertunda' => $iuranTertunda + $tagihanTertunda,
        ];
    }

    /** Agregasi keuangan konsolidasian seluruh BUMDes. */
    #[Computed]
    public function keuanganNasional(): array
    {
        /** @var ReportService $svc */
        $svc = app(ReportService::class);
        $periode = $this->filters['periode'] !== 'semua' ? $this->filters['periode'] : null;
        $nasional = $svc->untungRugiNasional($periode);

        $kasNasional = (float) KasBumdes::query()->sum('saldo');

        return [
            'kas_nasional' => $kasNasional,
            'total_input' => $nasional['total_input'],
            'total_output' => $nasional['total_output'],
            'untung_rugi' => $nasional['untung_rugi'],
        ];
    }

    /** Tampilkan modal rincian unit usaha per BUMDes. */
    public function showUnitDetail(string $idBumdes, string $namaBumdes): void
    {
        /** @var ReportService $svc */
        $svc = app(ReportService::class);
        $periode = $this->filters['periode'] !== 'semua' ? $this->filters['periode'] : null;

        $this->selectedBumdesId = $idBumdes;
        $this->selectedBumdesName = $namaBumdes;
        $this->selectedBumdesUnits = $svc->untungRugiBumdesWithUnits($idBumdes, $periode);
        $this->showUnitModal = true;
    }

    public function closeUnitModal(): void
    {
        $this->showUnitModal = false;
        $this->selectedBumdesId = null;
        $this->selectedBumdesName = '';
        $this->selectedBumdesUnits = [];
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filters = ['status_iuran' => '', 'periode' => 'bulanan'];
        $this->resetPage();
    }

    public function render(): View
    {
        /** @var ReportService $svc */
        $svc = app(ReportService::class);
        $periode = $this->filters['periode'] !== 'semua' ? $this->filters['periode'] : null;
        $bulanIni = now()->format('Y-m');

        $bumdesPaginator = $this->query()->paginate($this->perPage);
        $bumdesPaginator->through(function (Bumdes $bumdes) use ($svc, $periode, $bulanIni): array {
            $laporan = $svc->untungRugiBumdes($bumdes->id_bumdes, $periode);
            $iuranTerkini = IuranBumdes::query()
                ->where('id_bumdes', $bumdes->id_bumdes)
                ->where('bulan_tahun', $bulanIni)
                ->first();

            return [
                'id_bumdes' => $bumdes->id_bumdes,
                'nama_bumdes' => $bumdes->nama_bumdes,
                'nama_kelurahan' => $bumdes->kelurahan?->nama_lengkap ?? '—',
                'kas' => (float) ($bumdes->kas?->saldo ?? 0),
                'total_input' => $laporan['total_input'],
                'total_output' => $laporan['total_output'],
                'untung_rugi' => $laporan['untung_rugi'],
                'status_iuran' => $iuranTerkini?->status ?? 'belum_ada',
                'units_count' => $bumdes->units_count,
            ];
        });

        return view('livewire.monitoring.dashboard-nasional', [
            'bumdesRows' => $bumdesPaginator,
        ])->layout('layouts.panel', [
            'title' => 'Dashboard Monitoring Nasional',
            'header' => 'Dashboard Monitoring Nasional',
        ]);
    }
}
