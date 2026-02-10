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
        $rows = ($this->importKategori === 'master')
            ? $this->parseImportFileMaster($this->importFile)
            : $this->parseImportFile($this->importFile);
        if (empty($rows)) {
            $details = ['Tidak ada baris data ditemukan. Pastikan baris pertama adalah header.'];
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

    private function parseImportFile($file): array
    {
        $path = $file->getRealPath();
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
        $path = $file->getRealPath();
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
        $v = str_replace([' ', '-', '/', '_'], '', $v);
        $map = $this->getSheetNameToKategoriMap();
        return $map[$v] ?? null;
    }

    private function parseExcelMaster(string $path): array
    {
        try {
            $this->importParseError = null;
            $spreadsheet = IOFactory::load($path);
            $allRows = [];

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetName = $sheet->getTitle();
                $kategori = $this->normalizeSheetNameToKategori($sheetName);
                if ($kategori === null) {
                    continue; // sheet tidak dikenali, lewati
                }
                $data = $sheet->toArray(null, true, true, true);
                if (empty($data)) continue;

                // Baris bisa 0-based atau 1-based dari PhpSpreadsheet; pakai key pertama untuk header
                $rowKeys = array_keys($data);
                $headerKey = $rowKeys[0];
                $rawHeader = $data[$headerKey];
                if (!is_array($rawHeader) || empty($rawHeader)) continue;

                $normalizedByCol = [];
                foreach ($rawHeader as $col => $val) {
                    $trimmed = trim((string) $val);
                    $normalizedByCol[$col] = $this->normalizeHeaders([$trimmed])[0];
                }
                for ($i = 1; $i < count($rowKeys); $i++) {
                    $rowKey = $rowKeys[$i];
                    $row = ['kategori' => $kategori];
                    foreach ($rawHeader as $col => $_) {
                        $h = $normalizedByCol[$col];
                        if ($h !== '') {
                            $row[$h] = trim((string) ($data[$rowKey][$col] ?? ''));
                        }
                    }
                    if (!empty($row['nik_sasaran'] ?? '')) {
                        $allRows[] = $row;
                    }
                }
            }
            return $allRows;
        } catch (\Throwable $e) {
            $this->importParseError = $e->getMessage();
            return [];
        }
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
        $header = $this->normalizeHeaders(array_map('trim', $header));

        $rows = [];
        foreach ($lines as $line) {
            $cols = str_getcsv($line, $delimiter);
            if (count($cols) < 2) continue;
            $row = [];
            foreach ($header as $i => $h) {
                $row[$h] = $cols[$i] ?? '';
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
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);
            if (empty($data)) return [];

            // Baris bisa 0-based atau 1-based; pakai key pertama untuk header
            $rowKeys = array_keys($data);
            $headerKey = $rowKeys[0];
            $rawHeader = $data[$headerKey];
            if (!is_array($rawHeader)) return [];

            $normalizedByCol = [];
            foreach ($rawHeader as $col => $val) {
                $trimmed = trim((string) $val);
                $normalizedByCol[$col] = $this->normalizeHeaders([$trimmed])[0];
            }

            $rows = [];
            for ($i = 1; $i < count($rowKeys); $i++) {
                $rowKey = $rowKeys[$i];
                $row = [];
                foreach ($rawHeader as $col => $_) {
                    $h = $normalizedByCol[$col];
                    if ($h !== '') {
                        $row[$h] = trim((string) ($data[$rowKey][$col] ?? ''));
                    }
                }
                if (!empty($row['nik_sasaran'] ?? '')) {
                    $rows[] = $row;
                }
            }
            return $rows;
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
                $errorDetails[] = 'Baris ' . ($idx + 2) . ': ' . $e->getMessage();
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
                $errorDetails[] = 'Baris ' . ($idx + 2) . ' (' . ($row['kategori'] ?? '') . '): ' . $e->getMessage();
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
        if ($value === null || $value === '') return null;
        $value = trim((string) $value);
        if ($value === '') return null;
        try {
            // Excel serial date (numeric)
            if (is_numeric($value)) {
                $d = ExcelDate::excelToDateTimeObject((float) $value);
                return $d->format('Y-m-d');
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return $value;
            }
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $m)) {
                return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            }
            $d = Carbon::parse($value);
            return $d->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
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
        if (empty($v)) return null;
        $map = [
            'ibu rumah tangga' => 'Mengurus Rumah Tangga',
            'irt' => 'Mengurus Rumah Tangga',
            'pns' => 'Pegawai Negeri Sipil',
            'tni' => 'Tentara Nasional Indonesia',
            'polri' => 'Kepolisian RI',
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

    /** Pekerjaan untuk tabel orangtua: hanya nilai enum, selain itu 'Lainnya'. */
    private function normalizePekerjaanOrangtua(?string $value): string
    {
        $allowed = [
            'Belum/Tidak Bekerja', 'Mengurus Rumah Tangga', 'Pelajar/Mahasiswa', 'Pensiunan',
            'Pegawai Negeri Sipil', 'Tentara Nasional Indonesia', 'Kepolisian RI', 'Perdagangan',
            'Petani/Pekebun', 'Peternak', 'Nelayan/Perikanan', 'Industri', 'Konstruksi', 'Transportasi',
            'Karyawan Swasta', 'Karyawan BUMN', 'Karyawan BUMD', 'Karyawan Honorer', 'Buruh Harian Lepas',
            'Buruh Tani/Perkebunan', 'Buruh Nelayan/Perikanan', 'Buruh Peternakan', 'Pembantu Rumah Tangga',
            'Tukang Cukur', 'Tukang Listrik', 'Tukang Batu', 'Tukang Kayu', 'Tukang Sol Sepatu', 'Tukang Las/Pandai Besi',
            'Tukang Jahit', 'Tukang Gigi', 'Penata Rias', 'Penata Busana', 'Penata Rambut', 'Mekanik', 'Seniman',
            'Tabib', 'Paraji', 'Perancang Busana', 'Penterjemah', 'Imam Masjid', 'Pendeta', 'Pastor', 'Wartawan',
            'Ustadz/Mubaligh', 'Juru Masak', 'Promotor Acara', 'Anggota DPR-RI', 'Anggota DPD', 'Anggota BPK',
            'Presiden', 'Wakil Presiden', 'Anggota Mahkamah Konstitusi', 'Anggota Kabinet/Kementerian', 'Duta Besar',
            'Gubernur', 'Wakil Gubernur', 'Bupati', 'Wakil Bupati', 'Walikota', 'Wakil Walikota',
            'Anggota DPRD Provinsi', 'Anggota DPRD Kabupaten/Kota', 'Dosen', 'Guru', 'Pilot', 'Pengacara', 'Notaris',
            'Arsitek', 'Akuntan', 'Konsultan', 'Dokter', 'Bidan', 'Perawat', 'Apoteker', 'Psikiater/Psikolog',
            'Penyiar Televisi', 'Penyiar Radio', 'Pelaut', 'Peneliti', 'Sopir', 'Pialang', 'Paranormal', 'Pedagang',
            'Perangkat Desa', 'Kepala Desa', 'Biarawati', 'Wiraswasta', 'Lainnya',
        ];
        $v = $this->normalizePekerjaan($value);
        if ($v === null || $v === '') return 'Belum/Tidak Bekerja';
        return in_array($v, $allowed, true) ? $v : 'Lainnya';
    }

    /** Pekerjaan suami (sasaran_ibuhamils): hanya nilai enum, selain itu null. */
    private function normalizePekerjaanSuami(?string $value): ?string
    {
        $allowed = [
            'Belum/Tidak Bekerja', 'Mengurus Rumah Tangga', 'Pelajar/Mahasiswa', 'Pensiunan',
            'Pegawai Negeri Sipil', 'Tentara Nasional Indonesia', 'Kepolisian RI', 'Perdagangan',
            'Petani/Pekebun', 'Peternak', 'Nelayan/Perikanan', 'Industri', 'Konstruksi', 'Transportasi',
            'Karyawan Swasta', 'Karyawan BUMN', 'Karyawan BUMD', 'Karyawan Honorer', 'Buruh Harian Lepas',
            'Buruh Tani/Perkebunan', 'Buruh Nelayan/Perikanan', 'Buruh Peternakan', 'Pembantu Rumah Tangga',
            'Tukang Cukur', 'Tukang Listrik', 'Tukang Batu', 'Tukang Kayu', 'Tukang Sol Sepatu', 'Tukang Las/Pandai Besi',
            'Tukang Jahit', 'Tukang Gigi', 'Penata Rias', 'Penata Busana', 'Penata Rambut', 'Mekanik', 'Seniman',
            'Tabib', 'Paraji', 'Perancang Busana', 'Penterjemah', 'Imam Masjid', 'Pendeta', 'Pastor', 'Wartawan',
            'Ustadz/Mubaligh', 'Juru Masak', 'Promotor Acara', 'Anggota DPR-RI', 'Anggota DPD', 'Anggota BPK',
            'Presiden', 'Wakil Presiden', 'Anggota Mahkamah Konstitusi', 'Anggota Kabinet/Kementerian', 'Duta Besar',
            'Gubernur', 'Wakil Gubernur', 'Bupati', 'Wakil Bupati', 'Walikota', 'Wakil Walikota',
            'Anggota DPRD Provinsi', 'Anggota DPRD Kabupaten/Kota', 'Dosen', 'Guru', 'Pilot', 'Pengacara', 'Notaris',
            'Arsitek', 'Akuntan', 'Konsultan', 'Dokter', 'Bidan', 'Perawat', 'Apoteker', 'Psikiater/Psikolog',
            'Penyiar Televisi', 'Penyiar Radio', 'Pelaut', 'Peneliti', 'Sopir', 'Pialang', 'Paranormal', 'Pedagang',
            'Perangkat Desa', 'Kepala Desa', 'Biarawati', 'Wiraswasta', 'Lainnya',
        ];
        $v = $this->normalizePekerjaan($value);
        if ($v === null || $v === '') return null;
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
