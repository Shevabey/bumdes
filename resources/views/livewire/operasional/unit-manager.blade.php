<div class="space-y-6">
    {{-- Flash Message Alerts --}}
    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header Action --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Manajemen Unit Usaha</h2>
            <p class="text-sm text-slate-500">Kelola unit-unit usaha BUMDes beserta jenis layanan dan status operasionalnya.</p>
        </div>
        @can('create', \App\Models\UnitUsaha::class)
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Unit Usaha</span>
            </button>
        @endcan
    </div>

    {{-- Main Card --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        {{-- Search & Filter Bar --}}
        <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-wrap items-center gap-3">
                <div class="relative min-w-[220px] max-w-md flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari ID, nama, jenis unit..."
                        class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-8 text-sm placeholder-slate-400 focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800"
                    >
                    @if ($search)
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if (auth()->user()->hasRole('super_admin'))
                        <select
                            wire:model.live="filters.id_bumdes"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-slate-800 focus:outline-none"
                        >
                            <option value="">Semua BUMDes</option>
                            @foreach ($bumdesList as $bmd)
                                <option value="{{ $bmd->id_bumdes }}">{{ $bmd->nama_bumdes }}</option>
                            @endforeach
                        </select>
                    @endif

                    <select
                        wire:model.live="filters.jenis_unit"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-slate-800 focus:outline-none"
                    >
                        <option value="">Semua Jenis</option>
                        <option value="pamdes">PAMDes</option>
                        <option value="peternakan">Peternakan</option>
                        <option value="mitra_tani">Mitra Tani</option>
                        <option value="sewa_mobil">Sewa Mobil</option>
                        <option value="sampah">Sampah</option>
                        <option value="custom">Custom</option>
                    </select>

                    <select
                        wire:model.live="filters.status_aktif"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-slate-800 focus:outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>

                    @if ($search || array_filter($filters))
                        <button
                            type="button"
                            wire:click="resetFilters"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 hover:bg-slate-50"
                        >
                            Reset
                        </button>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span>Tampilkan</span>
                <select
                    wire:model.live="perPage"
                    class="rounded-lg border border-slate-200 px-2 py-1.5 text-xs focus:border-slate-800 focus:outline-none"
                >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>baris</span>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('id_unit')" class="flex items-center gap-1 hover:text-slate-900">
                                ID Unit
                                @if ($sortField === 'id_unit')
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('nama_unit')" class="flex items-center gap-1 hover:text-slate-900">
                                Nama Unit
                                @if ($sortField === 'nama_unit')
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">Jenis Unit</th>
                        <th class="px-4 py-3 text-left">BUMDes</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $unit)
                        <tr class="hover:bg-slate-50/60 transition-colors" wire:key="unit-{{ $unit->id_unit }}">
                            <td class="px-4 py-3">
                                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs font-mono text-slate-700">{{ $unit->id_unit }}</code>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $unit->nama_unit }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                    {{ ucwords(str_replace('_', ' ', $unit->jenis_unit)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $unit->bumdes?->nama_bumdes ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @can('update', $unit)
                                    <button
                                        wire:click="toggleStatus('{{ $unit->id_unit }}')"
                                        wire:confirm="Ubah status unit ini?"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium transition-colors {{ $unit->status_aktif ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}"
                                    >
                                        {{ $unit->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $unit->status_aktif ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $unit->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endcan
                            </td>
                            <td class="px-4 py-3 text-right">
                                @can('update', $unit)
                                    <button
                                        wire:click="openEditModal('{{ $unit->id_unit }}')"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <p>Tidak ada unit usaha ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($rows->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $rows->links() }}
            </div>
        @endif
    </div>

    {{-- ==================== MODAL CREATE / EDIT ==================== --}}
    @if ($showModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="closeModal"
        >
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-slate-900">
                        {{ $isEdit ? 'Edit Unit Usaha' : 'Tambah Unit Usaha Baru' }}
                    </h3>
                    <button wire:click="closeModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form wire:submit="save" class="space-y-4 p-6">
                    @if (!$isEdit)
                        {{-- BUMDes --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-id_bumdes">BUMDes <span class="text-rose-500">*</span></label>
                            <select
                                id="modal-id_bumdes"
                                wire:model="id_bumdes"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800 @error('id_bumdes') border-rose-500 @enderror"
                                {{ auth()->user()->hasRole('admin_bumdes') ? 'disabled' : '' }}
                            >
                                <option value="">-- Pilih BUMDes --</option>
                                @foreach ($bumdesList as $bmd)
                                    <option value="{{ $bmd->id_bumdes }}">{{ $bmd->nama_bumdes }}</option>
                                @endforeach
                            </select>
                            @error('id_bumdes') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Nama Unit --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-nama_unit">Nama Unit Usaha <span class="text-rose-500">*</span></label>
                        <input
                            id="modal-nama_unit"
                            type="text"
                            wire:model="nama_unit"
                            placeholder="Contoh: Air Bersih Desa Maju"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm placeholder-slate-400 focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800 @error('nama_unit') border-rose-500 @enderror"
                        >
                        @error('nama_unit') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Jenis Unit --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-jenis_unit">Jenis Unit <span class="text-rose-500">*</span></label>
                        <select
                            id="modal-jenis_unit"
                            wire:model="jenis_unit"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800 @error('jenis_unit') border-rose-500 @enderror"
                        >
                            <option value="">-- Pilih Jenis --</option>
                            <option value="pamdes">PAMDes</option>
                            <option value="peternakan">Peternakan</option>
                            <option value="mitra_tani">Mitra Tani</option>
                            <option value="sewa_mobil">Sewa Mobil</option>
                            <option value="sampah">Sampah</option>
                            <option value="custom">Custom</option>
                        </select>
                        @error('jenis_unit') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Status Aktif --}}
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input id="modal-status_aktif" type="checkbox" wire:model="status_aktif" class="peer sr-only">
                            <div class="peer h-5 w-9 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-slate-900 peer-checked:after:translate-x-full peer-focus:ring-2 peer-focus:ring-slate-800/30"></div>
                        </label>
                        <label for="modal-status_aktif" class="text-sm font-medium text-slate-700">Unit Aktif</label>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Unit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
