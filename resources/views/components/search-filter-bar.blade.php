@props([
    'placeholder' => 'Cari data...',
    'model' => 'search',
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 bg-white']) }}>
    <div class="flex flex-1 flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[240px] max-w-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input
                type="text"
                wire:model.live.debounce.300ms="{{ $model }}"
                placeholder="{{ $placeholder }}"
                class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-8 text-sm placeholder-slate-400 focus:border-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-800"
            >
        </div>

        @if (isset($filters))
            <div class="flex items-center gap-2">
                {{ $filters }}
            </div>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
