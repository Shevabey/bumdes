<?php

use App\Http\Controllers\AuthController;
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
});

Route::get('/', function () {
    return view('welcome');
});
