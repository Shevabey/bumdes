<?php

namespace App\Http\Livewire\Operasional;

use App\Http\Livewire\Shared\DataTable;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\UnitUsaha;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class UnitManager extends DataTable
{
    public string $sortField = 'id_unit';

    public string $sortDirection = 'asc';

    /**
     * @var array{id_bumdes: string, jenis_unit: string, status_aktif: string}
     */
    public array $filters = [
        'id_bumdes' => '',
        'jenis_unit' => '',
        'status_aktif' => '',
    ];

    public bool $showModal = false;

    public bool $isEdit = false;

    // Form fields
    public string $id_unit = '';

    public string $id_bumdes = '';

    public string $jenis_unit = '';

    public string $nama_unit = '';

    public bool $status_aktif = true;

    public function query(): Builder
    {
        $user = auth()->user();

        /** @var Akun $user */
        $query = UnitUsaha::query()->with('bumdes');

        // Scope: admin_bumdes dan operator BUMDes hanya lihat unit BUMDes sendiri
        if ($user->hasAnyRole(['admin_bumdes', 'sekretaris', 'bendahara'])) {
            $query->where('id_bumdes', $user->id_bumdes);
        }

        // admin_unit hanya lihat unitnya sendiri
        if ($user->hasRole('admin_unit')) {
            $query->where('id_unit', $user->id_unit);
        }

        if ($this->search !== '') {
            $query->where(function (Builder $q) {
                $q->where('nama_unit', 'like', '%'.$this->search.'%')
                    ->orWhere('id_unit', 'like', '%'.$this->search.'%')
                    ->orWhere('jenis_unit', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filters['id_bumdes'] !== '') {
            $query->where('id_bumdes', $this->filters['id_bumdes']);
        }

        if ($this->filters['jenis_unit'] !== '') {
            $query->where('jenis_unit', $this->filters['jenis_unit']);
        }

        if ($this->filters['status_aktif'] !== '') {
            $query->where('status_aktif', (bool) $this->filters['status_aktif']);
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    public function openCreateModal(): void
    {
        Gate::authorize('create', UnitUsaha::class);

        $this->resetValidation();
        $this->reset(['id_unit', 'nama_unit', 'jenis_unit']);
        $this->status_aktif = true;
        $this->isEdit = false;

        /** @var Akun $user */
        $user = auth()->user();

        // Pre-fill id_bumdes jika admin_bumdes
        $this->id_bumdes = $user->hasRole('admin_bumdes')
            ? ($user->id_bumdes ?? '')
            : '';

        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $unit = UnitUsaha::findOrFail($id);

        Gate::authorize('update', $unit);

        $this->resetValidation();
        $this->id_unit = $unit->id_unit;
        $this->id_bumdes = $unit->id_bumdes;
        $this->jenis_unit = $unit->jenis_unit;
        $this->nama_unit = $unit->nama_unit;
        $this->status_aktif = (bool) $unit->status_aktif;

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
            $unit = UnitUsaha::findOrFail($this->id_unit);
            Gate::authorize('update', $unit);

            $validated = $this->validate([
                'nama_unit' => ['required', 'string', 'max:100'],
                'jenis_unit' => ['required', 'string', 'in:pamdes,peternakan,mitra_tani,sewa_mobil,sampah,custom'],
                'status_aktif' => ['boolean'],
            ]);

            $unit->update([
                'nama_unit' => $validated['nama_unit'],
                'jenis_unit' => $validated['jenis_unit'],
                'status_aktif' => $validated['status_aktif'],
            ]);

            session()->flash('success', "Unit usaha {$unit->nama_unit} berhasil diperbarui.");
        } else {
            Gate::authorize('create', UnitUsaha::class);

            $validated = $this->validate([
                'id_bumdes' => ['required', 'string', 'exists:bumdes,id_bumdes'],
                'nama_unit' => ['required', 'string', 'max:100'],
                'jenis_unit' => ['required', 'string', 'in:pamdes,peternakan,mitra_tani,sewa_mobil,sampah,custom'],
                'status_aktif' => ['boolean'],
            ]);

            $idUnit = $this->generateNextId($validated['id_bumdes']);

            UnitUsaha::create([
                'id_unit' => $idUnit,
                'id_bumdes' => $validated['id_bumdes'],
                'nama_unit' => $validated['nama_unit'],
                'jenis_unit' => $validated['jenis_unit'],
                'skema_field' => [],
                'status_aktif' => $validated['status_aktif'] ?? true,
            ]);

            session()->flash('success', "Unit usaha {$validated['nama_unit']} berhasil ditambahkan.");
        }

        $this->showModal = false;
    }

    public function toggleStatus(string $id): void
    {
        $unit = UnitUsaha::findOrFail($id);

        Gate::authorize('update', $unit);

        $unit->update(['status_aktif' => ! $unit->status_aktif]);

        $text = $unit->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Unit usaha {$unit->nama_unit} berhasil {$text}.");
    }

    public function render(): View
    {
        /** @var Akun $user */
        $user = auth()->user();

        $bumdesList = $user->hasRole('super_admin')
            ? Bumdes::orderBy('nama_bumdes')->get()
            : Bumdes::where('id_bumdes', $user->id_bumdes)->get();

        return view('livewire.operasional.unit-manager', [
            'rows' => $this->rows,
            'bumdesList' => $bumdesList,
        ])->layout('layouts.panel', ['title' => 'Manajemen Unit Usaha', 'header' => 'Manajemen Unit Usaha']);
    }

    private function generateNextId(string $idBumdes): string
    {
        // Prefix: "UNIT-{kode_bumdes}-XXXXXX"
        $prefix = 'UNIT-'.strtoupper($idBumdes).'-';

        $lastId = UnitUsaha::where('id_unit', 'like', $prefix.'%')
            ->orderByDesc('id_unit')
            ->value('id_unit');

        $next = ((int) str_replace($prefix, '', $lastId ?? $prefix.'000000')) + 1;

        return $prefix.sprintf('%06d', $next);
    }
}
