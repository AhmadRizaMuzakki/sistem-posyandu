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

trait SasaranImportTrait
{
    use WithFileUploads;

    public $showImportModal = false;
    public $importKategori = '';
    public $importResult = null;
    public $importFile = null;

    public function openImportModal($kategori)
    {
        $this->importKategori = $kategori ?: 'dewasa';
        $this->importResult = null;
        $this->importFile = null;
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

        $rows = $this->parseImportFile($this->importFile);
        if (empty($rows)) {
            $this->importResult = ['added' => 0, 'skipped' => 0, 'errors' => 1, 'errorDetails' => ['Tidak ada baris data ditemukan. Pastikan baris pertama adalah header.']];
            return;
        }

        $result = $this->processImportRows($rows, $posyanduId);
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
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);
            if (empty($data)) return [];

            $header = array_map('trim', array_values(array_filter($data[0])));
            $header = $this->normalizeHeaders($header);
            $rows = [];
            for ($i = 1; $i < count($data); $i++) {
                $cols = array_values($data[$i]);
                $row = [];
                foreach ($header as $j => $h) {
                    $row[$h] = $cols[$j] ?? '';
                }
                if (!empty(trim($row['nik_sasaran'] ?? ''))) {
                    $rows[] = $row;
                }
            }
            return $rows;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function normalizeHeaders(array $headers): array
    {
        $map = ['status_kel' => 'status_keluarga'];
        return array_map(function ($h) use ($map) {
            $h = trim($h);
            return $map[$h] ?? $h;
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

    private function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if (empty($value)) return null;
        try {
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

    private function normalizeJenisKelamin(?string $value): ?string
    {
        $v = strtolower(trim((string) $value));
        if ($v === 'laki-laki' || $v === 'laki laki') return 'Laki-laki';
        if ($v === 'perempuan') return 'Perempuan';
        return null;
    }

    private function normalizePekerjaan(?string $value): ?string
    {
        $v = trim((string) $value);
        if (empty($v)) return null;
        $map = [
            'ibu rumah tangga' => 'Mengurus Rumah Tangga',
            'irt' => 'Mengurus Rumah Tangga',
        ];
        $lower = strtolower($v);
        return $map[$lower] ?? $v;
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
                    'pekerjaan' => $this->normalizePekerjaan($row['pekerjaan_orangtua'] ?? '') ?? 'Belum/Tidak Bekerja',
                    'pendidikan' => trim($row['pendidikan_orangtua'] ?? '') ?: null,
                    'kelamin' => $this->normalizeJenisKelamin($row['kelamin_orangtua'] ?? '') ?? 'Perempuan',
                    'alamat' => trim($row['alamat_sasaran'] ?? '') ?: null,
                    'kepersertaan_bpjs' => trim($row['kepersertaan_bpjs_orangtua'] ?? $row['kepersertaan_bpjs'] ?? '') ?: null,
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
                'kepersertaan_bpjs' => in_array(strtoupper(trim($row['kepersertaan_bpjs'] ?? '')), ['PBI', 'NON PBI']) ? strtoupper(trim($row['kepersertaan_bpjs'])) : null,
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
                'pekerjaan' => $this->normalizePekerjaan($row['pekerjaan_orangtua'] ?? '') ?? 'Belum/Tidak Bekerja',
                'pendidikan' => trim($row['pendidikan_orangtua'] ?? '') ?: null,
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
            'kepersertaan_bpjs' => in_array(strtoupper(trim($row['kepersertaan_bpjs'] ?? '')), ['PBI', 'NON PBI']) ? strtoupper(trim($row['kepersertaan_bpjs'])) : null,
            'nomor_bpjs' => trim($row['nomor_bpjs'] ?? '') ?: null,
            'nomor_telepon' => trim($row['nomor_telepon'] ?? '') ?: null,
            'pendidikan' => trim($row['pendidikan'] ?? '') ?: null,
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
            'pendidikan' => trim($row['pendidikan'] ?? '') ?: null,
            'alamat_sasaran' => trim($row['alamat_sasaran'] ?? '') ?: null,
            'rt' => trim($row['rt'] ?? '') ?: null,
            'rw' => trim($row['rw'] ?? '') ?: null,
            'kepersertaan_bpjs' => in_array(strtoupper(trim($row['kepersertaan_bpjs'] ?? '')), ['PBI', 'NON PBI']) ? strtoupper(trim($row['kepersertaan_bpjs'])) : null,
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
            'pendidikan' => trim($row['pendidikan'] ?? '') ?: null,
            'alamat_sasaran' => trim($row['alamat_sasaran'] ?? '') ?: null,
            'rt' => trim($row['rt'] ?? '') ?: null,
            'rw' => trim($row['rw'] ?? '') ?: null,
            'kepersertaan_bpjs' => in_array(strtoupper(trim($row['kepersertaan_bpjs'] ?? '')), ['PBI', 'NON PBI']) ? strtoupper(trim($row['kepersertaan_bpjs'])) : null,
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
            'pendidikan' => trim($row['pendidikan'] ?? '') ?: null,
            'alamat_sasaran' => trim($row['alamat_sasaran'] ?? '') ?: null,
            'rt' => trim($row['rt'] ?? '') ?: null,
            'rw' => trim($row['rw'] ?? '') ?: null,
            'kepersertaan_bpjs' => in_array(strtoupper(trim($row['kepersertaan_bpjs'] ?? '')), ['PBI', 'NON PBI']) ? strtoupper(trim($row['kepersertaan_bpjs'])) : null,
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
            'pendidikan' => trim($row['pendidikan'] ?? '') ?: null,
            'alamat_sasaran' => trim($row['alamat_sasaran'] ?? '') ?: null,
            'rt' => trim($row['rt'] ?? '') ?: null,
            'rw' => trim($row['rw'] ?? '') ?: null,
            'kepersertaan_bpjs' => in_array(strtoupper(trim($row['kepersertaan_bpjs'] ?? '')), ['PBI', 'NON PBI']) ? strtoupper(trim($row['kepersertaan_bpjs'])) : null,
            'nomor_bpjs' => trim($row['nomor_bpjs'] ?? '') ?: null,
            'nomor_telepon' => trim($row['nomor_telepon'] ?? '') ?: null,
            'nama_suami' => trim($row['nama_suami'] ?? '') ?: null,
            'nik_suami' => trim($row['nik_suami'] ?? '') ?: null,
            'pekerjaan_suami' => trim($row['pekerjaan_suami'] ?? '') ?: null,
            'status_keluarga_suami' => in_array($row['status_keluarga_suami'] ?? '', ['kepala keluarga', 'istri']) ? $row['status_keluarga_suami'] : null,
            'nik_orangtua' => null,
        ]);
    }
}
