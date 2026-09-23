<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Panel' }} | SIM-BUMDes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 flex-shrink-0 border-r border-slate-200 bg-white">
            <div class="flex h-16 items-center border-b border-slate-200 px-6">
                <a href="{{ route('panel.dashboard') }}" class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm font-bold text-white">B</span>
                    <span class="text-base font-bold tracking-tight text-slate-900">SIM-BUMDes</span>
                </a>
            </div>

            <nav class="space-y-6 p-4">
                {{-- Super Admin Menu --}}
                @role('super_admin')
                    <div>
                        <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Super Admin</p>
                        <div class="mt-2 space-y-1">
                            <a href="/super-admin/region" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>Wilayah (Region)</span>
                            </a>
                            <a href="/super-admin/bumdes" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>BUMDes</span>
                            </a>
                            <a href="/super-admin/akun" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>Manajemen Akun</span>
                            </a>
                            <a href="/super-admin/log-aktivitas" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>Log Aktivitas</span>
                            </a>
                        </div>
                    </div>
                @endrole

                {{-- BUMDes Operational Menu --}}
                @hasanyrole('admin_bumdes|sekretaris|bendahara|admin_unit|super_admin')
                    <div>
                        <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Operasional BUMDes</p>
                        <div class="mt-2 space-y-1">
                            <a href="/bumdes/unit" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>Unit Usaha</span>
                            </a>
                            <a href="/bumdes/pelanggan" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>Pelanggan</span>
                            </a>
                            <a href="/bumdes/transaksi" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>Transaksi Keuangan</span>
                            </a>
                            <a href="/bumdes/tagihan" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>Tagihan</span>
                            </a>
                            <a href="/bumdes/iuran" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>Iuran BUMDes</span>
                            </a>
                            <a href="/bumdes/referral" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>Program Referral</span>
                            </a>
                        </div>
                    </div>
                @endhasanyrole

                {{-- Monitoring Menu --}}
                @hasanyrole('pengawas|penasihat|direktur|super_admin')
                    <div>
                        <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Monitoring & Evaluasi</p>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('monitoring.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>Dashboard Nasional</span>
                            </a>
                            <a href="/monitoring/feedback" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                <span>Catatan & Feedback</span>
                            </a>
                        </div>
                    </div>
                @endhasanyrole
            </nav>
        </aside>

        {{-- Main Area --}}
        <div class="flex flex-1 flex-col">
            {{-- Top Header --}}
            <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-8">
                <div class="flex items-center gap-3">
                    <h1 class="text-lg font-semibold text-slate-900">{{ $header ?? $title ?? 'Panel' }}</h1>
                </div>

                @auth
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <div class="text-sm font-medium text-slate-800">{{ auth()->user()->nama ?? auth()->user()->username }}</div>
                            <div class="flex items-center justify-end gap-1.5 text-xs text-slate-500">
                                <span class="capitalize">{{ auth()->user()->roles->first()?->name ?? 'User' }}</span>
                                @if (auth()->user()->id_bumdes)
                                    <span>&bull;</span>
                                    <span>{{ auth()->user()->id_bumdes }}</span>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                            >
                                Keluar
                            </button>
                        </form>
                    </div>
                @endauth
            </header>

            {{-- Main Content Slot --}}
            <main class="flex-1 p-8">
                @if (session('success'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
