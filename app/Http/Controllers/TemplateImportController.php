<?php

namespace App\Http\Controllers;

use App\Services\SasaranImportTemplate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TemplateImportController extends Controller
{
    /**
     * Unduh template Excel import sasaran per kategori.
     * Format .xlsx agar data tampil per kolom di Excel.
     */
    public function __invoke(string $kategori)
    {
        $allowed = ['bayibalita', 'remaja', 'dewasa', 'ibuhamil', 'pralansia', 'lansia'];
        if (! in_array($kategori, $allowed)) {
            $kategori = 'dewasa';
        }

        $spreadsheet = SasaranImportTemplate::getExcelContent($kategori);
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'template_import_' . $kategori . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
