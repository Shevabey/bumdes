<?php

namespace App\Http\Livewire\Shared;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class DataTable extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * @var array<string, mixed>
     */
    public array $filters = [];

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 10;

    public string $title = '';

    public string $placeholder = 'Cari data...';

    public string $emptyMessage = 'Tidak ada data ditemukan.';

    /**
     * @var array<int, array{key: string, label: string, sortable?: bool}>
     */
    public array $columns = [];

    /**
     * @var array<string, array<string, string>>
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilters(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filters']);
        $this->resetPage();
    }

    public function query(): ?Builder
    {
        return null;
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $query = $this->query();

        if (! $query) {
            return new ConcretePaginator([], 0, $this->perPage, 1, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        }

        return $query->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.shared.data-table', [
            'rows' => $this->rows,
        ]);
    }
}
