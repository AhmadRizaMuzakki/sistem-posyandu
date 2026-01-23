<?php

namespace App\Livewire\Posyandu;

use App\Livewire\Posyandu\Traits\PosyanduHelper;
use App\Models\Jadwal;
use App\Models\PetugasKesehatan;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PosyanduJadwal extends Component
{
    use PosyanduHelper;

    public $currentMonth;
    public $currentYear;
    public $selectedDate = null;
    public $isModalOpen = false;
    public $jadwalId = null;
    public $id_petugas_kesehatan = null;
    public $keterangan = '';
    public $tanggal = '';
    public $presensi = 'belum_hadir'; // Default: belum_hadir
    public $activeTab = 'jadwal'; // 'jadwal' atau 'absen'
    public $absenDate = null; // Tanggal untuk tab absen
    public $isAddJadwalModalOpen = false; // Modal untuk tambah jadwal baru

    #[Layout('layouts.posyandudashboard')]

    public function mount()
    {
        $this->initializePosyandu();
        $this->currentMonth = date('n');
        $this->currentYear = date('Y');
        $this->absenDate = date('Y-m-d'); // Default ke hari ini
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function goToToday()
    {
        $this->currentMonth = date('n');
        $this->currentYear = date('Y');
    }

    public function openModal($date = null)
    {
        if ($date) {
            $this->selectedDate = $date;
            $this->tanggal = $date;
        }
        $this->isModalOpen = true;
    }

    public function openAddJadwalModal()
    {
        $this->selectedDate = null;
        $this->tanggal = date('Y-m-d');
        $this->jadwalId = null;
        $this->id_petugas_kesehatan = null;
        $this->keterangan = '';
        $this->presensi = 'belum_hadir';
        $this->isAddJadwalModalOpen = true;
    }

    public function closeAddJadwalModal()
    {
        $this->isAddJadwalModalOpen = false;
        $this->selectedDate = null;
        $this->jadwalId = null;
        $this->id_petugas_kesehatan = null;
        $this->keterangan = '';
        $this->tanggal = '';
        $this->presensi = 'belum_hadir';
    }

    public function openEditModal($jadwalId)
    {
        $jadwal = Jadwal::findOrFail($jadwalId);
        if ($jadwal->id_posyandu != $this->posyanduId) {
            abort(403, 'Unauthorized access');
        }
        
        $this->selectedDate = $jadwal->tanggal->format('Y-m-d');
        $this->tanggal = $jadwal->tanggal->format('Y-m-d');
        $this->jadwalId = $jadwal->id_jadwal;
        $this->id_petugas_kesehatan = $jadwal->id_petugas_kesehatan;
        $this->keterangan = $jadwal->keterangan ?? '';
        $this->presensi = $jadwal->presensi ?? 'belum_hadir';
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->selectedDate = null;
        $this->jadwalId = null;
        $this->id_petugas_kesehatan = null;
        $this->keterangan = '';
        $this->tanggal = '';
        $this->presensi = 'belum_hadir';
    }

    public function saveJadwal()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'id_petugas_kesehatan' => 'required|exists:petugas_kesehatan,id_petugas_kesehatan',
            'keterangan' => 'nullable|string',
            'presensi' => 'required|in:hadir,tidak_hadir,belum_hadir',
        ]);

        // Validasi petugas kesehatan milik posyandu yang sama
        $petugas = PetugasKesehatan::findOrFail($this->id_petugas_kesehatan);
        if ($petugas->id_posyandu != $this->posyanduId) {
            session()->flash('message', 'Petugas kesehatan tidak terdaftar di posyandu ini.');
            session()->flash('messageType', 'error');
            return;
        }

        // Pastikan presensi dalam lowercase dan valid
        $presensi = strtolower(trim($this->presensi));
        
        // Validasi nilai presensi
        if (!in_array($presensi, ['hadir', 'tidak_hadir', 'belum_hadir'])) {
            session()->flash('message', 'Nilai presensi tidak valid.');
            session()->flash('messageType', 'error');
            return;
        }

        if ($this->jadwalId) {
            // Update existing
            $jadwal = Jadwal::findOrFail($this->jadwalId);
            if ($jadwal->id_posyandu != $this->posyanduId) {
                abort(403, 'Unauthorized access');
            }
            $jadwal->update([
                'tanggal' => $this->tanggal,
                'id_petugas_kesehatan' => $this->id_petugas_kesehatan,
                'keterangan' => $this->keterangan ?: null,
                'presensi' => $presensi,
            ]);
            session()->flash('message', 'Jadwal berhasil diperbarui.');
        } else {
            // Create new - bisa multiple petugas di tanggal yang sama
            // Gunakan DB::table untuk memastikan enum di-quote dengan benar
            DB::table('jadwals')->insert([
                'id_posyandu' => $this->posyanduId,
                'tanggal' => $this->tanggal,
                'id_petugas_kesehatan' => $this->id_petugas_kesehatan,
                'keterangan' => $this->keterangan ?: null,
                'presensi' => $presensi, // Laravel akan otomatis quote string ini
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            session()->flash('message', 'Jadwal berhasil ditambahkan.');
        }

        session()->flash('messageType', 'success');
        if ($this->isAddJadwalModalOpen) {
            $this->closeAddJadwalModal();
        } else {
            $this->closeModal();
        }
    }

    public function deleteJadwal($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        if ($jadwal->id_posyandu != $this->posyanduId) {
            abort(403, 'Unauthorized access');
        }
        $jadwal->delete();
        session()->flash('message', 'Jadwal berhasil dihapus.');
        session()->flash('messageType', 'success');
    }

    public function getJadwalForDate($date)
    {
        return Jadwal::where('id_posyandu', $this->posyanduId)
            ->where('tanggal', $date)
            ->first();
    }

    public function updatePresensi($id, $presensi)
    {
        $jadwal = Jadwal::findOrFail($id);
        if ($jadwal->id_posyandu != $this->posyanduId) {
            abort(403, 'Unauthorized access');
        }
        // Pastikan presensi dalam lowercase dan valid
        $presensi = strtolower(trim($presensi));
        
        if (!in_array($presensi, ['hadir', 'tidak_hadir', 'belum_hadir'])) {
            session()->flash('message', 'Nilai presensi tidak valid.');
            session()->flash('messageType', 'error');
            return;
        }
        
        $jadwal->update([
            'presensi' => $presensi,
        ]);
        session()->flash('message', 'Presensi berhasil diperbarui.');
        session()->flash('messageType', 'success');
    }

    public function render()
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        // Calculate the date range for the calendar view (including previous and next month days)
        $firstDayOfWeek = $startOfMonth->dayOfWeek;
        $prevMonthStart = $startOfMonth->copy()->subMonth()->startOfMonth();
        $daysInPrevMonth = $prevMonthStart->daysInMonth;
        $calendarStartDate = $prevMonthStart->copy()->addDays($daysInPrevMonth - $firstDayOfWeek);
        $calendarEndDate = $endOfMonth->copy()->addDays(42 - ($firstDayOfWeek + $startOfMonth->daysInMonth));
        
        // Get all jadwal for the calendar view period with petugas kesehatan
        // Group by tanggal untuk mendapatkan multiple jadwal per tanggal
        $jadwals = Jadwal::where('id_posyandu', $this->posyanduId)
            ->whereBetween('tanggal', [$calendarStartDate->format('Y-m-d'), $calendarEndDate->format('Y-m-d')])
            ->with('petugasKesehatan')
            ->get()
            ->groupBy(function($jadwal) {
                return $jadwal->tanggal->format('Y-m-d');
            });
        
        // Get petugas kesehatan untuk dropdown
        $petugasKesehatanList = PetugasKesehatan::where('id_posyandu', $this->posyanduId)
            ->orderBy('nama_petugas_kesehatan')
            ->get();

        // Build calendar days
        $daysInMonth = $startOfMonth->daysInMonth;
        $firstDayOfWeek = $startOfMonth->dayOfWeek; // 0 = Sunday, 6 = Saturday
        
        $calendarDays = [];
        
        // Add days from previous month
        $prevMonth = $startOfMonth->copy()->subMonth();
        $daysInPrevMonth = $prevMonth->daysInMonth;
        for ($i = $firstDayOfWeek - 1; $i >= 0; $i--) {
            $day = $daysInPrevMonth - $i;
            $date = Carbon::create($prevMonth->year, $prevMonth->month, $day);
            $dateKey = $date->format('Y-m-d');
            $calendarDays[] = [
                'day' => $day,
                'date' => $dateKey,
                'isToday' => $date->isToday(),
                'isOtherMonth' => true,
                'jadwals' => $jadwals->get($dateKey, collect()), // Multiple jadwal per tanggal
            ];
        }
        
        // Add days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->currentYear, $this->currentMonth, $day);
            $dateKey = $date->format('Y-m-d');
            $calendarDays[] = [
                'day' => $day,
                'date' => $dateKey,
                'isToday' => $date->isToday(),
                'isOtherMonth' => false,
                'jadwals' => $jadwals->get($dateKey, collect()), // Multiple jadwal per tanggal
            ];
        }
        
        // Add days from next month to fill the grid (42 cells total = 6 weeks)
        $totalCells = count($calendarDays);
        $remainingCells = 42 - $totalCells;
        if ($remainingCells > 0) {
            $nextMonth = $endOfMonth->copy()->addDay();
            for ($day = 1; $day <= $remainingCells; $day++) {
                $date = Carbon::create($nextMonth->year, $nextMonth->month, $day);
            $dateKey = $date->format('Y-m-d');
            $calendarDays[] = [
                'day' => $day,
                'date' => $dateKey,
                'isToday' => $date->isToday(),
                'isOtherMonth' => true,
                'jadwals' => $jadwals->get($dateKey, collect()), // Multiple jadwal per tanggal
            ];
            }
        }

        $monthName = $startOfMonth->locale('id')->translatedFormat('F Y');

        // Get jadwal untuk tanggal yang dipilih (untuk ditampilkan di modal)
        $selectedDateJadwals = collect();
        if ($this->selectedDate) {
            $selectedDateJadwals = Jadwal::where('id_posyandu', $this->posyanduId)
                ->where('tanggal', $this->selectedDate)
                ->with('petugasKesehatan')
                ->get();
        }

        // Get jadwal untuk tab absen berdasarkan tanggal yang dipilih
        $absenJadwals = collect();
        if ($this->absenDate) {
            $absenJadwals = Jadwal::where('id_posyandu', $this->posyanduId)
                ->whereDate('tanggal', $this->absenDate)
                ->with('petugasKesehatan')
                ->orderBy('created_at')
                ->get();
        }

        // Get semua jadwal untuk tab Jadwal (tabel)
        $allJadwals = Jadwal::where('id_posyandu', $this->posyanduId)
            ->with('petugasKesehatan')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.posyandu.posyandu-jadwal', [
            'title' => 'Jadwal - ' . $this->posyandu->nama_posyandu,
            'posyandu' => $this->posyandu,
            'calendarDays' => $calendarDays,
            'monthName' => $monthName,
            'petugasKesehatanList' => $petugasKesehatanList,
            'selectedDateJadwals' => $selectedDateJadwals,
            'absenJadwals' => $absenJadwals,
            'allJadwals' => $allJadwals,
        ]);
    }
}
