<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\Orangtua;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\SasaranIbuhamil;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

trait SasaranImportTrait
{
    use WithFileUploads;

    public $showImportModal = false;
    public $importKategori = '';
    public $importResult = null;
    public $importFile = null;
    /** Pesan error dari parseExcel saat exception (agar user tahu penyebab gagal). */
    public $importParseError = null;

    /** Ringkasan sheet saat import master (diagnostic). */
    private array $masterImportDiagnostics = [];

    public function openImportModal($kategori)
    {
        $this->importKategori = $kategori ?: 'dewasa';
        $this->importResult = null;
        $this->importFile = null;
        $this->importParseError = null;
        $this->resetValidation('importFile');
        $this->showImportModal = true;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->importKategori = '';
        $this->importResult = null;
        $this->importFile = null;
        $this->resetValidation('importFile');
    }

    public function importSasaran()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ], [
            'importFile.required' => 'Pilih file untuk diimport.',
            'importFile.mimes' => 'Format file harus CSV, TXT, XLSX, atau XLS.',
            'importFile.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $posyanduId = $this->posyanduId ?? null;
        if (!$posyanduId) {
            $this->importResult = ['added' => 0, 'skipped' => 0, 'errors' => 1, 'errorDetails' => ['Posyandu tidak ditemukan.']];
            return;
        }

        $this->importParseError = null;
        try {
            $rows = ($this->importKategori === 'master')
                ? $this->parseImportFileMaster($this->importFile)
                : $this->parseImportFile($this->importFile);
        } catch (\Throwable $e) {
            $this->importResult = [
                'added' => 0,
                'skipped' => 0,
                'errors' => 1,
                'errorDetails' => ['Gagal membaca file: ' . $e->getMessage()],
            ];
            return;
        }
        if (empty($rows)) {
            $details = ['Tidak ada baris data ditemukan. Pastikan baris pertama adalah header dan kolom NIK terisi.'];
            if ($this->importKategori === 'master') {
                $details = array_merge($details, $this->buildMasterImportEmptyHints());
            }
            if (!empty($this->importParseError)) {
                $details[] = 'Error: ' . $this->importParseError;
            }
            $this->importResult = ['added' => 0, 'skipped' => 0, 'errors' => 1, 'errorDetails' => $details];
            return;
        }

        $result = ($this->importKategori === 'master')
            ? $this->processImportRowsMaster($rows, $posyanduId)
            : $this->processImportRows($rows, $posyanduId);
        $this->importResult = $result;
        $this->importFile = null;

        if (method_exists($this, 'refreshPosyandu')) {
            $this->refreshPosyandu();
        }
        if (method_exists($this, 'loadPosyanduWithRelations')) {
            $this->loadPosyanduWithRelations();
        }
    }

    private function resolveImportFilePath($file): string
    {
        $path = $file->getRealPath();
        if ($path && is_readable($path)) {
            return $path;
        }
        if (method_exists($file, 'path')) {
            $alt = $file->path();
            if ($alt && is_readable($alt)) {
                return $alt;
            }
        }
        throw new \RuntimeException('File upload tidak dapat dibaca. Coba upload ulang.');
    }

    private function parseImportFile($file): array
    {
        $path = $this->resolveImportFilePath($file);
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, ['xlsx', 'xls'])) {
            return $this->parseExcel($path);
        }

        return $this->parseCsv($path);
    }

    /**
     * Import master: Excel (banyak sheet) atau CSV (satu file dengan kolom "kategori" per baris).
     */
    private function parseImportFileMaster($file): array
    {
        $path = $this->resolveImportFilePath($file);
        $ext = strtolower($file->getClientOriginalExtension());
        if (in_array($ext, ['xlsx', 'xls'])) {
            return $this->parseExcelMaster($path);
        }
        if (in_array($ext, ['csv', 'txt'])) {
            return $this->parseCsvMaster($path);
        }
        $this->importParseError = 'Import master: gunakan file Excel (.xlsx, .xls) atau CSV (.csv, .txt).';
        return [];
    }

    /**
     * CSV master: satu file, baris pertama = header. Wajib ada kolom "kategori" (bayibalita, remaja, dewasa, ibuhamil, pralansia, lansia).
     */
    private function parseCsvMaster(string $path): array
    {
        return $this->parseCsv($path);
    }

    /**
     * Map nama sheet (normalized) ke kode kategori.
     */
    private function getSheetNameToKategoriMap(): array
    {
        return [
            'bayibalita' => 'bayibalita',
            'remaja' => 'remaja',
            'dewasa' => 'dewasa',
            'ibuhamil' => 'ibuhamil',
            'pralansia' => 'pralansia',
            'lansia' => 'lansia',
        ];
    }

    private function normalizeSheetNameToKategori(string $sheetName): ?string
    {
        $v = strtolower(trim($sheetName));
        $compact = str_replace([' ', '-', '/', '_'], '', $v);
        $map = $this->getSheetNameToKategoriMap();
        if (isset($map[$compact])) {
            return $map[$compact];
        }

        if (preg_match('/^anak\d*$/', $compact) || str_starts_with($compact, 'anak')) {
            return 'bayibalita';
        }
        if (str_contains($compact, 'pralansia')) {
            return 'pralansia';
        }
        if (str_contains($compact, 'balita') || str_contains($compact, 'bayi')) {
            return 'bayibalita';
        }
        if (str_contains($compact, 'remaja')) {
            return 'remaja';
        }
        if (str_contains($compact, 'ibuhamil') || (str_contains($v, 'ibu') && str_contains($v, 'hamil'))) {
            return 'ibuhamil';
        }
        if (str_contains($compact, 'lansia')) {
            return 'lansia';
        }
        if (str_contains($compact, 'dewasa')) {
            return 'dewasa';
        }

        return null;
    }

    private function buildMasterImportEmptyHints(): array
    {
        $hints = [];
        if (!empty($this->masterImportDiagnostics)) {
            foreach ($this->masterImportDiagnostics as $sheet) {
                $kategoriLabel = $sheet['kategori']
                    ? 'dikenali sebagai ' . $sheet['kategori']
                    : 'nama sheet tidak dikenali';
                $hints[] = 'Sheet "' . $sheet['name'] . '": ' . $kategoriLabel . ', ' . $sheet['data_rows'] . ' baris dengan NIK.';
            }
        }
        $hints[] = 'Sheet bayi/balita: nama sheet boleh "Bayi Balita", "anak 1", "anak 2", dll. Header boleh "NIK Anak", "Nama Lengkap Anak", "NIK Ibu (Istri)", dll.';
        $hints[] = 'Pastikan baris 1 = header dan baris 2 dst berisi NIK. Format NIK sebagai Teks di Excel.';

        return $hints;
    }

    private function inferKategoriFromHeaders(array $normalizedHeaders): ?string
    {
        $fields = array_flip(array_filter($normalizedHeaders));

        if (isset($fields['minggu_kandungan']) || isset($fields['nik_suami'])) {
            return 'ibuhamil';
        }
        if (isset($fields['nik_sasaran']) && isset($fields['nik_orangtua'])) {
            return 'bayibalita';
        }
        if (isset($fields['nik_sasaran']) && (isset($fields['pekerjaan']) || isset($fields['pendidikan']))) {
            return 'dewasa';
        }
        if (isset($fields['nik_sasaran'])) {
            return 'dewasa';
        }

        return null;
    }

    private function parseExcelMaster(string $path): array
    {
        try {
            $this->importParseError = null;
            $this->masterImportDiagnostics = [];
            $spreadsheet = IOFactory::load($path);
            $allRows = [];

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetName = $sheet->getTitle();
                $kategori = $this->normalizeSheetNameToKategori($sheetName);
                $sheetRows = $this->parseExcelSheet($sheet, $kategori);

                $effectiveKategori = $kategori;
                if ($effectiveKategori === null && !empty($sheetRows)) {
                    $effectiveKategori = $sheetRows[0]['kategori'] ?? null;
                }
                if ($effectiveKategori === null) {
                    $effectiveKategori = $this->inferKategoriFromHeaders(
                        $this->extractNormalizedHeadersFromSheet($sheet)
                    );
                }

                $this->masterImportDiagnostics[] = [
                    'name' => $sheetName,
                    'kategori' => $effectiveKategori,
                    'data_rows' => count($sheetRows),
                ];
                array_push($allRows, ...$sheetRows);
            }

            if (empty($allRows)) {
                foreach ($spreadsheet->getAllSheets() as $sheet) {
                    $sheetName = $sheet->getTitle();
                    $sheetRows = $this->parseExcelSheet($sheet, null, true);
                    if (empty($sheetRows)) {
                        continue;
                    }
                    $allRows = array_merge($allRows, $sheetRows);
                    $this->masterImportDiagnostics[] = [
                        'name' => $sheetName,
                        'kategori' => 'kolom kategori',
                        'data_rows' => count($sheetRows),
                    ];
                }
            }

            return $allRows;
        } catch (\Throwable $e) {
            $this->importParseError = $e->getMessage();
            return [];
        }
    }

    /**
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet  $sheet
     */
    private function extractNormalizedHeadersFromSheet($sheet): array
    {
        $data = $sheet->toArray(null, true, true, true);
        if (empty($data)) {
            return [];
        }
        $rawHeader = $data[array_key_first($data)] ?? [];
        if (!is_array($rawHeader)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($val) => $this->normalizeImportHeader((string) $val),
            $rawHeader
        )));
    }

    /**
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet  $sheet
     */
    private function parseExcelSheet($sheet, ?string $defaultKategori, bool $requireKategoriColumn = false): array
    {
        $data = $sheet->toArray(null, true, true, true);
        if (empty($data)) {
            return [];
        }

        $rowKeys = array_keys($data);
        $rawHeader = $data[$rowKeys[0]] ?? null;
        if (!is_array($rawHeader) || empty($rawHeader)) {
            return [];
        }

        $normalizedByCol = [];
        foreach ($rawHeader as $col => $val) {
            $normalizedByCol[$col] = $this->normalizeImportHeader((string) $val);
        }

        $hasKategoriCol = in_array('kategori', $normalizedByCol, true);
        if ($requireKategoriColumn && !$hasKategoriCol) {
            return [];
        }
        if ($defaultKategori === null && !$hasKategoriCol) {
            $inferred = $this->inferKategoriFromHeaders(array_values($normalizedByCol));
            if ($inferred === null) {
                return [];
            }
            $defaultKategori = $inferred;
        }

        $rows = [];
        for ($i = 1; $i < count($rowKeys); $i++) {
            $rowKey = $rowKeys[$i];
            $row = ['kategori' => $defaultKategori];

            foreach ($rawHeader as $col => $_) {
                $h = $normalizedByCol[$col];
                if ($h === '') {
                    continue;
                }
                $cellValue = $data[$rowKey][$col] ?? '';
                if (in_array($h, ['nik_sasaran', 'no_kk_sasaran', 'nik_orangtua', 'nik_suami', 'nomor_bpjs'], true)) {
                    $row[$h] = $this->normalizeNikCellValue($cellValue);
                } elseif ($h === 'kepersertaan_bpjs') {
                    $row[$h] = $this->normalizeKepersertaanBpjsFromImport($cellValue) ?? '';
                } else {
                    $row[$h] = trim((string) $cellValue);
                }
            }

            if ($this->isEmptyImportRow($row)) {
                continue;
            }

            if ($hasKategoriCol && $requireKategoriColumn) {
                $row['kategori'] = $this->normalizeKategori($row['kategori'] ?? '');
            }

            if (!empty($row['nik_sasaran'] ?? '')) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function isEmptyImportRow(array $row): bool
    {
        foreach ($row as $key => $value) {
            if ($key === 'kategori') {
                continue;
            }
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeNikCellValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if (is_float($value)) {
            return sprintf('%.0f', $value);
        }
        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            return '';
        }
        if (preg_match('/^(\d+)\.0+$/', $stringValue, $matches)) {
            return $matches[1];
        }
        if (preg_match('/^[0-9.eE+-]+$/', $stringValue) && (str_contains($stringValue, 'E') || str_contains($stringValue, 'e'))) {
            return sprintf('%.0f', (float) $stringValue);
        }

        return preg_replace('/\s+/', '', $stringValue) ?? $stringValue;
    }

    private function normalizeImportHeader(string $header): string
    {
        $raw = trim(mb_strtolower($header));
        if ($raw === '') {
            return '';
        }

        if (preg_match('/tempat\s*lahir\s*2/u', $raw)) {
            return 'tempat_lahir_orangtua';
        }
        if (preg_match('/tempat\s*lahir\s*3/u', $raw)) {
            return 'tempat_lahir';
        }
        if (preg_match('/tanggal\s*lahir\s*2/u', $raw)) {
            return 'tanggal_lahir_orangtua';
        }
        if (preg_match('/tanggal\s*lahir\s*3/u', $raw)) {
            return 'tanggal_lahir';
        }

        $withoutParens = trim(preg_replace('/\([^)]*\)/u', '', $raw));
        $withoutParens = preg_replace('/\s*:\s*\d+\s*$/u', '', $withoutParens);
        $clean = preg_replace('/\s+\d+$/u', '', trim($withoutParens));
        $clean = preg_replace('/[^a-z0-9\s_]/u', '', $clean);
        $clean = preg_replace('/\s+/u', ' ', trim($clean));
        $snake = str_replace(' ', '_', $clean);

        $exact = [
            'nik_sasaran' => 'nik_sasaran',
            'nik_anak' => 'nik_sasaran',
            'nama_sasaran' => 'nama_sasaran',
            'nama_lengkap_anak' => 'nama_sasaran',
            'no_kk_sasaran' => 'no_kk_sasaran',
            'no_kk' => 'no_kk_sasaran',
            'tempat_lahir' => 'tempat_lahir',
            'tanggal_lahir' => 'tanggal_lahir',
            'jenis_kelamin' => 'jenis_kelamin',
            'status_keluarga' => 'status_keluarga',
            'status_kel' => 'status_keluarga',
            'alamat_sasaran' => 'alamat_sasaran',
            'alamat_lengkap_sesuai' => 'alamat_sasaran',
            'rt' => 'rt',
            'rw' => 'rw',
            'kepersertaan_bpjs' => 'kepersertaan_bpjs',
            'nomor_bpjs' => 'nomor_bpjs',
            'no_bpjs' => 'nomor_bpjs',
            'no_bpjs_3' => 'nomor_bpjs',
            'nik_orangtua' => 'nik_orangtua',
            'nik_ibu' => 'nik_orangtua',
            'nik_ibu_istri' => 'nik_orangtua',
            'nama_orangtua' => 'nama_orangtua',
            'nama_lengkap_ibu' => 'nama_orangtua',
            'nama_lengkap_ibu_istri' => 'nama_orangtua',
            'tempat_lahir_orangtua' => 'tempat_lahir_orangtua',
            'tanggal_lahir_orangtua' => 'tanggal_lahir_orangtua',
            'pekerjaan_orangtua' => 'pekerjaan_orangtua',
            'pendidikan_orangtua' => 'pendidikan_orangtua',
            'status_keluarga_orangtua' => 'status_keluarga_orangtua',
            'nomor_telepon' => 'nomor_telepon',
            'pekerjaan' => 'pekerjaan',
            'pendidikan' => 'pendidikan',
            'kategori' => 'kategori',
            'minggu_kandungan' => 'minggu_kandungan',
            'nama_suami' => 'nama_suami',
            'nik_suami' => 'nik_suami',
            'pekerjaan_suami' => 'pekerjaan_suami',
            'status_keluarga_suami' => 'status_keluarga_suami',
        ];

        if (isset($exact[$snake])) {
            return $exact[$snake];
        }

        if (str_contains($snake, 'nik') && (str_contains($snake, 'ibu') || str_contains($snake, 'istri') || str_contains($snake, 'ortu') || str_contains($snake, 'orangtua'))) {
            return 'nik_orangtua';
        }
        if (str_contains($snake, 'nik') && str_contains($snake, 'anak')) {
            return 'nik_sasaran';
        }
        if (str_contains($snake, 'nama') && (str_contains($snake, 'ibu') || str_contains($snake, 'istri') || str_contains($snake, 'ortu') || str_contains($snake, 'orangtua'))) {
            return 'nama_orangtua';
        }
        if (str_contains($snake, 'nama') && str_contains($snake, 'anak')) {
            return 'nama_sasaran';
        }
        if (str_contains($snake, 'alamat')) {
            return 'alamat_sasaran';
        }
        if (str_contains($snake, 'bpjs') && str_contains($snake, 'no')) {
            return 'nomor_bpjs';
        }
        if (str_contains($snake, 'bpjs') || str_contains($snake, 'terdaftar_bpjs')) {
            return 'kepersertaan_bpjs';
        }
        if (str_contains($snake, 'tanggal') && str_contains($snake, 'lahir') && (str_contains($snake, 'ibu') || str_contains($snake, 'istri') || str_contains($snake, 'ortu'))) {
            return 'tanggal_lahir_orangtua';
        }
        if (str_contains($snake, 'tempat') && str_contains($snake, 'lahir') && (str_contains($snake, 'ibu') || str_contains($snake, 'istri') || str_contains($snake, 'ortu'))) {
            return 'tempat_lahir_orangtua';
        }
        if (str_contains($snake, 'tanggal') && str_contains($snake, 'lahir')) {
            return 'tanggal_lahir';
        }
        if (str_contains($snake, 'tempat') && str_contains($snake, 'lahir')) {
            return 'tempat_lahir';
        }
        if ($snake === 'nik') {
            return 'nik_sasaran';
        }
        if (str_contains($snake, 'no') && str_contains($snake, 'kk')) {
            return 'no_kk_sasaran';
        }

        return $this->normalizeHeaders([$header])[0];
    }

    private function normalizeKepersertaanBpjsFromImport(mixed $value): ?string
    {
        $v = strtoupper(trim((string) $value));
        if ($v === '') {
            return null;
        }
        if ($v === 'PBI' || str_starts_with($v, 'PBI ')) {
            return 'PBI';
        }
        if (str_contains($v, 'NON PBI') || str_contains($v, 'NON-PBI') || str_contains($v, 'TIDAK PENERIMA') || str_contains($v, 'NONPBI')) {
            return 'NON PBI';
        }
        if ($v === 'NON PBI') {
            return 'NON PBI';
        }

        return $this->normalizeKepersertaanBpjs($value);
    }

    private function parseCsv(string $path): array
    {
        $content = file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // Remove BOM

        $delimiters = [',', ';', "\t"];
        $delimiter = ',';
        $firstLine = strtok($content, "\n");
        foreach ($delimiters as $d) {
            if (substr_count($firstLine, $d) > substr_count($firstLine, $delimiter)) {
                $delimiter = $d;
            }
        }

        $lines = array_filter(explode("\n", $content));
        $header = str_getcsv(array_shift($lines), $delimiter);
        $header = array_map(fn ($h) => $this->normalizeImportHeader($h), array_map('trim', $header));

        $rows = [];
        foreach ($lines as $line) {
            $cols = str_getcsv($line, $delimiter);
            if (count($cols) < 2) continue;
            $row = [];
            foreach ($header as $i => $h) {
                $value = trim($cols[$i] ?? '');
                if (in_array($h, ['nik_sasaran', 'no_kk_sasaran', 'nik_orangtua', 'nik_suami', 'nomor_bpjs'], true)) {
                    $row[$h] = $this->normalizeNikCellValue($value);
                } elseif ($h === 'kepersertaan_bpjs') {
                    $row[$h] = $this->normalizeKepersertaanBpjsFromImport($value) ?? '';
                } else {
                    $row[$h] = $value;
                }
            }
            if (!empty(trim($row['nik_sasaran'] ?? ''))) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    private function parseExcel(string $path): array
    {
        try {
            $this->importParseError = null;
            $spreadsheet = IOFactory::load($path);

            return $this->parseExcelSheet(
                $spreadsheet->getActiveSheet(),
                $this->importKategori ?: null
            );
        } catch (\Throwable $e) {
            $this->importParseError = $e->getMessage();
            return [];
        }
    }

    private function normalizeHeaders(array $headers): array
    {
        $aliasMap = ['status_kel' => 'status_keluarga'];
        return array_map(function ($h) use ($aliasMap) {
            $h = trim((string) $h);
            $h = strtolower($h);
            $h = str_replace(' ', '_', $h);
            return $aliasMap[$h] ?? $h;
        }, $headers);
    }

    /**
     * Ubah pesan error teknis (SQL/database) menjadi pesan yang mudah dipahami pengguna.
     */
    private function formatImportErrorMessage(\Throwable $e): string
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'Could not parse') || str_contains($msg, 'Failed to parse time string')) {
            return 'Tanggal lahir tidak valid. Gunakan format DD/MM/YYYY (contoh: 15/06/2001).';
        }

        // Data truncated for column 'X' at row 1
        if (preg_match('/Data truncated for column [\'"]([^\'"]+)[\'"]/i', $msg, $m)) {
            $col = $m[1];
            $label = [
                'kepersertaan_bpjs' => 'Kepersertaan BPJS',
                'nomor_bpjs' => 'Nomor BPJS',
                'pendidikan' => 'Pendidikan',
                'pekerjaan' => 'Pekerjaan',
                'nama_sasaran' => 'Nama',
                'nik_sasaran' => 'NIK',
                'alamat_sasaran' => 'Alamat',
            ][$col] ?? ucfirst(str_replace('_', ' ', $col));
            return "Kolom \"{$label}\" terlalu panjang atau format tidak sesuai. Periksa nilai yang diisi (gunakan PBI/NON PBI untuk BPJS, format benar untuk pendidikan/pekerjaan).";
        }

        // Incorrect integer/string value
        if (preg_match('/Incorrect (integer|string|double) value/i', $msg) || preg_match('/Column [\'"]([^\'"]+)[\'"] cannot be null/i', $msg, $m)) {
            return 'Data tidak valid. Periksa kolom wajib diisi dan format data (tanggal, jenis kelamin, pendidikan, pekerjaan).';
        }

        // SQLSTATE umum
        if (str_contains($msg, 'SQLSTATE[') || str_contains($msg, 'Connection: mysql')) {
            return 'Data tidak valid. Periksa format kolom wajib: tanggal lahir (YYYY-MM-DD), jenis kelamin (Laki-laki/Perempuan), pendidikan, pekerjaan. Pastikan tidak ada data yang salah kolom.';
        }

        return $msg;
    }

    private function processImportRows(array $rows, int $posyanduId): array
    {
        $added = 0;
        $skipped = 0;
        $errors = 0;
        $errorDetails = [];

        foreach ($rows as $idx => $row) {
            try {
                $exists = $this->checkSasaranExists($row, $posyanduId);
                if ($exists) {
                    $skipped++;
                    continue;
                }
                $this->createSasaranFromRow($row, $posyanduId);
                $added++;
            } catch (\Throwable $e) {
                $errors++;
                $errorDetails[] = 'Baris ' . ($idx + 2) . ': ' . $this->formatImportErrorMessage($e);
            }
        }

        return [
            'added' => $added,
            'skipped' => $skipped,
            'errors' => $errors,
            'errorDetails' => $errorDetails,
        ];
    }

    /**
     * Import master: setiap baris punya kolom "kategori" (bayibalita, remaja, dewasa, ibuhamil, pralansia, lansia).
     */
    private function processImportRowsMaster(array $rows, int $posyanduId): array
    {
        $added = 0;
        $skipped = 0;
        $errors = 0;
        $errorDetails = [];
        $validKategori = ['bayibalita', 'remaja', 'dewasa', 'pralansia', 'lansia', 'ibuhamil'];

        foreach ($rows as $idx => $row) {
            try {
                $kategoriRaw = trim((string) ($row['kategori'] ?? ''));
                if ($kategoriRaw === '') {
                    $errors++;
                    $errorDetails[] = 'Baris ' . ($idx + 2) . ': Kolom kategori wajib diisi (bayibalita, remaja, dewasa, ibuhamil, pralansia, lansia).';
                    continue;
                }
                $kategori = $this->normalizeKategori($kategoriRaw);
                if (!in_array($kategori, $validKategori, true)) {
                    $errors++;
                    $errorDetails[] = 'Baris ' . ($idx + 2) . ': Kategori tidak valid "' . $kategoriRaw . '". Gunakan: bayibalita, remaja, dewasa, ibuhamil, pralansia, lansia.';
                    continue;
                }
                $prevKategori = $this->importKategori;
                $this->importKategori = $kategori;
                try {
                    $exists = $this->checkSasaranExists($row, $posyanduId);
                    if ($exists) {
                        $skipped++;
                    } else {
                        $this->createSasaranFromRow($row, $posyanduId);
                        $added++;
                    }
                } finally {
                    $this->importKategori = $prevKategori;
                }
            } catch (\Throwable $e) {
                $errors++;
                $errorDetails[] = 'Baris ' . ($idx + 2) . ' (' . ($row['kategori'] ?? '') . '): ' . $this->formatImportErrorMessage($e);
            }
        }

        return [
            'added' => $added,
            'skipped' => $skipped,
            'errors' => $errors,
            'errorDetails' => $errorDetails,
        ];
    }

    private function normalizeKategori(string $value): string
    {
        $v = strtolower(trim($value));
        $v = str_replace([' ', '-', '/', '_'], '', $v);
        $map = [
            'bayibalita' => 'bayibalita',
            'balita' => 'bayibalita',
            'remaja' => 'remaja',
            'dewasa' => 'dewasa',
            'ibuhamil' => 'ibuhamil',
            'pralansia' => 'pralansia',
            'lansia' => 'lansia',
        ];
        return $map[$v] ?? $v;
    }

    private function checkSasaranExists(array $row, int $posyanduId): bool
    {
        $nik = trim($row['nik_sasaran'] ?? '');
        if (empty($nik)) return false;

        $tables = [
            'bayibalita' => SasaranBayibalita::class,
            'remaja' => SasaranRemaja::class,
            'dewasa' => SasaranDewasa::class,
            'pralansia' => SasaranPralansia::class,
            'lansia' => SasaranLansia::class,
            'ibuhamil' => SasaranIbuhamil::class,
        ];
        $model = $tables[$this->importKategori] ?? SasaranDewasa::class;
        return $model::where('nik_sasaran', $nik)->where('id_posyandu', $posyanduId)->exists();
    }

    private function createSasaranFromRow(array $row, int $posyanduId): void
    {
        $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? '');
        if (!$tanggalLahir) {
            throw new \Exception('Tanggal lahir tidak valid.');
        }

        $jenisKelamin = $this->normalizeJenisKelamin($row['jenis_kelamin'] ?? '');
        if (!$jenisKelamin) {
            throw new \Exception('Jenis kelamin harus Laki-laki atau Perempuan.');
        }

        switch ($this->importKategori) {
            case 'bayibalita':
                $this->createSasaranBayibalita($row, $posyanduId, $tanggalLahir, $jenisKelamin);
                break;
            case 'remaja':
                $this->createSasaranRemaja($row, $posyanduId, $tanggalLahir, $jenisKelamin);
                break;
            case 'dewasa':
                $this->createSasaranDewasa($row, $posyanduId, $tanggalLahir, $jenisKelamin);
                break;
            case 'pralansia':
                $this->createSasaranPralansia($row, $posyanduId, $tanggalLahir, $jenisKelamin);
                break;
            case 'lansia':
                $this->createSasaranLansia($row, $posyanduId, $tanggalLahir, $jenisKelamin);
                break;
            case 'ibuhamil':
                $this->createSasaranIbuhamil($row, $posyanduId, $tanggalLahir, $jenisKelamin);
                break;
            default:
                $this->createSasaranDewasa($row, $posyanduId, $tanggalLahir, $jenisKelamin);
        }
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        try {
            if (is_numeric($value)) {
                $d = ExcelDate::excelToDateTimeObject((float) $value);

                return $d->format('Y-m-d');
            }

            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $m)) {
                return $this->buildValidDate((int) $m[3], (int) $m[2], (int) $m[1]);
            }

            if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $value, $m)) {
                return $this->buildValidDate((int) $m[3], (int) $m[2], (int) $m[1]);
            }

            if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $m)) {
                $year = (int) $m[1];
                $second = (int) $m[2];
                $third = (int) $m[3];

                $asYearMonthDay = $this->buildValidDate($year, $second, $third);
                if ($asYearMonthDay !== null) {
                    return $asYearMonthDay;
                }

                // Excel sering menghasilkan YYYY-DD-MM dari tampilan DD/MM/YYYY
                return $this->buildValidDate($year, $third, $second);
            }

            $d = Carbon::parse($value);

            return $d->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function buildValidDate(int $year, int $month, int $day): ?string
    {
        if ($year < 1900 || $year > 2100) {
            return null;
        }
        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    private function normalizeJenisKelamin($value): ?string
    {
        $v = strtolower(trim((string) $value));
        if (in_array($v, ['laki-laki', 'laki laki', 'l', 'lk', 'laki'], true)) return 'Laki-laki';
        if (in_array($v, ['perempuan', 'p', 'pr'], true)) return 'Perempuan';
        return null;
    }

    /** Hanya nilai PBI atau NON PBI; nilai numerik (nomor BPJS) dikembalikan null. */
    private function normalizeKepersertaanBpjs($value): ?string
    {
        $v = strtoupper(trim((string) $value));
        if ($v === 'PBI' || $v === 'NON PBI') return $v;
        if (preg_match('/^\d+$/', $v)) return null; // nomor BPJS jangan masuk kolom kepersertaan
        return null;
    }

    /** Map singkatan ke nilai enum pendidikan (SLTP, S1, D3, dll.). */
    private function normalizePendidikan($value): ?string
    {
        $v = trim((string) $value);
        if ($v === '') return null;
        $enumValues = [
            'Tidak/Belum Sekolah', 'Tidak Tamat SD/Sederajat', 'Tamat SD/Sederajat',
            'SLTP/Sederajat', 'SLTA/Sederajat', 'Diploma I/II', 'Akademi/Diploma III/Sarjana Muda',
            'Diploma IV/Strata I', 'Strata II', 'Strata III',
        ];
        $upper = strtoupper($v);
        if (in_array($v, $enumValues, true)) return $v;
        $map = [
            'SD' => 'Tamat SD/Sederajat',
            'SMP' => 'SLTP/Sederajat',
            'SMA' => 'SLTA/Sederajat',
            'SMK' => 'SLTA/Sederajat',
            'S1' => 'Diploma IV/Strata I',
            'S2' => 'Strata II',
            'S3' => 'Strata III',
            'D1' => 'Diploma I/II',
            'D2' => 'Diploma I/II',
            'D3' => 'Akademi/Diploma III/Sarjana Muda',
            'D4' => 'Diploma IV/Strata I',
        ];
        return $map[$upper] ?? null;
    }

    private function normalizePekerjaan(?string $value): ?string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return null;
        }
        $map = [
            'ibu rumah tangga' => 'Mengurus Rumah Tangga',
            'irt' => 'Mengurus Rumah Tangga',
            'pns' => 'Aparatur Sipil Negara (ASN)',
            'asn' => 'Aparatur Sipil Negara (ASN)',
            'pegawai negeri sipil' => 'Aparatur Sipil Negara (ASN)',
            'tni' => 'Tentara Nasional Indonesia',
            'polri' => 'Kepolisian RI (POLRI)',
            'kepolisian ri' => 'Kepolisian RI (POLRI)',
            'wiraswasta' => 'Wiraswasta',
            'swasta' => 'Karyawan Swasta',
            'petani' => 'Petani/Pekebun',
            'nelayan' => 'Nelayan/Perikanan',
            'guru' => 'Guru',
            'dosen' => 'Dosen',
            'bidan' => 'Bidan',
            'perawat' => 'Perawat',
            'dokter' => 'Dokter',
            'pedagang' => 'Pedagang',
            'buruh' => 'Buruh Harian Lepas',
            'pensiunan' => 'Pensiunan',
            'pelajar' => 'Pelajar/Mahasiswa',
            'mahasiswa' => 'Pelajar/Mahasiswa',
        ];
        $lower = strtolower($v);

        return $map[$lower] ?? $v;
    }

    /** @return list<string> */
    private function getOrangtuaPekerjaanAllowedValues(): array
    {
        return [
            'Belum/Tidak Bekerja', 'Mengurus Rumah Tangga', 'Pelajar/Mahasiswa', 'Pensiunan',
            'Aparatur Sipil Negara (ASN)', 'Tentara Nasional Indonesia', 'Kepolisian RI (POLRI)',
            'Perdagangan', 'Petani/Pekebun', 'Peternak', 'Nelayan/Perikanan', 'Industri',
            'Konstruksi', 'Transportasi', 'Karyawan Swasta', 'Karyawan BUMN', 'Karyawan BUMD',
            'Karyawan Honorer', 'Buruh Harian Lepas', 'Buruh Tani/Perkebunan', 'Buruh Nelayan/Perikanan',
            'Buruh Peternakan', 'Pembantu Rumah Tangga', 'Tukang Cukur', 'Tukang Listrik', 'Tukang Batu',
            'Tukang Kayu', 'Tukang Sol Sepatu', 'Tukang Las/Pandai Besi', 'Tukang Jahit', 'Tukang Gigi',
            'Penata Rias', 'Penata Busana', 'Penata Rambut', 'Mekanik', 'Seniman', 'Tabib', 'Paraji',
            'Perancang Busana', 'Penerjemah', 'Imam Masjid', 'Pendeta', 'Pastor', 'Wartawan',
            'Ustadz/Mubaligh', 'Juru Masak', 'Promotor Acara', 'Anggota DPR-RI', 'Anggota DPD', 'Anggota BPK',
            'Presiden', 'Wakil Presiden', 'Anggota Mahkamah Konstitusi', 'Anggota Kabinet/Kementerian',
            'Duta Besar/Kepala Perwakilan', 'Gubernur', 'Wakil Gubernur', 'Bupati', 'Wakil Bupati',
            'Walikota', 'Wakil Walikota', 'Anggota DPRD Provinsi', 'Anggota DPRD Kab/Kota', 'Dosen', 'Guru',
            'Pilot', 'Pengacara', 'Notaris', 'Arsitek', 'Akuntan', 'Konsultan', 'Dokter', 'Bidan', 'Perawat',
            'Apoteker', 'Psikiater/Psikolog', 'Penyiar Televisi', 'Penyiar Radio', 'Pelaut', 'Peneliti', 'Sopir',
            'Pialang', 'Paranormal', 'Pedagang', 'Perangkat Desa', 'Kepala Desa', 'Biarawati', 'Wiraswasta',
            'Artis', 'Atlet', 'Chef', 'Manajer', 'Tenaga Tata Usaha', 'Operator',
            'Pekerja Pengolahan, Kerajinan', 'Teknisi', 'Asisten Ahli', 'Gembala', 'Uskup', 'Biarawan',
            'Pandita', 'Pinandita', 'Bhikkhu', 'Xueshi', 'Wenshi', 'Jiaosheng', 'Lainnya',
        ];
    }

    private function mapLegacyOrangtuaPekerjaan(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $legacyMap = [
            'Pegawai Negeri Sipil' => 'Aparatur Sipil Negara (ASN)',
            'Kepolisian RI' => 'Kepolisian RI (POLRI)',
            'Penterjemah' => 'Penerjemah',
            'Anggota DPRD Kabupaten/Kota' => 'Anggota DPRD Kab/Kota',
            'Duta Besar' => 'Duta Besar/Kepala Perwakilan',
            'Bhikhu' => 'Bhikkhu',
        ];

        return $legacyMap[$value] ?? $value;
    }

    /** Pekerjaan untuk tabel orangtua: hanya nilai enum, selain itu 'Lainnya'. */
    private function normalizePekerjaanOrangtua(?string $value): string
    {
        $allowed = $this->getOrangtuaPekerjaanAllowedValues();
        $v = $this->mapLegacyOrangtuaPekerjaan($this->normalizePekerjaan($value));
        if ($v === null || $v === '') {
            return 'Belum/Tidak Bekerja';
        }

        return in_array($v, $allowed, true) ? $v : 'Lainnya';
    }

    /** Pekerjaan suami (sasaran_ibuhamils): hanya nilai enum, selain itu null. */
    private function normalizePekerjaanSuami(?string $value): ?string
    {
        $allowed = $this->getOrangtuaPekerjaanAllowedValues();
        $v = $this->mapLegacyOrangtuaPekerjaan($this->normalizePekerjaan($value));
        if ($v === null || $v === '') {
            return null;
        }

        return in_array($v, $allowed, true) ? $v : null;
    }

    private function createSasaranBayibalita(array $row, int $posyanduId, string $tanggalLahir, string $jenisKelamin): void
    {
        $nikOrtu = trim($row['nik_orangtua'] ?? '');
        $namaOrtu = trim($row['nama_orangtua'] ?? '');
        if (empty($nikOrtu) || empty($namaOrtu)) {
            throw new \Exception('NIK dan nama orangtua wajib diisi.');
        }

        DB::transaction(function () use ($row, $posyanduId, $tanggalLahir, $jenisKelamin, $nikOrtu, $namaOrtu) {
            $orangtua = Orangtua::firstOrCreate(
                ['nik' => $nikOrtu],
                [
                    'nama' => $namaOrtu,
                    'no_kk' => trim($row['no_kk_sasaran'] ?? $row['no_kk_sasaran'] ?? $nikOrtu) ?: $nikOrtu,
                    'tempat_lahir' => trim($row['tempat_lahir_orangtua'] ?? '') ?: null,
                    'tanggal_lahir' => $this->parseDate($row['tanggal_lahir_orangtua'] ?? '') ?: now(),
                    'pekerjaan' => $this->normalizePekerjaanOrangtua($row['pekerjaan_orangtua'] ?? ''),
                    'pendidikan' => $this->normalizePendidikan($row['pendidikan_orangtua'] ?? ''),
                    'kelamin' => $this->normalizeJenisKelamin($row['kelamin_orangtua'] ?? '') ?? 'Perempuan',
                    'alamat' => trim($row['alamat_sasaran'] ?? '') ?: null,
                    'kepersertaan_bpjs' => $this->normalizeKepersertaanBpjs($row['kepersertaan_bpjs_orangtua'] ?? $row['kepersertaan_bpjs'] ?? ''),
                    'nomor_bpjs' => trim($row['nomor_bpjs_orangtua'] ?? $row['nomor_bpjs'] ?? '') ?: null,
                    'nomor_telepon' => trim($row['nomor_telepon_orangtua'] ?? '') ?: null,
                ]
            );

            $user = User::firstOrCreate(
                ['email' => ($orangtua->no_kk ?? $nikOrtu) . '@gmail.com'],
                [
                    'name' => $orangtua->nama,
                    'password' => Hash::make('password'),
                ]
            );
            if (!$user->hasRole('orangtua')) {
                $user->assignRole('orangtua');
            }

            $umur = Carbon::parse($tanggalLahir)->age;
            SasaranBayibalita::create([
                'id_posyandu' => $posyanduId,
                'id_users' => null,
                'nama_sasaran' => trim($row['nama_sasaran'] ?? ''),
                'nik_sasaran' => trim($row['nik_sasaran'] ?? ''),
                'no_kk_sasaran' => trim($row['no_kk_sasaran'] ?? '') ?: null,
                'tempat_lahir' => trim($row['tempat_lahir'] ?? '') ?: null,
                'tanggal_lahir' => $tanggalLahir,
                'jenis_kelamin' => $jenisKelamin,
                'status_keluarga' => in_array($row['status_keluarga'] ?? '', ['kepala keluarga', 'istri', 'anak']) ? $row['status_keluarga'] : null,
                'umur_sasaran' => $umur,
                'nik_orangtua' => $nikOrtu,
                'alamat_sasaran' => trim($row['alamat_sasaran'] ?? '') ?: null,
                'rt' => trim($row['rt'] ?? '') ?: null,
                'rw' => trim($row['rw'] ?? '') ?: null,
                'kepersertaan_bpjs' => $this->normalizeKepersertaanBpjs($row['kepersertaan_bpjs'] ?? ''),
                'nomor_bpjs' => trim($row['nomor_bpjs'] ?? '') ?: null,
            ]);

            if ($orangtua->tanggal_lahir && $orangtua->umur >= 18 && $orangtua->umur <= 59) {
                $statusKeluarga = trim($row['status_keluarga_orangtua'] ?? $row['status_keluarga'] ?? '');
                if (in_array($statusKeluarga, ['kepala keluarga', 'istri', 'mertua', 'menantu', 'kerabat lain'])) {
                    $this->createOrUpdateSasaranOrangtua($orangtua, $posyanduId, $row, $statusKeluarga);
                }
            }
        });
    }

    private function createOrUpdateSasaranOrangtua($orangtua, int $posyanduId, array $row, string $statusKeluarga): void
    {
        $sasaranData = [
            'id_posyandu' => $posyanduId,
            'nama_sasaran' => $orangtua->nama,
            'nik_sasaran' => $orangtua->nik,
            'no_kk_sasaran' => $orangtua->no_kk,
            'tempat_lahir' => $orangtua->tempat_lahir,
            'tanggal_lahir' => $orangtua->tanggal_lahir,
            'jenis_kelamin' => $orangtua->kelamin,
            'umur_sasaran' => $orangtua->umur,
            'pekerjaan' => $orangtua->pekerjaan,
            'alamat_sasaran' => $orangtua->alamat,
            'rt' => trim($row['rt'] ?? '') ?: null,
            'rw' => trim($row['rw'] ?? '') ?: null,
            'kepersertaan_bpjs' => $orangtua->kepersertaan_bpjs,
            'nomor_bpjs' => $orangtua->nomor_bpjs,
            'nomor_telepon' => $orangtua->nomor_telepon,
            'nik_orangtua' => null,
            'status_keluarga' => $statusKeluarga,
        ];
        $umur = $orangtua->umur;
        $email = ($orangtua->no_kk ?? $orangtua->nik) . '@gmail.com';
        $user = User::where('email', $email)->first();
        if ($user) {
            $sasaranData['id_users'] = $user->id;
        }

        if ($umur >= 18 && $umur <= 45) {
            if (!SasaranDewasa::where('nik_sasaran', $orangtua->nik)->where('id_posyandu', $posyanduId)->exists()) {
                $sasaranData['pendidikan'] = $orangtua->pendidikan;
                SasaranDewasa::create($sasaranData);
            }
        } elseif ($umur >= 46 && $umur <= 59) {
            if (!SasaranPralansia::where('nik_sasaran', $orangtua->nik)->where('id_posyandu', $posyanduId)->exists()) {
                SasaranPralansia::create($sasaranData);
            }
        } elseif ($umur >= 60) {
            if (!SasaranLansia::where('nik_sasaran', $orangtua->nik)->where('id_posyandu', $posyanduId)->exists()) {
                SasaranLansia::create($sasaranData);
            }
        }
    }

    private function createSasaranRemaja(array $row, int $posyanduId, string $tanggalLahir, string $jenisKelamin): void
    {
        $nikOrtu = trim($row['nik_orangtua'] ?? '');
        $namaOrtu = trim($row['nama_orangtua'] ?? '');
        if (empty($nikOrtu) || empty($namaOrtu)) {
            throw new \Exception('NIK dan nama orangtua wajib diisi.');
        }

        Orangtua::firstOrCreate(
            ['nik' => $nikOrtu],
            [
                'nama' => $namaOrtu,
                'no_kk' => $row['no_kk_sasaran'] ?? $nikOrtu,
                'tempat_lahir' => trim($row['tempat_lahir_orangtua'] ?? '') ?: null,
                'tanggal_lahir' => $this->parseDate($row['tanggal_lahir_orangtua'] ?? '') ?: now(),
                'pekerjaan' => $this->normalizePekerjaanOrangtua($row['pekerjaan_orangtua'] ?? ''),
                'pendidikan' => $this->normalizePendidikan($row['pendidikan_orangtua'] ?? ''),
                'kelamin' => $this->normalizeJenisKelamin($row['kelamin_orangtua'] ?? '') ?? 'Perempuan',
                'alamat' => trim($row['alamat_sasaran'] ?? '') ?: null,
            ]
        );

        $umur = Carbon::parse($tanggalLahir)->age;
        SasaranRemaja::create([
            'id_posyandu' => $posyanduId,
            'id_users' => null,
            'nama_sasaran' => trim($row['nama_sasaran'] ?? ''),
            'nik_sasaran' => trim($row['nik_sasaran'] ?? ''),
            'no_kk_sasaran' => trim($row['no_kk_sasaran'] ?? '') ?: null,
            'tempat_lahir' => trim($row['tempat_lahir'] ?? '') ?: null,
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => $jenisKelamin,
            'status_keluarga' => in_array($row['status_keluarga'] ?? '', ['kepala keluarga', 'istri', 'anak']) ? $row['status_keluarga'] : null,
            'umur_sasaran' => $umur,
            'nik_orangtua' => $nikOrtu,
            'alamat_sasaran' => trim($row['alamat_sasaran'] ?? '') ?: null,
            'rt' => trim($row['rt'] ?? '') ?: null,
            'rw' => trim($row['rw'] ?? '') ?: null,
            'kepersertaan_bpjs' => $this->normalizeKepersertaanBpjs($row['kepersertaan_bpjs'] ?? ''),
            'nomor_bpjs' => trim($row['nomor_bpjs'] ?? '') ?: null,
            'nomor_telepon' => trim($row['nomor_telepon'] ?? '') ?: null,
            'pendidikan' => $this->normalizePendidikan($row['pendidikan'] ?? ''),
        ]);
    }

    private function createSasaranDewasa(array $row, int $posyanduId, string $tanggalLahir, string $jenisKelamin): void
    {
        $umur = Carbon::parse($tanggalLahir)->age;
        SasaranDewasa::create([
            'id_posyandu' => $posyanduId,
            'id_users' => null,
            'nama_sasaran' => trim($row['nama_sasaran'] ?? ''),
            'nik_sasaran' => trim($row['nik_sasaran'] ?? ''),
            'no_kk_sasaran' => trim($row['no_kk_sasaran'] ?? '') ?: null,
            'tempat_lahir' => trim($row['tempat_lahir'] ?? '') ?: null,
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => $jenisKelamin,
            'status_keluarga' => in_array($row['status_keluarga'] ?? '', ['kepala keluarga', 'istri', 'anak', 'mertua', 'menantu', 'kerabat lain']) ? $row['status_keluarga'] : null,
            'umur_sasaran' => $umur,
            'pekerjaan' => $this->normalizePekerjaan($row['pekerjaan'] ?? '') ?? trim($row['pekerjaan'] ?? '') ?: null,
            'pendidikan' => $this->normalizePendidikan($row['pendidikan'] ?? ''),
            'alamat_sasaran' => trim($row['alamat_sasaran'] ?? '') ?: null,
            'rt' => trim($row['rt'] ?? '') ?: null,
            'rw' => trim($row['rw'] ?? '') ?: null,
            'kepersertaan_bpjs' => $this->normalizeKepersertaanBpjs($row['kepersertaan_bpjs'] ?? ''),
            'nomor_bpjs' => trim($row['nomor_bpjs'] ?? '') ?: null,
            'nomor_telepon' => trim($row['nomor_telepon'] ?? '') ?: null,
            'nik_orangtua' => null,
        ]);
    }

    private function createSasaranPralansia(array $row, int $posyanduId, string $tanggalLahir, string $jenisKelamin): void
    {
        $umur = Carbon::parse($tanggalLahir)->age;
        SasaranPralansia::create([
            'id_posyandu' => $posyanduId,
            'id_users' => null,
            'nama_sasaran' => trim($row['nama_sasaran'] ?? ''),
            'nik_sasaran' => trim($row['nik_sasaran'] ?? ''),
            'no_kk_sasaran' => trim($row['no_kk_sasaran'] ?? '') ?: null,
            'tempat_lahir' => trim($row['tempat_lahir'] ?? '') ?: null,
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => $jenisKelamin,
            'status_keluarga' => in_array($row['status_keluarga'] ?? '', ['kepala keluarga', 'istri', 'anak', 'mertua', 'menantu', 'kerabat lain']) ? $row['status_keluarga'] : null,
            'umur_sasaran' => $umur,
            'pekerjaan' => $this->normalizePekerjaan($row['pekerjaan'] ?? '') ?? trim($row['pekerjaan'] ?? '') ?: null,
            'pendidikan' => $this->normalizePendidikan($row['pendidikan'] ?? ''),
            'alamat_sasaran' => trim($row['alamat_sasaran'] ?? '') ?: null,
            'rt' => trim($row['rt'] ?? '') ?: null,
            'rw' => trim($row['rw'] ?? '') ?: null,
            'kepersertaan_bpjs' => $this->normalizeKepersertaanBpjs($row['kepersertaan_bpjs'] ?? ''),
            'nomor_bpjs' => trim($row['nomor_bpjs'] ?? '') ?: null,
            'nomor_telepon' => trim($row['nomor_telepon'] ?? '') ?: null,
            'nik_orangtua' => null,
        ]);
    }

    private function createSasaranLansia(array $row, int $posyanduId, string $tanggalLahir, string $jenisKelamin): void
    {
        $umur = Carbon::parse($tanggalLahir)->age;
        SasaranLansia::create([
            'id_posyandu' => $posyanduId,
            'id_users' => null,
            'nama_sasaran' => trim($row['nama_sasaran'] ?? ''),
            'nik_sasaran' => trim($row['nik_sasaran'] ?? ''),
            'no_kk_sasaran' => trim($row['no_kk_sasaran'] ?? '') ?: null,
            'tempat_lahir' => trim($row['tempat_lahir'] ?? '') ?: null,
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => $jenisKelamin,
            'status_keluarga' => in_array($row['status_keluarga'] ?? '', ['kepala keluarga', 'istri', 'anak', 'mertua', 'menantu', 'kerabat lain']) ? $row['status_keluarga'] : null,
            'umur_sasaran' => $umur,
            'pekerjaan' => $this->normalizePekerjaan($row['pekerjaan'] ?? '') ?? trim($row['pekerjaan'] ?? '') ?: null,
            'pendidikan' => $this->normalizePendidikan($row['pendidikan'] ?? ''),
            'alamat_sasaran' => trim($row['alamat_sasaran'] ?? '') ?: null,
            'rt' => trim($row['rt'] ?? '') ?: null,
            'rw' => trim($row['rw'] ?? '') ?: null,
            'kepersertaan_bpjs' => $this->normalizeKepersertaanBpjs($row['kepersertaan_bpjs'] ?? ''),
            'nomor_bpjs' => trim($row['nomor_bpjs'] ?? '') ?: null,
            'nomor_telepon' => trim($row['nomor_telepon'] ?? '') ?: null,
            'nik_orangtua' => null,
        ]);
    }

    private function createSasaranIbuhamil(array $row, int $posyanduId, string $tanggalLahir, string $jenisKelamin): void
    {
        $umur = Carbon::parse($tanggalLahir)->age;
        SasaranIbuhamil::create([
            'id_posyandu' => $posyanduId,
            'nama_sasaran' => trim($row['nama_sasaran'] ?? ''),
            'nik_sasaran' => trim($row['nik_sasaran'] ?? ''),
            'no_kk_sasaran' => trim($row['no_kk_sasaran'] ?? '') ?: null,
            'tempat_lahir' => trim($row['tempat_lahir'] ?? '') ?: null,
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => $jenisKelamin,
            'status_keluarga' => in_array($row['status_keluarga'] ?? '', ['kepala keluarga', 'istri', 'anak']) ? $row['status_keluarga'] : null,
            'umur_sasaran' => $umur,
            'minggu_kandungan' => is_numeric($row['minggu_kandungan'] ?? '') ? (int) $row['minggu_kandungan'] : null,
            'pekerjaan' => $this->normalizePekerjaan($row['pekerjaan'] ?? '') ?? trim($row['pekerjaan'] ?? '') ?: null,
            'pendidikan' => $this->normalizePendidikan($row['pendidikan'] ?? ''),
            'alamat_sasaran' => trim($row['alamat_sasaran'] ?? '') ?: null,
            'rt' => trim($row['rt'] ?? '') ?: null,
            'rw' => trim($row['rw'] ?? '') ?: null,
            'kepersertaan_bpjs' => $this->normalizeKepersertaanBpjs($row['kepersertaan_bpjs'] ?? ''),
            'nomor_bpjs' => trim($row['nomor_bpjs'] ?? '') ?: null,
            'nomor_telepon' => trim($row['nomor_telepon'] ?? '') ?: null,
            'nama_suami' => trim($row['nama_suami'] ?? '') ?: null,
            'nik_suami' => trim($row['nik_suami'] ?? '') ?: null,
            'pekerjaan_suami' => $this->normalizePekerjaanSuami($row['pekerjaan_suami'] ?? ''),
            'status_keluarga_suami' => in_array($row['status_keluarga_suami'] ?? '', ['kepala keluarga', 'istri']) ? $row['status_keluarga_suami'] : null,
            'nik_orangtua' => null,
        ]);
    }
}
