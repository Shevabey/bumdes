<?php

namespace App\Http\Livewire\Operasional;

use App\Http\Livewire\Shared\DataTable;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\IuranBumdes;
use App\Services\IuranService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;

class IuranPanel extends DataTable
{
    use WithFileUploads;

    public string $sortField = 'bulan_tahun';

    public string $sortDirection = 'desc';

    /**
     * @var array{status: string, bulan_tahun: string, id_bumdes: string}
     */
    public array $filters = [
        'status' => '',
        'bulan_tahun' => '',
        'id_bumdes' => '',
    ];

    public bool $showBayarModal = false;

    public bool $showVerifikasiModal = false;

    public bool $showDetailModal = false;

    // Selection IDs
    public ?string $bayarIuranId = null;

    public ?string $verifikasiIuranId = null;

    public ?string $detailId = null;

    // Form fields bayar
    public string $sumber_dana = 'luar_kas';

    public string $metode_bayar = 'transfer';

    public string $bukti_pembayaran_url = '';

    /**
     * @var mixed
     */
    public $bukti_file = null;

    public function query(): Builder
    {
        /** @var Akun $user */
        $user = auth()->user();

        $query = IuranBumdes::query()->with(['bumdes.kelurahan', 'verifikator']);

        // Scope berjenjang
        if ($user->hasRole('super_admin') || $user->hasAnyRole(['pengawas', 'penasihat', 'direktur'])) {
            // Global access
        } elseif ($user->hasRole('admin_bumdes')) {
            $user->loadMissing('bumdes.kelurahan');
            $userKelurahan = $user->bumdes?->kelurahan;

            if (($userKelurahan?->is_koordinator ?? false) && $userKelurahan->parent_id !== null) {
                // Koordinator dapat melihat BUMDes di kecamatannya
                $query->whereHas('bumdes.kelurahan', function (Builder $qk) use ($userKelurahan): void {
                    $qk->where('parent_id', $userKelurahan->parent_id);
                });
            } else {
                $query->where('id_bumdes', $user->id_bumdes);
            }
        } elseif ($user->hasAnyRole(['sekretaris', 'bendahara'])) {
            $query->where('id_bumdes', $user->id_bumdes);
        } else {
            $query->whereRaw('0 = 1');
        }

        // Search
        if ($this->search !== '') {
            $query->where(function (Builder $q): void {
                $q->where('id_iuran', 'like', '%'.$this->search.'%')
                    ->orWhere('bulan_tahun', 'like', '%'.$this->search.'%')
                    ->orWhereHas('bumdes', function (Builder $qb): void {
                        $qb->where('nama_bumdes', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Filters
        if ($this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if ($this->filters['bulan_tahun'] !== '') {
            $query->where('bulan_tahun', $this->filters['bulan_tahun']);
        }

        if ($this->filters['id_bumdes'] !== '' && ($user->hasRole('super_admin') || ($user->hasRole('admin_bumdes') && ($user->bumdes?->kelurahan?->is_koordinator ?? false)))) {
            $query->where('id_bumdes', $this->filters['id_bumdes']);
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    #[Computed]
    public function stats(): array
    {
        $baseQuery = $this->query();

        $countBelumBayar = (int) (clone $baseQuery)->where('status', 'belum_bayar')->count();
        $nominalBelumBayar = (float) (clone $baseQuery)->where('status', 'belum_bayar')->sum('jumlah');

        $countMenunggu = (int) (clone $baseQuery)->where('status', 'menunggu_verifikasi')->count();
        $nominalMenunggu = (float) (clone $baseQuery)->where('status', 'menunggu_verifikasi')->sum('jumlah');

        $countLunas = (int) (clone $baseQuery)->where('status', 'lunas')->count();
        $nominalLunas = (float) (clone $baseQuery)->where('status', 'lunas')->sum('jumlah');

        $totalNominal = (float) (clone $baseQuery)->sum('jumlah');

        return [
            'countBelumBayar' => $countBelumBayar,
            'nominalBelumBayar' => $nominalBelumBayar,
            'countMenunggu' => $countMenunggu,
            'nominalMenunggu' => $nominalMenunggu,
            'countLunas' => $countLunas,
            'nominalLunas' => $nominalLunas,
            'totalNominal' => $totalNominal,
        ];
    }

    public function openBayarModal(string $id): void
    {
        $iuran = IuranBumdes::findOrFail($id);

        Gate::authorize('bayar', $iuran);

        $this->resetValidation();
        $this->bayarIuranId = $id;
        $this->sumber_dana = 'luar_kas';
        $this->metode_bayar = 'transfer';
        $this->bukti_pembayaran_url = '';
        $this->bukti_file = null;

        $this->showBayarModal = true;
    }

    public function closeBayarModal(): void
    {
        $this->showBayarModal = false;
        $this->bayarIuranId = null;
        $this->bukti_file = null;
    }

    public function prosesBayar(IuranService $service): void
    {
        if (! $this->bayarIuranId) {
            return;
        }

        $iuran = IuranBumdes::findOrFail($this->bayarIuranId);

        /** @var Akun $user */
        $user = auth()->user();

        Gate::authorize('bayar', $iuran);

        $rules = [
            'sumber_dana' => ['required', 'in:kas,luar_kas'],
            'metode_bayar' => ['required', 'in:transfer,tunai'],
        ];

        if ($this->metode_bayar === 'transfer') {
            if ($this->bukti_file) {
                $rules['bukti_file'] = ['image', 'max:2048'];
            } else {
                $rules['bukti_pembayaran_url'] = ['required', 'string'];
            }
        }

        $this->validate($rules);

        $urlBukti = $this->bukti_pembayaran_url;
        if ($this->bukti_file) {
            $path = $this->bukti_file->store('bukti_iuran', 'public');
            $urlBukti = Storage::url($path);
        }

        try {
            $service->bayar($iuran, $user, [
                'sumber_dana' => $this->sumber_dana,
                'metode_bayar' => $this->metode_bayar,
                'bukti_pembayaran_url' => $urlBukti,
            ]);

            session()->flash('success', "Pembayaran iuran {$iuran->id_iuran} berhasil dikirim dan menunggu verifikasi.");
            $this->closeBayarModal();
        } catch (Exception $e) {
            $this->addError('sumber_dana', $e->getMessage());
        }
    }

    public function openVerifikasiModal(string $id): void
    {
        $iuran = IuranBumdes::with(['bumdes', 'verifikator'])->findOrFail($id);

        Gate::authorize('verifikasi', $iuran);

        $this->verifikasiIuranId = $id;
        $this->showVerifikasiModal = true;
    }

    public function closeVerifikasiModal(): void
    {
        $this->showVerifikasiModal = false;
        $this->verifikasiIuranId = null;
    }

    public function prosesVerifikasi(bool $approve, IuranService $service): void
    {
        if (! $this->verifikasiIuranId) {
            return;
        }

        $iuran = IuranBumdes::findOrFail($this->verifikasiIuranId);

        /** @var Akun $user */
        $user = auth()->user();

        Gate::authorize('verifikasi', $iuran);

        try {
            $service->verifikasi($iuran, $user, $approve);

            $statusText = $approve ? 'disetujui (Lunas)' : 'ditolak dan dikembalikan ke status belum bayar';
            session()->flash('success', "Verifikasi iuran {$iuran->id_iuran} berhasil {$statusText}.");

            $this->closeVerifikasiModal();
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openDetailModal(string $id): void
    {
        $iuran = IuranBumdes::with(['bumdes.kelurahan', 'verifikator'])->findOrFail($id);

        Gate::authorize('view', $iuran);

        $this->detailId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailId = null;
    }

    public function generateBulanan(IuranService $service): void
    {
        /** @var Akun $user */
        $user = auth()->user();

        Gate::authorize('generate', IuranBumdes::class);

        $created = $service->generateBulanan(Carbon::now());

        session()->flash('success', "Generate tagihan iuran bulanan berhasil. Sebanyak {$created} data iuran baru dibuat.");
    }

    public function render(): View
    {
        /** @var Akun $user */
        $user = auth()->user();

        $selectedIuranBayar = $this->bayarIuranId ? IuranBumdes::with('bumdes')->find($this->bayarIuranId) : null;
        $selectedIuranVerifikasi = $this->verifikasiIuranId ? IuranBumdes::with('bumdes')->find($this->verifikasiIuranId) : null;
        $selectedIuranDetail = $this->detailId ? IuranBumdes::with(['bumdes.kelurahan', 'verifikator'])->find($this->detailId) : null;

        $bumdesOptions = [];
        if ($user->hasRole('super_admin') || ($user->hasRole('admin_bumdes') && ($user->bumdes?->kelurahan?->is_koordinator ?? false))) {
            $bumdesOptions = Bumdes::query()->where('status_aktif', true)->orderBy('nama_bumdes')->get();
        }

        return view('livewire.operasional.iuran-panel', [
            'iurans' => $this->query()->paginate($this->perPage),
            'stats' => $this->stats,
            'selectedIuranBayar' => $selectedIuranBayar,
            'selectedIuranVerifikasi' => $selectedIuranVerifikasi,
            'selectedIuranDetail' => $selectedIuranDetail,
            'bumdesOptions' => $bumdesOptions,
            'isCoordinator' => (bool) ($user->bumdes?->kelurahan?->is_koordinator ?? false),
        ])->layout('layouts.panel', ['title' => 'Manajemen Iuran BUMDes']);
    }
}
