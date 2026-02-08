<?php

namespace App\Http\Controllers;

use App\Services\SasaranImportTemplate;

class TemplateImportController extends Controller
{
    /**
     * Unduh template CSV import sasaran per kategori.
     * Kolom sama dengan input form sasaran agar import tidak gagal.
     */
    public function __invoke(string $kategori)
    {
        $allowed = ['bayibalita', 'remaja', 'dewasa', 'ibuhamil', 'pralansia', 'lansia'];
        if (! in_array($kategori, $allowed)) {
            $kategori = 'dewasa';
        }

        $csv = SasaranImportTemplate::getCsvContent($kategori);
        $bom = "\xEF\xBB\xBF";

        return response()->streamDownload(
            function () use ($csv, $bom) {
                echo $bom . $csv;
            },
            'template_import_' . $kategori . '.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }
}
