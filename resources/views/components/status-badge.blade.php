@props([
    'status' => '',
])

@php
    $normalized = strtolower(trim((string) $status));

    $config = match ($normalized) {
        'lunas', 'cair', 'selesai', 'aktif', '1', 'true' => [
            'bg' => 'bg-emerald-50',
            'text' => 'text-emerald-700',
            'border' => 'border-emerald-200',
            'dot' => 'bg-emerald-500',
            'label' => match ($normalized) {
                '1', 'true' => 'Aktif',
                default => ucfirst(str_replace('_', ' ', $normalized)),
            },
        ],
        'menunggu_verifikasi', 'sedang', 'pending' => [
            'bg' => 'bg-amber-50',
            'text' => 'text-amber-700',
            'border' => 'border-amber-200',
            'dot' => 'bg-amber-500',
            'label' => match ($normalized) {
                'menunggu_verifikasi' => 'Menunggu Verifikasi',
                'sedang' => 'Sedang Ditindaklanjuti',
                'pending' => 'Pending',
                default => ucfirst($normalized),
            },
        ],
        'belum_bayar', 'belum' => [
            'bg' => 'bg-orange-50',
            'text' => 'text-orange-700',
            'border' => 'border-orange-200',
            'dot' => 'bg-orange-500',
            'label' => match ($normalized) {
                'belum_bayar' => 'Belum Bayar',
                'belum' => 'Belum Ditindaklanjuti',
                default => ucfirst($normalized),
            },
        ],
        'ditolak', 'gagal', 'nonaktif', '0', 'false', 'kedaluwarsa' => [
            'bg' => 'bg-rose-50',
            'text' => 'text-rose-700',
            'border' => 'border-rose-200',
            'dot' => 'bg-rose-500',
            'label' => match ($normalized) {
                '0', 'false' => 'Nonaktif',
                default => ucfirst(str_replace('_', ' ', $normalized)),
            },
        ],
        'input' => [
            'bg' => 'bg-blue-50',
            'text' => 'text-blue-700',
            'border' => 'border-blue-200',
            'dot' => 'bg-blue-500',
            'label' => 'Input / Masuk',
        ],
        'output' => [
            'bg' => 'bg-purple-50',
            'text' => 'text-purple-700',
            'border' => 'border-purple-200',
            'dot' => 'bg-purple-500',
            'label' => 'Output / Keluar',
        ],
        default => [
            'bg' => 'bg-slate-50',
            'text' => 'text-slate-700',
            'border' => 'border-slate-200',
            'dot' => 'bg-slate-400',
            'label' => ucfirst(str_replace('_', ' ', $normalized)),
        ],
    };
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
    <span class="h-1.5 w-1.5 rounded-full {{ $config['dot'] }}"></span>
    <span>{{ $slot->isNotEmpty() ? $slot : $config['label'] }}</span>
</span>
