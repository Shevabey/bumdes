<?php

namespace App\Http\Livewire\SuperAdmin;

use App\Http\Livewire\Shared\DataTable;
use App\Models\Bumdes;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class BumdesManager extends DataTable
{
    public string $sortField = 'id_bumdes';

    public string $sortDirection = 'asc';

    /**
     * @var array{status_aktif: string}
     */
    public array $filters = [
        'status_aktif' => '',
    ];

    public bool $showModal = false;

    public bool $isEdit = false;

    public string $id_bumdes = '';

    public string $id_kelurahan = '';

    public string $nama_bumdes = '';

    public ?string $tanggal_berdiri = null;

    public bool $status_aktif = true;

    public function query(): Builder
    {
        $query = Bumdes::query()->with('kelurahan');

        if ($this->search !== '') {
            $query->where(function (Builder $q) {
                $q->where('nama_bumdes', 'like', '%'.$this->search.'%')
                    ->orWhere('id_bumdes', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filters['status_aktif'] !== '') {
            $query->where('status_aktif', (bool) $this->filters['status_aktif']);
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['id_bumdes', 'id_kelurahan', 'nama_bumdes']);
        $this->tanggal_berdiri = Carbon::today()->toDateString();
        $this->status_aktif = true;
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $this->resetValidation();
        $bumdes = Bumdes::findOrFail($id);

        $this->id_bumdes = $bumdes->id_bumdes;
        $this->id_kelurahan = $bumdes->id_kelurahan;
        $this->nama_bumdes = $bumdes->nama_bumdes;
        $this->tanggal_berdiri = $bumdes->tanggal_berdiri instanceof Carbon
            ? $bumdes->tanggal_berdiri->format('Y-m-d')
            : (string) $bumdes->tanggal_berdiri;
        $this->status_aktif = (bool) $bumdes->status_aktif;

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        $rules = [
            'id_kelurahan' => ['required', 'string', 'exists:region,id_region'],
            'nama_bumdes' => ['required', 'string', 'max:150'],
            'tanggal_berdiri' => ['nullable', 'date'],
            'status_aktif' => ['boolean'],
        ];

        if (! $this->isEdit) {
            $rules['id_bumdes'] = ['required', 'string', 'max:30', 'unique:bumdes,id_bumdes'];
        }

        $validated = $this->validate($rules);

        if ($this->isEdit) {
            $bumdes = Bumdes::findOrFail($this->id_bumdes);
            $bumdes->update([
                'id_kelurahan' => $validated['id_kelurahan'],
                'nama_bumdes' => $validated['nama_bumdes'],
                'tanggal_berdiri' => $validated['tanggal_berdiri'] ?: null,
                'status_aktif' => $validated['status_aktif'] ?? true,
            ]);

            session()->flash('success', "BUMDes {$bumdes->nama_bumdes} berhasil diperbarui.");
        } else {
            $bumdes = Bumdes::create([
                'id_bumdes' => $validated['id_bumdes'],
                'id_kelurahan' => $validated['id_kelurahan'],
                'nama_bumdes' => $validated['nama_bumdes'],
                'tanggal_berdiri' => $validated['tanggal_berdiri'] ?: null,
                'status_aktif' => $validated['status_aktif'] ?? true,
            ]);

            session()->flash('success', "BUMDes {$bumdes->nama_bumdes} berhasil ditambahkan.");
        }

        $this->showModal = false;
    }

    public function toggleStatus(string $id): void
    {
        $bumdes = Bumdes::findOrFail($id);
        $bumdes->update(['status_aktif' => ! $bumdes->status_aktif]);

        $statusText = $bumdes->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Status BUMDes {$bumdes->nama_bumdes} berhasil {$statusText}.");
    }

    public function render(): View
    {
        return view('livewire.super-admin.bumdes-manager', [
            'rows' => $this->rows,
            'kelurahanOptions' => Region::where('jenis_wilayah', 'kelurahan_desa')
                ->orderBy('nama_lengkap')
                ->get(),
        ])->layout('layouts.panel', ['title' => 'Manajemen BUMDes', 'header' => 'Manajemen BUMDes']);
    }
}
