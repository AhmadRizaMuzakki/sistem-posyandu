<?php

use App\Models\Posyandu;
use App\Livewire\Posyandu\Kaders;
use App\Livewire\Posyandu\Laporan as PosyanduLaporan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\IndexController;
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

Route::get('/', [IndexController::class, 'index'])->name('index');
Route::get('/posyandu/{id}/info', [IndexController::class, 'posyanduDetail'])->name('posyandu.public.detail');

// Serve file dari storage/app/public via PHP (tanpa symlink).
// Untuk Hostinger saat exec/symlink disabled; alternatif dari storage:link.
Route::get('/storage/{path}', StorageController::class)->where('path', '.*')->name('storage.serve');

Route::prefix('supervisor')->middleware(['auth', 'verified', 'role:superadmin'])->group(function () {
    Route::get('/', SuperAdminDashboard::class)->name('admin.dashboard');
    Route::get('/pengaturan', \App\Livewire\SuperAdmin\Pengaturan::class)->name('pengaturan');
    Route::get('/galeri', \App\Livewire\SuperAdmin\Galeri::class)->name('superadmin.galeri');

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
    Route::get('/posyandu/{id}/jadwal', \App\Livewire\SuperAdmin\PosyanduJadwal::class)->name('posyandu.jadwal');
    Route::get('/posyandu/{id}/laporan', SuperadminPosyanduLaporan::class)->name('posyandu.laporan');
    Route::get('/posyandu/{id}/galeri', \App\Livewire\SuperAdmin\PosyanduGaleri::class)->name('posyandu.galeri');
    Route::get('/posyandu/{id}/laporan/pdf/jenis-vaksin/{jenisVaksin}', [LaporanController::class, 'superadminPosyanduImunisasiPdfByJenisVaksin'])->name('superadmin.posyandu.laporan.pdf.jenis-vaksin');
    Route::get('/posyandu/{id}/laporan/pdf/nama/{nama}', [LaporanController::class, 'superadminPosyanduImunisasiPdfByNama'])->name('superadmin.posyandu.laporan.pdf.nama');
    Route::get('/posyandu/{id}/laporan/pdf/{kategori}', [LaporanController::class, 'superadminPosyanduImunisasiPdf'])->name('superadmin.posyandu.laporan.pdf.kategori');
    Route::get('/posyandu/{id}/laporan/absensi/pdf', [LaporanController::class, 'superadminPosyanduAbsensiPdf'])->name('superadmin.posyandu.laporan.absensi.pdf');
    Route::get('/posyandu/{id}/laporan/pdf', [LaporanController::class, 'superadminPosyanduImunisasiPdf'])->name('superadmin.posyandu.laporan.pdf');
    // Route kombinasi filter pendidikan (harus didefinisikan sebelum route tunggal)
    Route::get('/posyandu/{id}/pendidikan/pdf/kategori-sasaran/{kategoriSasaran}/pendidikan/{kategoriPendidikan}/nama/{nama}', [LaporanController::class, 'superadminPosyanduPendidikanPdfByAllFilters'])->name('superadmin.posyandu.pendidikan.pdf.all-filters');
    Route::get('/posyandu/{id}/pendidikan/pdf/kategori-sasaran/{kategoriSasaran}/pendidikan/{kategoriPendidikan}', [LaporanController::class, 'superadminPosyanduPendidikanPdfByKategoriSasaranAndPendidikan'])->name('superadmin.posyandu.pendidikan.pdf.kategori-sasaran-pendidikan');
    Route::get('/posyandu/{id}/pendidikan/pdf/kategori-sasaran/{kategoriSasaran}/nama/{nama}', [LaporanController::class, 'superadminPosyanduPendidikanPdfByKategoriSasaranAndNama'])->name('superadmin.posyandu.pendidikan.pdf.kategori-sasaran-nama');
    Route::get('/posyandu/{id}/pendidikan/pdf/pendidikan/{kategoriPendidikan}/nama/{nama}', [LaporanController::class, 'superadminPosyanduPendidikanPdfByPendidikanAndNama'])->name('superadmin.posyandu.pendidikan.pdf.pendidikan-nama');
    // Route filter tunggal pendidikan
    Route::get('/posyandu/{id}/pendidikan/pdf/kategori-sasaran/{kategoriSasaran}', [LaporanController::class, 'superadminPosyanduPendidikanPdfByKategoriSasaran'])->name('superadmin.posyandu.pendidikan.pdf.kategori-sasaran');
    Route::get('/posyandu/{id}/pendidikan/pdf/nama/{nama}', [LaporanController::class, 'superadminPosyanduPendidikanPdfByNama'])->name('superadmin.posyandu.pendidikan.pdf.nama');
    Route::get('/posyandu/{id}/pendidikan/pdf/{kategori}', [LaporanController::class, 'superadminPosyanduPendidikanPdf'])->name('superadmin.posyandu.pendidikan.pdf.kategori');
    Route::get('/posyandu/{id}/pendidikan/pdf', [LaporanController::class, 'superadminPosyanduPendidikanPdf'])->name('superadmin.posyandu.pendidikan.pdf');
    Route::get('/posyandu/{id}/sasaran/{kategori}/pdf', [LaporanController::class, 'superadminPosyanduSasaranPdf'])->name('superadmin.posyandu.sasaran.pdf');
    Route::get('/posyandu/{id}/sk/pdf', [LaporanController::class, 'superadminPosyanduSkPdf'])->name('superadmin.posyandu.sk.pdf');
});


Route::prefix('posyandu')->middleware(['auth', 'verified', 'role:adminPosyandu|superadmin'])->group(function () {
    Route::get('/', PosyanduDashboard::class)->name('adminPosyandu.dashboard');
    Route::get('/galeri', \App\Livewire\Posyandu\Galeri::class)->name('adminPosyandu.galeri');
    Route::get('/petugas-kesehatan', \App\Livewire\Posyandu\PosyanduPetugasKesehatan::class)->name('adminPosyandu.petugas-kesehatan');
    Route::get('/sasaran', \App\Livewire\Posyandu\PosyanduSasaran::class)->name('adminPosyandu.sasaran');
    Route::get('/sasaran/{kategori}/pdf', [LaporanController::class, 'posyanduSasaranPdf'])->name('adminPosyandu.sasaran.pdf');
    Route::get('/imunisasi', KaderImunisasi::class)->name('adminPosyandu.imunisasi');
    Route::get('/pendidikan', \App\Livewire\Posyandu\Pendidikan::class)->name('adminPosyandu.pendidikan');
    Route::get('/jadwal', \App\Livewire\Posyandu\PosyanduJadwal::class)->name('adminPosyandu.jadwal');
    Route::get('/laporan', PosyanduLaporan::class)->name('adminPosyandu.laporan');
    Route::get('/laporan/pdf/jenis-vaksin/{jenisVaksin}', [LaporanController::class, 'posyanduImunisasiPdfByJenisVaksin'])->name('adminPosyandu.laporan.pdf.jenis-vaksin');
    Route::get('/laporan/pdf/nama/{nama}', [LaporanController::class, 'posyanduImunisasiPdfByNama'])->name('adminPosyandu.laporan.pdf.nama');
    Route::get('/laporan/pdf/{kategori}', [LaporanController::class, 'posyanduImunisasiPdf'])->name('adminPosyandu.laporan.pdf.kategori');
    Route::get('/laporan/absensi/pdf', [LaporanController::class, 'posyanduAbsensiPdf'])->name('adminPosyandu.laporan.absensi.pdf');
    Route::get('/laporan/pdf', [LaporanController::class, 'posyanduImunisasiPdf'])->name('adminPosyandu.laporan.pdf');
    // Route kombinasi filter pendidikan (harus didefinisikan sebelum route tunggal)
    Route::get('/pendidikan/pdf/kategori-sasaran/{kategoriSasaran}/pendidikan/{kategoriPendidikan}/nama/{nama}', [LaporanController::class, 'posyanduPendidikanPdfByAllFilters'])->name('adminPosyandu.pendidikan.pdf.all-filters');
    Route::get('/pendidikan/pdf/kategori-sasaran/{kategoriSasaran}/pendidikan/{kategoriPendidikan}', [LaporanController::class, 'posyanduPendidikanPdfByKategoriSasaranAndPendidikan'])->name('adminPosyandu.pendidikan.pdf.kategori-sasaran-pendidikan');
    Route::get('/pendidikan/pdf/kategori-sasaran/{kategoriSasaran}/nama/{nama}', [LaporanController::class, 'posyanduPendidikanPdfByKategoriSasaranAndNama'])->name('adminPosyandu.pendidikan.pdf.kategori-sasaran-nama');
    Route::get('/pendidikan/pdf/pendidikan/{kategoriPendidikan}/nama/{nama}', [LaporanController::class, 'posyanduPendidikanPdfByPendidikanAndNama'])->name('adminPosyandu.pendidikan.pdf.pendidikan-nama');
    // Route filter tunggal pendidikan
    Route::get('/pendidikan/pdf/kategori-sasaran/{kategoriSasaran}', [LaporanController::class, 'posyanduPendidikanPdfByKategoriSasaran'])->name('adminPosyandu.pendidikan.pdf.kategori-sasaran');
    Route::get('/pendidikan/pdf/nama/{nama}', [LaporanController::class, 'posyanduPendidikanPdfByNama'])->name('adminPosyandu.pendidikan.pdf.nama');
    Route::get('/pendidikan/pdf/{kategori}', [LaporanController::class, 'posyanduPendidikanPdf'])->name('adminPosyandu.pendidikan.pdf.kategori');
    Route::get('/pendidikan/pdf', [LaporanController::class, 'posyanduPendidikanPdf'])->name('adminPosyandu.pendidikan.pdf');
    Route::get('/sk/pdf', [LaporanController::class, 'posyanduSkPdf'])->name('adminPosyandu.sk.pdf');
});

Route::prefix('orangtua')->middleware(['auth', 'verified', 'role:orangtua|superadmin'])->group(function () {
    Route::get('/', OrangtuaDashboard::class)->name('orangtua.dashboard');
    Route::get('/imunisasi', OrangtuaImunisasi::class)->name('orangtua.imunisasi');
});


require __DIR__.'/auth.php';
