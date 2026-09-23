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
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Manajemen Tagihan Pelanggan</h2>
            <p class="text-sm text-slate-500">Kelola tagihan layanan unit usaha, pencatatan kas tunai, dan verifikasi transfer pelanggan.</p>
        </div>
        @can('create', \App\Models\Tagihan::class)
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Tagihan Baru</span>
            </button>
        @endcan
    </div>

    {{-- Summary Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Belum Bayar --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Belum Bayar</span>
                <span class="rounded-lg bg-amber-50 p-2 text-amber-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">
                {{ $stats['countBelumBayar'] }} <span class="text-xs font-normal text-slate-400">tagihan</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">Rp {{ number_format($stats['nominalBelumBayar'], 0, ',', '.') }}</p>
        </div>

        {{-- Menunggu Verifikasi --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Menunggu Verifikasi</span>
                <span class="rounded-lg bg-blue-50 p-2 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-blue-600">
                {{ $stats['countMenunggu'] }} <span class="text-xs font-normal text-slate-400">transfer</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">Rp {{ number_format($stats['nominalMenunggu'], 0, ',', '.') }}</p>
        </div>

        {{-- Lunas --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Lunas</span>
                <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-600">
                {{ $stats['countLunas'] }} <span class="text-xs font-normal text-slate-400">tagihan</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">Rp {{ number_format($stats['nominalLunas'], 0, ',', '.') }}</p>
        </div>

        {{-- Total Tagihan --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Nilai Tagihan</span>
                <span class="rounded-lg bg-slate-100 p-2 text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-900">
                Rp {{ number_format($stats['totalNominal'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Akumulasi seluruh tagihan terpilih</p>
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
                        placeholder="Cari ID, pelanggan, unit..."
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
                    {{-- Filter Status --}}
                    <select
                        wire:model.live="filters.status"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-slate-800 focus:outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="belum_bayar">Belum Bayar</option>
                        <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                        <option value="lunas">Lunas</option>
                        <option value="ditolak">Ditolak</option>
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

                    {{-- Filter Tanggal Jatuh Tempo --}}
                    <div class="flex items-center gap-1">
                        <input
                            type="date"
                            wire:model.live="filters.jatuh_tempo_mulai"
                            class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-slate-700 focus:border-slate-800 focus:outline-none"
                            title="Jatuh Tempo Mulai"
                        >
                        <span class="text-xs text-slate-400">-</span>
                        <input
                            type="date"
                            wire:model.live="filters.jatuh_tempo_akhir"
                            class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-slate-700 focus:border-slate-800 focus:outline-none"
                            title="Jatuh Tempo Akhir"
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
                            <button wire:click="sortBy('id_tagihan')" class="flex items-center gap-1 hover:text-slate-900">
                                ID Tagihan
                                @if ($sortField === 'id_tagihan')
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
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Unit Usaha</th>
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
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('jatuh_tempo')" class="flex items-center gap-1 hover:text-slate-900">
                                Jatuh Tempo
                                @if ($sortField === 'jatuh_tempo')
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
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Metode</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $item)
                        @php
                            $isOverdue = in_array($item->status, ['belum_bayar', 'ditolak']) && \Carbon\Carbon::parse($item->jatuh_tempo)->isPast();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors {{ $isOverdue ? 'bg-amber-50/20' : '' }}" wire:key="tagihan-{{ $item->id_tagihan }}">
                            <td class="px-4 py-3">
                                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs font-mono text-slate-700">{{ $item->id_tagihan }}</code>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <div>{{ $item->pelanggan?->nama ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $item->id_pelanggan }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>{{ $item->unit?->nama_unit ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $item->unit?->bumdes?->nama_bumdes ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format((float) $item->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-slate-600 {{ $isOverdue ? 'text-rose-600 font-semibold' : '' }}">
                                    {{ \Carbon\Carbon::parse($item->jatuh_tempo)->translatedFormat('d M Y') }}
                                </div>
                                @if ($isOverdue)
                                    <div class="text-[10px] text-rose-500 font-medium">Lewat Jatuh Tempo</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if ($item->status === 'lunas')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Lunas
                                    </span>
                                @elseif ($item->status === 'menunggu_verifikasi')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                        <svg class="h-3 w-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Menunggu Verifikasi
                                    </span>
                                @elseif ($item->status === 'ditolak')
                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                                        Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if ($item->metode === 'tunai')
                                    <span class="inline-flex items-center rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">Tunai</span>
                                @elseif ($item->metode === 'transfer')
                                    <span class="inline-flex items-center rounded bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">Transfer</span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    {{-- Aksi Bayar Tunai --}}
                                    @can('bayarTunai', $item)
                                        @if (in_array($item->status, ['belum_bayar', 'ditolak']))
                                            <button
                                                type="button"
                                                wire:click="bayarTunai('{{ $item->id_tagihan }}')"
                                                wire:confirm="Lunasi tagihan {{ $item->id_tagihan }} sebesar Rp {{ number_format((float) $item->jumlah, 0, ',', '.') }} secara tunai?"
                                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-700 shadow-xs"
                                                title="Bayar Tunai"
                                            >
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                Bayar Tunai
                                            </button>
                                        @endif
                                    @endcan

                                    {{-- Aksi Verifikasi Bukti Transfer --}}
                                    @can('verifikasiTransfer', $item)
                                        @if ($item->status === 'menunggu_verifikasi')
                                            <button
                                                type="button"
                                                wire:click="openVerifikasiModal('{{ $item->id_tagihan }}')"
                                                class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-blue-700 shadow-xs"
                                                title="Verifikasi Bukti Transfer"
                                            >
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Verifikasi
                                            </button>
                                        @endif
                                    @endcan

                                    {{-- Detail --}}
                                    <button
                                        type="button"
                                        wire:click="openDetailModal('{{ $item->id_tagihan }}')"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                        title="Detail Tagihan"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>

                                    {{-- Edit --}}
                                    @can('update', $item)
                                        <button
                                            type="button"
                                            wire:click="openEditModal('{{ $item->id_tagihan }}')"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                            title="Edit Tagihan"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p>Tidak ada tagihan pelanggan ditemukan.</p>
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
                        {{ $isEdit ? 'Edit Tagihan Pelanggan' : 'Buat Tagihan Baru' }}
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
                            <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-tagihan-unit">Unit Usaha <span class="text-rose-500">*</span></label>
                            <select
                                id="modal-tagihan-unit"
                                wire:model.live="id_unit"
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

                        {{-- Pelanggan --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-tagihan-pelanggan">Pelanggan <span class="text-rose-500">*</span></label>
                            <select
                                id="modal-tagihan-pelanggan"
                                wire:model="id_pelanggan"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800 @error('id_pelanggan') border-rose-500 @enderror"
                                {{ empty($id_unit) ? 'disabled' : '' }}
                            >
                                <option value="">-- {{ empty($id_unit) ? 'Pilih Unit Terlebih Dahulu' : 'Pilih Pelanggan' }} --</option>
                                @foreach ($pelangganList as $pelanggan)
                                    <option value="{{ $pelanggan->id_pelanggan }}">{{ $pelanggan->nama }} ({{ $pelanggan->id_pelanggan }})</option>
                                @endforeach
                            </select>
                            @error('id_pelanggan') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Jumlah Nominal --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-tagihan-jumlah">Nominal Tagihan (IDR) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-semibold text-slate-400">Rp</span>
                            <input
                                id="modal-tagihan-jumlah"
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

                    {{-- Tanggal Jatuh Tempo --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700" for="modal-tagihan-tempo">Tanggal Jatuh Tempo <span class="text-rose-500">*</span></label>
                        <input
                            id="modal-tagihan-tempo"
                            type="date"
                            wire:model="jatuh_tempo"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800 @error('jatuh_tempo') border-rose-500 @enderror"
                        >
                        @error('jatuh_tempo') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
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
                            {{ $isEdit ? 'Simpan Perubahan' : 'Buat Tagihan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ==================== MODAL VERIFIKASI TRANSFER ==================== --}}
    @if ($showVerifikasiModal && $verifikasiTagihan)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="closeVerifikasiModal"
        >
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Verifikasi Bukti Transfer</h3>
                        <code class="text-xs font-mono text-slate-500">{{ $verifikasiTagihan->id_tagihan }}</code>
                    </div>
                    <button wire:click="closeVerifikasiModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="space-y-4 p-6 text-sm">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500">Nominal Tagihan:</span>
                            <span class="font-mono text-base font-bold text-slate-900">
                                Rp {{ number_format((float) $verifikasiTagihan->jumlah, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Pelanggan:</span>
                            <span class="font-medium text-slate-800">{{ $verifikasiTagihan->pelanggan?->nama }}</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Unit Usaha:</span>
                            <span class="text-slate-700">{{ $verifikasiTagihan->unit?->nama_unit }}</span>
                        </div>
                    </div>

                    {{-- Bukti Transfer Preview / Info --}}
                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Bukti Pembayaran</span>
                        <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50/50 p-4 text-center">
                            @if (!empty($verifikasiTagihan->bukti_transfer_url))
                                <div class="text-xs text-slate-600">
                                    <svg class="mx-auto mb-2 h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <code class="break-all font-mono text-[11px] text-slate-700">{{ $verifikasiTagihan->bukti_transfer_url }}</code>
                                </div>
                            @else
                                <p class="text-xs text-slate-400">Tidak ada lampiran URL bukti transfer</p>
                            @endif
                        </div>
                    </div>

                    {{-- Actions Approval --}}
                    <div class="grid grid-cols-2 gap-3 pt-3">
                        <button
                            type="button"
                            wire:click="prosesVerifikasiTransfer(false)"
                            wire:confirm="Tolak bukti transfer untuk tagihan ini?"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-100"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak Bukti
                        </button>
                        <button
                            type="button"
                            wire:click="prosesVerifikasiTransfer(true)"
                            wire:confirm="Setujui bukti transfer dan tandai tagihan ini LUNAS?"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui (Lunas)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== MODAL DETAIL ==================== --}}
    @if ($showDetailModal && $detailTagihan)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="closeDetailModal"
        >
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Detail Tagihan</h3>
                        <code class="text-xs font-mono text-slate-500">{{ $detailTagihan->id_tagihan }}</code>
                    </div>
                    <button wire:click="closeDetailModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="space-y-4 p-6 text-sm">
                    <div class="grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-4">
                        <div>
                            <span class="block text-xs text-slate-500">Status Pembayaran</span>
                            <span class="font-semibold {{ $detailTagihan->status === 'lunas' ? 'text-emerald-700' : ($detailTagihan->status === 'menunggu_verifikasi' ? 'text-blue-700' : 'text-amber-700') }}">
                                {{ ucwords(str_replace('_', ' ', $detailTagihan->status)) }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500">Nominal Tagihan</span>
                            <span class="font-mono text-base font-bold text-slate-900">
                                Rp {{ number_format((float) $detailTagihan->jumlah, 0, ',', '.') }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500">Jatuh Tempo</span>
                            <span class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($detailTagihan->jatuh_tempo)->translatedFormat('d F Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500">Metode Pembayaran</span>
                            <span class="font-medium text-slate-900">{{ $detailTagihan->metode ? ucwords($detailTagihan->metode) : '-' }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Pelanggan</span>
                        <p class="mt-1 font-medium text-slate-900">{{ $detailTagihan->pelanggan?->nama ?? '-' }}</p>
                        <p class="text-xs text-slate-500">Kontak: {{ $detailTagihan->pelanggan?->kontak ?? '-' }} (ID: {{ $detailTagihan->id_pelanggan }})</p>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Unit Usaha & BUMDes</span>
                        <p class="mt-1 font-medium text-slate-900">{{ $detailTagihan->unit?->nama_unit ?? '-' }}</p>
                        <p class="text-xs text-slate-500">{{ $detailTagihan->unit?->bumdes?->nama_bumdes ?? '-' }}</p>
                    </div>

                    @if ($detailTagihan->status === 'lunas' && $detailTagihan->diverifikasi_oleh)
                        <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 p-3">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-emerald-800">Verifikasi Pembayaran</span>
                            <p class="mt-1 text-xs text-emerald-900">Diverifikasi oleh: <span class="font-medium">{{ $detailTagihan->verifikator?->nama ?? $detailTagihan->diverifikasi_oleh }}</span></p>
                            @if ($detailTagihan->tanggal_verifikasi)
                                <p class="text-[11px] text-emerald-700">Waktu: {{ \Carbon\Carbon::parse($detailTagihan->tanggal_verifikasi)->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    @endif

                    @if (!empty($detailTagihan->bukti_transfer_url))
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Bukti Transfer</span>
                            <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 p-2.5">
                                <code class="break-all font-mono text-xs text-indigo-700">{{ $detailTagihan->bukti_transfer_url }}</code>
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
