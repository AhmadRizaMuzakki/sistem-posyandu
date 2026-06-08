<?php

namespace App\Livewire\Orangtua;

use App\Livewire\Orangtua\Traits\ImunisasiAnalyticsTrait;
use App\Models\Imunisasi;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Services\AntropometriService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Layout('layouts.orangtuadashboard')]
class OrangtuaImunisasi extends Component
{
    use ImunisasiAnalyticsTrait;

    /** Filter: nama sasaran (dari query ?sasaran=) */
    public string $filterNama = '';

    public function mount(): void
    {
        $this->syncFilterFromRequest();
    }

    protected function syncFilterFromRequest(): void
    {
        $sasaran = request()->query('sasaran', '');
        $this->filterNama = is_string($sasaran) ? trim($sasaran) : '';
    }

    /**
     * Bangun allSasaran dan imunisasiList (dengan filter).
     */
    protected function getImunisasiData()
    {
        $noKk = $this->resolveNoKk();
        $allSasaran = collect();

        if ($noKk) {
            $kategoriMap = [
                ['model' => SasaranBayibalita::class, 'id' => 'id_sasaran_bayibalita', 'slug' => 'bayibalita'],
                ['model' => SasaranRemaja::class, 'id' => 'id_sasaran_remaja', 'slug' => 'remaja'],
                ['model' => SasaranDewasa::class, 'id' => 'id_sasaran_dewasa', 'slug' => 'dewasa'],
                ['model' => SasaranPralansia::class, 'id' => 'id_sasaran_pralansia', 'slug' => 'pralansia'],
                ['model' => SasaranLansia::class, 'id' => 'id_sasaran_lansia', 'slug' => 'lansia'],
            ];

            foreach ($kategoriMap as $cfg) {
                $sasaranList = $cfg['model']::where('no_kk_sasaran', $noKk)->get();
                foreach ($sasaranList as $s) {
                    $allSasaran->push([
                        'id' => $s->{$cfg['id']},
                        'kategori' => $cfg['slug'],
                        'nama' => $s->nama_sasaran,
                        'nik' => $s->nik_sasaran,
                        'tanggal_lahir' => $s->tanggal_lahir,
                        'jenis_kelamin' => $s->jenis_kelamin,
                    ]);
                }
            }
        }

        $sasaranConditions = $allSasaran->map(fn ($s) => [
            'id' => $s['id'],
            'kategori' => $s['kategori'],
        ])->toArray();

        $allImunisasi = collect();
        if (!empty($sasaranConditions)) {
            $query = Imunisasi::where(function ($q) use ($sasaranConditions) {
                foreach ($sasaranConditions as $cond) {
                    $q->orWhere(function ($subQ) use ($cond) {
                        $subQ->where('id_sasaran', $cond['id'])
                            ->where('kategori_sasaran', $cond['kategori']);
                    });
                }
            });

            $allImunisasi = $query->orderBy('tanggal_imunisasi', 'desc')->get();
        }

        $groupedImunisasi = $allImunisasi->groupBy(fn ($im) => $im->kategori_sasaran . '_' . $im->id_sasaran);

        $imunisasiList = collect();
        foreach ($allSasaran as $sasaran) {
            if ($this->filterNama !== '' && trim($sasaran['nama']) !== trim($this->filterNama)) {
                continue;
            }

            $key = $sasaran['kategori'] . '_' . $sasaran['id'];
            $imunisasi = $groupedImunisasi->get($key, collect());

            if ($imunisasi->count() > 0) {
                $imunisasiList->push(['sasaran' => $sasaran, 'imunisasi' => $imunisasi]);
            }
        }

        return [
            'allSasaran' => $allSasaran,
            'imunisasiList' => $imunisasiList,
            'namaSasaranList' => $allSasaran->pluck('nama')->unique()->sort()->values()->toArray(),
        ];
    }

    public function render()
    {
        $data = $this->getImunisasiData();
        $antropometri = app(AntropometriService::class);
        $sasaranAnalytics = $this->getSasaranUntukAnalytics();
        $filterAktif = trim($this->filterNama) !== '';

        if ($filterAktif) {
            $sasaranAnalytics = $sasaranAnalytics->filter(
                fn ($s) => trim($s['nama']) === trim($this->filterNama)
            )->values();
        }

        $analytics = $filterAktif
            ? $this->getImunisasiAnalytics($sasaranAnalytics, $antropometri)
            : [
                'grafikPertumbuhan' => [],
                'grafikImunisasiJenis' => ['labels' => [], 'data' => []],
                'penilaianPerKategori' => [],
                'totalImunisasi' => 0,
            ];

        return view('livewire.orangtua.orangtua-imunisasi', [
            'imunisasiList' => $data['imunisasiList'],
            'allSasaran' => $data['allSasaran'],
            'namaSasaranList' => $data['namaSasaranList'],
            'filterAktif' => $filterAktif,
            'filterNama' => trim($this->filterNama),
            'grafikPertumbuhan' => $analytics['grafikPertumbuhan'],
            'grafikImunisasiJenis' => $analytics['grafikImunisasiJenis'],
            'penilaianPerKategori' => $analytics['penilaianPerKategori'],
            'totalImunisasi' => $analytics['totalImunisasi'],
        ]);
    }

    /**
     * Export data imunisasi ke PDF (dengan filter saat ini).
     */
    public function exportImunisasiPdf()
    {
        $data = $this->getImunisasiData();
        $rows = collect();
        $no = 1;
        foreach ($data['imunisasiList'] as $item) {
            foreach ($item['imunisasi'] as $im) {
                $rows->push((object) [
                    'no' => $no++,
                    'nama_sasaran' => $item['sasaran']['nama'] ?? '-',
                    'kategori_sasaran' => ucfirst($item['sasaran']['kategori'] ?? '-'),
                    'jenis_imunisasi' => $im->jenis_imunisasi ?? '-',
                    'tanggal_imunisasi' => $im->tanggal_imunisasi ? $im->tanggal_imunisasi->format('d/m/Y') : '-',
                    'tinggi_badan' => $im->tinggi_badan !== null ? number_format($im->tinggi_badan, 1, ',', '.') : '-',
                    'berat_badan' => $im->berat_badan !== null ? number_format($im->berat_badan, 1, ',', '.') : '-',
                    'tekanan_darah' => $im->tekanan_darah ? $im->tekanan_darah . ' mmHg' : '-',
                    'gula_darah' => $im->gula_darah !== null ? number_format($im->gula_darah, 0, ',', '.') . ' mg/dL' : '-',
                    'keterangan' => $im->keterangan ?? '-',
                ]);
            }
        }

        $user = Auth::user();
        $filename = 'Laporan_Imunisasi_' . date('Y-m-d_His') . '.pdf';

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);

        $html = view('pdf.laporan-orangtua-imunisasi', [
            'rows' => $rows,
            'user' => $user,
            'generatedAt' => now('Asia/Jakarta'),
            'filterNama' => $this->filterNama,
        ])->render();

        $dompdf->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
