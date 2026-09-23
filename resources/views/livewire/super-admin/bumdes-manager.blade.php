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
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Manajemen BUMDes Terdaftar</h2>
            <p class="text-sm text-slate-500">Daftar seluruh Badan Usaha Milik Desa yang terhubung ke dalam ekosistem SIM-BUMDes.</p>
        </div>
        <button
            type="button"
            wire:click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah BUMDes</span>
        </button>
    </div>

    {{-- Main Card --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        {{-- Search & Filter Bar --}}
        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100">
            <div class="flex flex-1 flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[240px] max-w-md">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari ID atau nama BUMDes..."
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

                <div class="flex items-center gap-2">
                    <select
                        wire:model.live="filters.status_aktif"
                        class="rounded-lg border border-slate-200 py-2 px-3 text-xs focus:border-slate-800 focus:outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>

                    @if ($search || $filters['status_aktif'] !== '')
                        <button
                            type="button"
                            wire:click="resetFilters"
                            class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100"
                        >
                            Reset
                        </button>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span>Tampilkan</span>
                <select wire:model.live="perPage" class="rounded-lg border border-slate-200 py-1.5 px-2 text-xs">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>baris</span>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-3.5">
                            <button type="button" wire:click="sortBy('id_bumdes')" class="inline-flex items-center gap-1 font-semibold hover:text-slate-900">
                                <span>ID BUMDes</span>
                                @if ($sortField === 'id_bumdes')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3.5">
                            <button type="button" wire:click="sortBy('nama_bumdes')" class="inline-flex items-center gap-1 font-semibold hover:text-slate-900">
                                <span>Nama BUMDes</span>
                                @if ($sortField === 'nama_bumdes')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3.5">Kelurahan / Desa</th>
                        <th scope="col" class="px-6 py-3.5">
                            <button type="button" wire:click="sortBy('tanggal_berdiri')" class="inline-flex items-center gap-1 font-semibold hover:text-slate-900">
                                <span>Tanggal Berdiri</span>
                                @if ($sortField === 'tanggal_berdiri')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-center">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs font-semibold text-slate-900">
                                {{ $item->id_bumdes }}
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-900">
                                {{ $item->nama_bumdes }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $item->kelurahan?->nama_lengkap ?? '-' }}
                                <span class="font-mono text-slate-400">({{ $item->id_kelurahan }})</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $item->tanggal_berdiri ? $item->tanggal_berdiri->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <x-status-badge :status="$item->status_aktif ? 'aktif' : 'nonaktif'" />
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="toggleStatus('{{ $item->id_bumdes }}')"
                                        wire:confirm="Ubah status aktif BUMDes {{ $item->nama_bumdes }}?"
                                        class="rounded-md border px-2.5 py-1 text-xs font-medium transition-colors {{ $item->status_aktif ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}"
                                    >
                                        {{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="openEditModal('{{ $item->id_bumdes }}')"
                                        class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                                    >
                                        Edit
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <p class="text-sm font-medium text-slate-600">Tidak ada data BUMDes ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($rows->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $rows->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Form Dialog --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-bold text-slate-900">
                        {{ $isEdit ? 'Edit Data BUMDes' : 'Tambah BUMDes Baru' }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Kode ID BUMDes</label>
                        <input
                            type="text"
                            wire:model="id_bumdes"
                            placeholder="Contoh: BMD-SDS-001"
                            {{ $isEdit ? 'disabled' : '' }}
                            class="mt-1 w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-slate-800 focus:outline-none disabled:bg-slate-100 disabled:text-slate-500 font-mono"
                        >
                        @error('id_bumdes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Kelurahan / Desa Wilayah</label>
                        <select
                            wire:model="id_kelurahan"
                            class="mt-1 w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-slate-800 focus:outline-none"
                        >
                            <option value="">-- Pilih Kelurahan/Desa --</option>
                            @foreach ($kelurahanOptions as $kel)
                                <option value="{{ $kel->id_region }}">
                                    {{ $kel->id_region }} — {{ $kel->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_kelurahan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nama BUMDes</label>
                        <input
                            type="text"
                            wire:model="nama_bumdes"
                            placeholder="Contoh: BUMDes Sendangsari Makmur"
                            class="mt-1 w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-slate-800 focus:outline-none"
                        >
                        @error('nama_bumdes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Tanggal Berdiri</label>
                        <input
                            type="date"
                            wire:model="tanggal_berdiri"
                            class="mt-1 w-full rounded-lg border border-slate-200 py-2 px-3 text-sm focus:border-slate-800 focus:outline-none"
                        >
                        @error('tanggal_berdiri') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input
                            type="checkbox"
                            id="status_aktif"
                            wire:model="status_aktif"
                            class="rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                        >
                        <label for="status_aktif" class="text-sm font-medium text-slate-700">
                            Status Aktif
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        >
                            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah BUMDes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
