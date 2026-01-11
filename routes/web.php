<?php

use App\Models\Posyandu;
use App\Livewire\Posyandu\Kaders;
use App\Livewire\Posyandu\Laporan as PosyanduLaporan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LaporanController;
use App\Livewire\SuperAdmin\Kader\Edit;
use App\Livewire\SuperAdmin\Kader\Create;
use App\Livewire\SuperAdmin\Kader\Destroy;
use App\Livewire\SuperAdmin\PosyanduDetail;
use App\Livewire\SuperAdmin\PosyanduInfo;
use App\Livewire\SuperAdmin\PosyanduKader;
use App\Livewire\SuperAdmin\PosyanduSasaran;
use App\Livewire\SuperAdmin\PosyanduImunisasi;
use App\Livewire\SuperAdmin\Pendidikan as PosyanduPendidikan;
use App\Livewire\SuperAdmin\PosyanduList;
use App\Livewire\Orangtua\OrangtuaDashboard;
use App\Livewire\Orangtua\OrangtuaImunisasi;
use App\Livewire\Posyandu\PosyanduDashboard;
use App\Livewire\Posyandu\KaderImunisasi;
use App\Livewire\SuperAdmin\PosyanduLaporan as SuperadminPosyanduLaporan;
use App\Livewire\SuperAdmin\SuperAdminDashboard;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('supervisor')->middleware(['auth', 'verified', 'role:superadmin'])->group(function () {
    Route::get('/', SuperAdminDashboard::class)->name('admin.dashboard');

    // Route list posyandu
    Route::get('/posyandu', PosyanduList::class)->name('posyandu.list');

    // Route detail posyandu (default ke info)
    Route::get('/posyandu/{id}', PosyanduInfo::class)->name('posyandu.detail');

    // Route halaman terpisah untuk posyandu
    Route::get('/posyandu/{id}/info', PosyanduInfo::class)->name('posyandu.info');
    Route::get('/posyandu/{id}/kader', PosyanduKader::class)->name('posyandu.kader');
    Route::get('/posyandu/{id}/petugas-kesehatan', \App\Livewire\SuperAdmin\PosyanduPetugasKesehatan::class)->name('posyandu.petugas-kesehatan');
    Route::get('/posyandu/{id}/sasaran', PosyanduSasaran::class)->name('posyandu.sasaran');
    Route::get('/posyandu/{id}/imunisasi', PosyanduImunisasi::class)->name('posyandu.imunisasi');
    Route::get('/posyandu/{id}/pendidikan', PosyanduPendidikan::class)->name('posyandu.pendidikan');
    Route::get('/posyandu/{id}/laporan', SuperadminPosyanduLaporan::class)->name('posyandu.laporan');
    Route::get('/posyandu/{id}/laporan/pdf/jenis-vaksin/{jenisVaksin}', [LaporanController::class, 'superadminPosyanduImunisasiPdfByJenisVaksin'])->name('superadmin.posyandu.laporan.pdf.jenis-vaksin');
    Route::get('/posyandu/{id}/laporan/pdf/nama/{nama}', [LaporanController::class, 'superadminPosyanduImunisasiPdfByNama'])->name('superadmin.posyandu.laporan.pdf.nama');
    Route::get('/posyandu/{id}/laporan/pdf/{kategori}', [LaporanController::class, 'superadminPosyanduImunisasiPdf'])->name('superadmin.posyandu.laporan.pdf.kategori');
    Route::get('/posyandu/{id}/laporan/pdf', [LaporanController::class, 'superadminPosyanduImunisasiPdf'])->name('superadmin.posyandu.laporan.pdf');
    Route::get('/posyandu/{id}/pendidikan/pdf/{kategori}', [LaporanController::class, 'superadminPosyanduPendidikanPdf'])->name('superadmin.posyandu.pendidikan.pdf.kategori');
    Route::get('/posyandu/{id}/pendidikan/pdf', [LaporanController::class, 'superadminPosyanduPendidikanPdf'])->name('superadmin.posyandu.pendidikan.pdf');
    Route::get('/posyandu/{id}/sasaran/{kategori}/pdf', [LaporanController::class, 'superadminPosyanduSasaranPdf'])->name('superadmin.posyandu.sasaran.pdf');
    Route::get('/posyandu/{id}/sk/pdf', [LaporanController::class, 'superadminPosyanduSkPdf'])->name('superadmin.posyandu.sk.pdf');
});


Route::prefix('posyandu')->middleware(['auth', 'verified', 'role:adminPosyandu|superadmin'])->group(function () {
    Route::get('/', PosyanduDashboard::class)->name('adminPosyandu.dashboard');
    Route::get('/petugas-kesehatan', \App\Livewire\Posyandu\PosyanduPetugasKesehatan::class)->name('adminPosyandu.petugas-kesehatan');
    Route::get('/sasaran', \App\Livewire\Posyandu\PosyanduSasaran::class)->name('adminPosyandu.sasaran');
    Route::get('/sasaran/{kategori}/pdf', [LaporanController::class, 'posyanduSasaranPdf'])->name('adminPosyandu.sasaran.pdf');
    Route::get('/imunisasi', KaderImunisasi::class)->name('adminPosyandu.imunisasi');
    Route::get('/pendidikan', \App\Livewire\Posyandu\Pendidikan::class)->name('adminPosyandu.pendidikan');
    Route::get('/laporan', PosyanduLaporan::class)->name('adminPosyandu.laporan');
    Route::get('/laporan/pdf/jenis-vaksin/{jenisVaksin}', [LaporanController::class, 'posyanduImunisasiPdfByJenisVaksin'])->name('adminPosyandu.laporan.pdf.jenis-vaksin');
    Route::get('/laporan/pdf/nama/{nama}', [LaporanController::class, 'posyanduImunisasiPdfByNama'])->name('adminPosyandu.laporan.pdf.nama');
    Route::get('/laporan/pdf/{kategori}', [LaporanController::class, 'posyanduImunisasiPdf'])->name('adminPosyandu.laporan.pdf.kategori');
    Route::get('/laporan/pdf', [LaporanController::class, 'posyanduImunisasiPdf'])->name('adminPosyandu.laporan.pdf');
    Route::get('/pendidikan/pdf/{kategori}', [LaporanController::class, 'posyanduPendidikanPdf'])->name('adminPosyandu.pendidikan.pdf.kategori');
    Route::get('/pendidikan/pdf', [LaporanController::class, 'posyanduPendidikanPdf'])->name('adminPosyandu.pendidikan.pdf');
    Route::get('/sk/pdf', [LaporanController::class, 'posyanduSkPdf'])->name('adminPosyandu.sk.pdf');
});

Route::prefix('orangtua')->middleware(['auth', 'verified', 'role:orangtua|superadmin'])->group(function () {
    Route::get('/', OrangtuaDashboard::class)->name('orangtua.dashboard');
    Route::get('/imunisasi', OrangtuaImunisasi::class)->name('orangtua.imunisasi');
});


require __DIR__.'/auth.php';
