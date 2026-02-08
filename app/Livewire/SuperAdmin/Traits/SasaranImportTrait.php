<?php

namespace App\Livewire\SuperAdmin\Traits;

use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\SasaranIbuhamil;
use App\Models\Orangtua;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\WithFileUploads;

trait SasaranImportTrait
{
    use WithFileUploads;

    public $showImportModal = false;
    public $importKategori = '';
    public $importFile = null;
    public $importResult = null;

    protected $importKategoriLabels = [
        'bayibalita' => 'Bayi/Balita',
        'remaja' => 'Remaja',
        'dewasa' => 'Dewasa',
        'ibuhamil' => 'Ibu Hamil',
        'pralansia' => 'Pralansia',
        'lansia' => 'Lansia',
    ];

    public function openImportModal($kategori)
    {
        $this->importKategori = $kategori;
        $this->importFile = null;
        $this->importResult = null;
        $this->showImportModal = true;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->importKategori = '';
        $this->importFile = null;
        $this->importResult = null;
    }

    /**
     * Unduh file CSV contoh untuk import sesuai kategori sasaran.
     * Kolom disesuaikan dengan input form masing-masing sasaran.
     */
    public function downloadTemplateImport()
    {
        $csv = \App\Services\SasaranImportTemplate::getCsvContent($this->importKategori ?: 'dewasa');
        $bom = "\xEF\xBB\xBF";
        return response()->streamDownload(
            function () use ($csv, $bom) {
                echo $bom . $csv;
            },
            'template_import_' . ($this->importKategori ?: 'dewasa') . '.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function importSasaran()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ], [
            'importFile.required' => 'File wajib dipilih.',
            'importFile.mimes' => 'Format file: CSV, TXT, atau Excel (.xlsx, .xls).',
        ]);

        $path = $this->importFile->getRealPath();
        $ext = strtolower($this->importFile->getClientOriginalExtension());

        $rows = [];
        if (in_array($ext, ['csv', 'txt'])) {
            $rows = $this->readCsv($path);
        } elseif (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class) && in_array($ext, ['xlsx', 'xls'])) {
            $rows = $this->readExcel($path);
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            session()->flash('message', 'Format Excel (.xlsx/.xls) memerlukan library PhpSpreadsheet. Di server tanpa ekstensi zip, gunakan file CSV (Excel/Google Sheets: File → Simpan sebagai CSV).');
            session()->flash('messageType', 'error');
            return;
        }

        if (empty($rows)) {
            session()->flash('message', 'File kosong atau format tidak valid. Pastikan baris pertama berisi header (nik_sasaran, nama_sasaran, no_kk_sasaran, tanggal_lahir, jenis_kelamin, alamat_sasaran) dan ada data di baris berikutnya.');
            session()->flash('messageType', 'error');
            return;
        }

        $result = $this->processImportRows($rows);
        $this->importResult = $result;
        $this->refreshPosyandu();

        $total = $result['added'] + $result['skipped'] + $result['errors'];
        if ($result['errors'] === 0 && $result['added'] > 0) {
            $msg = "Import berhasil. {$result['added']} data ditambahkan.";
            if ($result['skipped'] > 0) {
                $msg .= " {$result['skipped']} baris dilewati (NIK sudah ada, tidak duplikat).";
            }
            session()->flash('message', $msg);
            session()->flash('messageType', 'success');
        } elseif ($result['errors'] > 0 && $result['added'] > 0) {
            $msg = "Import selesai dengan sebagian gagal. Berhasil: {$result['added']}, Dilewati (duplikat): {$result['skipped']}, Gagal: {$result['errors']}. Periksa baris yang gagal (NIK kosong atau format data tidak valid).";
            session()->flash('message', $msg);
            session()->flash('messageType', 'warning');
        } elseif ($result['errors'] > 0 && $result['added'] === 0) {
            $msg = "Tidak ada data yang berhasil diimport. Gagal: {$result['errors']} baris. Pastikan kolom: nik_sasaran, nama_sasaran, no_kk_sasaran, tanggal_lahir (YYYY-MM-DD atau DD/MM/YYYY), jenis_kelamin (Laki-laki/Perempuan), alamat_sasaran.";
            session()->flash('message', $msg);
            session()->flash('messageType', 'error');
        } else {
            $msg = "Semua {$total} baris sudah ada (duplikat). Tidak ada data baru yang ditambahkan.";
            session()->flash('message', $msg);
            session()->flash('messageType', 'warning');
        }
    }

    protected function readCsv($path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        if (!$handle) {
            return [];
        }
        $header = null;
        while (($line = fgetcsv($handle, 0, ',')) !== false) {
            $line = array_map(fn ($v) => trim((string) $v), $line);
            if ($header === null) {
                $header = $line;
                continue;
            }
            $max = max(count($header), count($line));
            $h = array_pad($header, $max, '');
            $v = array_pad($line, $max, '');
            $row = @array_combine($h, $v) ?: [];
            if ($row && $this->rowHasData($row)) {
                $rows[] = $row;
            }
        }
        fclose($handle);
        return $rows;
    }

    protected function readExcel($path): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            session()->flash('message', 'Untuk file Excel (.xlsx/.xls), aktifkan ekstensi zip PHP dan jalankan: composer require phpoffice/phpspreadsheet');
            session()->flash('messageType', 'error');
            return [];
        }
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            // formatData=true agar NIK/no_kk/nomor dibaca sebagai string (tanpa kehilangan digit)
            $rows = $sheet->toArray(null, true, true, false);
            $header = array_shift($rows);
            $header = array_map('trim', array_map('strval', $header));
            $result = [];
            foreach ($rows as $row) {
                $row = array_map(function ($v) {
                    if (is_float($v) && $v == floor($v) && strlen((string)(int)$v) >= 10) {
                        return sprintf('%.0f', $v);
                    }
                    return trim((string) $v);
                }, array_pad($row, count($header), ''));
                $assoc = @array_combine($header, array_pad($row, count($header), '')) ?: [];
                if ($assoc && $this->rowHasData($assoc)) {
                    $result[] = $assoc;
                }
            }
            return $result;
        } catch (\Throwable $e) {
            session()->flash('message', 'Gagal membaca file Excel: ' . $e->getMessage());
            session()->flash('messageType', 'error');
            return [];
        }
    }

    protected function rowHasData(array $row): bool
    {
        return !empty(trim(implode('', $row)));
    }

    protected function processImportRows(array $rows): array
    {
        $result = ['added' => 0, 'skipped' => 0, 'errors' => 0];
        $posyanduId = $this->posyanduId;

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2; // 1-based + header
            try {
                $nik = preg_replace('/\D/', '', $this->getVal($row, 'nik_sasaran', 'NIK', 'nik') ?? '');
                if (empty($nik)) {
                    $result['errors']++;
                    continue;
                }

                $exists = $this->checkDuplicate($nik, $posyanduId);
                if ($exists) {
                    $result['skipped']++;
                    continue;
                }

                $inserted = $this->insertSasaranRow($row, $posyanduId);
                if ($inserted) {
                    $result['added']++;
                } else {
                    $result['errors']++;
                }
            } catch (\Throwable $e) {
                $result['errors']++;
            }
        }

        return $result;
    }

    protected function checkDuplicate(string $nik, int $posyanduId): bool
    {
        $nik = preg_replace('/\D/', '', $nik);
        if (empty($nik)) {
            return true;
        }
        return match ($this->importKategori) {
            'bayibalita' => SasaranBayibalita::where('nik_sasaran', $nik)->where('id_posyandu', $posyanduId)->exists(),
            'remaja' => SasaranRemaja::where('nik_sasaran', $nik)->where('id_posyandu', $posyanduId)->exists(),
            'dewasa' => SasaranDewasa::where('nik_sasaran', $nik)->where('id_posyandu', $posyanduId)->exists(),
            'ibuhamil' => SasaranIbuhamil::where('nik_sasaran', $nik)->where('id_posyandu', $posyanduId)->exists(),
            'pralansia' => SasaranPralansia::where('nik_sasaran', $nik)->where('id_posyandu', $posyanduId)->exists(),
            'lansia' => SasaranLansia::where('nik_sasaran', $nik)->where('id_posyandu', $posyanduId)->exists(),
            default => true,
        };
    }

    protected function getVal(array $row, ...$keys): ?string
    {
        foreach ($keys as $k) {
            if (isset($row[$k]) && $row[$k] !== null && $row[$k] !== '') {
                return trim((string) $row[$k]);
            }
        }
        $rowLower = array_change_key_case($row, CASE_LOWER);
        foreach ($keys as $k) {
            $kNorm = strtolower(str_replace([' ', '_'], '', $k));
            foreach ($rowLower as $rk => $rv) {
                if (str_replace([' ', '_'], '', $rk) === $kNorm && $rv !== null && $rv !== '') {
                    return trim((string) $rv);
                }
            }
        }
        return null;
    }

    protected function parseDate($val): ?string
    {
        if ($val === null || $val === '') {
            return null;
        }
        if ($val instanceof \DateTimeInterface) {
            return $val->format('Y-m-d');
        }
        $numVal = is_numeric($val) ? (float) $val : 0;
        if ($numVal > 10000 && class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
            try {
                $d = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($numVal);
                return $d ? $d->format('Y-m-d') : null;
            } catch (\Throwable $e) {
            }
        }
        $val = trim((string) $val);
        if ($val === '') {
            return null;
        }
        try {
            if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})$#', $val, $m)) {
                $d = Carbon::createFromFormat('d/m/Y', $m[1] . '/' . $m[2] . '/' . $m[3]);
                return $d ? $d->format('Y-m-d') : null;
            }
            $val = str_replace(['/', '-'], '-', $val);
            $d = Carbon::parse($val);
            return $d->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function insertSasaranRow(array $row, int $posyanduId): bool
    {
        $nik = preg_replace('/\D/', '', $this->getVal($row, 'nik_sasaran', 'NIK', 'nik') ?? '');
        if (empty($nik)) {
            return false;
        }

        $nama = $this->getVal($row, 'nama_sasaran', 'nama', 'Nama') ?? '-';
        $noKk = preg_replace('/\D/', '', $this->getVal($row, 'no_kk_sasaran', 'no_kk', 'No KK') ?? '');
        $tempatLahir = $this->getVal($row, 'tempat_lahir', 'Tempat Lahir');
        $tanggalLahir = $this->parseDate($this->getVal($row, 'tanggal_lahir', 'Tanggal Lahir', 'tanggal_lahir'));
        $jenisKelamin = $this->getVal($row, 'jenis_kelamin', 'Jenis Kelamin') ?? 'Laki-laki';
        if (!in_array($jenisKelamin, ['Laki-laki', 'Perempuan'])) {
            $jenisKelamin = stripos($jenisKelamin, 'L') === 0 ? 'Laki-laki' : 'Perempuan';
        }
        $alamat = $this->getVal($row, 'alamat_sasaran', 'alamat', 'Alamat') ?? '-';
        $rt = $this->getVal($row, 'rt', 'RT');
        $rw = $this->getVal($row, 'rw', 'RW');
        $statusKeluarga = $this->getVal($row, 'status_keluarga', 'status_kel', 'Status Keluarga');
        $kepersertaanBpjs = $this->getVal($row, 'kepersertaan_bpjs', 'Kepersertaan BPJS');
        if ($kepersertaanBpjs && !in_array($kepersertaanBpjs, ['PBI', 'NON PBI'])) {
            $kepersertaanBpjs = stripos($kepersertaanBpjs, 'PBI') !== false ? 'PBI' : 'NON PBI';
        }
        $nomorBpjs = $this->getVal($row, 'nomor_bpjs', 'Nomor BPJS');
        $nomorTelepon = $this->getVal($row, 'nomor_telepon', 'Nomor Telepon');
        $pekerjaan = $this->getVal($row, 'pekerjaan', 'Pekerjaan');
        $pendidikan = $this->getVal($row, 'pendidikan', 'Pendidikan');

        $umur = null;
        if ($tanggalLahir) {
            $umur = Carbon::parse($tanggalLahir)->age;
        }

        try {
            return match ($this->importKategori) {
                'bayibalita' => $this->insertBalita($row, $posyanduId, compact('nik', 'nama', 'noKk', 'tempatLahir', 'tanggalLahir', 'jenisKelamin', 'alamat', 'rt', 'rw', 'statusKeluarga', 'kepersertaanBpjs', 'nomorBpjs', 'umur')),
                'remaja' => $this->insertRemaja($row, $posyanduId, compact('nik', 'nama', 'noKk', 'tempatLahir', 'tanggalLahir', 'jenisKelamin', 'alamat', 'rt', 'rw', 'statusKeluarga', 'kepersertaanBpjs', 'nomorBpjs', 'nomorTelepon', 'pendidikan', 'umur')),
                'dewasa' => $this->insertDewasa($posyanduId, compact('nik', 'nama', 'noKk', 'tempatLahir', 'tanggalLahir', 'jenisKelamin', 'alamat', 'rt', 'rw', 'statusKeluarga', 'kepersertaanBpjs', 'nomorBpjs', 'nomorTelepon', 'pekerjaan', 'pendidikan', 'umur')),
                'ibuhamil' => $this->insertIbuHamil($row, $posyanduId, compact('nik', 'nama', 'noKk', 'tempatLahir', 'tanggalLahir', 'jenisKelamin', 'alamat', 'rt', 'rw', 'statusKeluarga', 'kepersertaanBpjs', 'nomorBpjs', 'nomorTelepon', 'pekerjaan', 'pendidikan', 'umur')),
                'pralansia' => $this->insertPralansia($posyanduId, compact('nik', 'nama', 'noKk', 'tempatLahir', 'tanggalLahir', 'jenisKelamin', 'alamat', 'rt', 'rw', 'statusKeluarga', 'kepersertaanBpjs', 'nomorBpjs', 'nomorTelepon', 'pekerjaan', 'pendidikan', 'umur')),
                'lansia' => $this->insertLansia($posyanduId, compact('nik', 'nama', 'noKk', 'tempatLahir', 'tanggalLahir', 'jenisKelamin', 'alamat', 'rt', 'rw', 'statusKeluarga', 'kepersertaanBpjs', 'nomorBpjs', 'nomorTelepon', 'pekerjaan', 'pendidikan', 'umur')),
                default => false,
            };
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function insertBalita(array $row, int $posyanduId, array $data): bool
    {
        $nikOrtu = preg_replace('/\D/', '', $this->getVal($row, 'nik_orangtua', 'nik_orangtua') ?? '');
        $namaOrtu = $this->getVal($row, 'nama_orangtua', 'nama_orangtua') ?? $data['nama'];
        if (empty($nikOrtu)) {
            $nikOrtu = $data['nik'];
        }
        $tempatLahirOrtu = $this->getVal($row, 'tempat_lahir_orangtua', 'tempat_lahir_orangtua');
        $tanggalLahirOrtu = $this->parseDate($this->getVal($row, 'tanggal_lahir_orangtua', 'tanggal_lahir_orangtua'));
        $pekerjaanOrtu = $this->getVal($row, 'pekerjaan_orangtua', 'pekerjaan_orangtua') ?? 'Lainnya';

        return DB::transaction(function () use ($posyanduId, $data, $nikOrtu, $namaOrtu, $tempatLahirOrtu, $tanggalLahirOrtu, $pekerjaanOrtu) {
            Orangtua::firstOrCreate(
                ['nik' => $nikOrtu],
                [
                    'nik' => $nikOrtu,
                    'nama' => $namaOrtu,
                    'no_kk' => $data['noKk'] ?: null,
                    'tempat_lahir' => $tempatLahirOrtu ?? $data['tempatLahir'],
                    'tanggal_lahir' => $tanggalLahirOrtu,
                    'pekerjaan' => $pekerjaanOrtu,
                    'kelamin' => 'Perempuan',
                    'alamat' => $data['alamat'],
                ]
            );

            $email = ($data['noKk'] ?: $nikOrtu) . '@gmail.com';
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $namaOrtu, 'password' => Hash::make($data['noKk'] ?: $nikOrtu), 'email_verified_at' => now()]
            );
            if (!$user->hasRole('orangtua')) {
                $user->assignRole('orangtua');
            }

            SasaranBayibalita::create([
                'id_posyandu' => $posyanduId,
                'id_users' => $user->id,
                'nama_sasaran' => $data['nama'],
                'nik_sasaran' => $data['nik'],
                'no_kk_sasaran' => $data['noKk'] ?: null,
                'tempat_lahir' => $data['tempatLahir'],
                'tanggal_lahir' => $data['tanggalLahir'],
                'jenis_kelamin' => $data['jenisKelamin'],
                'status_keluarga' => $data['statusKeluarga'] ?: null,
                'umur_sasaran' => $data['umur'],
                'nik_orangtua' => $nikOrtu,
                'alamat_sasaran' => $data['alamat'],
                'rt' => $data['rt'] ?? null,
                'rw' => $data['rw'] ?? null,
                'kepersertaan_bpjs' => $data['kepersertaanBpjs'] ?: null,
                'nomor_bpjs' => $data['nomorBpjs'] ?? null,
            ]);
            return true;
        });
    }

    protected function insertRemaja(array $row, int $posyanduId, array $data): bool
    {
        $nikOrtu = preg_replace('/\D/', '', $this->getVal($row, 'nik_orangtua', 'nik_orangtua') ?? '') ?: $data['nik'];
        $namaOrtu = $this->getVal($row, 'nama_orangtua', 'nama_orangtua') ?? $data['nama'];

        return DB::transaction(function () use ($posyanduId, $data, $nikOrtu, $namaOrtu) {
            $orangtua = Orangtua::firstOrCreate(['nik' => $nikOrtu], [
                'nik' => $nikOrtu, 'nama' => $namaOrtu, 'no_kk' => $data['noKk'] ?: null,
                'tempat_lahir' => null, 'tanggal_lahir' => null, 'pekerjaan' => 'Lainnya', 'kelamin' => 'Perempuan', 'alamat' => $data['alamat'],
            ]);
            $email = ($data['noKk'] ?: $nikOrtu) . '@gmail.com';
            $user = User::firstOrCreate(['email' => $email], ['name' => $namaOrtu, 'password' => Hash::make($data['noKk'] ?: $nikOrtu), 'email_verified_at' => now()]);
            if (!$user->hasRole('orangtua')) {
                $user->assignRole('orangtua');
            }

            SasaranRemaja::create([
                'id_posyandu' => $posyanduId,
                'id_users' => $user->id,
                'nama_sasaran' => $data['nama'],
                'nik_sasaran' => $data['nik'],
                'no_kk_sasaran' => $data['noKk'] ?: null,
                'tempat_lahir' => $data['tempatLahir'],
                'tanggal_lahir' => $data['tanggalLahir'],
                'jenis_kelamin' => $data['jenisKelamin'],
                'status_keluarga' => $data['statusKeluarga'] ?: null,
                'umur_sasaran' => $data['umur'],
                'pendidikan' => $data['pendidikan'] ?? null,
                'nik_orangtua' => $nikOrtu,
                'alamat_sasaran' => $data['alamat'],
                'rt' => $data['rt'] ?? null,
                'rw' => $data['rw'] ?? null,
                'kepersertaan_bpjs' => $data['kepersertaanBpjs'] ?? null,
                'nomor_bpjs' => $data['nomorBpjs'] ?? null,
                'nomor_telepon' => $data['nomorTelepon'] ?? null,
            ]);
            return true;
        });
    }

    protected function insertDewasa(int $posyanduId, array $data): bool
    {
        return DB::transaction(function () use ($posyanduId, $data) {
            $email = ($data['noKk'] ?: $data['nik']) . '@gmail.com';
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $data['nama'],
                'password' => Hash::make($data['noKk'] ?: $data['nik']),
                'email_verified_at' => now(),
            ]);
            if (!$user->hasRole('orangtua')) {
                $user->assignRole('orangtua');
            }

            SasaranDewasa::create([
                'id_posyandu' => $posyanduId,
                'id_users' => $user->id,
                'nama_sasaran' => $data['nama'],
                'nik_sasaran' => $data['nik'],
                'no_kk_sasaran' => $data['noKk'] ?: null,
                'tempat_lahir' => $data['tempatLahir'],
                'tanggal_lahir' => $data['tanggalLahir'],
                'jenis_kelamin' => $data['jenisKelamin'],
                'umur_sasaran' => $data['umur'],
                'pekerjaan' => $data['pekerjaan'] ?? 'Lainnya',
                'pendidikan' => $data['pendidikan'] ?? null,
                'alamat_sasaran' => $data['alamat'],
                'rt' => $data['rt'] ?? null,
                'rw' => $data['rw'] ?? null,
                'kepersertaan_bpjs' => $data['kepersertaanBpjs'] ?? null,
                'nomor_bpjs' => $data['nomorBpjs'] ?? null,
                'nomor_telepon' => $data['nomorTelepon'] ?? null,
                'status_keluarga' => $data['statusKeluarga'] ?? null,
            ]);
            return true;
        });
    }

    protected function insertIbuHamil(array $row, int $posyanduId, array $data): bool
    {
        $mingguKandungan = (int) ($this->getVal($row, 'minggu_kandungan', 'Minggu Kandungan') ?? 0);
        $namaSuami = $this->getVal($row, 'nama_suami', 'Nama Suami');
        $nikSuami = preg_replace('/\D/', '', $this->getVal($row, 'nik_suami', 'NIK Suami') ?? '');
        $pekerjaanSuami = $this->getVal($row, 'pekerjaan_suami', 'Pekerjaan Suami');
        $statusKeluargaSuami = $this->getVal($row, 'status_keluarga_suami', 'Status Keluarga Suami');

        SasaranIbuhamil::create([
            'id_posyandu' => $posyanduId,
            'nama_sasaran' => $data['nama'],
            'nik_sasaran' => $data['nik'],
            'no_kk_sasaran' => $data['noKk'] ?: null,
            'tempat_lahir' => $data['tempatLahir'],
            'tanggal_lahir' => $data['tanggalLahir'],
            'jenis_kelamin' => $data['jenisKelamin'],
            'status_keluarga' => $data['statusKeluarga'] ?? null,
            'umur_sasaran' => $data['umur'],
            'minggu_kandungan' => $mingguKandungan ?: null,
            'pekerjaan' => $data['pekerjaan'] ?? null,
            'pendidikan' => $data['pendidikan'] ?? null,
            'alamat_sasaran' => $data['alamat'],
            'rt' => $data['rt'] ?? null,
            'rw' => $data['rw'] ?? null,
            'kepersertaan_bpjs' => $data['kepersertaanBpjs'] ?? null,
            'nomor_bpjs' => $data['nomorBpjs'] ?? null,
            'nomor_telepon' => $data['nomorTelepon'] ?? null,
            'nama_suami' => $namaSuami,
            'nik_suami' => $nikSuami ?: null,
            'pekerjaan_suami' => $pekerjaanSuami,
            'status_keluarga_suami' => $statusKeluargaSuami,
        ]);
        return true;
    }

    protected function insertPralansia(int $posyanduId, array $data): bool
    {
        return DB::transaction(function () use ($posyanduId, $data) {
            $email = ($data['noKk'] ?: $data['nik']) . '@gmail.com';
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $data['nama'],
                'password' => Hash::make($data['noKk'] ?: $data['nik']),
                'email_verified_at' => now(),
            ]);
            if (!$user->hasRole('orangtua')) {
                $user->assignRole('orangtua');
            }

            SasaranPralansia::create([
                'id_posyandu' => $posyanduId,
                'id_users' => $user->id,
                'nama_sasaran' => $data['nama'],
                'nik_sasaran' => $data['nik'],
                'no_kk_sasaran' => $data['noKk'] ?: null,
                'tempat_lahir' => $data['tempatLahir'],
                'tanggal_lahir' => $data['tanggalLahir'],
                'jenis_kelamin' => $data['jenisKelamin'],
                'umur_sasaran' => $data['umur'],
                'pekerjaan' => $data['pekerjaan'] ?? 'Lainnya',
                'pendidikan' => $data['pendidikan'] ?? null,
                'alamat_sasaran' => $data['alamat'],
                'rt' => $data['rt'] ?? null,
                'rw' => $data['rw'] ?? null,
                'kepersertaan_bpjs' => $data['kepersertaanBpjs'] ?? null,
                'nomor_bpjs' => $data['nomorBpjs'] ?? null,
                'nomor_telepon' => $data['nomorTelepon'] ?? null,
                'status_keluarga' => $data['statusKeluarga'] ?? null,
            ]);
            return true;
        });
    }

    protected function insertLansia(int $posyanduId, array $data): bool
    {
        return DB::transaction(function () use ($posyanduId, $data) {
            $email = ($data['noKk'] ?: $data['nik']) . '@gmail.com';
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $data['nama'],
                'password' => Hash::make($data['noKk'] ?: $data['nik']),
                'email_verified_at' => now(),
            ]);
            if (!$user->hasRole('orangtua')) {
                $user->assignRole('orangtua');
            }

            SasaranLansia::create([
                'id_posyandu' => $posyanduId,
                'id_users' => $user->id,
                'nama_sasaran' => $data['nama'],
                'nik_sasaran' => $data['nik'],
                'no_kk_sasaran' => $data['noKk'] ?: null,
                'tempat_lahir' => $data['tempatLahir'],
                'tanggal_lahir' => $data['tanggalLahir'],
                'jenis_kelamin' => $data['jenisKelamin'],
                'umur_sasaran' => $data['umur'],
                'pekerjaan' => $data['pekerjaan'] ?? 'Lainnya',
                'pendidikan' => $data['pendidikan'] ?? null,
                'alamat_sasaran' => $data['alamat'],
                'rt' => $data['rt'] ?? null,
                'rw' => $data['rw'] ?? null,
                'kepersertaan_bpjs' => $data['kepersertaanBpjs'] ?? null,
                'nomor_bpjs' => $data['nomorBpjs'] ?? null,
                'nomor_telepon' => $data['nomorTelepon'] ?? null,
                'status_keluarga' => $data['statusKeluarga'] ?? null,
            ]);
            return true;
        });
    }
}
