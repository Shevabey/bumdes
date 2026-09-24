<div>
    {{-- ═══════════════════════════════════════════════════════════════
         HEADER PAGE
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mb-8 flex items-start justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard Monitoring Nasional</h2>
            <p class="mt-1 text-sm text-slate-500">Ringkasan kinerja seluruh BUMDes • Diperbarui setiap halaman dimuat</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ now()->format('d M Y, H:i') }} WIB
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         BARIS 1 — 4 KARTU INDIKATOR OTOMATIS NASIONAL
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {{-- BUMDes Aktif --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $this->indikatorNasional['total_bumdes_aktif'] }}</p>
                    <p class="text-xs font-medium text-slate-500">BUMDes Aktif</p>
                </div>
            </div>
        </div>

        {{-- BUMDes Menunggak Iuran --}}
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-amber-700">{{ $this->indikatorNasional['bumdes_menunggak_iuran'] }}</p>
                    <p class="text-xs font-medium text-amber-600">Menunggak Iuran</p>
                </div>
            </div>
        </div>

        {{-- Unit Tanpa Aktivitas --}}
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-100">
                    <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-rose-700">{{ $this->indikatorNasional['unit_tanpa_aktivitas'] }}</p>
                    <p class="text-xs font-medium text-rose-600">Unit Tanpa Aktivitas</p>
                </div>
            </div>
        </div>

        {{-- Verifikasi Tertunda --}}
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-blue-700">{{ $this->indikatorNasional['verifikasi_tertunda'] }}</p>
                    <p class="text-xs font-medium text-blue-600">Verifikasi Tertunda</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         BARIS 2 — 4 KARTU KEUANGAN KONSOLIDASIAN NASIONAL
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {{-- Saldo Kas Nasional --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Saldo Kas Nasional</p>
            <p class="mt-2 text-xl font-bold text-slate-900">
                Rp {{ number_format($this->keuanganNasional['kas_nasional'], 0, ',', '.') }}
            </p>
        </div>

        {{-- Total Omzet (Input) --}}
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-500">Total Omzet (Input)</p>
            <p class="mt-2 text-xl font-bold text-emerald-700">
                Rp {{ number_format($this->keuanganNasional['total_input'], 0, ',', '.') }}
            </p>
        </div>

        {{-- Total Pengeluaran (Output) --}}
        <div class="rounded-xl border border-rose-100 bg-rose-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-500">Total Pengeluaran (Output)</p>
            <p class="mt-2 text-xl font-bold text-rose-700">
                Rp {{ number_format($this->keuanganNasional['total_output'], 0, ',', '.') }}
            </p>
        </div>

        {{-- Laba / Rugi Bersih Konsolidasian --}}
        <div class="rounded-xl border {{ $this->keuanganNasional['untung_rugi'] >= 0 ? 'border-teal-200 bg-teal-50' : 'border-red-200 bg-red-50' }} p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider {{ $this->keuanganNasional['untung_rugi'] >= 0 ? 'text-teal-500' : 'text-red-500' }}">
                Laba / Rugi Bersih Nasional
            </p>
            <p class="mt-2 text-xl font-bold {{ $this->keuanganNasional['untung_rugi'] >= 0 ? 'text-teal-700' : 'text-red-700' }}">
                {{ $this->keuanganNasional['untung_rugi'] >= 0 ? '+' : '' }}Rp {{ number_format($this->keuanganNasional['untung_rugi'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         BARIS 3 — FILTER & SEARCH BAR
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input
                id="dashboard-search"
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama BUMDes, kode, atau nama desa..."
                class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm focus:border-slate-400 focus:outline-none"
            />
        </div>

        <select id="filter-periode" wire:model.live="filters.periode" class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none">
            <option value="bulanan">Bulan Ini</option>
            <option value="mingguan">Minggu Ini</option>
            <option value="semua">Semua Periode</option>
        </select>

        <select id="filter-status-iuran" wire:model.live="filters.status_iuran" class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none">
            <option value="">Semua Status Iuran</option>
            <option value="lunas">Lunas</option>
            <option value="belum_bayar">Belum Bayar</option>
            <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
        </select>

        <button
            id="btn-reset-filters"
            wire:click="resetFilters"
            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900"
        >
            Reset
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         BARIS 4 — TABEL INTERAKTIF PERFORMA BUMDES
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">BUMDes</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Wilayah</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Status Iuran</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Saldo Kas</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Omzet</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Pengeluaran</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Laba / Rugi</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Unit Aktif</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($bumdesRows as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-slate-900">{{ $row['nama_bumdes'] }}</div>
                                <div class="text-xs text-slate-400">{{ $row['id_bumdes'] }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $row['nama_kelurahan'] }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusIuran = $row['status_iuran'];
                                    $labelIuran = match($statusIuran) {
                                        'lunas' => 'Lunas',
                                        'menunggu_verifikasi' => 'Menunggu',
                                        'belum_bayar' => 'Belum Bayar',
                                        default => 'Belum Ada',
                                    };
                                @endphp
                                <x-status-badge :status="$statusIuran" :label="$labelIuran" />
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-slate-700">
                                Rp {{ number_format($row['kas'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-emerald-700">
                                Rp {{ number_format($row['total_input'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-rose-700">
                                Rp {{ number_format($row['total_output'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-semibold {{ $row['untung_rugi'] >= 0 ? 'text-teal-700' : 'text-red-600' }}">
                                    {{ $row['untung_rugi'] >= 0 ? '+' : '' }}Rp {{ number_format($row['untung_rugi'], 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-slate-700">
                                {{ $row['units_count'] }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button
                                    wire:click="showUnitDetail('{{ $row['id_bumdes'] }}', '{{ addslashes($row['nama_bumdes']) }}')"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                                >
                                    Rincian Unit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-slate-400">
                                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-sm font-medium">Tidak ada BUMDes yang sesuai filter</p>
                                    <button wire:click="resetFilters" class="text-xs text-slate-500 underline hover:text-slate-700">Reset filter</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($bumdesRows->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $bumdesRows->links() }}
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         MODAL RINCIAN UNIT USAHA
    ═══════════════════════════════════════════════════════════════ --}}
    @if ($showUnitModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            wire:click.self="closeUnitModal"
        >
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Rincian Unit Usaha</h3>
                        <p class="text-sm text-slate-500">{{ $selectedBumdesName }}</p>
                    </div>
                    <button wire:click="closeUnitModal" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Unit Usaha</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Omzet</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Pengeluaran</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Laba / Rugi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($selectedBumdesUnits as $unit)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-3">
                                        <div class="text-sm font-medium text-slate-900">{{ $unit['nama_unit'] }}</div>
                                        <div class="text-xs text-slate-400">{{ $unit['id_unit'] }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm text-emerald-700">
                                        Rp {{ number_format($unit['total_input'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm text-rose-700">
                                        Rp {{ number_format($unit['total_output'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <span class="text-sm font-semibold {{ $unit['untung_rugi'] >= 0 ? 'text-teal-700' : 'text-red-600' }}">
                                            {{ $unit['untung_rugi'] >= 0 ? '+' : '' }}Rp {{ number_format($unit['untung_rugi'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach

                            @if (empty($selectedBumdesUnits))
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400">Tidak ada unit usaha ditemukan.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end border-t border-slate-100 px-6 py-4">
                    <button
                        id="btn-close-unit-modal"
                        wire:click="closeUnitModal"
                        class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
