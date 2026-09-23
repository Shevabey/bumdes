<?php

namespace App\Http\Livewire\SuperAdmin;

use App\Http\Livewire\Shared\DataTable;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\Pelanggan;
use App\Models\UnitUsaha;
use App\Services\AkunService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class AkunManager extends DataTable
{
    public string $sortField = 'id_akun';

    public string $sortDirection = 'asc';

    /**
     * @var array{role: string, id_bumdes: string, status_aktif: string}
     */
    public array $filters = [
        'role' => '',
        'id_bumdes' => '',
        'status_aktif' => '',
    ];

    public bool $showModal = false;

    public bool $isEdit = false;

    public string $id_akun = '';

    public string $nama = '';

    public string $username = '';

    public string $password = '';

    public string $role = 'admin_bumdes';

    public ?string $id_bumdes = null;

    public ?string $id_unit = null;

    public ?string $id_pelanggan = null;

    public bool $status_aktif = true;

    // Reset Password Modal
    public bool $showResetPasswordModal = false;

    public string $resetPasswordIdAkun = '';

    public string $resetPasswordUsername = '';

    public string $new_password = '';

    public function query(): Builder
    {
        $query = Akun::query()->with(['roles', 'bumdes', 'unit']);

        if ($this->search !== '') {
            $query->where(function (Builder $q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('username', 'like', '%'.$this->search.'%')
                    ->orWhere('id_akun', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filters['role'] !== '') {
            $query->whereHas('roles', function (Builder $q) {
                $q->where('name', $this->filters['role']);
            });
        }

        if ($this->filters['id_bumdes'] !== '') {
            $query->where('id_bumdes', $this->filters['id_bumdes']);
        }

        if ($this->filters['status_aktif'] !== '') {
            $query->where('status_aktif', (bool) $this->filters['status_aktif']);
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset([
            'id_akun',
            'nama',
            'username',
            'password',
            'id_bumdes',
            'id_unit',
            'id_pelanggan',
        ]);
        $this->role = 'admin_bumdes';
        $this->status_aktif = true;
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $this->resetValidation();
        $akun = Akun::findOrFail($id);

        $this->id_akun = $akun->id_akun;
        $this->nama = $akun->nama;
        $this->username = $akun->username;
        $this->role = $akun->roles->first()?->name ?? $akun->role;
        $this->id_bumdes = $akun->id_bumdes;
        $this->id_unit = $akun->id_unit;
        $this->status_aktif = (bool) $akun->status_aktif;
        $this->password = '';

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function openResetPasswordModal(string $id): void
    {
        $this->resetValidation();
        $akun = Akun::findOrFail($id);

        $this->resetPasswordIdAkun = $akun->id_akun;
        $this->resetPasswordUsername = $akun->username;
        $this->new_password = '';
        $this->showResetPasswordModal = true;
    }

    public function closeResetPasswordModal(): void
    {
        $this->showResetPasswordModal = false;
    }

    public function save(): void
    {
        if ($this->isEdit) {
            $validated = $this->validate([
                'nama' => ['required', 'string', 'max:150'],
                'status_aktif' => ['boolean'],
                'password' => ['nullable', 'string', 'min:6'],
            ]);

            $akun = Akun::findOrFail($this->id_akun);
            $updateData = [
                'nama' => $validated['nama'],
                'status_aktif' => $validated['status_aktif'] ?? true,
            ];

            if (! empty($validated['password'])) {
                $updateData['password_hash'] = Hash::make($validated['password']);
            }

            $akun->update($updateData);

            session()->flash('success', "Akun {$akun->username} berhasil diperbarui.");
        } else {
            $rules = [
                'nama' => ['required', 'string', 'max:150'],
                'username' => ['required', 'string', 'max:50', 'unique:akun,username'],
                'password' => ['required', 'string', 'min:6'],
                'role' => ['required', 'in:super_admin,pengawas,penasihat,direktur,admin_bumdes,sekretaris,bendahara,admin_unit,pengguna'],
                'id_bumdes' => ['nullable', 'string', 'exists:bumdes,id_bumdes'],
                'id_unit' => ['nullable', 'string', 'exists:unit_usaha,id_unit'],
                'id_pelanggan' => ['nullable', 'string', 'exists:pelanggan,id_pelanggan'],
                'status_aktif' => ['boolean'],
            ];

            $validated = $this->validate($rules);

            /** @var Akun $currentUser */
            $currentUser = auth()->user();

            $akun = (new AkunService)->create($currentUser, [
                'nama' => $validated['nama'],
                'username' => $validated['username'],
                'password' => $validated['password'],
                'role' => $validated['role'],
                'id_bumdes' => $validated['id_bumdes'] ?: null,
                'id_unit' => $validated['id_unit'] ?: null,
                'id_pelanggan' => $validated['id_pelanggan'] ?: null,
                'status_aktif' => $validated['status_aktif'] ?? true,
            ]);

            session()->flash('success', "Akun {$akun->username} ({$akun->role}) berhasil ditambahkan.");
        }

        $this->showModal = false;
    }

    public function toggleStatus(string $id): void
    {
        $akun = Akun::findOrFail($id);

        if ($akun->id_akun === auth()->id()) {
            session()->flash('error', 'Tidak dapat menonaktifkan akun sendiri.');

            return;
        }

        $akun->update(['status_aktif' => ! $akun->status_aktif]);

        $statusText = $akun->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Akun {$akun->username} berhasil {$statusText}.");
    }

    public function resetPassword(): void
    {
        $this->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $akun = Akun::findOrFail($this->resetPasswordIdAkun);
        $akun->update([
            'password_hash' => Hash::make($this->new_password),
        ]);

        session()->flash('success', "Password akun {$akun->username} berhasil direset.");
        $this->showResetPasswordModal = false;
    }

    public function render(): View
    {
        $units = $this->id_bumdes
            ? UnitUsaha::where('id_bumdes', $this->id_bumdes)->orderBy('nama_unit')->get()
            : UnitUsaha::orderBy('nama_unit')->get();

        $pelangganQuery = Pelanggan::whereNull('id_akun');
        if ($this->id_unit) {
            $pelangganQuery->where('id_unit', $this->id_unit);
        }

        return view('livewire.super-admin.akun-manager', [
            'rows' => $this->rows,
            'bumdesList' => Bumdes::orderBy('nama_bumdes')->get(),
            'unitList' => $units,
            'pelangganList' => $pelangganQuery->get(),
        ])->layout('layouts.panel', ['title' => 'Manajemen Akun', 'header' => 'Manajemen Akun']);
    }
}
