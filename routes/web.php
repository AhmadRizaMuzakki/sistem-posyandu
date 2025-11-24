<?php


use App\Http\Controllers\orangtua;
use App\Http\Controllers\Supervisor;
use App\Http\Controllers\Posyandu;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('supervisor')->middleware(['auth', 'verified', 'role:superadmin'])->group(function () {
    Route::get('/', [Supervisor::class, 'index'])->name('admin.dashboard');
});


Route::prefix('posyandu')->middleware(['auth', 'verified', 'role:adminPosyandu|superadmin'])->group(function () {
    Route::get('/', [Posyandu::class, 'index'])->name('adminPosyandu.dashboard');
});


Route::prefix('orangtua')->middleware(['auth', 'verified', 'role:orangtua|superadmin'])->group(function () {
    Route::get('/', [orangtua::class, 'index'])->name('orangtua.dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
