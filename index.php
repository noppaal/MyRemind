<?php
include 'config.php';

if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}

$nim = $_SESSION['nim'];

// ================= 1. LOGIKA STATISTIK & DATA =================

// A. Hitung Statistik
$resAktif = mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE StatusTugas = 'Aktif' AND NIM = '$nim'");
$countAktif = mysqli_fetch_assoc($resAktif)['total'];

$resSelesai = mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE StatusTugas = 'Selesai' AND NIM = '$nim'");
$countSelesai = mysqli_fetch_assoc($resSelesai)['total'];
$countTotal = $countAktif + $countSelesai;

// B. Jadwal Hari Ini
$hariIniIndo = ['Sun'=>'Minggu','Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu'];
$hariInggris = date('D');
$hariIni = $hariIniIndo[$hariInggris];
$resJadwalHariIni = mysqli_query($conn, "SELECT COUNT(*) as total FROM jadwalkuliah WHERE NIM = '$nim' AND Hari = '$hariIni'");
$countJadwal = mysqli_fetch_assoc($resJadwalHariIni)['total'];

// C. Marker Kalender
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

// Enhanced deadline map with task details
$deadlinesMap = [];
$queryKalender = mysqli_query($conn, "SELECT DAY(Deadline) as hari, JudulTugas, StatusTugas, Deadline 
                                      FROM tugas 
                                      WHERE NIM = '$nim' 
                                      AND MONTH(Deadline) = '$bulanIni' 
                                      AND YEAR(Deadline) = '$tahunIni'
                                      ORDER BY Deadline ASC");
while($row = mysqli_fetch_assoc($queryKalender)) {
    $day = $row['hari'];
    if (!isset($deadlinesMap[$day])) {
        $deadlinesMap[$day] = [];
    }
    
    // Determine urgency
    $deadlineDate = new DateTime($row['Deadline']);
    $now = new DateTime();
    $diff = $now->diff($deadlineDate);
    
    if ($now > $deadlineDate && $row['StatusTugas'] != 'Selesai') {
        $urgency = 'overdue'; // Red
    } elseif ($diff->days <= 3 && $row['StatusTugas'] != 'Selesai') {
        $urgency = 'urgent'; // Orange
    } else {
        $urgency = 'normal'; // Blue
    }
    
    $deadlinesMap[$day][] = [
        'title' => $row['JudulTugas'],
        'status' => $row['StatusTugas'],
        'urgency' => $urgency,
        'deadline' => $row['Deadline']
    ];
}

// D. Query Jadwal Hari Ini (Untuk Overview)
$queryJadwalToday = "SELECT j.*, m.NamaMK, d.NamaDosen 
                     FROM jadwalkuliah j 
                     LEFT JOIN matakuliah m ON j.KodeMK = m.KodeMK 
                     LEFT JOIN dosen d ON m.KodeDosen = d.KodeDosen
                     WHERE j.NIM = '$nim' AND j.Hari = '$hariIni' 
                     ORDER BY j.JamMulai ASC";
$resultJadwalToday = mysqli_query($conn, $queryJadwalToday);

// E. Query Tugas AKTIF (Untuk Widget & Tab Filter)
$queryActive = "SELECT t.*, m.NamaMK 
                FROM tugas t 
                LEFT JOIN matakuliah m ON t.KodeMK = m.KodeMK 
                WHERE t.StatusTugas='Aktif' AND t.NIM = '$nim' 
                ORDER BY t.Deadline ASC";
$resActive = mysqli_query($conn, $queryActive);

// F. Query Tugas SELESAI (Untuk Tab Filter Selesai)
$queryDone = "SELECT t.*, m.NamaMK 
              FROM tugas t 
              LEFT JOIN matakuliah m ON t.KodeMK = m.KodeMK 
              WHERE t.StatusTugas='Selesai' AND t.NIM = '$nim' 
              ORDER BY t.Deadline DESC LIMIT 20";
$resDone = mysqli_query($conn, $queryDone);

// --- PEMISAHAN DATA KE ARRAY ---
$tugasSemua = [];
$tugasMendesak = [];
$tugasTerlewat = [];
$tugasSelesaiList = [];
$tugasUpcomingWidget = [];
$tugasOverdueWidget = [];

$now = new DateTime();

// Proses Tugas Aktif
while($t = mysqli_fetch_assoc($resActive)) {
    $deadlineDate = new DateTime($t['Deadline']);
    if(empty($t['NamaMK'])) $t['NamaMK'] = "Tugas Umum (LMS)";
    
    $tugasSemua[] = $t; // Masuk Tab Semua
    
    if ($now > $deadlineDate) {
        $tugasTerlewat[] = $t;       // Masuk Tab Terlewat
        $tugasOverdueWidget[] = $t;  // Masuk Widget Terlewat
    } else {
        $tugasUpcomingWidget[] = $t; // Masuk Widget Upcoming
        
        // Cek Mendesak (H-3)
        $diff = $now->diff($deadlineDate);
        if($diff->days <= 3) {
            $tugasMendesak[] = $t;   // Masuk Tab Mendesak
        }
    }
}

// Proses Tugas Selesai
while($t = mysqli_fetch_assoc($resDone)) {
    if(empty($t['NamaMK'])) $t['NamaMK'] = "Tugas Umum (LMS)";
    $tugasSelesaiList[] = $t;
}

// G. Query List Jadwal Lengkap (Tab Jadwal)
$queryListJadwal = "SELECT j.*, m.NamaMK, d.NamaDosen 
                    FROM jadwalkuliah j 
                    LEFT JOIN matakuliah m ON j.KodeMK = m.KodeMK 
                    LEFT JOIN dosen d ON m.KodeDosen = d.KodeDosen
                    WHERE j.NIM = '$nim' 
                    ORDER BY FIELD(j.Hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), j.JamMulai ASC";
$resultJadwal = mysqli_query($conn, $queryListJadwal);

// Nama bulan Indonesia
$namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$bulanSekarang = $namaBulan[(int)$bulanIni];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyRemind - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .modal.active {
            display: flex !important;
        }
        .modal-content {
            animation: modalSlideIn 0.3s ease;
        }
    </style>
</head>
<body class="min-h-screen font-sans" style="background: linear-gradient(to bottom, #EEF2FF, #FAF5FF, #FDF2F8);">
    <!-- Notification Toast -->
    <?php if (isset($_GET['msg'])): ?>
    <div id="notification-toast" class="fixed top-4 right-4 z-50 bg-white rounded-lg shadow-xl border-l-4 p-4 max-w-sm animate-slide-in">
        <?php
        $msg = $_GET['msg'];
        $icon = 'fa-check-circle';
        $color = 'border-green-500';
        $bg_color = 'bg-green-50';
        $text_color = 'text-green-800';
        $message = '';
        
        switch($msg) {
            case 'import_success':
                $imported = isset($_GET['imported']) ? $_GET['imported'] : 0;
                $skipped = isset($_GET['skipped']) ? $_GET['skipped'] : 0;
                $message = "Berhasil mengimpor $imported event dari LMS!";
                if ($skipped > 0) {
                    $message .= " ($skipped event dilewati karena sudah ada)";
                }
                break;
            case 'invalid_url':
                $icon = 'fa-exclamation-circle';
                $color = 'border-red-500';
                $bg_color = 'bg-red-50';
                $text_color = 'text-red-800';
                $message = 'URL tidak valid. Pastikan URL benar.';
                break;
            case 'invalid_ical_format':
                $icon = 'fa-exclamation-circle';
                $color = 'border-red-500';
                $bg_color = 'bg-red-50';
                $text_color = 'text-red-800';
                $message = 'Format URL harus berupa file .ics';
                break;
            case 'fetch_failed':
                $icon = 'fa-exclamation-circle';
                $color = 'border-red-500';
                $bg_color = 'bg-red-50';
                $text_color = 'text-red-800';
                $message = 'Gagal mengambil data dari URL. Periksa koneksi atau URL.';
                break;
            case 'no_events':
                $icon = 'fa-info-circle';
                $color = 'border-yellow-500';
                $bg_color = 'bg-yellow-50';
                $text_color = 'text-yellow-800';
                $message = 'Tidak ada event ditemukan di kalender.';
                break;
        }
        ?>
        <div class="flex items-start gap-3 <?= $bg_color ?> p-3 rounded">
            <i class="fas <?= $icon ?> <?= $text_color ?> text-xl mt-0.5"></i>
            <div class="flex-1">
                <p class="<?= $text_color ?> font-medium text-sm"><?= $message ?></p>
            </div>
            <button onclick="closeNotification()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('notification-toast');
            if (toast) {
                toast.style.animation = 'slide-out 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
        
        function closeNotification() {
            const toast = document.getElementById('notification-toast');
            if (toast) {
                toast.style.animation = 'slide-out 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }
        }
    </script>
    <style>
        @keyframes slide-in {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slide-out {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
    </style>
    <?php endif; ?>
    
    <div class="max-w-md mx-auto min-h-screen shadow-xl">
        <!-- Top Header Bar -->
        <div class="bg-white w-full shadow-md px-4 py-3 flex justify-between items-center">
            <div class="text-xl font-bold text-gray-800">MyRemind</div>
            <div class="flex gap-2">        
                <button class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center" title="Mode Gelap">
                    <i class="fas fa-moon text-sm"></i>
                </button>
                <button class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center" title="Pengaturan">
                    <i class="fas fa-cog text-sm"></i>
                </button>
                <button class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center" title="Logout" onclick="window.location.href='logout.php'">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                </button>
            </div>
        </div>
        
      
        <!-- Navigation Tabs -->
        <div class="mx-4 mt-4">
            <div class="bg-white rounded-3xl shadow-lg p-2  ">
            <div class="flex gap-3">
                <button class="nav-tab flex-1 py-3 px-5 rounded-full font-medium text-sm transition-all duration-300 flex items-center justify-center gap-2 text-white shadow-md" style="background: linear-gradient(to right, #4F39F6, #9810FA);" data-tab="kalender">
                    <i class="far fa-calendar"></i>
                    Kalender
                </button>
                <button class="nav-tab flex-1 py-3 px-5 rounded-full font-medium text-sm transition-all duration-300 flex items-center justify-center gap-2 bg-white text-gray-700 border border-gray-300 hover:bg-gray-50" data-tab="tugas">
                    <i class="fas fa-check-square"></i>
                    Tugas
                </button>
                <button class="nav-tab flex-1 py-3 px-5 rounded-full font-medium text-sm transition-all duration-300 flex items-center justify-center gap-2 bg-white text-gray-700 border border-gray-300 hover:bg-gray-50" data-tab="grup">
                    <i class="fas fa-users"></i>
                    Grup
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="p-4">
            <!-- Kalender Tab -->
            <div id="tab-kalender" class="tab-content">
                <div class="flex gap-2 mb-4">
                    <button class="flex-1 py-3 px-4 rounded-full font-semibold text-sm transition-all duration-300 flex items-center justify-center gap-2 text-white hover:shadow-lg shadow-md whitespace-nowrap" style="background: linear-gradient(to right, #4F39F6, #9810FA);" onclick="openModal('modalJadwal')">
                        <i class="fas fa-plus"></i>
                        Tambah Jadwal
                    </button>
                    <button class="flex-1 py-3 px-4 rounded-full font-semibold text-sm transition-all duration-300 flex items-center justify-center gap-2 bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white hover:shadow-lg shadow-md whitespace-nowrap" onclick="openModal('modalTugas')">
                        <i class="fas fa-plus"></i>
                        Tambah Tugas
                    </button>
                </div>
                
                <!-- LMS Integration Button -->
                <div class="mb-4">
                    <button class="w-full py-3 px-4 rounded-full font-semibold text-sm transition-all duration-300 flex items-center justify-center gap-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white hover:shadow-lg shadow-md" onclick="openModal('modalIntegrasLMS')">
                        <i class="fas fa-sync-alt"></i>
                        Integrasi LMS Telkom University
                    </button>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-lg border border-gray-200">
                    <!-- Calendar Header -->
                    <div class="flex justify-between items-center mb-5">
                        <div class="text-lg font-bold text-gray-800 bg-gradient-to-r from-purple-600 to-fuchsia-600 bg-clip-text text-transparent">
                            <?= $bulanSekarang ?> <?= $tahunIni ?>
                        </div>
                        <div class="flex gap-2">
                            <?php
                            $prevMonth = $bulanIni - 1;
                            $prevYear = $tahunIni;
                            if ($prevMonth < 1) {
                                $prevMonth = 12;
                                $prevYear--;
                            }
                            
                            $nextMonth = $bulanIni + 1;
                            $nextYear = $tahunIni;
                            if ($nextMonth > 12) {
                                $nextMonth = 1;
                                $nextYear++;
                            }
                            ?>
                            <a href="?cal_month=<?= $prevMonth ?>&cal_year=<?= $prevYear ?>" class="w-8 h-8 rounded-lg bg-gradient-to-r from-purple-500 to-fuchsia-500 text-white hover:shadow-lg transition-all duration-300 flex items-center justify-center">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                            <a href="?cal_month=<?= date('m') ?>&cal_year=<?= date('Y') ?>" class="px-3 h-8 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center text-xs font-medium">
                                Hari Ini
                            </a>
                            <a href="?cal_month=<?= $nextMonth ?>&cal_year=<?= $nextYear ?>" class="w-8 h-8 rounded-lg bg-gradient-to-r from-purple-500 to-fuchsia-500 text-white hover:shadow-lg transition-all duration-300 flex items-center justify-center">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-2">
                        <!-- Day Headers -->
                        <div class="text-center text-xs font-bold text-gray-600 py-2">Min</div>
                        <div class="text-center text-xs font-bold text-gray-600 py-2">Sen</div>
                        <div class="text-center text-xs font-bold text-gray-600 py-2">Sel</div>
                        <div class="text-center text-xs font-bold text-gray-600 py-2">Rab</div>
                        <div class="text-center text-xs font-bold text-gray-600 py-2">Kam</div>
                        <div class="text-center text-xs font-bold text-gray-600 py-2">Jum</div>
                        <div class="text-center text-xs font-bold text-gray-600 py-2">Sab</div>

                        <?php
                        // Empty cells before first day
                        for ($i = 1; $i < $hariPertama; $i++) {
                            echo '<div class="aspect-square"></div>';
                        }

                        // Days of the month
                        $hariIniAngka = (int)date('d');
                        $bulanIniCurrent = date('m');
                        $tahunIniCurrent = date('Y');
                        $isCurrentMonth = ($bulanIni == $bulanIniCurrent && $tahunIni == $tahunIniCurrent);
                        
                        for ($day = 1; $day <= $jumlahHari; $day++) {
                            $isToday = ($day == $hariIniAngka && $isCurrentMonth);
                            $dayOfWeek = date('N', strtotime("$tahunIni-$bulanIni-$day"));
                            $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                            
                            // Base classes
                            $classes = 'aspect-square flex flex-col items-center justify-center rounded-xl text-sm cursor-pointer transition-all duration-200 relative group';
                            
                            if ($isToday) {
                                $classes .= ' bg-gradient-to-br from-purple-600 to-fuchsia-600 text-white font-bold shadow-lg';
                            } elseif ($isWeekend) {
                                $classes .= ' text-gray-400 hover:bg-gray-50';
                            } else {
                                $classes .= ' text-gray-700 hover:bg-purple-50 hover:shadow-md';
                            }
                            
                            $hasDeadlines = isset($deadlinesMap[$day]);
                            $taskCount = $hasDeadlines ? count($deadlinesMap[$day]) : 0;
                            
                            echo '<div class="' . $classes . '" data-date="' . $day . '" data-month="' . $bulanIni . '" data-year="' . $tahunIni . '">';
                            echo '<span class="' . ($isToday ? 'text-white' : '') . '">' . $day . '</span>';
                            
                            // Deadline markers
                            if ($hasDeadlines) {
                                echo '<div class="flex gap-0.5 mt-1 absolute bottom-1">';
                                
                                // Show up to 3 dots
                                $maxDots = min(3, $taskCount);
                                $urgencyColors = [];
                                
                                foreach ($deadlinesMap[$day] as $task) {
                                    $urgencyColors[] = $task['urgency'];
                                }
                                
                                // Sort by urgency priority
                                usort($urgencyColors, function($a, $b) {
                                    $priority = ['overdue' => 0, 'urgent' => 1, 'normal' => 2];
                                    return $priority[$a] - $priority[$b];
                                });
                                
                                for ($i = 0; $i < $maxDots; $i++) {
                                    $urgency = $urgencyColors[$i];
                                    $color = $urgency == 'overdue' ? 'bg-red-500' : ($urgency == 'urgent' ? 'bg-orange-500' : 'bg-blue-500');
                                    echo '<span class="w-1.5 h-1.5 rounded-full ' . $color . '"></span>';
                                }
                                
                                if ($taskCount > 3) {
                                    echo '<span class="text-[8px] ml-0.5 font-bold ' . ($isToday ? 'text-white' : 'text-gray-600') . '">+</span>';
                                }
                                
                                echo '</div>';
                                
                                // Tooltip on hover
                                echo '<div class="hidden group-hover:block absolute top-full left-1/2 transform -translate-x-1/2 mt-2 bg-gray-900 text-white text-xs rounded-lg py-2 px-3 z-20 whitespace-nowrap shadow-xl">';
                                echo '<div class="font-semibold mb-1">' . $taskCount . ' Tugas</div>';
                                foreach (array_slice($deadlinesMap[$day], 0, 3) as $task) {
                                    $icon = $task['urgency'] == 'overdue' ? '🔴' : ($task['urgency'] == 'urgent' ? '🟡' : '🔵');
                                    echo '<div class="text-[10px]">' . $icon . ' ' . htmlspecialchars(substr($task['title'], 0, 20)) . '</div>';
                                }
                                if ($taskCount > 3) {
                                    echo '<div class="text-[10px] text-gray-400 mt-1">+' . ($taskCount - 3) . ' lainnya</div>';
                                }
                                echo '</div>';
                            }
                            
                            echo '</div>';
                        }
                        ?>
                    </div>
                    
                    <!-- Legend -->
                    <div class="mt-5 pt-4 border-t border-gray-200 flex flex-wrap gap-3 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            <span class="text-gray-600">Terlewat</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                            <span class="text-gray-600">Mendesak</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-gray-600">Normal</span>
                        </div>
                    </div>
                </div>
                
                <!-- Deadline Tugas Section -->
                <div class="mt-6 bg-white rounded-2xl p-5 shadow-lg border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Deadline Tugas</h3>
                    
                    <!-- Tabs -->
                    <div class="flex gap-4 mb-4 border-b border-gray-200">
                        <button class="deadline-tab pb-2 px-1 font-semibold text-sm transition-all duration-300 border-b-2 border-purple-600 text-purple-600" data-tab="akan-datang">
                            Akan Datang 
                            <?php
                            $upcomingCount = 0;
                            foreach ($tugasSemua as $t) {
                                if ($t['StatusTugas'] != 'Selesai') {
                                    $deadline = new DateTime($t['Deadline']);
                                    $now = new DateTime();
                                    if ($deadline >= $now) {
                                        $upcomingCount++;
                                    }
                                }
                            }
                            ?>
                            <span class="text-purple-600">(<?= $upcomingCount ?>)</span>
                        </button>
                        <button class="deadline-tab pb-2 px-1 font-semibold text-sm transition-all duration-300 border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="terlewat">
                            Terlewat 
                            <?php
                            $overdueCount = 0;
                            foreach ($tugasSemua as $t) {
                                if ($t['StatusTugas'] != 'Selesai') {
                                    $deadline = new DateTime($t['Deadline']);
                                    $now = new DateTime();
                                    if ($deadline < $now) {
                                        $overdueCount++;
                                    }
                                }
                            }
                            ?>
                            <span class="text-gray-500">(<?= $overdueCount ?>)</span>
                        </button>
                    </div>
                    
                    <!-- Akan Datang Content -->
                    <div id="deadline-akan-datang" class="deadline-content">
                        <?php
                        $upcomingTasks = [];
                        foreach ($tugasSemua as $t) {
                            if ($t['StatusTugas'] != 'Selesai') {
                                $deadline = new DateTime($t['Deadline']);
                                $now = new DateTime();
                                if ($deadline >= $now) {
                                    $upcomingTasks[] = $t;
                                }
                            }
                        }
                        
                        // Sort by deadline (nearest first)
                        usort($upcomingTasks, function($a, $b) {
                            return strtotime($a['Deadline']) - strtotime($b['Deadline']);
                        });
                        
                        if (count($upcomingTasks) > 0):
                            foreach (array_slice($upcomingTasks, 0, 5) as $t):
                                $deadline = new DateTime($t['Deadline']);
                                $now = new DateTime();
                                $diff = $now->diff($deadline);
                                $daysLeft = $diff->days;
                                
                                // Determine badge
                                if ($daysLeft == 0) {
                                    $badge = 'H-0';
                                    $badgeColor = 'bg-red-500';
                                } elseif ($daysLeft == 1) {
                                    $badge = 'H-1';
                                    $badgeColor = 'bg-orange-500';
                                } else {
                                    $badge = 'H-' . $daysLeft;
                                    $badgeColor = 'bg-blue-500';
                                }
                        ?>
                        <div class="mb-3 p-4 bg-gray-50 rounded-lg border border-gray-200 hover:shadow-md transition-all duration-300">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-gray-800 text-sm flex-1"><?= htmlspecialchars($t['JudulTugas']) ?></h4>
                                <span class="<?= $badgeColor ?> text-white text-xs font-bold px-2 py-1 rounded-full ml-2"><?= $badge ?></span>
                            </div>
                            <p class="text-xs text-gray-600 mb-2"><?= htmlspecialchars($t['NamaMK']) ?></p>
                            <div class="flex items-center text-xs text-gray-500">
                                <i class="far fa-clock mr-1"></i>
                                <?= date('d M Y, H:i', strtotime($t['Deadline'])) ?>
                            </div>
                        </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                        <div class="text-center py-8 text-gray-400 text-sm">
                            <i class="fas fa-check-circle text-4xl mb-2"></i>
                            <p>Tidak ada tugas yang akan datang</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Terlewat Content -->
                    <div id="deadline-terlewat" class="deadline-content hidden">
                        <?php
                        $overdueTasks = [];
                        foreach ($tugasSemua as $t) {
                            if ($t['StatusTugas'] != 'Selesai') {
                                $deadline = new DateTime($t['Deadline']);
                                $now = new DateTime();
                                if ($deadline < $now) {
                                    $overdueTasks[] = $t;
                                }
                            }
                        }
                        
                        // Sort by deadline (most recent first)
                        usort($overdueTasks, function($a, $b) {
                            return strtotime($b['Deadline']) - strtotime($a['Deadline']);
                        });
                        
                        if (count($overdueTasks) > 0):
                            foreach (array_slice($overdueTasks, 0, 5) as $t):
                        ?>
                        <div class="mb-3 p-4 bg-red-50 rounded-lg border border-red-200 hover:shadow-md transition-all duration-300">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-gray-800 text-sm flex-1"><?= htmlspecialchars($t['JudulTugas']) ?></h4>
                                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full ml-2">
                                    <i class="fas fa-exclamation-circle"></i>
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mb-2"><?= htmlspecialchars($t['NamaMK']) ?></p>
                            <div class="flex items-center text-xs text-red-600">
                                <i class="far fa-clock mr-1"></i>
                                <?= date('d M Y, H:i', strtotime($t['Deadline'])) ?>
                            </div>
                        </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                        <div class="text-center py-8 text-gray-400 text-sm">
                            <i class="fas fa-check-circle text-4xl mb-2"></i>
                            <p>Tidak ada tugas yang terlewat</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tugas Tab -->
            <div id="tab-tugas" class="tab-content hidden">
                <div class="mb-4">
                    <button class="w-full py-3 px-5 rounded-full font-medium text-sm transition-all duration-300 flex items-center justify-center gap-2 bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white hover:shadow-lg shadow-md" onclick="openModal('modalTugas')">
                        <i class="fas fa-plus"></i>
                        Tambah Tugas Baru
                    </button>
                </div>

                <!-- To Do Section -->
                <?php 
                $todoTasks = array_filter($tugasSemua, function($t) {
                    return $t['StatusTugas'] == 'Aktif';
                });
                $todoCount = count($todoTasks);
                ?>
                <?php if ($todoCount > 0): ?>
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-lg font-semibold text-gray-800">To Do</h3>
                            <span class="bg-gray-200 text-gray-700 text-sm font-medium px-3 py-1 rounded-full"><?= $todoCount ?></span>
                        </div>
                        <div class="flex flex-col gap-3">
                            <?php foreach ($todoTasks as $t): ?>
                                <?php include 'item_tugas.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- In Progress Section -->
                <?php 
                // Query tasks with "In Progress" status
                $queryInProgress = "SELECT t.*, m.NamaMK 
                                   FROM tugas t 
                                   LEFT JOIN matakuliah m ON t.KodeMK = m.KodeMK 
                                   WHERE t.StatusTugas='In Progress' AND t.NIM = '$nim' 
                                   ORDER BY t.Deadline ASC";
                $resInProgress = mysqli_query($conn, $queryInProgress);
                $inProgressTasks = [];
                while($t = mysqli_fetch_assoc($resInProgress)) {
                    if(empty($t['NamaMK'])) $t['NamaMK'] = "Tugas Umum (LMS)";
                    $inProgressTasks[] = $t;
                }
                $inProgressCount = count($inProgressTasks);
                ?>
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">In Progress</h3>
                        <span class="bg-blue-100 text-blue-700 text-sm font-medium px-3 py-1 rounded-full"><?= $inProgressCount ?></span>
                    </div>
                    <?php if ($inProgressCount > 0): ?>
                        <div class="flex flex-col gap-3">
                            <?php foreach ($inProgressTasks as $t): ?>
                                <?php include 'item_tugas.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-400 text-sm">Tidak ada tugas dalam progress</div>
                    <?php endif; ?>
                </div>

                <!-- Done Section -->
                <?php 
                $doneCount = count($tugasSelesaiList);
                ?>
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">Done</h3>
                        <span class="bg-green-100 text-green-700 text-sm font-medium px-3 py-1 rounded-full"><?= $doneCount ?></span>
                    </div>
                    <?php if ($doneCount > 0): ?>
                        <div class="flex flex-col gap-3">
                            <?php foreach ($tugasSelesaiList as $t): ?>
                                <?php include 'item_tugas.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-400 text-sm">Belum ada tugas selesai</div>
                    <?php endif; ?>
                </div>

                <?php if (count($tugasSemua) == 0 && $doneCount == 0): ?>
                    <div class="text-center py-16">
                        <div class="text-6xl text-gray-300 mb-4">
                            <i class="far fa-check-circle"></i>
                        </div>
                        <div class="text-sm text-gray-400 font-medium">Belum ada tugas</div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Grup Tab -->
            <div id="tab-grup" class="tab-content hidden">
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-800">Grup</div>
                            <div class="text-xs text-gray-400 mt-0.5">Kolaborasi dengan teman satu kelas</div>
                        </div>
                        <button class="py-2 px-4 rounded-lg font-medium text-sm transition-all duration-300 bg-purple-600 text-white hover:bg-purple-700" onclick="openModal('modalGrup')">
                            Buat Grup
                        </button>
                    </div>
                </div>

                <!-- Example Group -->
                <div class="flex flex-col gap-3">
                    <div class="bg-white rounded-lg p-3 flex items-center gap-3 border border-gray-200 transition-all duration-300 hover:shadow-md">
                        <div class="w-12 h-12 rounded-lg bg-purple-600 flex items-center justify-center text-white text-lg">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800 text-sm mb-0.5">Testing</div>
                            <div class="text-xs text-gray-400">
                                <i class="fas fa-user"></i> 2 anggota
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div>
        <!-- Navigation Tabs -->
        
    </div>

    <!-- Modal Tambah Jadwal -->
    <div id="modalJadwal" class="modal hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-5">
        <div class="modal-content bg-white rounded-2xl p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5">
                <div class="text-lg font-bold text-gray-800">Tambah Jadwal</div>
                <button class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center" onclick="closeModal('modalJadwal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="proses_tambah.php" method="POST">
                <input type="hidden" name="type" value="jadwal">
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">
                        Mata Kuliah <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="matakuliah" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" placeholder="Contoh: Pemrograman Web" required>
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">
                        Hari <span class="text-red-500">*</span>
                    </label>
                    <select name="hari" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" required>
                        <option value="">Pilih Hari</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">
                        Jam Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="jam_mulai" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" required>
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">
                        Jam Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="jam_selesai" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" required>
                </div>
                <div class="mb-5">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">Ruangan</label>
                    <input type="text" name="ruangan" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" placeholder="Contoh: Lab 301">
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" class="flex-1 py-2.5 px-4 border border-gray-300 bg-white text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-all duration-300" onclick="closeModal('modalJadwal')">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-purple-600 text-white rounded-lg font-medium text-sm hover:bg-purple-700 transition-all duration-300">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Tugas -->
    <div id="modalTugas" class="modal hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-5">
        <div class="modal-content bg-white rounded-2xl p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5">
                <div class="text-lg font-bold text-gray-800">Tambah Tugas</div>
                <button class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center" onclick="closeModal('modalTugas')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="proses_tambah.php" method="POST">
                <input type="hidden" name="type" value="tugas">
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">
                        Judul Tugas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="judul" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" placeholder="Contoh: Tugas UTS Basis Data" required>
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">Mata Kuliah</label>
                    <input type="text" name="matakuliah" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" placeholder="Contoh: Basis Data">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">
                        Deadline <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="deadline" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" required>
                </div>
                <div class="mb-5">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">Deskripsi</label>
                    <textarea name="deskripsi" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300 min-h-[80px] resize-y" placeholder="Deskripsi tugas..."></textarea>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" class="flex-1 py-2.5 px-4 border border-gray-300 bg-white text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-all duration-300" onclick="closeModal('modalTugas')">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-purple-600 text-white rounded-lg font-medium text-sm hover:bg-purple-700 transition-all duration-300">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Buat Grup -->
    <div id="modalGrup" class="modal hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-5">
        <div class="modal-content bg-white rounded-2xl p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5">
                <div class="text-lg font-bold text-gray-800">Buat Grup Baru</div>
                <button class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center" onclick="closeModal('modalGrup')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="proses_tambah.php" method="POST">
                <input type="hidden" name="type" value="grup">
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">
                        Nama Grup <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_grup" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" placeholder="Contoh: Kelas 3IF-01" required>
                </div>
                <div class="mb-5">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">Deskripsi</label>
                    <textarea name="deskripsi" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300 min-h-[80px] resize-y" placeholder="Deskripsi grup..."></textarea>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" class="flex-1 py-2.5 px-4 border border-gray-300 bg-white text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-all duration-300" onclick="closeModal('modalGrup')">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-purple-600 text-white rounded-lg font-medium text-sm hover:bg-purple-700 transition-all duration-300">Buat Grup</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Integrasi LMS -->
    <div id="modalIntegrasLMS" class="modal hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-5">
        <div class="modal-content bg-white rounded-2xl p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5">
                <div class="text-lg font-bold text-gray-800">Integrasi LMS Telkom University</div>
                <button class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center" onclick="closeModal('modalIntegrasLMS')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-5">
                <div class="flex items-start gap-2 mb-3">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                    <div class="text-sm text-blue-900 font-semibold">Cara mendapatkan URL Kalender LMS:</div>
                </div>
                <ol class="text-xs text-blue-800 space-y-2 ml-6 list-decimal">
                    <li>Login ke LMS Telkom University (iGracias)</li>
                    <li>Buka halaman "Kain Kalender" atau "Calendar"</li>
                    <li>Cari opsi "Export Calendar" atau "Ekspor Kalender"</li>
                    <li>Pilih format iCal atau .ics</li>
                    <li>Copy URL yang diberikan (biasanya berformat .ics)</li>
                    <li>Paste URL tersebut di form di bawah</li>
                </ol>
            </div>
            
            <form action="proses_import_ical.php" method="POST" id="formIntegrasLMS">
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">
                        URL Kalender iCal <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-link text-gray-400 text-sm"></i>
                        </div>
                        <input type="url" name="ical_url" id="ical_url" class="w-full py-2.5 pl-10 pr-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300" placeholder="https://lms.telkomuniversity.ac.id/calendar/export_execute.php?..." required>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">URL harus berformat .ics dan iCal dari LMS Telkom University</p>
                </div>
                
                <!-- Example URL -->
                <div class="mb-5">
                    <label class="block font-medium text-gray-700 mb-2 text-xs">Contoh URL:</label>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <code class="text-xs text-gray-600 break-all">
                            https://lms.telkomuniversity.ac.id/calendar/export_execute.php?userid=XXXXX&authtoken=XXXXXX&preset_what=all&preset_time=weeknow
                        </code>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" class="flex-1 py-2.5 px-4 border border-gray-300 bg-white text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-all duration-300" onclick="closeModal('modalIntegrasLMS')">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg font-medium text-sm hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-sync-alt mr-2"></i>Simpan URL
                    </button>
                </div>
            </form>
            
            <!-- Loading indicator -->
            <div id="loadingIntegrasi" class="hidden mt-4 text-center">
                <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
                <p class="text-sm text-gray-600 mt-2">Mengimpor kalender...</p>
            </div>
        </div>
    </div>

    <script>
        // Tab Switching
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active styles from all tabs
                document.querySelectorAll('.nav-tab').forEach(t => {
                    t.style.background = '';
                    t.classList.remove('text-white', 'shadow-md');
                    t.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-300', 'hover:bg-gray-50');
                });
                
                // Add active styles to clicked tab
                this.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-300', 'hover:bg-gray-50');
                this.classList.add('text-white', 'shadow-md');
                this.style.background = 'linear-gradient(to right, #4F39F6, #9810FA)';
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Show selected tab content
                const tabName = this.getAttribute('data-tab');
                document.getElementById('tab-' + tabName).classList.remove('hidden');
            });
        });

        // Modal Functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Dark mode toggle (placeholder)
        document.querySelector('[title="Mode Gelap"]').addEventListener('click', function() {
            alert('Fitur mode gelap akan segera hadir!');
        });

        // Settings button (placeholder)
        document.querySelector('[title="Pengaturan"]').addEventListener('click', function() {
            alert('Halaman pengaturan akan segera hadir!');
        });

        // Task menu toggle
        document.addEventListener('click', function(e) {
            // Toggle menu when clicking three-dot button
            if (e.target.closest('.task-menu-btn')) {
                e.stopPropagation();
                const btn = e.target.closest('.task-menu-btn');
                const taskId = btn.getAttribute('data-task-id');
                const menu = document.getElementById('menu-' + taskId);
                
                // Close all other menus
                document.querySelectorAll('.task-menu').forEach(m => {
                    if (m !== menu) {
                        m.classList.add('hidden');
                    }
                });
                
                // Toggle current menu
                menu.classList.toggle('hidden');
            } else {
                // Close all menus when clicking outside
                document.querySelectorAll('.task-menu').forEach(m => {
                    m.classList.add('hidden');
                });
            }
        });
        
        // Deadline Tab Switching
        document.querySelectorAll('.deadline-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');
                
                // Update tab styles
                document.querySelectorAll('.deadline-tab').forEach(t => {
                    t.classList.remove('border-purple-600', 'text-purple-600');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                this.classList.remove('border-transparent', 'text-gray-500');
                this.classList.add('border-purple-600', 'text-purple-600');
                
                // Show/hide content
                document.querySelectorAll('.deadline-content').forEach(content => {
                    content.classList.add('hidden');
                });
                document.getElementById('deadline-' + targetTab).classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
