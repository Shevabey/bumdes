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
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Manajemen Transaksi Keuangan</h2>
            <p class="text-sm text-slate-500">Pencatatan arus kas masuk (pemasukan) dan keluar (pengeluaran) operasional unit usaha.</p>
        </div>
        @can('create', \App\Models\Transaksi::class)
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Transaksi Baru</span>
            </button>
        @endcan
    </div>

    {{-- Summary Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pemasukan (Input)</span>
                <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-600">
                Rp {{ number_format($stats['totalInput'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Total akumulasi transaksi masuk</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pengeluaran (Output)</span>
                <span class="rounded-lg bg-rose-50 p-2 text-rose-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-rose-600">
                Rp {{ number_format($stats['totalOutput'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Total akumulasi biaya & operasional</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Saldo Arus Kas Bersih</span>
                <span class="rounded-lg bg-slate-100 p-2 text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold {{ $stats['saldoBersih'] >= 0 ? 'text-slate-900' : 'text-rose-600' }}">
                Rp {{ number_format($stats['saldoBersih'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Selisih pemasukan - pengeluaran</p>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        {{-- Search & Filter Bar --}}
        <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-wrap items-center gap-3">
                <div class="relative min-w-[220px] max-w-xs flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari ID, keterangan, unit..."
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
                    {{-- Filter Tipe --}}
                    <select
                        wire:model.live="filters.tipe"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-slate-800 focus:outline-none"
                    >
                        <option value="">Semua Tipe</option>
                        <option value="input">Pemasukan (Input)</option>
                        <option value="output">Pengeluaran (Output)</option>
                    </select>

                    {{-- Filter Unit --}}
                    @if (!auth()->user()->hasRole('admin_unit'))
                        <select
                            wire:model.live="filters.id_unit"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-slate-800 focus:outline-none"
                        >
                            <option value="">Semua Unit</option>
                            @foreach ($unitList as $unit)
                                <option value="{{ $unit->id_unit }}">{{ $unit->nama_unit }}</option>
                            @endforeach
                        </select>
                    @endif

                    {{-- Filter BUMDes (Khusus Super Admin) --}}
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

                    {{-- Filter Tanggal Mulai & Akhir --}}
                    <div class="flex items-center gap-1">
                        <input
                            type="date"
                            wire:model.live="filters.tanggal_mulai"
                            class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-slate-700 focus:border-slate-800 focus:outline-none"
                            title="Tanggal Mulai"
                        >
                        <span class="text-xs text-slate-400">-</span>
                        <input
                            type="date"
                            wire:model.live="filters.tanggal_akhir"
                            class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-slate-700 focus:border-slate-800 focus:outline-none"
                            title="Tanggal Akhir"
                        >
                    </div>

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
                            <button wire:click="sortBy('id_transaksi')" class="flex items-center gap-1 hover:text-slate-900">
                                ID Transaksi
                                @if ($sortField === 'id_transaksi')
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
                            <button wire:click="sortBy('tanggal')" class="flex items-center gap-1 hover:text-slate-900">
                                Tanggal
                                @if ($sortField === 'tanggal')
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
                        <th class="px-4 py-3 text-left">Unit Usaha</th>
                        <th class="px-4 py-3 text-center">Tipe</th>
                        <th class="px-4 py-3 text-right">
                            <button wire:click="sortBy('jumlah')" class="flex items-center justify-end gap-1 hover:text-slate-900 ml-auto">
                                Jumlah (IDR)
                                @if ($sortField === 'jumlah')
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
                        <th class="px-4 py-3 text-left">Keterangan</th>
                        <th class="px-4 py-3 text-left">Dicatat Oleh</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $trx)
                        <tr class="hover:bg-slate-50/60 transition-colors" wire:key="trx-{{ $trx->id_transaksi }}">
                            <td class="px-4 py-3">
                                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs font-mono text-slate-700">{{ $trx->id_transaksi }}</code>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($trx->tanggal)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <div>{{ $trx->unit?->nama_unit ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $trx->unit?->bumdes?->nama_bumdes ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($trx->tipe === 'input')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                        Pemasukan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                        Pengeluaran
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold whitespace-nowrap {{ $trx->tipe === 'input' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $trx->tipe === 'input' ? '+' : '-' }} Rp {{ number_format((float) $trx->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 max-w-xs truncate" title="{{ $trx->detail['keterangan'] ?? '-' }}">
                                <span>{{ $trx->detail['keterangan'] ?? '-' }}</span>
                                @if (!empty($trx->detail['kategori']))
                                    <span class="ml-1 inline-flex rounded bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-500">{{ $trx->detail['kategori'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                {{ $trx->pencatat?->nama ?? $trx->dicatat_oleh }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button
                                        type="button"
                                        wire:click="openDetailModal('{{ $trx->id_transaksi }}')"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                        title="Detail Transaksi"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Detail
                                    </button>
                                    @can('update', $trx)
                                        <button
                                            type="button"
                                            wire:click="openEditModal('{{ $trx->id_transaksi }}')"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                            title="Edit Transaksi"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                <p>Tidak ada transaksi keuangan ditemukan.</p>
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
                        {{ $isEdit ? 'Edit Transaksi Keuangan' : 'Catat Transaksi Baru' }}
                    </h3>
                    <button wire:click="closeModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form wire:submit="save" class="space-y-4 p-6">
                    @if (!$isEdit)
                        {{-- Unit Usaha --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-id_unit">Unit Usaha <span class="text-rose-500">*</span></label>
                            <select
                                id="modal-id_unit"
                                wire:model="id_unit"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800 @error('id_unit') border-rose-500 @enderror"
                                {{ auth()->user()->hasRole('admin_unit') ? 'disabled' : '' }}
                            >
                                <option value="">-- Pilih Unit Usaha --</option>
                                @foreach ($unitList as $unit)
                                    <option value="{{ $unit->id_unit }}">{{ $unit->nama_unit }} ({{ $unit->bumdes?->nama_bumdes ?? '' }})</option>
                                @endforeach
                            </select>
                            @error('id_unit') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Tipe Transaksi --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">Tipe Transaksi <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-3 text-sm font-medium transition-all {{ $tipe === 'input' ? 'border-emerald-600 bg-emerald-50/50 text-emerald-800' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                <input type="radio" wire:model.live="tipe" value="input" class="sr-only">
                                <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                <span>Pemasukan</span>
                            </label>
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-3 text-sm font-medium transition-all {{ $tipe === 'output' ? 'border-rose-600 bg-rose-50/50 text-rose-800' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                <input type="radio" wire:model.live="tipe" value="output" class="sr-only">
                                <svg class="h-4 w-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                <span>Pengeluaran</span>
                            </label>
                        </div>
                        @error('tipe') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Jumlah & Tanggal --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-jumlah">Nominal (IDR) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-semibold text-slate-400">Rp</span>
                                <input
                                    id="modal-jumlah"
                                    type="number"
                                    step="0.01"
                                    min="1"
                                    wire:model="jumlah"
                                    placeholder="0"
                                    class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm font-mono placeholder-slate-400 focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800 @error('jumlah') border-rose-500 @enderror"
                                >
                            </div>
                            @error('jumlah') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-tanggal">Tanggal <span class="text-rose-500">*</span></label>
                            <input
                                id="modal-tanggal"
                                type="date"
                                wire:model="tanggal"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800 @error('tanggal') border-rose-500 @enderror"
                            >
                            @error('tanggal') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-kategori">Kategori (Opsional)</label>
                        <input
                            id="modal-kategori"
                            type="text"
                            wire:model="kategori"
                            placeholder="Contoh: Operasional, Gaji, Penjualan, Pemeliharaan"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm placeholder-slate-400 focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800 @error('kategori') border-rose-500 @enderror"
                        >
                        @error('kategori') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Keterangan / Deskripsi --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-keterangan">Keterangan / Rincian <span class="text-rose-500">*</span></label>
                        <textarea
                            id="modal-keterangan"
                            wire:model="keterangan"
                            rows="3"
                            placeholder="Deskripsi transaksi atau peruntukan biaya..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm placeholder-slate-400 focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800 @error('keterangan') border-rose-500 @enderror"
                        ></textarea>
                        @error('keterangan') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
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
                            {{ $isEdit ? 'Simpan Perubahan' : 'Catat Transaksi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ==================== MODAL DETAIL ==================== --}}
    @if ($showDetailModal && $detailTransaksi)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="closeDetailModal"
        >
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Detail Transaksi</h3>
                        <code class="text-xs font-mono text-slate-500">{{ $detailTransaksi->id_transaksi }}</code>
                    </div>
                    <button wire:click="closeDetailModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="space-y-4 p-6 text-sm">
                    <div class="grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-4">
                        <div>
                            <span class="block text-xs text-slate-500">Tipe Transaksi</span>
                            <span class="font-semibold {{ $detailTransaksi->tipe === 'input' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $detailTransaksi->tipe === 'input' ? 'Pemasukan (Input)' : 'Pengeluaran (Output)' }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500">Jumlah Nominal</span>
                            <span class="font-mono text-base font-bold {{ $detailTransaksi->tipe === 'input' ? 'text-emerald-700' : 'text-rose-700' }}">
                                Rp {{ number_format((float) $detailTransaksi->jumlah, 0, ',', '.') }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500">Tanggal Transaksi</span>
                            <span class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($detailTransaksi->tanggal)->translatedFormat('d F Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500">Waktu Pencatatan</span>
                            <span class="text-xs text-slate-700">{{ $detailTransaksi->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Unit Usaha & BUMDes</span>
                        <p class="mt-1 font-medium text-slate-900">{{ $detailTransaksi->unit?->nama_unit ?? '-' }}</p>
                        <p class="text-xs text-slate-500">{{ $detailTransaksi->unit?->bumdes?->nama_bumdes ?? '-' }}</p>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Dicatat Oleh</span>
                        <p class="mt-1 text-slate-900">{{ $detailTransaksi->pencatat?->nama ?? $detailTransaksi->dicatat_oleh }}</p>
                        <p class="text-xs text-slate-500">Username: {{ $detailTransaksi->pencatat?->username ?? '-' }} (ID: {{ $detailTransaksi->dicatat_oleh }})</p>
                    </div>

                    @if (!empty($detailTransaksi->detail))
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Rincian & Keterangan</span>
                            <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50/50 p-3 text-xs text-slate-700">
                                <p class="font-medium text-slate-900">{{ $detailTransaksi->detail['keterangan'] ?? '-' }}</p>
                                @if (!empty($detailTransaksi->detail['kategori']))
                                    <p class="mt-1 text-slate-500">Kategori: <span class="font-medium text-slate-700">{{ $detailTransaksi->detail['kategori'] }}</span></p>
                                @endif
                                @if (!empty($detailTransaksi->detail['jenis_unit']))
                                    <p class="mt-0.5 text-slate-500">Jenis Unit: <span class="font-medium text-slate-700">{{ $detailTransaksi->detail['jenis_unit'] }}</span></p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end pt-2">
                        <button
                            type="button"
                            wire:click="closeDetailModal"
                            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
