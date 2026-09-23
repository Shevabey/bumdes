<?php

namespace App\Http\Livewire\Operasional;

use App\Http\Livewire\Shared\DataTable;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\Transaksi;
use App\Models\UnitUsaha;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;

class TransaksiManager extends DataTable
{
    public string $sortField = 'tanggal';

    public string $sortDirection = 'desc';

    /**
     * @var array{id_unit: string, tipe: string, tanggal_mulai: string, tanggal_akhir: string, id_bumdes: string}
     */
    public array $filters = [
        'id_unit' => '',
        'tipe' => '',
        'tanggal_mulai' => '',
        'tanggal_akhir' => '',
        'id_bumdes' => '',
    ];

    public bool $showModal = false;

    public bool $isEdit = false;

    public bool $showDetailModal = false;

    // Form fields
    public string $id_transaksi = '';

    public string $id_unit = '';

    public string $tipe = 'input';

    public string|float $jumlah = '';

    public string $tanggal = '';

    public string $keterangan = '';

    public string $kategori = '';

    // Detail view
    public ?string $detailId = null;

    public function mount(): void
    {
        $this->tanggal = Carbon::today()->toDateString();
    }

    public function query(): Builder
    {
        /** @var Akun $user */
        $user = auth()->user();

        $query = Transaksi::query()->with(['unit.bumdes', 'pencatat']);

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
                $q->where('id_transaksi', 'like', '%'.$this->search.'%')
                    ->orWhere('detail->keterangan', 'like', '%'.$this->search.'%')
                    ->orWhere('detail->kategori', 'like', '%'.$this->search.'%')
                    ->orWhereHas('unit', function (Builder $qu) {
                        $qu->where('nama_unit', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Filters
        if ($this->filters['id_unit'] !== '') {
            $query->where('id_unit', $this->filters['id_unit']);
        }

        if ($this->filters['tipe'] !== '') {
            $query->where('tipe', $this->filters['tipe']);
        }

        if ($this->filters['tanggal_mulai'] !== '') {
            $query->whereDate('tanggal', '>=', $this->filters['tanggal_mulai']);
        }

        if ($this->filters['tanggal_akhir'] !== '') {
            $query->whereDate('tanggal', '<=', $this->filters['tanggal_akhir']);
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
        // Clone query tanpa pagination untuk summary cards
        $baseQuery = $this->query();

        $totalInput = (float) (clone $baseQuery)->where('tipe', 'input')->sum('jumlah');
        $totalOutput = (float) (clone $baseQuery)->where('tipe', 'output')->sum('jumlah');
        $saldoBersih = $totalInput - $totalOutput;

        return [
            'totalInput' => $totalInput,
            'totalOutput' => $totalOutput,
            'saldoBersih' => $saldoBersih,
        ];
    }

    public function openCreateModal(): void
    {
        Gate::authorize('create', Transaksi::class);

        $this->resetValidation();
        $this->reset(['id_transaksi', 'keterangan', 'kategori']);
        $this->tipe = 'input';
        $this->jumlah = '';
        $this->tanggal = Carbon::today()->toDateString();
        $this->isEdit = false;

        /** @var Akun $user */
        $user = auth()->user();

        // Pre-fill id_unit jika admin_unit
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
        $transaksi = Transaksi::findOrFail($id);

        Gate::authorize('update', $transaksi);

        $this->resetValidation();
        $this->id_transaksi = $transaksi->id_transaksi;
        $this->id_unit = $transaksi->id_unit;
        $this->tipe = $transaksi->tipe;
        $this->jumlah = (float) $transaksi->jumlah;
        $this->tanggal = $transaksi->tanggal ? Carbon::parse($transaksi->tanggal)->toDateString() : '';
        $this->keterangan = $transaksi->detail['keterangan'] ?? '';
        $this->kategori = $transaksi->detail['kategori'] ?? '';

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function openDetailModal(string $id): void
    {
        $transaksi = Transaksi::with(['unit.bumdes', 'pencatat'])->findOrFail($id);

        Gate::authorize('view', $transaksi);

        $this->detailId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailId = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        /** @var Akun $user */
        $user = auth()->user();

        if ($this->isEdit) {
            $transaksi = Transaksi::with('unit')->findOrFail($this->id_transaksi);
            Gate::authorize('update', $transaksi);

            $validated = $this->validate([
                'tipe' => ['required', 'in:input,output'],
                'jumlah' => ['required', 'numeric', 'min:1'],
                'tanggal' => ['required', 'date'],
                'keterangan' => ['required', 'string', 'max:255'],
                'kategori' => ['nullable', 'string', 'max:100'],
            ]);

            $detail = $transaksi->detail ?? [];
            $detail['keterangan'] = $validated['keterangan'];
            $detail['kategori'] = $validated['kategori'] ?: null;

            $transaksi->update([
                'tipe' => $validated['tipe'],
                'jumlah' => $validated['jumlah'],
                'tanggal' => $validated['tanggal'],
                'detail' => $detail,
            ]);

            session()->flash('success', "Transaksi {$transaksi->id_transaksi} berhasil diperbarui.");
        } else {
            Gate::authorize('create', Transaksi::class);

            $validated = $this->validate([
                'id_unit' => ['required', 'string', 'exists:unit_usaha,id_unit'],
                'tipe' => ['required', 'in:input,output'],
                'jumlah' => ['required', 'numeric', 'min:1'],
                'tanggal' => ['required', 'date'],
                'keterangan' => ['required', 'string', 'max:255'],
                'kategori' => ['nullable', 'string', 'max:100'],
            ]);

            $unit = UnitUsaha::findOrFail($validated['id_unit']);

            // Validasi otorisasi kepemilikan unit
            if ($user->hasRole('admin_unit') && $unit->id_unit !== $user->id_unit) {
                abort(403, 'Admin Unit hanya dapat mencatat transaksi di unitnya sendiri.');
            }
            if ($user->hasAnyRole(['admin_bumdes', 'bendahara', 'sekretaris']) && $unit->id_bumdes !== $user->id_bumdes) {
                abort(403, 'Anda hanya dapat mencatat transaksi pada unit di BUMDes sendiri.');
            }

            $dateFormatted = Carbon::parse($validated['tanggal'])->format('Ymd');
            $idTransaksi = $this->generateNextId($dateFormatted);

            Transaksi::create([
                'id_transaksi' => $idTransaksi,
                'id_unit' => $validated['id_unit'],
                'tipe' => $validated['tipe'],
                'jumlah' => $validated['jumlah'],
                'tanggal' => $validated['tanggal'],
                'dicatat_oleh' => $user->id_akun,
                'detail' => [
                    'keterangan' => $validated['keterangan'],
                    'kategori' => $validated['kategori'] ?: null,
                    'jenis_unit' => $unit->jenis_unit,
                ],
            ]);

            session()->flash('success', "Transaksi {$idTransaksi} berhasil dicatat.");
        }

        $this->showModal = false;
    }

    public function render(): View
    {
        /** @var Akun $user */
        $user = auth()->user();

        // Scope unit list untuk filter & dropdown
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

        $detailTransaksi = $this->detailId
            ? Transaksi::with(['unit.bumdes', 'pencatat'])->find($this->detailId)
            : null;

        return view('livewire.operasional.transaksi-manager', [
            'rows' => $this->rows,
            'stats' => $this->stats,
            'unitList' => $unitList,
            'bumdesList' => $bumdesList,
            'detailTransaksi' => $detailTransaksi,
        ])->layout('layouts.panel', ['title' => 'Manajemen Transaksi', 'header' => 'Manajemen Transaksi']);
    }

    private function generateNextId(string $dateFormatted): string
    {
        $prefix = "TRX-{$dateFormatted}-";

        $lastId = Transaksi::where('id_transaksi', 'like', $prefix.'%')
            ->orderByDesc('id_transaksi')
            ->value('id_transaksi');

        $next = ((int) str_replace($prefix, '', $lastId ?? $prefix.'000000')) + 1;

        return $prefix.sprintf('%06d', $next);
    }
}
