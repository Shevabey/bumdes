<?php

use App\Http\Controllers\AuthController;
use App\Http\Livewire\Operasional\IuranPanel;
use App\Http\Livewire\Operasional\PelangganManager;
use App\Http\Livewire\Operasional\ReferralPanel;
use App\Http\Livewire\Operasional\TagihanManager;
use App\Http\Livewire\Operasional\TransaksiManager;
use App\Http\Livewire\Operasional\UnitManager;
use App\Http\Livewire\SuperAdmin\AkunManager;
use App\Http\Livewire\SuperAdmin\BumdesManager;
use App\Http\Livewire\SuperAdmin\LogAktivitasViewer;
use App\Http\Livewire\SuperAdmin\RegionManager;
use App\Models\Bumdes;
use App\Models\UnitUsaha;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'account.active'])->group(function (): void {
    Route::get('/panel', function () {
        return response()->json(['authenticated' => true]);
    })->name('panel.dashboard');

    Route::middleware('can:view,bumdes')->get('/panel/bumdes/{bumdes}', function (Bumdes $bumdes) {
        return response()->json(['id_bumdes' => $bumdes->id_bumdes]);
    })->name('panel.bumdes.show');

    Route::middleware('can:view,unit')->get('/panel/unit/{unit}', function (UnitUsaha $unit) {
        return response()->json(['id_unit' => $unit->id_unit]);
    })->name('panel.unit.show');

    Route::middleware('role:pengawas|penasihat|direktur|super_admin')
        ->get('/monitoring', function () {
            return response()->json(['monitoring' => true]);
        })->name('monitoring.dashboard');

    Route::middleware(['role:super_admin'])->prefix('super-admin')->group(function (): void {
        Route::get('/region', RegionManager::class)->name('super-admin.region');
        Route::get('/bumdes', BumdesManager::class)->name('super-admin.bumdes');
        Route::get('/akun', AkunManager::class)->name('super-admin.akun');
        Route::get('/log-aktivitas', LogAktivitasViewer::class)->name('super-admin.log');
    });

    // Operasional — diakses role BUMDes & Unit
    Route::middleware(['role:super_admin|admin_bumdes|sekretaris|bendahara|admin_unit|direktur|pengawas|penasihat'])
        ->prefix('operasional')
        ->group(function (): void {
            Route::get('/unit-usaha', UnitManager::class)->name('operasional.unit');
            Route::get('/pelanggan', PelangganManager::class)->name('operasional.pelanggan');
            Route::get('/transaksi', TransaksiManager::class)->name('operasional.transaksi');
            Route::get('/tagihan', TagihanManager::class)->name('operasional.tagihan');
            Route::get('/iuran', IuranPanel::class)->name('operasional.iuran');
            Route::get('/referral', ReferralPanel::class)->name('operasional.referral');
        });

    Route::get('/bumdes/iuran', fn () => redirect()->route('operasional.iuran'))->name('bumdes.iuran');
    Route::get('/bumdes/referral', fn () => redirect()->route('operasional.referral'))->name('bumdes.referral');
});

Route::get('/', function () {
    return view('welcome');
});
