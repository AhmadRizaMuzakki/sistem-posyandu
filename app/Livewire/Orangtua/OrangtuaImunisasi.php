<?php

namespace App\Livewire\Orangtua;

use App\Models\Imunisasi;
use App\Models\SasaranBayibalita;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Layout('layouts.orangtuadashboard')]
class OrangtuaImunisasi extends Component
{
    public $userId;

    /** Filter: nama sasaran (anak) */
    public $filterNama = '';

    /** Filter: jenis imunisasi */
    public $filterJenisImunisasi = '';

    public function mount()
    {
        $this->userId = Auth::id();
    }

    /**
     * Bangun allSasaran dan imunisasiList (dengan filter).
     */
    protected function getImunisasiData()
    {
        $sasaranBayi = SasaranBayibalita::where('id_users', $this->userId)->get();
        $sasaranRemaja = SasaranRemaja::where('id_users', $this->userId)->get();
        $sasaranDewasa = SasaranDewasa::where('id_users', $this->userId)->get();
        $sasaranPralansia = SasaranPralansia::where('id_users', $this->userId)->get();
        $sasaranLansia = SasaranLansia::where('id_users', $this->userId)->get();

        $allSasaran = collect();
        foreach ($sasaranBayi as $s) {
            $allSasaran->push(['id' => $s->id_sasaran_bayibalita, 'kategori' => 'bayibalita', 'nama' => $s->nama_sasaran, 'nik' => $s->nik_sasaran, 'tanggal_lahir' => $s->tanggal_lahir]);
        }
        foreach ($sasaranRemaja as $s) {
            $allSasaran->push(['id' => $s->id_sasaran_remaja, 'kategori' => 'remaja', 'nama' => $s->nama_sasaran, 'nik' => $s->nik_sasaran, 'tanggal_lahir' => $s->tanggal_lahir]);
        }
        foreach ($sasaranDewasa as $s) {
            $allSasaran->push(['id' => $s->id_sasaran_dewasa, 'kategori' => 'dewasa', 'nama' => $s->nama_sasaran, 'nik' => $s->nik_sasaran, 'tanggal_lahir' => $s->tanggal_lahir]);
        }
        foreach ($sasaranPralansia as $s) {
            $allSasaran->push(['id' => $s->id_sasaran_pralansia, 'kategori' => 'pralansia', 'nama' => $s->nama_sasaran, 'nik' => $s->nik_sasaran, 'tanggal_lahir' => $s->tanggal_lahir]);
        }
        foreach ($sasaranLansia as $s) {
            $allSasaran->push(['id' => $s->id_sasaran_lansia, 'kategori' => 'lansia', 'nama' => $s->nama_sasaran, 'nik' => $s->nik_sasaran, 'tanggal_lahir' => $s->tanggal_lahir]);
        }

        // Optimasi: Batch query semua imunisasi sekaligus (menghindari N+1)
        $sasaranConditions = $allSasaran->map(fn($s) => [
            'id' => $s['id'],
            'kategori' => $s['kategori']
        ])->toArray();

        // Build batch query
        $allImunisasi = collect();
        if (!empty($sasaranConditions)) {
            $query = Imunisasi::where(function($q) use ($sasaranConditions) {
                foreach ($sasaranConditions as $cond) {
                    $q->orWhere(function($subQ) use ($cond) {
                        $subQ->where('id_sasaran', $cond['id'])
                             ->where('kategori_sasaran', $cond['kategori']);
                    });
                }
            });

            if ($this->filterJenisImunisasi !== '') {
                $query->where('jenis_imunisasi', $this->filterJenisImunisasi);
            }

            $allImunisasi = $query->orderBy('tanggal_imunisasi', 'desc')->get();
        }

        // Group imunisasi by sasaran
        $groupedImunisasi = $allImunisasi->groupBy(function($im) {
            return $im->kategori_sasaran . '_' . $im->id_sasaran;
        });

        // Ambil semua jenis imunisasi dari hasil query
        $jenisImunisasiList = $allImunisasi->pluck('jenis_imunisasi')
            ->unique()
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        // Build imunisasiList berdasarkan grouped data
        $imunisasiList = collect();
        foreach ($allSasaran as $sasaran) {
            if ($this->filterNama !== '' && $sasaran['nama'] !== $this->filterNama) {
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
            'jenisImunisasiList' => $jenisImunisasiList,
        ];
    }

    public function render()
    {
        $data = $this->getImunisasiData();
        return view('livewire.orangtua.orangtua-imunisasi', [
            'imunisasiList' => $data['imunisasiList'],
            'allSasaran' => $data['allSasaran'],
            'namaSasaranList' => $data['namaSasaranList'],
            'jenisImunisasiList' => $data['jenisImunisasiList'],
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
                $tensi = '-';
                if ($im->sistol !== null && $im->diastol !== null) {
                    $tensi = $im->sistol . '/' . $im->diastol;
                }
                $rows->push((object) [
                    'no' => $no++,
                    'nama_sasaran' => $item['sasaran']['nama'] ?? '-',
                    'kategori_sasaran' => ucfirst($item['sasaran']['kategori'] ?? '-'),
                    'jenis_imunisasi' => $im->jenis_imunisasi ?? '-',
                    'tanggal_imunisasi' => $im->tanggal_imunisasi ? $im->tanggal_imunisasi->format('d/m/Y') : '-',
                    'tinggi_badan' => $im->tinggi_badan !== null ? number_format($im->tinggi_badan, 1, ',', '.') : '-',
                    'berat_badan' => $im->berat_badan !== null ? number_format($im->berat_badan, 1, ',', '.') : '-',
                    'sistol' => $im->sistol,
                    'diastol' => $im->diastol,
                    'tensi' => $tensi,
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
            'filterJenisImunisasi' => $this->filterJenisImunisasi,
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
