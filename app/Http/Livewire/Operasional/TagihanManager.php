<?php

namespace App\Http\Livewire\Operasional;

use App\Http\Livewire\Shared\DataTable;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\UnitUsaha;
use App\Services\TagihanService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;

class TagihanManager extends DataTable
{
    public string $sortField = 'jatuh_tempo';

    public string $sortDirection = 'asc';

    /**
     * @var array{id_unit: string, status: string, jatuh_tempo_mulai: string, jatuh_tempo_akhir: string, id_bumdes: string}
     */
    public array $filters = [
        'id_unit' => '',
        'status' => '',
        'jatuh_tempo_mulai' => '',
        'jatuh_tempo_akhir' => '',
        'id_bumdes' => '',
    ];

    public bool $showModal = false;

    public bool $isEdit = false;

    public bool $showVerifikasiModal = false;

    public bool $showDetailModal = false;

    // Form fields
    public string $id_tagihan = '';

    public string $id_unit = '';

    public string $id_pelanggan = '';

    public string|float $jumlah = '';

    public string $jatuh_tempo = '';

    // Modal state
    public ?string $verifikasiTagihanId = null;

    public ?string $detailId = null;

    public function mount(): void
    {
        $this->jatuh_tempo = Carbon::today()->addDays(14)->toDateString();
    }

    public function query(): Builder
    {
        /** @var Akun $user */
        $user = auth()->user();

        $query = Tagihan::query()->with(['pelanggan', 'unit.bumdes', 'verifikator']);

        // Scope berjenjang
        if ($user->hasRole('admin_unit')) {
            $query->where('id_unit', $user->id_unit);
        } elseif ($user->hasAnyRole(['admin_bumdes', 'sekretaris', 'bendahara'])) {
            $query->whereHas('unit', function (Builder $q) use ($user) {
                $q->where('id_bumdes', $user->id_bumdes);
            });
        }

        // Search
        if ($this->search !== '') {
            $query->where(function (Builder $q) {
                $q->where('id_tagihan', 'like', '%'.$this->search.'%')
                    ->orWhereHas('pelanggan', function (Builder $qp) {
                        $qp->where('nama', 'like', '%'.$this->search.'%')
                            ->orWhere('id_pelanggan', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('unit', function (Builder $qu) {
                        $qu->where('nama_unit', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Filters
        if ($this->filters['id_unit'] !== '') {
            $query->where('id_unit', $this->filters['id_unit']);
        }

        if ($this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if ($this->filters['jatuh_tempo_mulai'] !== '') {
            $query->whereDate('jatuh_tempo', '>=', $this->filters['jatuh_tempo_mulai']);
        }

        if ($this->filters['jatuh_tempo_akhir'] !== '') {
            $query->whereDate('jatuh_tempo', '<=', $this->filters['jatuh_tempo_akhir']);
        }

        if ($this->filters['id_bumdes'] !== '' && $user->hasRole('super_admin')) {
            $query->whereHas('unit', function (Builder $q) {
                $q->where('id_bumdes', $this->filters['id_bumdes']);
            });
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    #[Computed]
    public function stats(): array
    {
        $baseQuery = $this->query();

        $countBelumBayar = (int) (clone $baseQuery)->where('status', 'belum_bayar')->count();
        $nominalBelumBayar = (float) (clone $baseQuery)->where('status', 'belum_bayar')->sum('jumlah');

        $countMenunggu = (int) (clone $baseQuery)->where('status', 'menunggu_verifikasi')->count();
        $nominalMenunggu = (float) (clone $baseQuery)->where('status', 'menunggu_verifikasi')->sum('jumlah');

        $countLunas = (int) (clone $baseQuery)->where('status', 'lunas')->count();
        $nominalLunas = (float) (clone $baseQuery)->where('status', 'lunas')->sum('jumlah');

        $totalNominal = (float) (clone $baseQuery)->sum('jumlah');

        return [
            'countBelumBayar' => $countBelumBayar,
            'nominalBelumBayar' => $nominalBelumBayar,
            'countMenunggu' => $countMenunggu,
            'nominalMenunggu' => $nominalMenunggu,
            'countLunas' => $countLunas,
            'nominalLunas' => $nominalLunas,
            'totalNominal' => $totalNominal,
        ];
    }

    public function updatedIdUnit(): void
    {
        // Reset id_pelanggan saat unit berubah
        $this->id_pelanggan = '';
    }

    public function openCreateModal(): void
    {
        Gate::authorize('create', Tagihan::class);

        $this->resetValidation();
        $this->reset(['id_tagihan', 'id_pelanggan', 'jumlah']);
        $this->jatuh_tempo = Carbon::today()->addDays(14)->toDateString();
        $this->isEdit = false;

        /** @var Akun $user */
        $user = auth()->user();

        if ($user->hasRole('admin_unit')) {
            $this->id_unit = $user->id_unit ?? '';
        } elseif ($this->filters['id_unit'] !== '') {
            $this->id_unit = $this->filters['id_unit'];
        } else {
            $this->id_unit = '';
        }

        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $tagihan = Tagihan::findOrFail($id);

        Gate::authorize('update', $tagihan);

        $this->resetValidation();
        $this->id_tagihan = $tagihan->id_tagihan;
        $this->id_unit = $tagihan->id_unit;
        $this->id_pelanggan = $tagihan->id_pelanggan;
        $this->jumlah = (float) $tagihan->jumlah;
        $this->jatuh_tempo = Carbon::parse($tagihan->jatuh_tempo)->toDateString();

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function openDetailModal(string $id): void
    {
        $tagihan = Tagihan::with(['pelanggan', 'unit.bumdes', 'verifikator'])->findOrFail($id);

        Gate::authorize('view', $tagihan);

        $this->detailId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailId = null;
    }

    public function openVerifikasiModal(string $id): void
    {
        $tagihan = Tagihan::with(['pelanggan', 'unit.bumdes'])->findOrFail($id);

        Gate::authorize('verifikasiTransfer', $tagihan);

        $this->verifikasiTagihanId = $id;
        $this->showVerifikasiModal = true;
    }

    public function closeVerifikasiModal(): void
    {
        $this->showVerifikasiModal = false;
        $this->verifikasiTagihanId = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function bayarTunai(string $id, TagihanService $service): void
    {
        $tagihan = Tagihan::findOrFail($id);

        /** @var Akun $user */
        $user = auth()->user();

        Gate::authorize('bayarTunai', $tagihan);

        $service->bayarTunai($tagihan, $user);

        session()->flash('success', "Tagihan {$tagihan->id_tagihan} berhasil dilunasi via tunai.");
    }

    public function prosesVerifikasiTransfer(bool $approve, TagihanService $service): void
    {
        if (! $this->verifikasiTagihanId) {
            return;
        }

        $tagihan = Tagihan::findOrFail($this->verifikasiTagihanId);

        /** @var Akun $user */
        $user = auth()->user();

        Gate::authorize('verifikasiTransfer', $tagihan);

        $service->verifikasiTransfer($tagihan, $user, $approve);

        $statusText = $approve ? 'disetujui (lunas)' : 'ditolak';
        session()->flash('success', "Bukti transfer tagihan {$tagihan->id_tagihan} berhasil {$statusText}.");

        $this->showVerifikasiModal = false;
        $this->verifikasiTagihanId = null;
    }

    public function save(): void
    {
        /** @var Akun $user */
        $user = auth()->user();

        if ($this->isEdit) {
            $tagihan = Tagihan::with('unit')->findOrFail($this->id_tagihan);
            Gate::authorize('update', $tagihan);

            $validated = $this->validate([
                'jumlah' => ['required', 'numeric', 'min:1'],
                'jatuh_tempo' => ['required', 'date'],
            ]);

            $tagihan->update([
                'jumlah' => $validated['jumlah'],
                'jatuh_tempo' => $validated['jatuh_tempo'],
            ]);

            session()->flash('success', "Tagihan {$tagihan->id_tagihan} berhasil diperbarui.");
        } else {
            Gate::authorize('create', Tagihan::class);

            $validated = $this->validate([
                'id_unit' => ['required', 'string', 'exists:unit_usaha,id_unit'],
                'id_pelanggan' => ['required', 'string', 'exists:pelanggan,id_pelanggan'],
                'jumlah' => ['required', 'numeric', 'min:1'],
                'jatuh_tempo' => ['required', 'date'],
            ]);

            $unit = UnitUsaha::findOrFail($validated['id_unit']);

            // Otorisasi kepemilikan unit
            if ($user->hasRole('admin_unit') && $unit->id_unit !== $user->id_unit) {
                abort(403, 'Admin Unit hanya dapat membuat tagihan di unitnya sendiri.');
            }
            if ($user->hasAnyRole(['admin_bumdes', 'bendahara', 'sekretaris']) && $unit->id_bumdes !== $user->id_bumdes) {
                abort(403, 'Anda hanya dapat membuat tagihan pada unit di BUMDes sendiri.');
            }

            // Pastikan pelanggan terdaftar di unit yang dipilih
            $pelanggan = Pelanggan::where('id_pelanggan', $validated['id_pelanggan'])
                ->where('id_unit', $validated['id_unit'])
                ->firstOrFail();

            $dateFormatted = Carbon::parse($validated['jatuh_tempo'])->format('Ymd');
            $idTagihan = $this->generateNextId($dateFormatted);

            Tagihan::create([
                'id_tagihan' => $idTagihan,
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'id_unit' => $validated['id_unit'],
                'jumlah' => $validated['jumlah'],
                'jatuh_tempo' => $validated['jatuh_tempo'],
                'status' => 'belum_bayar',
            ]);

            session()->flash('success', "Tagihan {$idTagihan} untuk pelanggan {$pelanggan->nama} berhasil dibuat.");
        }

        $this->showModal = false;
    }

    public function render(): View
    {
        /** @var Akun $user */
        $user = auth()->user();

        if ($user->hasRole('admin_unit')) {
            $unitList = UnitUsaha::where('id_unit', $user->id_unit)->get();
        } elseif ($user->hasAnyRole(['admin_bumdes', 'sekretaris', 'bendahara'])) {
            $unitList = UnitUsaha::where('id_bumdes', $user->id_bumdes)->orderBy('nama_unit')->get();
        } else {
            $unitList = UnitUsaha::orderBy('nama_unit')->get();
        }

        $bumdesList = $user->hasRole('super_admin')
            ? Bumdes::orderBy('nama_bumdes')->get()
            : Bumdes::where('id_bumdes', $user->id_bumdes)->get();

        // Pelanggan list untuk modal create (filter by unit terpilih)
        $pelangganList = $this->id_unit
            ? Pelanggan::where('id_unit', $this->id_unit)->where('status_aktif', true)->orderBy('nama')->get()
            : collect();

        $detailTagihan = $this->detailId
            ? Tagihan::with(['pelanggan', 'unit.bumdes', 'verifikator'])->find($this->detailId)
            : null;

        $verifikasiTagihan = $this->verifikasiTagihanId
            ? Tagihan::with(['pelanggan', 'unit.bumdes'])->find($this->verifikasiTagihanId)
            : null;

        return view('livewire.operasional.tagihan-manager', [
            'rows' => $this->rows,
            'stats' => $this->stats,
            'unitList' => $unitList,
            'bumdesList' => $bumdesList,
            'pelangganList' => $pelangganList,
            'detailTagihan' => $detailTagihan,
            'verifikasiTagihan' => $verifikasiTagihan,
        ])->layout('layouts.panel', ['title' => 'Manajemen Tagihan Pelanggan', 'header' => 'Manajemen Tagihan']);
    }

    private function generateNextId(string $dateFormatted): string
    {
        $prefix = "TAG-{$dateFormatted}-";

        $lastId = Tagihan::where('id_tagihan', 'like', $prefix.'%')
            ->orderByDesc('id_tagihan')
            ->value('id_tagihan');

        $next = ((int) str_replace($prefix, '', $lastId ?? $prefix.'000000')) + 1;

        return $prefix.sprintf('%06d', $next);
    }
}
