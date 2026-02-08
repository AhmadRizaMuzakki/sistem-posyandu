<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TemplateImportController extends Controller
{
    public function __invoke(string $kategori)
    {
        $kategori = strtolower($kategori);
        $validKategori = ['bayibalita', 'remaja', 'dewasa', 'pralansia', 'lansia', 'ibuhamil'];
        if (!in_array($kategori, $validKategori)) {
            $kategori = 'dewasa';
        }

        $headers = $this->getTemplateHeaders($kategori);

        return response()->streamDownload(function () use ($headers) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template');
            $sheet->fromArray([$headers], null, 'A1');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'template-import-sasaran-' . $kategori . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function getTemplateHeaders(string $kategori): array
    {
        $dewasaHeaders = [
            'nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'tempat_lahir', 'tanggal_lahir',
            'jenis_kelamin', 'status_keluarga', 'alamat_sasaran', 'rt', 'rw',
            'kepersertaan_bpjs', 'nomor_bpjs', 'nomor_telepon', 'pekerjaan', 'pendidikan',
        ];
        return match ($kategori) {
            'bayibalita' => [
                'nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'tempat_lahir', 'tanggal_lahir',
                'jenis_kelamin', 'status_keluarga', 'alamat_sasaran', 'rt', 'rw',
                'kepersertaan_bpjs', 'nomor_bpjs', 'nik_orangtua', 'nama_orangtua',
                'tempat_lahir_orangtua', 'tanggal_lahir_orangtua', 'pekerjaan_orangtua', 'status_keluarga_orangtua',
            ],
            'remaja' => [
                'nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'tempat_lahir', 'tanggal_lahir',
                'jenis_kelamin', 'status_keluarga', 'alamat_sasaran', 'rt', 'rw',
                'kepersertaan_bpjs', 'nomor_bpjs', 'nomor_telepon', 'pendidikan',
                'nik_orangtua', 'nama_orangtua', 'tempat_lahir_orangtua', 'tanggal_lahir_orangtua',
                'pekerjaan_orangtua', 'status_keluarga_orangtua',
            ],
            'dewasa' => $dewasaHeaders,
            'pralansia' => $dewasaHeaders,
            'lansia' => $dewasaHeaders,
            'ibuhamil' => [
                'nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'tempat_lahir', 'tanggal_lahir',
                'jenis_kelamin', 'status_keluarga', 'alamat_sasaran', 'rt', 'rw',
                'kepersertaan_bpjs', 'nomor_bpjs', 'nomor_telepon', 'pekerjaan', 'pendidikan',
                'minggu_kandungan', 'nama_suami', 'nik_suami', 'pekerjaan_suami', 'status_keluarga_suami',
            ],
            default => $dewasaHeaders,
        };
    }
}
