<?php

namespace App\Services;

use Carbon\Carbon;

class AntropometriService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('antropometri');
    }

    public function normalizeJenisKelamin(?string $jenisKelamin): string
    {
        $jk = strtolower(trim((string) $jenisKelamin));
        if (in_array($jk, ['l', 'laki-laki', 'laki laki', 'pria'], true)) {
            return 'L';
        }
        if (in_array($jk, ['p', 'perempuan', 'wanita'], true)) {
            return 'P';
        }

        return 'L';
    }

    public function labelJenisKelamin(string $jk): string
    {
        return $jk === 'P' ? 'Perempuan' : 'Laki-laki';
    }

    public function hitungUmurBulan(?Carbon $tanggalLahir, ?Carbon $tanggalUkur): ?int
    {
        if (!$tanggalLahir || !$tanggalUkur) {
            return null;
        }

        return (int) $tanggalLahir->diffInMonths($tanggalUkur);
    }

    public function hitungImt(?float $beratBadan, ?float $tinggiBadan): ?float
    {
        if ($beratBadan === null || $tinggiBadan === null || $tinggiBadan <= 0) {
            return null;
        }

        $tinggiMeter = $tinggiBadan / 100;

        return round($beratBadan / ($tinggiMeter * $tinggiMeter), 1);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function evaluasi(
        ?float $beratBadan,
        ?float $tinggiBadan,
        ?Carbon $tanggalLahir,
        ?Carbon $tanggalUkur,
        ?string $jenisKelamin,
        ?string $tekananDarah = null,
        ?float $gulaDarah = null
    ): ?array {
        if ($beratBadan === null || $tinggiBadan === null) {
            return null;
        }

        $jk = $this->normalizeJenisKelamin($jenisKelamin);
        $umurBulan = $this->hitungUmurBulan($tanggalLahir, $tanggalUkur);
        $imt = $this->hitungImt($beratBadan, $tinggiBadan);

        if ($umurBulan === null || $imt === null) {
            return null;
        }

        $umurTahun = (int) floor($umurBulan / 12);
        $sisaBulan = $umurBulan % 12;
        $umurLabel = $umurTahun > 0
            ? $umurTahun . ' thn' . ($sisaBulan > 0 ? ' ' . $sisaBulan . ' bln' : '')
            : $umurBulan . ' bln';

        if ($umurBulan > 120) {
            $hasil = $this->evaluasiImtDewasa($beratBadan, $tinggiBadan, $imt, $umurLabel, $jk);
        } elseif ($umurBulan >= 61) {
            $hasil = $this->evaluasiImtAnak($beratBadan, $tinggiBadan, $imt, $jk, $umurBulan, $umurLabel);
        } else {
            $hasil = $this->evaluasiAnakBalita($beratBadan, $tinggiBadan, $jk, $umurBulan, $umurLabel, $imt);
        }

        return $this->lampirkanTandaVital($hasil, $tekananDarah, $gulaDarah);
    }

    /**
     * @return array<string, mixed>
     */
    protected function evaluasiAnakBalita(
        float $beratBadan,
        float $tinggiBadan,
        string $jk,
        int $umurBulan,
        string $umurLabel,
        float $imt
    ): array {
        $bulanKunci = max(12, min(60, $umurBulan));
        $standarBb = $this->config['bb_u'][$jk][$bulanKunci] ?? null;
        $standarTb = $this->config['tb_u'][$jk][$bulanKunci] ?? null;
        $standarBbTb = $this->lookupBbTb($jk, $tinggiBadan);

        $bbU = $this->klasifikasiStandar($beratBadan, $standarBb, 'bb_u');
        $tbU = $this->klasifikasiStandar($tinggiBadan, $standarTb, 'tb_u');
        $bbTb = $standarBbTb
            ? $this->klasifikasiStandar($beratBadan, $standarBbTb, 'bb_tb')
            : [
                'kode' => 'unknown',
                'status' => 'Tidak tersedia',
                'color' => 'gray',
                'rekomendasi' => 'Tinggi badan di luar rentang tabel BB/TB. Gunakan penilaian BB/U dan TB/U sebagai acuan.',
            ];

        $indeks = [
            $this->lengkapiIndeksTampilan([
                'nama' => 'Berat Badan Menurut Umur',
                'singkat' => 'BB/U',
                'status' => $bbU['status'],
                'rekomendasi' => $bbU['rekomendasi'],
                'color' => $bbU['color'],
            ]),
            $this->lengkapiIndeksTampilan([
                'nama' => 'Tinggi Badan Menurut Umur',
                'singkat' => 'TB/U',
                'status' => $tbU['status'],
                'rekomendasi' => $tbU['rekomendasi'],
                'color' => $tbU['color'],
            ]),
            $this->lengkapiIndeksTampilan([
                'nama' => 'Berat Badan Menurut Tinggi Badan',
                'singkat' => 'BB/TB',
                'status' => $bbTb['status'],
                'rekomendasi' => $bbTb['rekomendasi'],
                'color' => $bbTb['color'],
            ]),
        ];

        $kesimpulan = $this->kesimpulanDariIndeks($indeks);

        return [
            'metode' => 'bb_tb_u',
            'metode_label' => 'Standar Antropometri Anak (PMK 2/2020)',
            'umur_bulan' => $umurBulan,
            'umur_label' => $umurLabel,
            'berat_badan' => $beratBadan,
            'tinggi_badan' => $tinggiBadan,
            'imt' => $imt,
            'jenis_kelamin' => $this->labelJenisKelamin($jk),
            'indeks' => $indeks,
            'kategori' => $kesimpulan['kategori'],
            'kategori_color' => $kesimpulan['color'],
            'penjelasan' => $kesimpulan['penjelasan'],
            'card' => $this->buildCard($jk, $umurBulan, $beratBadan, $tinggiBadan, $indeks, $kesimpulan),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function evaluasiImtAnak(
        float $beratBadan,
        float $tinggiBadan,
        float $imt,
        string $jk,
        int $umurBulan,
        string $umurLabel
    ): array {
        $tahun = (int) floor($umurBulan / 12);
        $bulanKunci = min(120, max(60, $tahun * 12));
        $standar = $this->config['imt_u'][$jk][$bulanKunci] ?? $this->config['imt_u'][$jk][120] ?? null;

        $klas = $standar
            ? $this->klasifikasiStandarSederhana($imt, $standar['min'], $standar['max'])
            : ['kode' => 'unknown', 'status' => 'Tidak Diketahui', 'color' => 'gray'];

        $status = match ($klas['kode']) {
            'sangat_rendah' => 'Gizi Buruk',
            'rendah' => 'Gizi Kurang',
            'normal' => 'Gizi Baik',
            'tinggi' => 'Berisiko Overweight',
            'sangat_tinggi' => 'Obesitas',
            default => 'Tidak Diketahui',
        };

        $saran = match ($klas['kode']) {
            'normal' => sprintf(
                'Pertahankan pola makan sehat. IMT ideal usia ini sekitar %.1f–%.1f kg/m².',
                $standar['min'] ?? 0,
                $standar['max'] ?? 0
            ),
            'rendah', 'sangat_rendah' => 'Perbanyak asupan bergizi dan konsultasikan ke posyandu/puskesmas.',
            default => 'Kurangi makanan berlemak/tinggi gula dan konsultasikan ke petugas kesehatan.',
        };

        $nilaiImt = sprintf('%.1f kg/m²', $imt);
        $rekomendasi = sprintf('Status: %s (IMT %s). %s', $status, $nilaiImt, $saran);

        $indeks = [[
            'nama' => 'Indeks Massa Tubuh menurut Umur',
            'singkat' => 'IMT/U',
            'status' => $status,
            'nilai' => $nilaiImt,
            'saran' => $saran,
            'rekomendasi' => $rekomendasi,
            'color' => $klas['color'],
        ]];

        $kesimpulan = ['kategori' => $status, 'color' => $klas['color'], 'penjelasan' => $rekomendasi];

        return [
            'metode' => 'imt_u',
            'metode_label' => 'Standar IMT/U usia 5–10 tahun (PMK 2/2020)',
            'umur_bulan' => $umurBulan,
            'umur_label' => $umurLabel,
            'berat_badan' => $beratBadan,
            'tinggi_badan' => $tinggiBadan,
            'imt' => $imt,
            'jenis_kelamin' => $this->labelJenisKelamin($jk),
            'indeks' => $indeks,
            'kategori' => $status,
            'kategori_color' => $klas['color'],
            'penjelasan' => $rekomendasi,
            'card' => $this->buildCard($jk, $umurBulan, $beratBadan, $tinggiBadan, $indeks, $kesimpulan),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function evaluasiImtDewasa(
        float $beratBadan,
        float $tinggiBadan,
        float $imt,
        string $umurLabel,
        string $jk
    ): array {
        $kategori = 'Tidak Diketahui';
        $color = 'gray';
        $rekomendasi = '';

        foreach ($this->config['imt_dewasa'] as $rentang) {
            $hasMin = array_key_exists('min', $rentang);
            $hasMax = array_key_exists('max', $rentang);

            if (!$hasMin && $hasMax && $imt < $rentang['max']) {
                $kategori = $rentang['label'];
                $color = $rentang['color'];
                break;
            }
            if ($hasMin && $hasMax && $imt >= $rentang['min'] && $imt <= $rentang['max']) {
                $kategori = $rentang['label'];
                $color = $rentang['color'];
                break;
            }
            if ($hasMin && !$hasMax && $imt > $rentang['min']) {
                $kategori = $rentang['label'];
                $color = $rentang['color'];
                break;
            }
        }

        $saran = match ($color) {
            'green' => 'Pertahankan pola hidup sehat dan aktivitas fisik rutin.',
            'orange', 'yellow' => 'Perhatikan asupan gizi dan konsultasikan ke petugas kesehatan.',
            default => 'Segera konsultasikan ke petugas kesehatan untuk penanganan.',
        };

        $nilaiImt = sprintf('%.1f kg/m²', $imt);
        $rekomendasi = sprintf('Status: %s (IMT %s). %s', $kategori, $nilaiImt, $saran);

        $indeks = [[
            'nama' => 'Indeks Massa Tubuh Dewasa',
            'singkat' => 'IMT',
            'status' => $kategori,
            'nilai' => $nilaiImt,
            'saran' => $saran,
            'rekomendasi' => $rekomendasi,
            'color' => $color,
        ]];

        $kesimpulan = ['kategori' => $kategori, 'color' => $color, 'penjelasan' => $rekomendasi];

        return [
            'metode' => 'imt_dewasa',
            'metode_label' => 'IMT Dewasa (Populasi Indonesia)',
            'umur_label' => $umurLabel,
            'berat_badan' => $beratBadan,
            'tinggi_badan' => $tinggiBadan,
            'imt' => $imt,
            'jenis_kelamin' => $this->labelJenisKelamin($jk),
            'indeks' => $indeks,
            'kategori' => $kategori,
            'kategori_color' => $color,
            'penjelasan' => $rekomendasi,
            'card' => $this->buildCard($jk, null, $beratBadan, $tinggiBadan, $indeks, $kesimpulan, $umurLabel),
        ];
    }

    /**
     * @param  array<int, array{nama: string, singkat: string, status: string, rekomendasi: string, color: string}>  $indeks
     * @param  array{kategori: string, color: string, penjelasan: string}  $kesimpulan
     * @return array<string, mixed>
     */
    protected function buildCard(
        string $jk,
        ?int $umurBulan,
        float $beratBadan,
        float $tinggiBadan,
        array $indeks,
        array $kesimpulan,
        ?string $umurLabel = null
    ): array {
        return [
            'jenis_kelamin' => $this->labelJenisKelamin($jk),
            'umur_bulan' => $umurBulan,
            'umur_label' => $umurLabel ?? ($umurBulan !== null ? $umurBulan . ' Bulan' : '-'),
            'berat_badan' => $beratBadan,
            'tinggi_badan' => $tinggiBadan,
            'indeks' => $indeks,
            'kesimpulan' => $kesimpulan['kategori'],
            'warna' => $kesimpulan['color'],
        ];
    }

    /**
     * @param  array<string, float>|null  $standar
     * @return array{kode: string, status: string, color: string, rekomendasi: string}
     */
    protected function klasifikasiStandar(float $nilai, ?array $standar, string $tipe): array
    {
        if (!$standar || !isset($standar['median'])) {
            return [
                'kode' => 'unknown',
                'status' => 'Tidak tersedia',
                'color' => 'gray',
                'rekomendasi' => 'Data standar tidak tersedia untuk pengukuran ini.',
            ];
        }

        $kode = $this->tentukanKodeSd($nilai, $standar);
        $status = $this->labelStatus($kode, $tipe);
        $color = $this->warnaStatus($kode);
        $rekomendasi = $this->rekomendasiStatus($kode, $tipe, $standar, $nilai);

        return compact('kode', 'status', 'color', 'rekomendasi');
    }

    /**
     * @param  array<string, float>  $standar
     */
    protected function tentukanKodeSd(float $nilai, array $standar): string
    {
        if ($nilai < $standar['sd3_min']) {
            return 'sangat_rendah';
        }
        if ($nilai < $standar['min']) {
            return 'rendah';
        }
        if ($nilai <= $standar['max']) {
            return 'normal';
        }
        if ($nilai <= $standar['sd3_max']) {
            return 'tinggi';
        }

        return 'sangat_tinggi';
    }

    protected function labelStatus(string $kode, string $tipe): string
    {
        return match ($tipe) {
            'bb_u' => match ($kode) {
                'sangat_rendah' => 'Berat badan sangat kurang',
                'rendah' => 'Berat badan kurang',
                'normal' => 'Normal',
                'tinggi' => 'Berat badan lebih',
                'sangat_tinggi' => 'Obesitas',
                default => 'Tidak tersedia',
            },
            'tb_u' => match ($kode) {
                'sangat_rendah' => 'Sangat pendek',
                'rendah' => 'Pendek',
                'normal' => 'Normal',
                'tinggi' => 'Tinggi',
                'sangat_tinggi' => 'Sangat tinggi',
                default => 'Tidak tersedia',
            },
            'bb_tb' => match ($kode) {
                'sangat_rendah' => 'Gizi buruk',
                'rendah' => 'Gizi kurang',
                'normal' => 'Normal',
                'tinggi' => 'Berisiko overweight',
                'sangat_tinggi' => 'Obesitas',
                default => 'Tidak tersedia',
            },
            default => 'Tidak tersedia',
        };
    }

    protected function warnaStatus(string $kode): string
    {
        return match ($kode) {
            'normal' => 'green',
            'rendah', 'tinggi' => 'orange',
            default => 'red',
        };
    }

    /**
     * @param  array<string, float>  $standar
     */
    protected function rekomendasiStatus(string $kode, string $tipe, array $standar, float $nilai): string
    {
        $median = $standar['median'];
        $satuan = $tipe === 'tb_u' ? 'cm' : 'kg';
        $labelNilai = match ($tipe) {
            'tb_u' => 'Panjang/tinggi badan',
            'bb_tb' => 'Berat badan untuk tinggi badan ini',
            default => 'Berat badan',
        };

        if ($kode === 'normal') {
            return sprintf(
                'Status: Normal. Pertahankan dengan memberikan makanan sehat dan bergizi seimbang. %s ideal usia/ukuran ini sekitar %s %s.',
                $labelNilai,
                $this->formatAngka($median),
                $satuan
            );
        }

        if (in_array($kode, ['rendah', 'sangat_rendah'], true)) {
            return sprintf(
                'Status: %s. Tingkatkan asupan gizi seimbang. %s ideal sekitar %s %s. Konsultasikan ke posyandu jika perlu.',
                $this->labelStatus($kode, $tipe),
                $labelNilai,
                $this->formatAngka($median),
                $satuan
            );
        }

        return sprintf(
            'Status: %s. %s anak saat ini %s %s (ideal ~%s %s). Konsultasikan ke petugas kesehatan untuk tindak lanjut.',
            $this->labelStatus($kode, $tipe),
            $labelNilai,
            $this->formatAngka($nilai),
            $satuan,
            $this->formatAngka($median),
            $satuan
        );
    }

    /**
     * @return array{kode: string, status: string, color: string}
     */
    protected function klasifikasiStandarSederhana(float $nilai, float $min, float $max): array
    {
        if ($nilai < $min) {
            return ['kode' => 'rendah', 'status' => 'Di bawah normal', 'color' => 'orange'];
        }
        if ($nilai > $max) {
            return ['kode' => 'sangat_tinggi', 'status' => 'Di atas normal', 'color' => 'red'];
        }

        return ['kode' => 'normal', 'status' => 'Normal', 'color' => 'green'];
    }

    /**
     * @param  array<int, array{color: string, status: string}>  $indeks
     * @return array{kategori: string, color: string, penjelasan: string}
     */
    protected function kesimpulanDariIndeks(array $indeks): array
    {
        $prioritas = ['red' => 4, 'orange' => 3, 'yellow' => 2, 'green' => 1, 'gray' => 0];
        $terburuk = $indeks[0];
        $maxP = -1;

        foreach ($indeks as $item) {
            $p = $prioritas[$item['color']] ?? 0;
            if ($p > $maxP) {
                $maxP = $p;
                $terburuk = $item;
            }
        }

        $adaTandaVital = collect($indeks)->contains(fn ($i) => in_array($i['singkat'] ?? '', ['TD', 'GD'], true));
        $adaAntropometri = collect($indeks)->contains(fn ($i) => in_array($i['singkat'] ?? '', ['BB/U', 'TB/U', 'BB/TB', 'IMT/U', 'IMT'], true));

        if ($maxP <= 1) {
            $kategori = match (true) {
                $adaTandaVital && $adaAntropometri => 'Kondisi Kesehatan Baik',
                $adaTandaVital => 'Tanda Vital Normal',
                default => 'Pertumbuhan Normal',
            };

            $penjelasan = match (true) {
                $adaTandaVital && $adaAntropometri => 'Status gizi dan tanda vital (tekanan darah, gula darah) dalam batas normal.',
                $adaTandaVital => 'Tekanan darah dan gula darah dalam batas normal.',
                default => 'Indeks pertumbuhan dalam batas normal sesuai PMK No. 2 Tahun 2020.',
            };

            return [
                'kategori' => $kategori,
                'color' => 'green',
                'penjelasan' => $penjelasan,
            ];
        }

        $perluTindak = collect($indeks)
            ->filter(fn ($i) => ($prioritas[$i['color']] ?? 0) > 1)
            ->pluck('singkat')
            ->implode(', ');

        return [
            'kategori' => $terburuk['status'],
            'color' => $terburuk['color'],
            'penjelasan' => 'Perhatikan indikator yang perlu tindak lanjut: ' . $perluTindak . '.',
        ];
    }

    /**
     * @param  array{nama: string, singkat: string, status: string, rekomendasi: string, color: string, nilai?: string, saran?: string}  $item
     * @return array{nama: string, singkat: string, status: string, rekomendasi: string, color: string, nilai?: string, saran?: string}
     */
    protected function lengkapiIndeksTampilan(array $item): array
    {
        if (!isset($item['saran']) && !empty($item['rekomendasi'])) {
            $item['saran'] = $this->ekstrakSaranRekomendasi($item['rekomendasi']);
        }

        return $item;
    }

    protected function ekstrakSaranRekomendasi(string $rekomendasi): string
    {
        if (preg_match('/^Status:\s*.+\)\.\s+(.+)$/us', $rekomendasi, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^Status:\s*.+?\.\s+(.+)$/us', $rekomendasi, $matches)) {
            return $matches[1];
        }

        return $rekomendasi;
    }

    /**
     * @return array{sistol: int, diastol: int}|null
     */
    protected function parseTekananDarah(?string $tekananDarah): ?array
    {
        if (!$tekananDarah || !preg_match('/^(\d{2,3})\/(\d{2,3})$/', trim($tekananDarah), $matches)) {
            return null;
        }

        return [
            'sistol' => (int) $matches[1],
            'diastol' => (int) $matches[2],
        ];
    }

    /**
     * @return array{nama: string, singkat: string, status: string, rekomendasi: string, color: string}|null
     */
    protected function evaluasiTekananDarah(string $tekananDarah): ?array
    {
        $parsed = $this->parseTekananDarah($tekananDarah);
        if (!$parsed) {
            return null;
        }

        $sistol = $parsed['sistol'];
        $diastol = $parsed['diastol'];

        if ($sistol < 90 || $diastol < 60) {
            $status = 'Hipotensi';
            $color = 'orange';
            $saran = 'Perbanyak asupan cairan, hindari berdiri terlalu lama, dan konsultasikan ke petugas kesehatan.';
        } elseif ($sistol < 120 && $diastol < 80) {
            $status = 'Normal';
            $color = 'green';
            $saran = 'Pertahankan pola hidup sehat, olahraga rutin, dan batasi garam.';
        } elseif ($sistol < 140 && $diastol < 90) {
            $status = 'Prehipertensi';
            $color = 'yellow';
            $saran = 'Kurangi garam, perbanyak aktivitas fisik, dan pantau tekanan darah secara berkala.';
        } elseif ($sistol < 160 && $diastol < 100) {
            $status = 'Hipertensi Tingkat 1';
            $color = 'orange';
            $saran = 'Segera konsultasikan ke petugas kesehatan untuk evaluasi dan penanganan lebih lanjut.';
        } else {
            $status = 'Hipertensi Tingkat 2';
            $color = 'red';
            $saran = 'Segera konsultasikan ke fasilitas kesehatan untuk pemeriksaan dan penanganan medis.';
        }

        return [
            'nama' => 'Tekanan Darah',
            'singkat' => 'TD',
            'status' => $status,
            'nilai' => $tekananDarah . ' mmHg',
            'saran' => $saran,
            'rekomendasi' => sprintf(
                'Status: %s (%s mmHg). %s',
                $status,
                $tekananDarah,
                $saran
            ),
            'color' => $color,
        ];
    }

    /**
     * @return array{nama: string, singkat: string, status: string, rekomendasi: string, color: string}
     */
    protected function evaluasiGulaDarah(float $gulaDarah): array
    {
        if ($gulaDarah < 70) {
            $status = 'Hipoglikemia';
            $color = 'red';
            $saran = 'Segera konsumsi makanan/minuman mengandung gula dan konsultasikan ke petugas kesehatan.';
        } elseif ($gulaDarah <= 100) {
            $status = 'Normal';
            $color = 'green';
            $saran = 'Pertahankan pola makan seimbang dan aktivitas fisik rutin.';
        } elseif ($gulaDarah <= 125) {
            $status = 'Tinggi (Waspada)';
            $color = 'yellow';
            $saran = 'Kurangi asupan gula dan karbohidrat olahan, serta lakukan pemeriksaan ulang.';
        } elseif ($gulaDarah < 200) {
            $status = 'Tinggi';
            $color = 'orange';
            $saran = 'Segera konsultasikan ke petugas kesehatan untuk pemeriksaan gula darah puasa.';
        } else {
            $status = 'Sangat Tinggi';
            $color = 'red';
            $saran = 'Segera konsultasikan ke fasilitas kesehatan untuk evaluasi diabetes mellitus.';
        }

        return [
            'nama' => 'Gula Darah',
            'singkat' => 'GD',
            'status' => $status,
            'nilai' => sprintf('%.0f mg/dL', $gulaDarah),
            'saran' => $saran,
            'rekomendasi' => sprintf(
                'Status: %s (%.0f mg/dL). %s',
                $status,
                $gulaDarah,
                $saran
            ),
            'color' => $color,
        ];
    }

    /**
     * @param  array<string, mixed>  $hasil
     * @return array<string, mixed>
     */
    protected function lampirkanTandaVital(array $hasil, ?string $tekananDarah, ?float $gulaDarah): array
    {
        $indeks = $hasil['indeks'] ?? [];
        $tambahan = [];

        if ($tekananDarah) {
            $td = $this->evaluasiTekananDarah($tekananDarah);
            if ($td) {
                $tambahan[] = $td;
            }
        }

        if ($gulaDarah !== null) {
            $tambahan[] = $this->evaluasiGulaDarah($gulaDarah);
        }

        if (!empty($tambahan)) {
            $indeks = array_merge($indeks, $tambahan);
            $kesimpulan = $this->kesimpulanDariIndeks($indeks);

            $hasil['indeks'] = $indeks;
            $hasil['kategori'] = $kesimpulan['kategori'];
            $hasil['kategori_color'] = $kesimpulan['color'];
            $hasil['penjelasan'] = $kesimpulan['penjelasan'];

            if (!empty($hasil['card'])) {
                $hasil['card']['indeks'] = $indeks;
                $hasil['card']['kesimpulan'] = $kesimpulan['kategori'];
                $hasil['card']['warna'] = $kesimpulan['color'];
            }
        }

        if (!empty($hasil['card'])) {
            $hasil['card']['tekanan_darah'] = $tekananDarah;
            $hasil['card']['gula_darah'] = $gulaDarah;
        }

        return $hasil;
    }

    /**
     * @return array<string, float>|null
     */
    protected function lookupBbTb(string $jk, float $tinggi): ?array
    {
        $data = $this->config['bb_tb'][$jk] ?? [];
        if (empty($data)) {
            return null;
        }

        $rounded = (string) round($tinggi, 1);
        if (isset($data[$rounded])) {
            return $data[$rounded];
        }

        $closest = null;
        $minDiff = PHP_FLOAT_MAX;
        foreach ($data as $h => $std) {
            $diff = abs((float) $h - $tinggi);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $std;
            }
        }

        return $minDiff <= 5 ? $closest : null;
    }

    protected function formatAngka(float $nilai): string
    {
        return number_format($nilai, 1, ',', '.');
    }
}
