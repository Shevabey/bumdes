<div class="space-y-4">
    @if ($title)
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-800">{{ $title }}</h2>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        {{-- Search & Controls Bar --}}
        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100">
            <div class="flex flex-1 items-center gap-3">
                <div class="relative flex-1 max-w-md">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ $placeholder }}"
                        class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-8 text-sm placeholder-slate-400 focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800"
                    >
                    @if ($search)
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>

                @if ($search || !empty(array_filter($filters)))
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100"
                    >
                        <span>Reset</span>
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span>Tampilkan</span>
                    <select
                        wire:model.live="perPage"
                        class="rounded-lg border border-slate-200 py-1.5 px-2 text-xs focus:border-slate-800 focus:outline-none"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>baris</span>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        @foreach ($columns as $col)
                            <th scope="col" class="px-6 py-3.5">
                                @if (!empty($col['sortable']))
                                    <button
                                        type="button"
                                        wire:click="sortBy('{{ $col['key'] }}')"
                                        class="group inline-flex items-center gap-1 font-semibold hover:text-slate-900"
                                    >
                                        <span>{{ $col['label'] }}</span>
                                        <span class="text-slate-400 group-hover:text-slate-700">
                                            @if ($sortField === $col['key'])
                                                @if ($sortDirection === 'asc')
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                                @else
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                @endif
                                            @else
                                                <svg class="h-3.5 w-3.5 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                            @endif
                                        </span>
                                    </button>
                                @else
                                    {{ $col['label'] }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            @foreach ($columns as $col)
                                <td class="px-6 py-4">
                                    {{ data_get($row, $col['key'], '-') }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ max(count($columns), 1) }}" class="px-6 py-12 text-center">
                                <div class="mx-auto flex flex-col items-center justify-center text-slate-400">
                                    <svg class="h-10 w-10 stroke-current mb-2 opacity-60" fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-sm font-medium text-slate-600">{{ $emptyMessage }}</p>
                                    @if ($search)
                                        <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian Anda.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if ($rows->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
</div>
