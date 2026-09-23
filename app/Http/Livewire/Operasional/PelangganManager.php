<?php

namespace App\Http\Livewire\Operasional;

use App\Http\Livewire\Shared\DataTable;
use App\Models\Akun;
use App\Models\Pelanggan;
use App\Models\UnitUsaha;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class PelangganManager extends DataTable
{
    public string $sortField = 'id_pelanggan';

    public string $sortDirection = 'asc';

    /**
     * @var array{id_unit: string, status_aktif: string}
     */
    public array $filters = [
        'id_unit' => '',
        'status_aktif' => '',
    ];

    public bool $showModal = false;

    public bool $isEdit = false;

    // Form fields
    public string $id_pelanggan = '';

    public string $id_unit = '';

    public string $nama = '';

    public string $kontak = '';

    public bool $status_aktif = true;

    public function query(): Builder
    {
        /** @var Akun $user */
        $user = auth()->user();

        $query = Pelanggan::query()->with('unit.bumdes');

        // Scope: admin_unit hanya lihat pelanggan di unitnya
        if ($user->hasRole('admin_unit')) {
            $query->where('id_unit', $user->id_unit);
        } elseif ($user->hasAnyRole(['admin_bumdes', 'sekretaris', 'bendahara'])) {
            // Scope: level BUMDes — lihat pelanggan di semua unit BUMDes sendiri
            $query->whereHas('unit', function (Builder $q) use ($user) {
                $q->where('id_bumdes', $user->id_bumdes);
            });
        }
        // super_admin, direktur, pengawas, penasihat — no scope

        if ($this->search !== '') {
            $query->where(function (Builder $q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('id_pelanggan', 'like', '%'.$this->search.'%')
                    ->orWhere('kontak', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filters['id_unit'] !== '') {
            $query->where('id_unit', $this->filters['id_unit']);
        }

        if ($this->filters['status_aktif'] !== '') {
            $query->where('status_aktif', (bool) $this->filters['status_aktif']);
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    public function openCreateModal(): void
    {
        Gate::authorize('create', Pelanggan::class);

        $this->resetValidation();
        $this->reset(['id_pelanggan', 'nama', 'kontak']);
        $this->status_aktif = true;
        $this->isEdit = false;

        /** @var Akun $user */
        $user = auth()->user();

        // Pre-fill id_unit jika admin_unit
        $this->id_unit = $user->hasRole('admin_unit')
            ? ($user->id_unit ?? '')
            : '';

        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $pelanggan = Pelanggan::findOrFail($id);

        Gate::authorize('update', $pelanggan);

        $this->resetValidation();
        $this->id_pelanggan = $pelanggan->id_pelanggan;
        $this->id_unit = $pelanggan->id_unit;
        $this->nama = $pelanggan->nama;
        $this->kontak = $pelanggan->kontak ?? '';
        $this->status_aktif = (bool) $pelanggan->status_aktif;

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        if ($this->isEdit) {
            $pelanggan = Pelanggan::findOrFail($this->id_pelanggan);
            Gate::authorize('update', $pelanggan);

            $validated = $this->validate([
                'nama' => ['required', 'string', 'max:150'],
                'kontak' => ['nullable', 'string', 'max:20'],
                'status_aktif' => ['boolean'],
            ]);

            $pelanggan->update([
                'nama' => $validated['nama'],
                'kontak' => $validated['kontak'] ?: null,
                'status_aktif' => $validated['status_aktif'],
            ]);

            session()->flash('success', "Pelanggan {$pelanggan->nama} berhasil diperbarui.");
        } else {
            Gate::authorize('create', Pelanggan::class);

            $validated = $this->validate([
                'id_unit' => ['required', 'string', 'exists:unit_usaha,id_unit'],
                'nama' => ['required', 'string', 'max:150'],
                'kontak' => ['nullable', 'string', 'max:20'],
                'status_aktif' => ['boolean'],
            ]);

            $idPelanggan = $this->generateNextId($validated['id_unit']);

            Pelanggan::create([
                'id_pelanggan' => $idPelanggan,
                'id_unit' => $validated['id_unit'],
                'nama' => $validated['nama'],
                'kontak' => $validated['kontak'] ?: null,
                'status_aktif' => $validated['status_aktif'] ?? true,
            ]);

            session()->flash('success', "Pelanggan {$validated['nama']} berhasil ditambahkan.");
        }

        $this->showModal = false;
    }

    public function toggleStatus(string $id): void
    {
        $pelanggan = Pelanggan::findOrFail($id);

        Gate::authorize('toggleStatus', $pelanggan);

        $pelanggan->update(['status_aktif' => ! $pelanggan->status_aktif]);

        $text = $pelanggan->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Pelanggan {$pelanggan->nama} berhasil {$text}.");
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

        return view('livewire.operasional.pelanggan-manager', [
            'rows' => $this->rows,
            'unitList' => $unitList,
        ])->layout('layouts.panel', ['title' => 'Manajemen Pelanggan', 'header' => 'Manajemen Pelanggan']);
    }

    private function generateNextId(string $idUnit): string
    {
        $prefix = 'PLG-'.strtoupper($idUnit).'-';

        $lastId = Pelanggan::where('id_pelanggan', 'like', $prefix.'%')
            ->orderByDesc('id_pelanggan')
            ->value('id_pelanggan');

        $next = ((int) str_replace($prefix, '', $lastId ?? $prefix.'000000')) + 1;

        return $prefix.sprintf('%06d', $next);
    }
}
