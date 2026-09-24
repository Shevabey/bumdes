<?php

namespace App\Http\Livewire\Operasional;

use App\Http\Livewire\Shared\DataTable;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\Referral;
use App\Services\ReferralService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Livewire\Attributes\Computed;

class ReferralPanel extends DataTable
{
    public string $sortField = 'tanggal_generate';

    public string $sortDirection = 'desc';

    /**
     * @var array{status: string, tipe_relasi: string, id_bumdes: string}
     */
    public array $filters = [
        'status' => '',
        'tipe_relasi' => '',
        'id_bumdes' => '',
    ];

    public string $inputKodeRedeem = '';

    public bool $showDetailModal = false;

    public ?string $detailId = null;

    public function query(): Builder
    {
        /** @var Akun $user */
        $user = auth()->user();

        $query = Referral::query()->with(['pengaju', 'penerima']);

        // Scope berjenjang
        if ($user->hasRole('super_admin') || $user->hasAnyRole(['pengawas', 'penasihat', 'direktur'])) {
            if ($this->filters['id_bumdes'] !== '') {
                $query->where(function (Builder $q): void {
                    $q->where('id_bumdes_pengaju', $this->filters['id_bumdes'])
                        ->orWhere('id_bumdes_penerima', $this->filters['id_bumdes']);
                });
            }
        } elseif ($user->id_bumdes) {
            if ($this->filters['tipe_relasi'] === 'diajukan') {
                $query->where('id_bumdes_pengaju', $user->id_bumdes);
            } elseif ($this->filters['tipe_relasi'] === 'diterima') {
                $query->where('id_bumdes_penerima', $user->id_bumdes);
            } else {
                $query->where(function (Builder $q) use ($user): void {
                    $q->where('id_bumdes_pengaju', $user->id_bumdes)
                        ->orWhere('id_bumdes_penerima', $user->id_bumdes);
                });
            }
        } else {
            $query->whereRaw('0 = 1');
        }

        // Search
        if ($this->search !== '') {
            $query->where(function (Builder $q): void {
                $q->where('kode_unik', 'like', '%'.$this->search.'%')
                    ->orWhere('id_referral', 'like', '%'.$this->search.'%')
                    ->orWhereHas('pengaju', function (Builder $qp): void {
                        $qp->where('nama_bumdes', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('penerima', function (Builder $qr): void {
                        $qr->where('nama_bumdes', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Filters
        if ($this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    #[Computed]
    public function stats(): array
    {
        $baseQuery = $this->query();

        $countAktif = (int) (clone $baseQuery)->where('status', 'aktif')->count();
        $countPending = (int) (clone $baseQuery)->where('status', 'pending')->count();
        $countCair = (int) (clone $baseQuery)->where('status', 'cair')->count();
        $countGagalKedaluwarsa = (int) (clone $baseQuery)->whereIn('status', ['kedaluwarsa', 'gagal'])->count();
        $totalInsentifCair = $countCair * 10000;

        return [
            'countAktif' => $countAktif,
            'countPending' => $countPending,
            'countCair' => $countCair,
            'countGagalKedaluwarsa' => $countGagalKedaluwarsa,
            'totalInsentifCair' => $totalInsentifCair,
        ];
    }

    #[Computed]
    public function activeReferral(): ?Referral
    {
        /** @var Akun $user */
        $user = auth()->user();
        $idBumdes = $user->id_bumdes;

        if (! $idBumdes && $user->hasRole('super_admin')) {
            $idBumdes = $this->filters['id_bumdes'] ?: Bumdes::where('status_aktif', true)->orderBy('nama_bumdes')->value('id_bumdes');
        }

        if (! $idBumdes) {
            return null;
        }

        return Referral::query()
            ->where('id_bumdes_pengaju', $idBumdes)
            ->where('status', 'aktif')
            ->where('tanggal_expired', '>', Carbon::now())
            ->latest('tanggal_generate')
            ->first();
    }

    public function generateActiveCode(ReferralService $service): void
    {
        /** @var Akun $user */
        $user = auth()->user();

        Gate::authorize('generate', Referral::class);

        $idBumdes = $user->id_bumdes;
        if (! $idBumdes && $user->hasRole('super_admin')) {
            $idBumdes = $this->filters['id_bumdes'] ?: Bumdes::where('status_aktif', true)->orderBy('nama_bumdes')->value('id_bumdes');
        }

        if (! $idBumdes) {
            session()->flash('error', 'Pilih BUMDes terlebih dahulu untuk membuat kode referral.');

            return;
        }

        try {
            $referral = $service->generateOrGetActive($idBumdes, $user);
            session()->flash('success', "Kode referral aktif BUMDes berhasil dimuat: {$referral->kode_unik}.");
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function redeemCode(ReferralService $service): void
    {
        /** @var Akun $user */
        $user = auth()->user();

        Gate::authorize('redeem', Referral::class);

        $this->validate([
            'inputKodeRedeem' => ['required', 'string', 'min:4', 'max:20'],
        ]);

        $idBumdes = $user->id_bumdes;
        if (! $idBumdes && $user->hasRole('super_admin')) {
            $idBumdes = $this->filters['id_bumdes'] ?: null;
        }

        if (! $idBumdes) {
            $this->addError('inputKodeRedeem', 'BUMDes penerima belum ditentukan.');

            return;
        }

        try {
            $cleanCode = strtoupper(trim($this->inputKodeRedeem));
            $redeemed = $service->redeem($cleanCode, $idBumdes, $user);

            session()->flash('success', "Kode referral {$cleanCode} berhasil diklaim. Status menunggu 15 hari verifikasi aktivitas transaksi (batas: {$redeemed->batas_verifikasi?->format('d/m/Y')}).");
            $this->inputKodeRedeem = '';
            $this->resetValidation();
        } catch (InvalidArgumentException $e) {
            $this->addError('inputKodeRedeem', $e->getMessage());
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openDetailModal(string $id): void
    {
        $referral = Referral::with(['pengaju', 'penerima'])->findOrFail($id);

        Gate::authorize('view', $referral);

        $this->detailId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailId = null;
    }

    public function render(): View
    {
        /** @var Akun $user */
        $user = auth()->user();

        $selectedReferralDetail = $this->detailId
            ? Referral::with(['pengaju', 'penerima'])->find($this->detailId)
            : null;

        $bumdesOptions = [];
        if ($user->hasRole('super_admin') || $user->hasAnyRole(['pengawas', 'penasihat', 'direktur'])) {
            $bumdesOptions = Bumdes::query()->where('status_aktif', true)->orderBy('nama_bumdes')->get();
        }

        return view('livewire.operasional.referral-panel', [
            'referrals' => $this->query()->paginate($this->perPage),
            'stats' => $this->stats,
            'activeReferral' => $this->activeReferral,
            'selectedReferralDetail' => $selectedReferralDetail,
            'bumdesOptions' => $bumdesOptions,
        ])->layout('layouts.panel', ['title' => 'Program Referral BUMDes']);
    }
}
