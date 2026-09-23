<?php

namespace App\Http\Livewire\SuperAdmin;

use App\Http\Livewire\Shared\DataTable;
use App\Models\Bumdes;
use App\Models\Region;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class RegionManager extends DataTable
{
    public string $sortField = 'id_region';

    public string $sortDirection = 'asc';

    /**
     * @var array{jenis_wilayah: string}
     */
    public array $filters = [
        'jenis_wilayah' => '',
    ];

    public bool $showModal = false;

    public bool $isEdit = false;

    public string $id_region = '';

    public string $jenis_wilayah = 'kelurahan_desa';

    public string $nama_lengkap = '';

    public ?string $parent_id = null;

    public bool $is_koordinator = false;

    public function query(): Builder
    {
        $query = Region::query()->with('parent');

        if ($this->search !== '') {
            $query->where(function (Builder $q) {
                $q->where('nama_lengkap', 'like', '%'.$this->search.'%')
                    ->orWhere('id_region', 'like', '%'.$this->search.'%');
            });
        }

        if (! empty($this->filters['jenis_wilayah'])) {
            $query->where('jenis_wilayah', $this->filters['jenis_wilayah']);
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['id_region', 'nama_lengkap', 'parent_id', 'is_koordinator']);
        $this->jenis_wilayah = 'kelurahan_desa';
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $this->resetValidation();
        $region = Region::findOrFail($id);

        $this->id_region = $region->id_region;
        $this->jenis_wilayah = $region->jenis_wilayah;
        $this->nama_lengkap = $region->nama_lengkap;
        $this->parent_id = $region->parent_id;
        $this->is_koordinator = (bool) $region->is_koordinator;

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
            'jenis_wilayah' => ['required', 'in:provinsi,kabupaten_kota,kecamatan,kelurahan_desa'],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'string', 'exists:region,id_region'],
            'is_koordinator' => ['boolean'],
        ];

        if (! $this->isEdit) {
            $rules['id_region'] = ['required', 'string', 'max:20', 'unique:region,id_region'];
        }

        $validated = $this->validate($rules);

        if ($this->isEdit) {
            $region = Region::findOrFail($this->id_region);
            $region->update([
                'jenis_wilayah' => $validated['jenis_wilayah'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'parent_id' => $validated['parent_id'] ?: null,
                'is_koordinator' => $validated['is_koordinator'] ?? false,
            ]);

            session()->flash('success', "Wilayah {$region->nama_lengkap} berhasil diperbarui.");
        } else {
            $region = Region::create([
                'id_region' => $validated['id_region'],
                'jenis_wilayah' => $validated['jenis_wilayah'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'parent_id' => $validated['parent_id'] ?: null,
                'is_koordinator' => $validated['is_koordinator'] ?? false,
            ]);

            session()->flash('success', "Wilayah {$region->nama_lengkap} berhasil ditambahkan.");
        }

        $this->showModal = false;
    }

    public function toggleKoordinator(string $id): void
    {
        $region = Region::findOrFail($id);
        $region->update(['is_koordinator' => ! $region->is_koordinator]);

        session()->flash('success', "Status koordinator wilayah {$region->nama_lengkap} diperbarui.");
    }

    public function delete(string $id): void
    {
        $region = Region::findOrFail($id);

        if ($region->children()->exists()) {
            session()->flash('error', 'Tidak dapat menghapus wilayah yang masih memiliki sub-wilayah.');

            return;
        }

        if (Bumdes::where('id_kelurahan', $region->id_region)->exists()) {
            session()->flash('error', 'Tidak dapat menghapus wilayah yang memiliki BUMDes terdaftar.');

            return;
        }

        $nama = $region->nama_lengkap;
        $region->delete();

        session()->flash('success', "Wilayah {$nama} berhasil dihapus.");
    }

    public function render(): View
    {
        return view('livewire.super-admin.region-manager', [
            'rows' => $this->rows,
            'parentOptions' => Region::orderBy('id_region')->get(),
        ])->layout('layouts.panel', ['title' => 'Manajemen Wilayah', 'header' => 'Manajemen Wilayah (Region)']);
    }
}
