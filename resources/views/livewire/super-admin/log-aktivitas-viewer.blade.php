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
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Log Audit Aktivitas Sistem</h2>
            <p class="text-sm text-slate-500">Histori audit trail interaksi akun, mutasi data, dan event keamanan sistem.</p>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        {{-- Search & Filter Bar --}}
        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100">
            <div class="flex flex-1 flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[220px] max-w-md">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari deskripsi, modul, ID pelaku..."
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
                    <select
                        wire:model.live="filters.log_name"
                        class="rounded-lg border border-slate-200 py-2 px-3 text-xs focus:border-slate-800 focus:outline-none font-mono"
                    >
                        <option value="">Semua Modul</option>
                        @foreach ($logNames as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>

                    <select
                        wire:model.live="filters.event"
                        class="rounded-lg border border-slate-200 py-2 px-3 text-xs focus:border-slate-800 focus:outline-none"
                    >
                        <option value="">Semua Aksi</option>
                        @foreach ($events as $ev)
                            <option value="{{ $ev }}">{{ $ev }}</option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span>Dari:</span>
                        <input
                            type="date"
                            wire:model.live="filters.dari_tanggal"
                            class="rounded-lg border border-slate-200 py-1.5 px-2 text-xs focus:border-slate-800 focus:outline-none"
                        >
                    </div>

                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span>Sampai:</span>
                        <input
                            type="date"
                            wire:model.live="filters.sampai_tanggal"
                            class="rounded-lg border border-slate-200 py-1.5 px-2 text-xs focus:border-slate-800 focus:outline-none"
                        >
                    </div>

                    @if ($search || !empty(array_filter($filters)))
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
                    <option value="100">100</option>
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
                            <button type="button" wire:click="sortBy('id')" class="inline-flex items-center gap-1 font-semibold hover:text-slate-900">
                                <span>ID</span>
                                @if ($sortField === 'id')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3.5">
                            <button type="button" wire:click="sortBy('created_at')" class="inline-flex items-center gap-1 font-semibold hover:text-slate-900">
                                <span>Waktu</span>
                                @if ($sortField === 'created_at')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3.5">Modul / Log</th>
                        <th scope="col" class="px-6 py-3.5">Aksi / Event</th>
                        <th scope="col" class="px-6 py-3.5">Pelaku (Causer)</th>
                        <th scope="col" class="px-6 py-3.5">Deskripsi</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs font-semibold text-slate-900">
                                #{{ $item->id }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">
                                {{ $item->created_at ? $item->created_at->format('d M Y H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-mono font-medium text-slate-700">
                                    {{ $item->log_name ?? 'default' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badgeColor = match ($item->event) {
                                        'created' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'updated' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'deleted' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'login' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'logout' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium capitalize {{ $badgeColor }}">
                                    {{ $item->event ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if ($item->causer)
                                    <div class="font-medium text-slate-900">{{ $item->causer->nama }}</div>
                                    <div class="text-slate-400 font-mono">{{ $item->causer->username }} ({{ $item->causer_id }})</div>
                                @elseif ($item->causer_id)
                                    <div class="font-mono text-slate-600">{{ $item->causer_id }}</div>
                                @else
                                    <span class="text-slate-400 italic">Sistem (Otomatis)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ $item->description }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    wire:click="showDetail({{ $item->id }})"
                                    class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                                >
                                    <span>Payload JSON</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <p class="text-sm font-medium text-slate-600">Tidak ada data log aktivitas ditemukan.</p>
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

    {{-- Modal Detail Log JSON --}}
    @if ($showDetailModal && $selectedLogData)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            Detail Log Aktivitas #{{ $selectedLogData['id'] }}
                        </h3>
                        <p class="text-xs text-slate-500">Direkam pada {{ $selectedLogData['created_at'] }}</p>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4 rounded-lg bg-slate-50 p-3 text-xs">
                        <div>
                            <span class="text-slate-400">Modul/Log:</span>
                            <span class="font-mono font-semibold text-slate-700">{{ $selectedLogData['log_name'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Aksi/Event:</span>
                            <span class="font-semibold capitalize text-slate-700">{{ $selectedLogData['event'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Pelaku (Causer):</span>
                            <span class="font-semibold text-slate-700">{{ $selectedLogData['causer_name'] }} ({{ $selectedLogData['causer_id'] }})</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Subject:</span>
                            <span class="font-mono text-slate-700">{{ $selectedLogData['subject_type'] }} #{{ $selectedLogData['subject_id'] }}</span>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-700 mb-1">Deskripsi</div>
                        <div class="rounded-lg border border-slate-200 bg-white p-3 text-sm text-slate-800">
                            {{ $selectedLogData['description'] }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-700 mb-1">Properties / Changes Payload (JSON)</div>
                        <pre class="rounded-lg bg-slate-900 p-4 text-xs font-mono text-emerald-400 overflow-x-auto max-h-72"><code>{{ json_encode($selectedLogData['properties'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 pt-4">
                        <button
                            type="button"
                            wire:click="closeDetailModal"
                            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
