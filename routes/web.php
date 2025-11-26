<?php



use App\Models\Posyandu;
use App\Livewire\Posyandu\Kader;
use App\Livewire\Posyandu\Kaders;
use Illuminate\Support\Facades\Route;
use App\Livewire\SuperAdmin\Kader\Edit;
use App\Livewire\SuperAdmin\Kader\Create;
use App\Livewire\SuperAdmin\Kader\Destroy;
use App\Http\Controllers\ProfileController;
use App\Livewire\SuperAdmin\PosyanduDetail;
use App\Livewire\Orangtua\OrangtuaDashboard;
use App\Livewire\Posyandu\PosyanduDashboard;
use App\Livewire\SuperAdmin\SuperAdminDashboard;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('supervisor')->middleware(['auth', 'verified', 'role:superadmin'])->group(function () {
    Route::get('/', SuperAdminDashboard::class)->name('admin.dashboard');

    // Tambah route detail posyandu khusus superadmin dashboard
    Route::get('/posyandu/{id}', PosyanduDetail::class)->name('posyandu.detail');
    
});


Route::prefix('posyandu')->middleware(['auth', 'verified', 'role:adminPosyandu|superadmin'])->group(function () {
    Route::get('/', PosyanduDashboard::class)->name('adminPosyandu.dashboard');
});


Route::prefix('orangtua')->middleware(['auth', 'verified', 'role:orangtua|superadmin'])->group(function () {
    Route::get('/', OrangtuaDashboard::class)->name('orangtua.dashboard');
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
