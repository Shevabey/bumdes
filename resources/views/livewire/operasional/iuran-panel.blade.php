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
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Manajemen Iuran BUMDes</h2>
            <p class="text-sm text-slate-500">Pengelolaan iuran rutin bulanan BUMDes, pembayaran kas/luar kas, dan verifikasi oleh BUMDes Koordinator.</p>
        </div>
        @can('generate', \App\Models\IuranBumdes::class)
            <button
                type="button"
                wire:click="generateBulanan"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600 disabled:opacity-50"
            >
                <svg wire:loading.remove wire:target="generateBulanan" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <svg wire:loading wire:target="generateBulanan" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span>Generate Iuran Bulanan</span>
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
                {{ $stats['countBelumBayar'] }} <span class="text-xs font-normal text-slate-400">periode</span>
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
                {{ $stats['countMenunggu'] }} <span class="text-xs font-normal text-slate-400">pengajuan</span>
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
                {{ $stats['countLunas'] }} <span class="text-xs font-normal text-slate-400">terverifikasi</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">Rp {{ number_format($stats['nominalLunas'], 0, ',', '.') }}</p>
        </div>

        {{-- Total Iuran --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Nilai Iuran</span>
                <span class="rounded-lg bg-slate-100 p-2 text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-900">
                Rp {{ number_format($stats['totalNominal'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-500">Seluruh tagihan iuran terdaftar</p>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
            {{-- Search --}}
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari ID iuran, bulan, BUMDes..."
                    class="block w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm placeholder-slate-400 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"
                />
            </div>

            {{-- Filter Status --}}
            <select
                wire:model.live="filters.status"
                class="rounded-lg border border-slate-200 py-2 pl-3 pr-8 text-sm text-slate-700 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"
            >
                <option value="">Semua Status</option>
                <option value="belum_bayar">Belum Bayar</option>
                <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                <option value="lunas">Lunas</option>
            </select>

            {{-- Filter Bulan Tahun --}}
            <input
                type="text"
                wire:model.live="filters.bulan_tahun"
                placeholder="Format: YYYY-MM"
                class="rounded-lg border border-slate-200 py-2 px-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"
            />

            {{-- Filter BUMDes (jika ada opsi) --}}
            @if (!empty($bumdesOptions))
                <select
                    wire:model.live="filters.id_bumdes"
                    class="rounded-lg border border-slate-200 py-2 pl-3 pr-8 text-sm text-slate-700 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"
                >
                    <option value="">Semua BUMDes</option>
                    @foreach ($bumdesOptions as $b)
                        <option value="{{ $b->id_bumdes }}">{{ $b->nama_bumdes }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        {{-- Reset Filters --}}
        <div>
            <button
                type="button"
                wire:click="resetFilters"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 focus:outline-none sm:w-auto"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Reset</span>
            </button>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="cursor-pointer px-6 py-3 hover:text-slate-700" wire:click="sortBy('id_iuran')">
                            <div class="flex items-center gap-1">
                                <span>ID Iuran</span>
                                @if ($sortField === 'id_iuran')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">BUMDes</th>
                        <th scope="col" class="cursor-pointer px-6 py-3 hover:text-slate-700" wire:click="sortBy('bulan_tahun')">
                            <div class="flex items-center gap-1">
                                <span>Periode</span>
                                @if ($sortField === 'bulan_tahun')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">Nominal</th>
                        <th scope="col" class="px-6 py-3">Sumber & Metode</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Verifikator</th>
                        <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($iurans as $item)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 font-mono text-xs font-medium text-slate-900">
                                {{ $item->id_iuran }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $item->bumdes?->nama_bumdes ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $item->id_bumdes }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-800">
                                    {{ $item->bulan_tahun }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">
                                Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs">
                                @if ($item->sumber_dana || $item->metode_bayar)
                                    <div class="flex flex-col gap-1">
                                        <span class="capitalize text-slate-700">
                                            {{ str_replace('_', ' ', $item->sumber_dana ?? '-') }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-slate-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            <span class="capitalize">{{ $item->metode_bayar ?? '-' }}</span>
                                        </span>
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <x-status-badge :status="$item->status" />
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs">
                                @if ($item->verifikator)
                                    <div class="font-medium text-slate-800">{{ $item->verifikator->nama ?? $item->verifikator->username }}</div>
                                    <div class="text-slate-400">{{ $item->tanggal_verifikasi?->format('d/m/Y H:i') }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-xs">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Bayar Button --}}
                                    @can('bayar', $item)
                                        <button
                                            type="button"
                                            wire:click="openBayarModal('{{ $item->id_iuran }}')"
                                            class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2.5 py-1.5 font-medium text-indigo-700 hover:bg-indigo-100 focus:outline-none"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span>Bayar</span>
                                        </button>
                                    @endcan

                                    {{-- Verifikasi Button --}}
                                    @can('verifikasi', $item)
                                        <button
                                            type="button"
                                            wire:click="openVerifikasiModal('{{ $item->id_iuran }}')"
                                            class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1.5 font-medium text-blue-700 hover:bg-blue-100 focus:outline-none"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Verifikasi</span>
                                        </button>
                                    @endcan

                                    {{-- Detail Button --}}
                                    @can('view', $item)
                                        <button
                                            type="button"
                                            wire:click="openDetailModal('{{ $item->id_iuran }}')"
                                            class="inline-flex items-center gap-1 rounded-md bg-slate-50 px-2.5 py-1.5 font-medium text-slate-700 hover:bg-slate-100 focus:outline-none"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detail</span>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="mt-2 text-sm font-medium text-slate-900">Tidak ada data iuran BUMDes</p>
                                <p class="text-xs text-slate-400">Gunakan filter pencarian atau generate tagihan iuran baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($iurans->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $iurans->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Bayar Iuran --}}
    @if ($showBayarModal && $selectedIuranBayar)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-bold text-slate-900">Pembayaran Iuran BUMDes</h3>
                    <button type="button" wire:click="closeBayarModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Detail Iuran Singkat --}}
                <div class="mt-4 rounded-lg bg-slate-50 p-4 text-xs text-slate-600 space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-400">ID Iuran:</span>
                        <span class="font-mono font-semibold text-slate-800">{{ $selectedIuranBayar->id_iuran }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">BUMDes:</span>
                        <span class="font-medium text-slate-800">{{ $selectedIuranBayar->bumdes?->nama_bumdes }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Periode:</span>
                        <span class="font-medium text-slate-800">{{ $selectedIuranBayar->bulan_tahun }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-1.5 text-sm font-semibold text-slate-900">
                        <span>Jumlah Iuran:</span>
                        <span class="text-indigo-600">Rp {{ number_format($selectedIuranBayar->jumlah, 0, ',', '.') }}</span>
                    </div>
                </div>

                <form wire:submit.prevent="prosesBayar" class="mt-4 space-y-4">
                    {{-- Sumber Dana --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Sumber Dana</label>
                        <div class="mt-2 grid grid-cols-2 gap-3">
                            <label class="flex cursor-pointer items-center justify-between rounded-lg border p-3 text-sm {{ $sumber_dana === 'kas' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 font-semibold' : 'border-slate-200 text-slate-700' }}">
                                <span class="flex items-center gap-2">
                                    <input type="radio" wire:model.live="sumber_dana" value="kas" class="text-indigo-600 focus:ring-indigo-500">
                                    <span>Kas BUMDes</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-center justify-between rounded-lg border p-3 text-sm {{ $sumber_dana === 'luar_kas' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 font-semibold' : 'border-slate-200 text-slate-700' }}">
                                <span class="flex items-center gap-2">
                                    <input type="radio" wire:model.live="sumber_dana" value="luar_kas" class="text-indigo-600 focus:ring-indigo-500">
                                    <span>Luar Kas</span>
                                </span>
                            </label>
                        </div>
                        @if ($sumber_dana === 'kas')
                            <p class="mt-1 text-xs text-amber-600">
                                * Saldo kas BUMDes akan otomatis dipotong Rp {{ number_format($selectedIuranBayar->jumlah, 0, ',', '.') }} dan dicatat sebagai mutasi kas keluar.
                            </p>
                        @endif
                        @error('sumber_dana') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    {{-- Metode Bayar --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Metode Bayar</label>
                        <div class="mt-2 grid grid-cols-2 gap-3">
                            <label class="flex cursor-pointer items-center justify-between rounded-lg border p-3 text-sm {{ $metode_bayar === 'transfer' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 font-semibold' : 'border-slate-200 text-slate-700' }}">
                                <span class="flex items-center gap-2">
                                    <input type="radio" wire:model.live="metode_bayar" value="transfer" class="text-indigo-600 focus:ring-indigo-500">
                                    <span>Transfer Bank</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-center justify-between rounded-lg border p-3 text-sm {{ $metode_bayar === 'tunai' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 font-semibold' : 'border-slate-200 text-slate-700' }}">
                                <span class="flex items-center gap-2">
                                    <input type="radio" wire:model.live="metode_bayar" value="tunai" class="text-indigo-600 focus:ring-indigo-500">
                                    <span>Tunai</span>
                                </span>
                            </label>
                        </div>
                        @error('metode_bayar') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    {{-- Bukti Pembayaran (Wajib jika Transfer) --}}
                    @if ($metode_bayar === 'transfer')
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Bukti Transfer <span class="text-rose-500">*</span>
                            </label>

                            {{-- File Upload --}}
                            <input
                                type="file"
                                wire:model="bukti_file"
                                accept="image/*"
                                class="block w-full text-xs text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                            />
                            @error('bukti_file') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror

                            {{-- Preview jika ada file upload --}}
                            @if ($bukti_file)
                                <div class="mt-2">
                                    <p class="text-xs text-slate-400 mb-1">Preview Gambar:</p>
                                    <img src="{{ $bukti_file->temporaryUrl() }}" alt="Preview Bukti" class="h-32 rounded-lg border object-cover">
                                </div>
                            @endif

                            {{-- Alternatif URL --}}
                            <div class="pt-1">
                                <span class="text-xs text-slate-400">Atau masukkan URL bukti pembayaran:</span>
                                <input
                                    type="text"
                                    wire:model="bukti_pembayaran_url"
                                    placeholder="https://... atau /storage/bukti/..."
                                    class="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"
                                />
                                @error('bukti_pembayaran_url') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
                        <button
                            type="button"
                            wire:click="closeBayarModal"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            <svg wire:loading wire:target="prosesBayar" class="h-3.5 w-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Kirim Pembayaran</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Verifikasi Iuran (Koordinator) --}}
    @if ($showVerifikasiModal && $selectedIuranVerifikasi)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-bold text-slate-900">Verifikasi Pembayaran Iuran</h3>
                    <button type="button" wire:click="closeVerifikasiModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3 text-xs text-slate-600">
                    <div class="rounded-lg bg-slate-50 p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-400">ID Iuran:</span>
                            <span class="font-mono font-semibold text-slate-800">{{ $selectedIuranVerifikasi->id_iuran }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">BUMDes Pengirim:</span>
                            <span class="font-medium text-slate-800">{{ $selectedIuranVerifikasi->bumdes?->nama_bumdes }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Periode:</span>
                            <span class="font-medium text-slate-800">{{ $selectedIuranVerifikasi->bulan_tahun }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Jumlah:</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($selectedIuranVerifikasi->jumlah, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Sumber Dana:</span>
                            <span class="font-medium capitalize text-slate-800">{{ str_replace('_', ' ', $selectedIuranVerifikasi->sumber_dana ?? '-') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Metode Bayar:</span>
                            <span class="font-medium capitalize text-slate-800">{{ $selectedIuranVerifikasi->metode_bayar ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Tanggal Bayar:</span>
                            <span class="text-slate-800">{{ $selectedIuranVerifikasi->tanggal_bayar?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Bukti Pembayaran --}}
                    @if ($selectedIuranVerifikasi->bukti_pembayaran_url)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <p class="mb-2 font-semibold text-slate-700">Bukti Pembayaran:</p>
                            <a href="{{ $selectedIuranVerifikasi->bukti_pembayaran_url }}" target="_blank" class="block overflow-hidden rounded border border-slate-200 bg-slate-50 text-center hover:opacity-95">
                                <img src="{{ $selectedIuranVerifikasi->bukti_pembayaran_url }}" alt="Bukti Transfer" class="max-h-48 w-full object-contain p-2">
                                <span class="block border-t border-slate-200 bg-white py-1.5 text-center text-xs font-medium text-indigo-600 hover:underline">
                                    Buka Gambar Ukuran Penuh ↗
                                </span>
                            </a>
                        </div>
                    @else
                        <div class="rounded-lg bg-amber-50 p-3 text-amber-700">
                            Pembayaran tunai / tanpa bukti transfer digital terlampir.
                        </div>
                    @endif
                </div>

                {{-- Action Buttons Verifikasi --}}
                <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        wire:click="closeVerifikasiModal"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="prosesVerifikasi(false)"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Tolak Pembayaran</span>
                    </button>
                    <button
                        type="button"
                        wire:click="prosesVerifikasi(true)"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Setujui (Lunas)</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Detail Iuran --}}
    @if ($showDetailModal && $selectedIuranDetail)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-bold text-slate-900">Detail Iuran BUMDes</h3>
                    <button type="button" wire:click="closeDetailModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3 text-xs text-slate-600">
                    <div class="rounded-lg bg-slate-50 p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-400">ID Iuran:</span>
                            <span class="font-mono font-semibold text-slate-800">{{ $selectedIuranDetail->id_iuran }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">BUMDes:</span>
                            <span class="font-medium text-slate-800">{{ $selectedIuranDetail->bumdes?->nama_bumdes }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Kelurahan:</span>
                            <span class="text-slate-800">{{ $selectedIuranDetail->bumdes?->kelurahan?->nama_lengkap ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Periode:</span>
                            <span class="font-medium text-slate-800">{{ $selectedIuranDetail->bulan_tahun }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-1.5 text-sm font-semibold">
                            <span class="text-slate-700">Jumlah Tagihan:</span>
                            <span class="text-indigo-600">Rp {{ number_format($selectedIuranDetail->jumlah, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-slate-400">Status:</span>
                            <x-status-badge :status="$selectedIuranDetail->status" />
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 p-4 space-y-2">
                        <p class="font-semibold uppercase tracking-wider text-slate-500 text-[10px]">Data Pembayaran & Verifikasi</p>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Sumber Dana:</span>
                            <span class="capitalize text-slate-800">{{ str_replace('_', ' ', $selectedIuranDetail->sumber_dana ?? '-') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Metode Bayar:</span>
                            <span class="capitalize text-slate-800">{{ $selectedIuranDetail->metode_bayar ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Tanggal Bayar:</span>
                            <span class="text-slate-800">{{ $selectedIuranDetail->tanggal_bayar?->format('d/m/Y H:i:s') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Diverifikasi Oleh:</span>
                            <span class="text-slate-800">{{ $selectedIuranDetail->verifikator?->nama ?? $selectedIuranDetail->verifikator?->username ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Tanggal Verifikasi:</span>
                            <span class="text-slate-800">{{ $selectedIuranDetail->tanggal_verifikasi?->format('d/m/Y H:i:s') ?? '-' }}</span>
                        </div>
                    </div>

                    @if ($selectedIuranDetail->bukti_pembayaran_url)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <p class="mb-2 font-semibold text-slate-700">Bukti Pembayaran Terlampir:</p>
                            <a href="{{ $selectedIuranDetail->bukti_pembayaran_url }}" target="_blank" class="block overflow-hidden rounded border border-slate-200 bg-slate-50 text-center hover:opacity-95">
                                <img src="{{ $selectedIuranDetail->bukti_pembayaran_url }}" alt="Bukti Transfer" class="max-h-48 w-full object-contain p-2">
                            </a>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end border-t border-slate-100 pt-4">
                    <button
                        type="button"
                        wire:click="closeDetailModal"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
