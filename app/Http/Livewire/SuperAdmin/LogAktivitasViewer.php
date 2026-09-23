<?php

namespace App\Http\Livewire\SuperAdmin;

use App\Http\Livewire\Shared\DataTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class LogAktivitasViewer extends DataTable
{
    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    /**
     * @var array{log_name: string, event: string, dari_tanggal: string, sampai_tanggal: string}
     */
    public array $filters = [
        'log_name' => '',
        'event' => '',
        'dari_tanggal' => '',
        'sampai_tanggal' => '',
    ];

    public bool $showDetailModal = false;

    public ?int $selectedLogId = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $selectedLogData = null;

    public function query(): Builder
    {
        $query = Activity::query()->with('causer');

        if ($this->search !== '') {
            $query->where(function (Builder $q) {
                $q->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('log_name', 'like', '%'.$this->search.'%')
                    ->orWhere('event', 'like', '%'.$this->search.'%')
                    ->orWhere('causer_id', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filters['log_name'] !== '') {
            $query->where('log_name', $this->filters['log_name']);
        }

        if ($this->filters['event'] !== '') {
            $query->where('event', $this->filters['event']);
        }

        if ($this->filters['dari_tanggal'] !== '') {
            $query->where('created_at', '>=', $this->filters['dari_tanggal'].' 00:00:00');
        }

        if ($this->filters['sampai_tanggal'] !== '') {
            $query->where('created_at', '<=', $this->filters['sampai_tanggal'].' 23:59:59');
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    public function showDetail(int $id): void
    {
        $activity = Activity::with('causer')->findOrFail($id);

        $this->selectedLogId = $activity->id;
        $this->selectedLogData = [
            'id' => $activity->id,
            'log_name' => $activity->log_name ?? '-',
            'description' => $activity->description,
            'event' => $activity->event ?? '-',
            'subject_type' => $activity->subject_type ?? '-',
            'subject_id' => $activity->subject_id ?? '-',
            'causer_id' => $activity->causer_id ?? '-',
            'causer_name' => $activity->causer?->nama ?? $activity->causer?->username ?? 'Sistem',
            'created_at' => $activity->created_at?->format('d M Y H:i:s') ?? '-',
            'properties' => $activity->properties ? $activity->properties->toArray() : [],
        ];

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedLogId = null;
        $this->selectedLogData = null;
    }

    public function render(): View
    {
        $logNames = Activity::select('log_name')
            ->distinct()
            ->whereNotNull('log_name')
            ->pluck('log_name');

        $events = Activity::select('event')
            ->distinct()
            ->whereNotNull('event')
            ->pluck('event');

        return view('livewire.super-admin.log-aktivitas-viewer', [
            'rows' => $this->rows,
            'logNames' => $logNames,
            'events' => $events,
        ])->layout('layouts.panel', [
            'title' => 'Log Aktivitas Sistem',
            'header' => 'Log Aktivitas Sistem',
        ]);
    }
}
