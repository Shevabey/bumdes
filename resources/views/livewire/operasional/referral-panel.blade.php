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
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Program Referral BUMDes</h2>
            <p class="text-sm text-slate-500">Inisiatif kolaborasi lintas BUMDes. Dapatkan insentif kas Rp 10.000 dengan membagikan atau mengklaim kode referral dan menjaga keaktifan operasional.</p>
        </div>
    </div>

    {{-- Hero Section: Kode Aktif & Form Redeem --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Card 1: Kode Referral Aktif BUMDes --}}
        <div class="rounded-xl border border-indigo-100 bg-gradient-to-br from-indigo-50/60 via-white to-indigo-50/30 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-700">Kode Referral BUMDes Anda</span>
                @if ($activeReferral)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                        Belum Ada / Kedaluwarsa
                    </span>
                @endif
            </div>

            @if ($activeReferral)
                <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-3xl font-extrabold tracking-wider text-slate-900 font-mono">{{ $activeReferral->kode_unik }}</div>
                        <p class="mt-1 text-xs text-slate-500">
                            Berlaku s/d: <span class="font-medium text-slate-700">{{ $activeReferral->tanggal_expired?->format('d/m/Y H:i') }}</span>
                            (5 hari masa aktif)
                        </p>
                    </div>
                    @can('generate', \App\Models\Referral::class)
                        <button
                            type="button"
                            wire:click="generateActiveCode"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-white px-3 py-2 text-xs font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50 disabled:opacity-50"
                        >
                            <svg wire:loading.remove wire:target="generateActiveCode" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <svg wire:loading wire:target="generateActiveCode" class="h-3.5 w-3.5 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Perbarui Kode</span>
                        </button>
                    @endcan
                </div>
            @else
                <div class="mt-4 flex flex-col items-start gap-3">
                    <p class="text-xs text-slate-500">Anda belum memiliki kode referral aktif atau kode sebelumnya sudah kedaluwarsa.</p>
                    @can('generate', \App\Models\Referral::class)
                        <button
                            type="button"
                            wire:click="generateActiveCode"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50"
                        >
                            <svg wire:loading wire:target="generateActiveCode" class="h-3.5 w-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Buat Kode Referral Baru</span>
                        </button>
                    @endcan
                </div>
            @endif
        </div>

        {{-- Card 2: Form Redeem Kode Referral --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-600">Klaim / Redeem Kode Referral</h3>
            <p class="mt-1 text-xs text-slate-500">Punya kode referral dari BUMDes mitra? Masukkan kode di bawah untuk mengaktifkan program insentif kas.</p>

            <form wire:submit.prevent="redeemCode" class="mt-4 space-y-3">
                <div class="flex gap-2">
                    <input
                        type="text"
                        wire:model="inputKodeRedeem"
                        placeholder="Contoh: ACT001"
                        class="block w-full uppercase font-mono rounded-lg border border-slate-200 px-3 py-2 text-sm placeholder-slate-400 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"
                    />
                    @can('redeem', \App\Models\Referral::class)
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                        >
                            <svg wire:loading wire:target="redeemCode" class="h-3.5 w-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Klaim Kode</span>
                        </button>
                    @endcan
                </div>
                @error('inputKodeRedeem')
                    <span class="block text-xs text-rose-600">{{ $message }}</span>
                @enderror
                <p class="text-[11px] text-slate-400">
                    * BUMDes tidak dapat mengklaim kode milik sendiri dan hanya dapat mengklaim program referral satu kali.
                </p>
            </form>
        </div>
    </div>

    {{-- Summary Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Aktif --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kode Aktif</span>
                <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-600">
                {{ $stats['countAktif'] }} <span class="text-xs font-normal text-slate-400">kode</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">Siap dibagikan ke BUMDes lain</p>
        </div>

        {{-- Pending --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pending (Verifikasi)</span>
                <span class="rounded-lg bg-amber-50 p-2 text-amber-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">
                {{ $stats['countPending'] }} <span class="text-xs font-normal text-slate-400">klaim</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">Masa tunggu 15 hari transaksi</p>
        </div>

        {{-- Cair --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Berhasil Cair</span>
                <span class="rounded-lg bg-blue-50 p-2 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-blue-600">
                {{ $stats['countCair'] }} <span class="text-xs font-normal text-slate-400">referral</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">Memenuhi syarat aktivitas</p>
        </div>

        {{-- Total Insentif Kas --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Kas Cair</span>
                <span class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-indigo-600">
                Rp {{ number_format($stats['totalInsentifCair'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-500">Insentif Rp 10.000 / referral cair</p>
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
                    placeholder="Cari kode referral, ID, nama BUMDes..."
                    class="block w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm placeholder-slate-400 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"
                />
            </div>

            {{-- Filter Status --}}
            <select
                wire:model.live="filters.status"
                class="rounded-lg border border-slate-200 py-2 pl-3 pr-8 text-sm text-slate-700 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"
            >
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="pending">Pending</option>
                <option value="cair">Cair</option>
                <option value="kedaluwarsa">Kedaluwarsa</option>
                <option value="gagal">Gagal</option>
            </select>

            {{-- Filter Relasi (hanya untuk pengurus BUMDes) --}}
            @if (auth()->user()->id_bumdes)
                <select
                    wire:model.live="filters.tipe_relasi"
                    class="rounded-lg border border-slate-200 py-2 pl-3 pr-8 text-sm text-slate-700 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"
                >
                    <option value="">Semua Relasi</option>
                    <option value="diajukan">Sebagai Pengaju</option>
                    <option value="diterima">Sebagai Penerima</option>
                </select>
            @endif

            {{-- Filter BUMDes (untuk Super Admin) --}}
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
                        <th scope="col" class="px-6 py-3">Kode Unik</th>
                        <th scope="col" class="px-6 py-3">ID Referral</th>
                        <th scope="col" class="px-6 py-3">BUMDes Pengaju</th>
                        <th scope="col" class="px-6 py-3">BUMDes Penerima</th>
                        <th scope="col" class="cursor-pointer px-6 py-3 hover:text-slate-700" wire:click="sortBy('tanggal_generate')">
                            <div class="flex items-center gap-1">
                                <span>Tgl Generate</span>
                                @if ($sortField === 'tanggal_generate')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">Batas Verifikasi</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($referrals as $item)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 font-mono font-bold text-slate-900">
                                <span class="rounded bg-slate-100 px-2 py-1">{{ $item->kode_unik }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 font-mono text-xs text-slate-500">
                                {{ $item->id_referral }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $item->pengaju?->nama_bumdes ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $item->id_bumdes_pengaju }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($item->penerima)
                                    <div class="font-medium text-slate-900">{{ $item->penerima->nama_bumdes }}</div>
                                    <div class="text-xs text-slate-400">{{ $item->id_bumdes_penerima }}</div>
                                @else
                                    <span class="italic text-slate-400">Belum di-redeem</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs">
                                <div>{{ $item->tanggal_generate?->format('d/m/Y') }}</div>
                                <div class="text-slate-400">Exp: {{ $item->tanggal_expired?->format('d/m/Y') }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs">
                                @if ($item->batas_verifikasi)
                                    <div>{{ $item->batas_verifikasi->format('d/m/Y') }}</div>
                                    @if ($item->status === 'pending')
                                        <div class="text-amber-600 font-medium">Dalam verifikasi</div>
                                    @endif
                                @elseif ($item->tanggal_cair)
                                    <div class="text-emerald-600 font-medium">Cair: {{ $item->tanggal_cair->format('d/m/Y') }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <x-status-badge :status="$item->status" />
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-xs">
                                @can('view', $item)
                                    <button
                                        type="button"
                                        wire:click="openDetailModal('{{ $item->id_referral }}')"
                                        class="inline-flex items-center gap-1 rounded-md bg-slate-50 px-2.5 py-1.5 font-medium text-slate-700 hover:bg-slate-100 focus:outline-none"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Detail</span>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <p class="mt-2 text-sm font-medium text-slate-900">Belum ada riwayat referral</p>
                                <p class="text-xs text-slate-400">Bagikan kode referral BUMDes Anda atau redeem kode dari mitra.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($referrals->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $referrals->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Detail Referral --}}
    @if ($showDetailModal && $selectedReferralDetail)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-bold text-slate-900">Detail Alur Referral</h3>
                    <button type="button" wire:click="closeDetailModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4 text-xs text-slate-600">
                    {{-- Header Identitas --}}
                    <div class="rounded-lg bg-slate-50 p-4 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Kode Unik:</span>
                            <span class="rounded bg-white px-2 py-0.5 font-mono text-base font-extrabold text-slate-900 border">{{ $selectedReferralDetail->kode_unik }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">ID Referral:</span>
                            <span class="font-mono text-slate-800">{{ $selectedReferralDetail->id_referral }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-slate-400">Status Saat Ini:</span>
                            <x-status-badge :status="$selectedReferralDetail->status" />
                        </div>
                    </div>

                    {{-- Data Relasi --}}
                    <div class="grid grid-cols-2 gap-3 rounded-lg border border-slate-200 p-4">
                        <div>
                            <span class="text-slate-400 block text-[11px]">BUMDes Pengaju</span>
                            <span class="font-medium text-slate-800">{{ $selectedReferralDetail->pengaju?->nama_bumdes ?? '-' }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ $selectedReferralDetail->id_bumdes_pengaju }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">BUMDes Penerima</span>
                            @if ($selectedReferralDetail->penerima)
                                <span class="font-medium text-slate-800">{{ $selectedReferralDetail->penerima->nama_bumdes }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $selectedReferralDetail->id_bumdes_penerima }}</span>
                            @else
                                <span class="italic text-slate-400">Belum di-redeem</span>
                            @endif
                        </div>
                    </div>

                    {{-- Timeline Lifecycle --}}
                    <div class="rounded-lg border border-slate-200 p-4 space-y-2.5">
                        <p class="font-semibold uppercase tracking-wider text-slate-500 text-[10px]">Kronologi Program (Siklus 15 Hari)</p>
                        
                        <div class="flex justify-between">
                            <span class="text-slate-400">1. Tanggal Dibuat:</span>
                            <span class="text-slate-800 font-medium">{{ $selectedReferralDetail->tanggal_generate?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">2. Batas Kedaluwarsa Kode:</span>
                            <span class="text-slate-800 font-medium">{{ $selectedReferralDetail->tanggal_expired?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">3. Tanggal Di-redeem:</span>
                            <span class="text-slate-800 font-medium">{{ $selectedReferralDetail->tanggal_redeem?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">4. Batas Verifikasi Aktivitas:</span>
                            <span class="text-slate-800 font-medium">{{ $selectedReferralDetail->batas_verifikasi?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-2 font-semibold">
                            <span class="text-slate-700">5. Pencairan Kas (+Rp 10.000):</span>
                            @if ($selectedReferralDetail->status === 'cair')
                                <span class="text-emerald-600">Cair pada {{ $selectedReferralDetail->tanggal_cair?->format('d/m/Y H:i') }}</span>
                            @elseif ($selectedReferralDetail->status === 'gagal')
                                <span class="text-rose-600">Gagal (tidak ada aktivitas)</span>
                            @elseif ($selectedReferralDetail->status === 'pending')
                                <span class="text-amber-600">Menunggu verifikasi transaksi</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </div>
                    </div>
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
