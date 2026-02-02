<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Models\Galeri;
use App\Models\JadwalKegiatan;
use App\Models\SasaranBayibalita;
use App\Models\SasaranIbuhamil;
use App\Models\SasaranRemaja;
use App\Models\SasaranDewasa;
use App\Models\SasaranPralansia;
use App\Models\SasaranLansia;
use App\Models\Orangtua;
use App\Models\Kader;
use App\Models\Imunisasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    /** TTL cache dalam detik (5 menit). */
    private const CACHE_TTL = 300;

    public function index(Request $request)
    {
        $posyanduId = $request->query('posyandu');
        $filterBulan = $request->query('filter_bulan');
        $search = $request->query('search');

        $filterBulan = $filterBulan ? (int) $filterBulan : null;

        if ($posyanduId) {
            $posyandu = Posyandu::select('id_posyandu', 'nama_posyandu', 'alamat_posyandu', 'domisili_posyandu', 'logo_posyandu', 'sk_posyandu')
                ->find($posyanduId);
        } else {
            $posyandu = Posyandu::select('id_posyandu', 'nama_posyandu', 'alamat_posyandu', 'domisili_posyandu', 'logo_posyandu', 'sk_posyandu')
                ->first();
        }

        // Cache daftar posyandu (jarang berubah)
        $daftarPosyandu = Cache::remember('index_daftar_posyandu', self::CACHE_TTL, function () {
            return Posyandu::select('id_posyandu', 'nama_posyandu', 'alamat_posyandu', 'domisili_posyandu', 'logo_posyandu')
                ->orderBy('nama_posyandu')
                ->get();
        });

        // Query acara dengan eager loading (hindari N+1)
        $query = JadwalKegiatan::with('posyandu:id_posyandu,nama_posyandu');

        $currentYear = Carbon::now()->year;
        if ($filterBulan && $filterBulan >= 1 && $filterBulan <= 12) {
            $startOfMonth = Carbon::create($currentYear, $filterBulan, 1)->startOfDay();
            $endOfMonth = Carbon::create($currentYear, $filterBulan, 1)->endOfMonth()->endOfDay();
            $query->whereBetween('tanggal', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
        } else {
            $today = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->addMonths(2)->endOfMonth();
            $query->where('tanggal', '>=', $today->format('Y-m-d'))
                ->where('tanggal', '<=', $endDate->format('Y-m-d'));
        }

        if ($search) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_kegiatan', 'like', $searchTerm)
                    ->orWhere('tempat', 'like', $searchTerm)
                    ->orWhere('deskripsi', 'like', $searchTerm);
            });
        }

        $acaraList = $query->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->paginate(12)
            ->withQueryString();

        // Cache bulan list (static per tahun)
        $bulanList = Cache::remember('index_bulan_list_' . $currentYear, 3600, function () use ($currentYear) {
            $list = [];
            for ($i = 1; $i <= 12; $i++) {
                $date = Carbon::create($currentYear, $i, 1);
                $list[] = [
                    'value' => $i,
                    'label' => $date->locale('id')->translatedFormat('F'),
                ];
            }
            return $list;
        });

        $currentMonth = Carbon::now()->month;
        $periodStart = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
        $periodEnd = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();

        // Cache statistik (reduce 8+ queries ke 1 cache hit)
        $stats = Cache::remember("index_stats_{$currentYear}_{$currentMonth}", self::CACHE_TTL, function () use ($periodStart, $periodEnd) {
            $start = $periodStart->format('Y-m-d H:i:s');
            $end = $periodEnd->format('Y-m-d H:i:s');

            return [
                'totalBalita' => SasaranBayibalita::whereBetween('created_at', [$start, $end])->count(),
                'totalRemaja' => SasaranRemaja::whereBetween('created_at', [$start, $end])->count(),
                'totalOrangtua' => SasaranDewasa::whereBetween('created_at', [$start, $end])->count(),
                'totalIbuHamil' => SasaranIbuhamil::whereBetween('created_at', [$start, $end])->count(),
                'totalPralansia' => SasaranPralansia::whereBetween('created_at', [$start, $end])->count(),
                'totalLansia' => SasaranLansia::whereBetween('created_at', [$start, $end])->count(),
                'totalKader' => Kader::count(),
                'totalBayiBalitaTerimunisasi' => (int) DB::table('imunisasi')
                    ->where('kategori_sasaran', 'bayibalita')
                    ->whereNotNull('id_sasaran')
                    ->whereBetween('created_at', [$start, $end])
                    ->selectRaw('COUNT(DISTINCT id_sasaran) as cnt')
                    ->value('cnt'),
            ];
        });

        $totalBalita = $stats['totalBalita'];
        $totalBayiBalitaTerimunisasi = $stats['totalBayiBalitaTerimunisasi'];
        $cakupanImunisasi = $totalBalita > 0
            ? min(100, round(($totalBayiBalitaTerimunisasi / $totalBalita) * 100))
            : 0;

        if (!$posyandu) {
            $posyandu = null;
        }

        // Cache galeri (5 menit)
        $galeriKegiatan = Cache::remember('index_galeri_12', self::CACHE_TTL, function () {
            return Galeri::latest()->take(12)->get();
        });

        return view('index', [
            'posyandu' => $posyandu,
            'acaraList' => $acaraList,
            'daftarPosyandu' => $daftarPosyandu,
            'filterBulan' => $filterBulan,
            'bulanList' => $bulanList,
            'search' => $search,
            'totalBalita' => $totalBalita,
            'totalRemaja' => $stats['totalRemaja'],
            'totalOrangtua' => $stats['totalOrangtua'],
            'totalIbuHamil' => $stats['totalIbuHamil'],
            'totalPralansia' => $stats['totalPralansia'],
            'totalLansia' => $stats['totalLansia'],
            'totalKader' => $stats['totalKader'],
            'cakupanImunisasi' => $cakupanImunisasi,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'galeriKegiatan' => $galeriKegiatan,
        ]);
    }

    /**
     * Halaman detail posyandu untuk publik (dari klik "Lihat Detail" di Daftar Posyandu).
     * Menampilkan logo, info posyandu, dan daftar kader (tanpa NIK).
     * Gunakan withCount untuk statistik (hindari N+1 & load koleksi besar).
     */
    public function posyanduDetail(string $id)
    {
        $posyandu = Posyandu::with(['kader.user'])
            ->withCount([
                'sasaran_bayibalita',
                'sasaran_remaja',
                'sasaran_dewasa',
                'sasaran_ibuhamil',
                'sasaran_pralansia',
                'sasaran_lansia',
                'kader',
            ])
            ->findOrFail($id);

        return view('posyandu-detail', ['posyandu' => $posyandu]);
    }
}
