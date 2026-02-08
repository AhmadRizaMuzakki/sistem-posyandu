<?php

namespace App\Services;

/**
 * Template CSV import sasaran per kategori.
 * Baris 1 = header kolom, Baris 2+ = contoh data.
 * Import memetakan data per kolom berdasarkan nama header (urutan kolom bebas).
 */
class SasaranImportTemplate
{
    protected static array $templates = [
        'bayibalita' => [
            'header' => ['nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'status_keluarga', 'alamat_sasaran', 'rt', 'rw', 'kepersertaan_bpjs', 'nomor_bpjs', 'nik_orangtua', 'nama_orangtua', 'tempat_lahir_orangtua', 'tanggal_lahir_orangtua', 'pekerjaan_orangtua'],
            'rows' => [
                ['3201234567890001', 'Ahmad Budi', '3201234567890002', 'Jakarta', '2022-05-15', 'Laki-laki', 'anak', 'Jl. Merdeka No 10 RT 01 RW 02', '01', '02', 'NON PBI', '0001234567890', '3201234567890003', 'Siti Aminah', 'Bandung', '1990-03-20', 'Ibu Rumah Tangga'],
                ['3201234567890004', 'Dewi Kusuma', '3201234567890005', 'Bogor', '2023-01-10', 'Perempuan', 'anak', 'Jl. Sudirman No 5', '02', '01', 'PBI', '', '3201234567890006', 'Budi Santoso', 'Jakarta', '1988-07-12', 'Wiraswasta'],
            ],
        ],
        'remaja' => [
            'header' => ['nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'status_keluarga', 'alamat_sasaran', 'rt', 'rw', 'kepersertaan_bpjs', 'nomor_bpjs', 'nomor_telepon', 'pendidikan', 'nik_orangtua', 'nama_orangtua'],
            'rows' => [
                ['3201234567890011', 'Fajar Nugraha', '3201234567890012', 'Depok', '2010-08-22', 'Laki-laki', 'anak', 'Jl. Kenanga No 3', '01', '03', 'NON PBI', '', '081234567890', 'SLTP/Sederajat', '3201234567890013', 'Ani Wijaya'],
                ['3201234567890014', 'Maya Sari', '3201234567890015', 'Bekasi', '2009-11-05', 'Perempuan', 'anak', 'Jl. Melati No 7', '02', '02', 'PBI', '0001234567891', '081298765432', 'SLTA/Sederajat', '3201234567890016', 'Bambang Susilo'],
            ],
        ],
        'dewasa' => [
            'header' => ['nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'status_keluarga', 'alamat_sasaran', 'rt', 'rw', 'kepersertaan_bpjs', 'nomor_bpjs', 'nomor_telepon', 'pekerjaan', 'pendidikan'],
            'rows' => [
                ['3201234567890021', 'Eko Prasetyo', '3201234567890022', 'Jakarta', '1985-04-18', 'Laki-laki', 'kepala keluarga', 'Jl. Diponegoro No 15', '01', '01', 'NON PBI', '', '081312345678', 'Karyawan Swasta', 'Diploma IV/Strata I'],
                ['3201234567890023', 'Rina Kartika', '3201234567890022', 'Surabaya', '1987-09-25', 'Perempuan', 'istri', 'Jl. Diponegoro No 15', '01', '01', 'NON PBI', '0001234567892', '081376543210', 'Mengurus Rumah Tangga', 'SLTA/Sederajat'],
            ],
        ],
        'ibuhamil' => [
            'header' => ['nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'status_keluarga', 'alamat_sasaran', 'rt', 'rw', 'kepersertaan_bpjs', 'nomor_bpjs', 'nomor_telepon', 'pekerjaan', 'pendidikan', 'minggu_kandungan', 'nama_suami', 'nik_suami', 'pekerjaan_suami', 'status_keluarga_suami'],
            'rows' => [
                ['3201234567890031', 'Dian Puspita', '3201234567890032', 'Bogor', '1992-02-14', 'Perempuan', 'istri', 'Jl. Cendana No 8', '03', '02', 'NON PBI', '0001234567893', '081455512345', 'Mengurus Rumah Tangga', 'SLTA/Sederajat', '20', 'Hendra Gunawan', '3201234567890033', 'Karyawan Swasta', 'kepala keluarga'],
                ['3201234567890034', 'Wulan Fitri', '3201234567890035', 'Tangerang', '1995-06-30', 'Perempuan', 'istri', 'Jl. Anggrek No 12', '01', '04', 'PBI', '', '081466678901', 'Mengurus Rumah Tangga', 'SLTP/Sederajat', '12', 'Ahmad Rizki', '3201234567890036', 'Wiraswasta', 'kepala keluarga'],
            ],
        ],
        'pralansia' => [
            'header' => ['nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'status_keluarga', 'alamat_sasaran', 'rt', 'rw', 'kepersertaan_bpjs', 'nomor_bpjs', 'nomor_telepon', 'pekerjaan', 'pendidikan'],
            'rows' => [
                ['3201234567890041', 'Slamet Widodo', '3201234567890042', 'Semarang', '1970-12-05', 'Laki-laki', 'kepala keluarga', 'Jl. Pahlawan No 20', '02', '03', 'NON PBI', '', '081577712345', 'Wiraswasta', 'SLTA/Sederajat'],
                ['3201234567890043', 'Siti Rahayu', '3201234567890042', 'Yogyakarta', '1972-03-18', 'Perempuan', 'istri', 'Jl. Pahlawan No 20', '02', '03', 'NON PBI', '0001234567894', '081577765432', 'Mengurus Rumah Tangga', 'Tamat SD/Sederajat'],
            ],
        ],
        'lansia' => [
            'header' => ['nik_sasaran', 'nama_sasaran', 'no_kk_sasaran', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'status_keluarga', 'alamat_sasaran', 'rt', 'rw', 'kepersertaan_bpjs', 'nomor_bpjs', 'nomor_telepon', 'pekerjaan', 'pendidikan'],
            'rows' => [
                ['3201234567890051', 'Marno Suparno', '3201234567890052', 'Solo', '1960-07-22', 'Laki-laki', 'kepala keluarga', 'Jl. Veteran No 5', '01', '05', 'PBI', '0001234567895', '081698765432', 'Pensiunan', 'SLTA/Sederajat'],
                ['3201234567890053', 'Surti Wulandari', '3201234567890052', 'Klaten', '1962-11-08', 'Perempuan', 'istri', 'Jl. Veteran No 5', '01', '05', 'PBI', '0001234567896', '', 'Mengurus Rumah Tangga', 'Tamat SD/Sederajat'],
            ],
        ],
    ];

    public static function getCsvContent(string $kategori): string
    {
        $data = self::$templates[$kategori] ?? self::$templates['dewasa'];
        $lines = [self::escapeCsvRow($data['header'])];
        foreach ($data['rows'] as $row) {
            $lines[] = self::escapeCsvRow($row);
        }
        return implode("\r\n", $lines);
    }

    /**
     * Generate Excel (.xlsx) template - data per kolom agar tampil benar di Excel.
     */
    public static function getExcelContent(string $kategori): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $data = self::$templates[$kategori] ?? self::$templates['dewasa'];
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template');
        $rowNum = 1;
        $sheet->fromArray($data['header'], null, 'A' . $rowNum);
        $rowNum++;
        foreach ($data['rows'] as $row) {
            $sheet->fromArray($row, null, 'A' . $rowNum);
            $rowNum++;
        }
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return $spreadsheet;
    }

    protected static function escapeCsvRow(array $row): string
    {
        return implode(',', array_map(function ($v) {
            return strpos($v, ',') !== false || strpos($v, '"') !== false || strpos($v, "\n") !== false
                ? '"' . str_replace('"', '""', $v) . '"' : $v;
        }, $row));
    }

    public static function getKategoriList(): array
    {
        return array_keys(self::$templates);
    }
}
