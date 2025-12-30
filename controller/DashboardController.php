<?php
/**
 * DashboardController - Controller untuk Dashboard
 * Mengelola tampilan dashboard utama
 */

require_once __DIR__ . '/../model/TaskModel.php';
require_once __DIR__ . '/../model/ScheduleModel.php';

function showDashboard() {
    $nim = $_SESSION['nim'];
    
    // Get statistics
    $stats = getTaskStatistics($nim);
    $countAktif = $stats['aktif'];
    $countSelesai = $stats['selesai'];
    $countTotal = $stats['total'];
    
    // Get calendar data
    $calendarData = getCalendarData($nim);
    $bulanIni = $calendarData['bulan'];
    $tahunIni = $calendarData['tahun'];
    $jumlahHari = $calendarData['jumlah_hari'];
    $hariPertama = $calendarData['hari_pertama'];
    $deadlinesMap = $calendarData['deadlines_map'];
    $bulanSekarang = $calendarData['nama_bulan'];
    
    // Get current day info
    $hariInggris = date('l');
    $namaHariMap = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    $namaHari = $namaHariMap[$hariInggris];
    $hariIni = $namaHari;
    
    // Get schedule count for today
    $countJadwal = getScheduleCount($nim, $hariIni);
    
    // Get schedules for today
    $jadwalHariIni = getSchedulesByDay($nim, $hariIni);
    
    // Get tasks
    $tugasSemua = getActiveTasks($nim);
    $tugasSelesaiList = getCompletedTasks($nim);
    $tugasInProgress = getInProgressTasks($nim);
    
    // Separate tasks by urgency
    $tugasMendesak = [];
    $tugasTerlewat = [];
    $tugasUpcomingWidget = [];
    $tugasOverdueWidget = [];
    
    $now = new DateTime();
    
    foreach ($tugasSemua as $t) {
        $deadlineDate = new DateTime($t['Deadline']);
        
        if ($now > $deadlineDate) {
            $tugasTerlewat[] = $t;
            $tugasOverdueWidget[] = $t;
        } else {
            $tugasUpcomingWidget[] = $t;
            
            $diff = $now->diff($deadlineDate);
            if ($diff->days <= 3) {
                $tugasMendesak[] = $t;
            }
        }
    }
    
    // Load dashboard view
    require_once __DIR__ . '/../view/dashboard/index.php';
}

function getCalendarData($nim) {
    // Handle month navigation
    if (isset($_GET['cal_month']) && isset($_GET['cal_year'])) {
        $bulanIni = str_pad($_GET['cal_month'], 2, '0', STR_PAD_LEFT);
        $tahunIni = $_GET['cal_year'];
    } else {
        $bulanIni = date('m');
        $tahunIni = date('Y');
    }
    
    $jumlahHari = date('t', strtotime("$tahunIni-$bulanIni-01"));
    $hariPertama = date('N', strtotime("$tahunIni-$bulanIni-01"));
    
    // Get tasks for calendar
    $tasks = getTasksByDate($nim, $bulanIni, $tahunIni);
    
    // Build deadlines map
    $deadlinesMap = [];
    $now = new DateTime();
    
    foreach ($tasks as $row) {
        $day = $row['hari'];
        if (!isset($deadlinesMap[$day])) {
            $deadlinesMap[$day] = [];
        }
        
        // Determine urgency
        $deadlineDate = new DateTime($row['Deadline']);
        $diff = $now->diff($deadlineDate);
        
        if ($now > $deadlineDate && $row['StatusTugas'] != 'Selesai') {
            $urgency = 'overdue';
        } elseif ($diff->days <= 3 && $row['StatusTugas'] != 'Selesai') {
            $urgency = 'urgent';
        } else {
            $urgency = 'normal';
        }
        
        $deadlinesMap[$day][] = [
            'title' => $row['JudulTugas'],
            'status' => $row['StatusTugas'],
            'urgency' => $urgency,
            'deadline' => $row['Deadline']
        ];
    }
    
    // Nama bulan Indonesia
    $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $bulanSekarang = $namaBulan[(int)$bulanIni];
    
    return [
        'bulan' => $bulanIni,
        'tahun' => $tahunIni,
        'jumlah_hari' => $jumlahHari,
        'hari_pertama' => $hariPertama,
        'deadlines_map' => $deadlinesMap,
        'nama_bulan' => $bulanSekarang
    ];
}
?>
