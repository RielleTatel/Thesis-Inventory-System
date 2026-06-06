<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DepartmentAccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicThesisController;
use App\Http\Controllers\User\ThesisController;
use Illuminate\Support\Facades\Route;

// Public viewer (no auth) — browse/search landing + thesis detail (FR-6.x).
Route::get('/', [PublicThesisController::class, 'index'])->name('public.thesis.index');
Route::get('/theses/{thesis}', [PublicThesisController::class, 'show'])->name('public.thesis.show');

Route::get('/dashboard', function () {
    // Each role has its own home; the bare dashboard is just a router.
    $user = auth()->user();
    if ($user?->hasRole('department')) {
        return redirect()->route('department.theses.index');
    }
    if ($user?->hasRole('administrator')) {
        return redirect()->route('admin.accounts.index');
    }

    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Department thesis management — scoped to the logged-in department (FR-3.4/3.6).
Route::middleware(['auth', 'role:department'])
    ->prefix('department')
    ->name('department.')
    ->group(function () {
        // Bare area root → the department's default page (no dead 404).
        Route::redirect('/', '/department/theses');

        Route::resource('theses', ThesisController::class)->except(['show']);
    });

// Administrator area — department account management + activity log (FR-2.x/7.x).
Route::middleware(['auth', 'role:administrator'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Bare area root → the admin's default page (no dead 404).
        Route::redirect('/', '/admin/accounts');

        Route::resource('accounts', DepartmentAccountController::class)->except(['show']);
        Route::patch('accounts/{account}/toggle', [DepartmentAccountController::class, 'toggle'])
            ->name('accounts.toggle');

        // Admin-only audit trail (FR-7.x).
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    });

require __DIR__.'/auth.php';
