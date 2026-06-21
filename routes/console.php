<?php

use App\Services\SasaranKategoriService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sasaran:sync-kategori {--posyandu= : ID posyandu tertentu}', function () {
    $service = app(SasaranKategoriService::class);
    $posyanduId = $this->option('posyandu');

    if ($posyanduId) {
        $migrated = $service->syncForPosyandu((int) $posyanduId);
        $this->info("Sinkronisasi selesai. {$migrated} sasaran dipindahkan (posyandu #{$posyanduId}).");

        return;
    }

    $migrated = $service->syncAll();
    $this->info("Sinkronisasi selesai. {$migrated} sasaran dipindahkan ke kategori sesuai umur.");
})->purpose('Pindahkan sasaran ke kategori yang sesuai umur');
