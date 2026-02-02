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
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        // Ambil posyandu pertama sebagai default, atau bisa dikonfigurasi
        // Jika ada parameter posyandu di query string, gunakan itu
        $posyanduId = $request->query('posyandu');
        $filterBulan = $request->query('filter_bulan');
        $search = $request->query('search');
        
        // Parse filter bulan jika ada
        $filterBulan = $filterBulan ? (int)$filterBulan : null;
        
        if ($posyanduId) {
            $posyandu = Posyandu::find($posyanduId);
        } else {
            // Ambil posyandu pertama sebagai default
            $posyandu = Posyandu::first();
        }

        // Ambil semua posyandu untuk filter dropdown dan daftar posyandu
        $daftarPosyandu = Posyandu::select('id_posyandu', 'nama_posyandu', 'alamat_posyandu', 'domisili_posyandu', 'logo_posyandu')
            ->orderBy('nama_posyandu')
            ->get();

        // Ambil acara dari SEMUA posyandu untuk ditampilkan di halaman depan
        $query = JadwalKegiatan::with('posyandu');

        // Filter berdasarkan bulan jika dipilih (menggunakan tahun saat ini)
        if ($filterBulan && $filterBulan >= 1 && $filterBulan <= 12) {
            $currentYear = Carbon::now()->year;
            $startOfMonth = Carbon::create($currentYear, $filterBulan, 1)->startOfDay();
            $endOfMonth = Carbon::create($currentYear, $filterBulan, 1)->endOfMonth()->endOfDay();
            $query->whereBetween('tanggal', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
        } else {
            // Default: tampilkan acara dari bulan ini dan beberapa bulan ke depan
            $today = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->addMonths(2)->endOfMonth();
            $query->where('tanggal', '>=', $today->format('Y-m-d'))
                  ->where('tanggal', '<=', $endDate->format('Y-m-d'));
        }

        // Search berdasarkan nama acara atau tempat
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', '%' . $search . '%')
                  ->orWhere('tempat', 'like', '%' . $search . '%')
                  ->orWhere('deskripsi', 'like', '%' . $search . '%');
            });
        }

        // Pagination: 12 acara per halaman
        $acaraList = $query->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->paginate(12)
            ->withQueryString(); // Mempertahankan query string untuk filter
        
        // Generate list bulan untuk dropdown (hanya nama bulan, tanpa tahun)
        $bulanList = [];
        for ($i = 1; $i <= 12; $i++) {
            $date = Carbon::create(Carbon::now()->year, $i, 1);
            $bulanList[] = [
                'value' => $i,
                'label' => $date->locale('id')->translatedFormat('F'),
            ];
        }
        
        // Filter berdasarkan bulan ini di tahun ini
        // Statistik diambil dari SEMUA posyandu dan difilter untuk bulan ini di tahun ini
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $startOfMonth = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
        $endOfMonth = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();
        
        // Hitung statistik gabungan dari SEMUA posyandu yang dibuat/ditambahkan bulan ini di tahun ini
        // Tidak ada filter id_posyandu, sehingga mencakup semua posyandu
        $totalBalita = SasaranBayibalita::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();
        $totalRemaja = SasaranRemaja::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();
        $totalOrangtua = SasaranDewasa::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count(); // Dewasa = Orangtua
        $totalIbuHamil = SasaranIbuhamil::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();
        $totalPralansia = SasaranPralansia::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();
        $totalLansia = SasaranLansia::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();
        $totalKader = Kader::count(); // Kader tidak difilter karena biasanya tidak berubah per bulan
        
        // Hitung cakupan imunisasi (persentase dari total bayi/balita yang sudah diimunisasi)
        // Filter imunisasi dari SEMUA posyandu yang dibuat bulan ini di tahun ini
        $totalBayiBalitaTerimunisasi = DB::table('imunisasi')
            ->where('kategori_sasaran', 'bayibalita')
            ->whereNotNull('id_sasaran')
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->distinct('id_sasaran')
            ->count('id_sasaran');
        
        $cakupanImunisasi = $totalBalita > 0 
            ? round(($totalBayiBalitaTerimunisasi / $totalBalita) * 100) 
            : 0;
        // Batasi maksimal 100%
        $cakupanImunisasi = min($cakupanImunisasi, 100);
        
        if (!$posyandu) {
            // Jika tidak ada posyandu, tetap tampilkan halaman dengan data kosong
            $posyandu = null;
        }

        // Galeri: tampilkan SEMUA foto yang diunggah dari dashboard Super Admin dan Posyandu (tanpa filter posyandu)
        $galeriKegiatan = Galeri::latest()->take(12)->get();

        return view('index', [
            'posyandu' => $posyandu,
            'acaraList' => $acaraList,
            'daftarPosyandu' => $daftarPosyandu,
            'filterBulan' => $filterBulan,
            'bulanList' => $bulanList,
            'search' => $search,
            'totalBalita' => $totalBalita,
            'totalRemaja' => $totalRemaja,
            'totalOrangtua' => $totalOrangtua,
            'totalIbuHamil' => $totalIbuHamil,
            'totalPralansia' => $totalPralansia,
            'totalLansia' => $totalLansia,
            'totalKader' => $totalKader,
            'cakupanImunisasi' => $cakupanImunisasi,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'galeriKegiatan' => $galeriKegiatan,
        ]);
    }

    /**
     * Halaman detail posyandu untuk publik (dari klik "Lihat Detail" di Daftar Posyandu).
     * Menampilkan logo, info posyandu, dan daftar kader (tanpa NIK).
     */
    public function posyanduDetail(string $id)
    {
        $posyandu = Posyandu::with([
            'kader.user',
            'sasaran_bayibalita',
            'sasaran_remaja',
            'sasaran_dewasa',
            'sasaran_ibuhamil',
            'sasaran_pralansia',
            'sasaran_lansia',
        ])->findOrFail($id);
        return view('posyandu-detail', ['posyandu' => $posyandu]);
    }
}
