<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicThesisController;
use Illuminate\Support\Facades\Route;

// Public viewer (no auth) — browse/search landing + thesis detail (FR-6.x).
Route::get('/', [PublicThesisController::class, 'index'])->name('public.thesis.index');
Route::get('/theses/{thesis}', [PublicThesisController::class, 'show'])->name('public.thesis.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
