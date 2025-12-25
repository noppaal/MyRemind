<?php
/**
 * Dashboard - Full HTML View
 * File ini adalah view lengkap untuk dashboard
 * Location: view/dashboard/index.php
 */

require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['nim'])) {
    header("Location: ../../public/login.php");
    exit;
}

// Load models
require_once __DIR__ . '/../../model/Database.php';
require_once __DIR__ . '/../../model/TaskModel.php';
require_once __DIR__ . '/../../model/ScheduleModel.php';
require_once __DIR__ . '/../../model/GroupModel.php';

$nim = $_SESSION['nim'];
$conn = getConnection();

// ================= 1. LOGIKA STATISTIK & DATA =================

// A. Hitung Statistik
$resAktif = mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE StatusTugas = 'Aktif' AND NIM = '$nim'");
$countAktif = mysqli_fetch_assoc($resAktif)['total'];

$resSelesai = mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE StatusTugas = 'Selesai' AND NIM = '$nim'");
$countSelesai = mysqli_fetch_assoc($resSelesai)['total'];
$countTotal = $countAktif + $countSelesai;

// B. Nama Hari dan Tanggal
$hariInggris = date('l'); // e.g., Monday, Tuesday
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
$hariIni = $namaHari; // For jadwal query

// Count jadwal for today using the new $hariIni
$resJadwalHariIni = mysqli_query($conn, "SELECT COUNT(*) as total FROM jadwalkuliah WHERE NIM = '$nim' AND Hari = '$hariIni'");
$countJadwal = mysqli_fetch_assoc($resJadwalHariIni)['total'];

// C. Nama Bulan untuk Kalender
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

// H. Load Groups using GroupModel
require_once __DIR__ . '/../../model/GroupModel.php';
$myGroups = getAllGroups($nim);

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
            darkMode: 'class',
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
<body class="min-h-screen font-sans transition-colors duration-300 dark:bg-gray-900" style="background: linear-gradient(to bottom, #EEF2FF, #FAF5FF, #FDF2F8);">
    <!-- Notification Toast -->
    <?php if (isset($_GET['msg'])): ?>
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
        case 'migration_required':
            $message = 'Migration database belum dijalankan! Silakan jalankan query ALTER TABLE di phpMyAdmin.';
            $icon = 'fa-exclamation-triangle';
            $color = 'border-red-500';
            $bg_color = 'bg-red-50';
            $text_color = 'text-red-700';
            break;
        case 'progress_sukses':
            $message = 'Status tugas berhasil diubah ke "In Progress"!';
            $icon = 'fa-check-circle';
            $color = 'border-green-500';
            $bg_color = 'bg-green-50';
            $text_color = 'text-green-700';
            break;
        case 'password_changed':
            $message = 'Password berhasil diubah!';
            $bg_color = 'bg-green-100';
            $text_color = 'text-green-700';
            break;
        case 'wrong_old_password':
            $message = 'Password lama tidak sesuai!';
            $bg_color = 'bg-red-100';
            $text_color = 'text-red-700';
            break;
        case 'password_mismatch':
            $message = 'Password baru dan konfirmasi tidak cocok!';
            $bg_color = 'bg-red-100';
            $text_color = 'text-red-700';
            break;
        case 'password_too_short':
            $message = 'Password minimal 6 karakter!';
            $bg_color = 'bg-red-100';
            $text_color = 'text-red-700';
            break;
        case 'change_failed':
            $message = 'Gagal mengubah password!';
            $bg_color = 'bg-red-100';
            $text_color = 'text-red-700';
            break;
        default:
            $message = ' berhasil reload halaman.';
            break;
    }
    ?>
    <div id="notification-toast" class="fixed top-4 right-4 z-50 bg-white rounded-lg shadow-xl border-l-4 <?= $color ?> p-4 max-w-sm animate-slide-in">
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
    
    <div class="max-w-md mx-auto min-h-screen shadow-xl bg-white dark:bg-gray-900 transition-colors duration-300">
        <!-- Top Header Bar -->
        <div class="bg-white dark:bg-gray-800 w-full shadow-md px-4 py-3 flex justify-between items-center transition-colors duration-300">
            <div class="text-xl font-bold text-gray-800 dark:text-white">MyRemind</div>
            <div class="flex gap-2">        
                <button id="darkModeToggle" class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300 flex items-center justify-center" title="Mode Gelap">
                    <i class="fas fa-moon text-sm dark:hidden"></i>
                    <i class="fas fa-sun text-sm hidden dark:inline"></i>
                </button>
                <button class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300 flex items-center justify-center" title="Pengaturan" onclick="window.location.href='setting_profile.php'">
                    <i class="fas fa-cog text-sm"></i>
                </button>
                <button class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300 flex items-center justify-center" title="Logout" onclick="window.location.href='logout.php'">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                </button>
            </div>
        </div>
        
      
        <!-- Navigation Tabs -->
        <div class="mx-4 mt-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg p-2 transition-colors duration-300">
            <div class="flex gap-3">
                <button class="nav-tab flex-1 py-3 px-5 rounded-full font-medium text-sm transition-all duration-300 flex items-center justify-center gap-2 text-white shadow-md" style="background: linear-gradient(to right, #4F39F6, #9810FA);" data-tab="kalender">
                    <i class="far fa-calendar"></i>
                    Kalender
                </button>
                <button class="nav-tab flex-1 py-3 px-5 rounded-full font-medium text-sm transition-all duration-300 flex items-center justify-center gap-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600" data-tab="tugas">
                    <i class="fas fa-check-square"></i>
                    Tugas
                </button>
                <button class="nav-tab flex-1 py-3 px-5 rounded-full font-medium text-sm transition-all duration-300 flex items-center justify-center gap-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600" data-tab="grup">
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
                            
                            // Make date clickable
                            $clickable = $hasDeadlines ? 'onclick="showTasksForDate(' . $day . ', \'' . $bulanIni . '\', \'' . $tahunIni . '\')"' : '';
                            
                            echo '<div class="' . $classes . '" data-date="' . $day . '" data-month="' . $bulanIni . '" data-year="' . $tahunIni . '" ' . $clickable . '>';
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
                
                <!-- Jadwal Kuliah Hari Ini Section -->
                <div class="mt-6 bg-white rounded-2xl p-5 shadow-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Jadwal Kuliah</h3>
                            <p class="text-xs text-gray-500 mt-1" id="jadwal-subtitle"><?= $namaHari ?>, <?= date('d F Y') ?></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <select id="filter-hari" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-300 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                <option value="<?= $namaHari ?>"><?= $namaHari ?> (Hari Ini)</option>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                            <span class="bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full" id="jadwal-count">
                                <?= $countJadwal ?> Kelas
                            </span>
                        </div>
                    </div>
                    
                    <div id="jadwal-container">
                    <?php
                    $jadwalHariIni = [];
                    mysqli_data_seek($resultJadwalToday, 0); // Reset pointer
                    while($j = mysqli_fetch_assoc($resultJadwalToday)) {
                        $jadwalHariIni[] = $j;
                    }
                    $countJadwal = count($jadwalHariIni);
                    ?>
                    <?php if ($countJadwal > 0): ?>
                        <div class="space-y-3">
                            <?php foreach ($jadwalHariIni as $j): 
                                // Calculate time until class starts
                                $now = new DateTime();
                                $jamMulai = new DateTime(date('Y-m-d') . ' ' . $j['JamMulai']);
                                $diff = $now->diff($jamMulai);
                                $minutesUntil = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                                
                                // Check if class is starting in 30 minutes or less (and hasn't started yet)
                                $isStartingSoon = ($minutesUntil <= 30 && $minutesUntil > 0 && $diff->invert == 0);
                                
                                // Check if class is currently ongoing
                                $jamSelesai = new DateTime(date('Y-m-d') . ' ' . $j['JamSelesai']);
                                $isOngoing = ($now >= $jamMulai && $now <= $jamSelesai);
                            ?>
                            <div class="p-4 bg-gradient-to-r <?= $isStartingSoon ? 'from-orange-50 to-red-50 border-orange-300 shadow-orange-100 shadow-lg' : ($isOngoing ? 'from-green-50 to-emerald-50 border-green-300' : 'from-blue-50 to-purple-50 border-blue-200') ?> rounded-lg border hover:shadow-md transition-all duration-300 relative">
                                <?php if ($isStartingSoon): ?>
                                <!-- H-30 Warning Badge -->
                                <div class="absolute -top-2 -right-2 bg-gradient-to-r from-orange-500 to-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg animate-pulse z-10">
                                    <i class="fas fa-bell mr-1"></i><?= $minutesUntil ?> menit lagi
                                </div>
                                <?php elseif ($isOngoing): ?>
                                <!-- Ongoing Badge -->
                                <div class="absolute -top-2 -right-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                    <i class="fas fa-circle mr-1 animate-pulse"></i>Sedang Berlangsung
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 text-center">
                                        <div class="<?= $isStartingSoon ? 'bg-orange-500' : ($isOngoing ? 'bg-green-500' : 'bg-blue-500') ?> text-white rounded-lg px-3 py-2">
                                            <div class="text-xs font-semibold"><?= date('H:i', strtotime($j['JamMulai'])) ?></div>
                                            <div class="text-[10px]">-</div>
                                            <div class="text-xs font-semibold"><?= date('H:i', strtotime($j['JamSelesai'])) ?></div>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800 text-sm mb-1"><?= htmlspecialchars($j['NamaMK']) ?></h4>
                                        <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-door-open"></i>
                                                <span><?= htmlspecialchars($j['Ruangan']) ?></span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-users"></i>
                                                <span><?= htmlspecialchars($j['Kelas']) ?></span>
                                            </div>
                                            <?php if (!empty($j['NamaDosen'])): ?>
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                                <span><?= htmlspecialchars($j['NamaDosen']) ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 text-gray-400">
                            <i class="fas fa-calendar-times text-5xl mb-3"></i>
                            <p class="text-sm font-medium">Tidak ada jadwal kuliah hari ini.</p>
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
                                <?php include __DIR__ . '/../../item_tugas.php'; ?>
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
                                <?php include __DIR__ . '/../../item_tugas.php'; ?>
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
                                <?php include __DIR__ . '/../../item_tugas.php'; ?>
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
                <!-- Action Buttons -->
                <div class="flex gap-2 mb-4">
                    <button class="flex-1 py-3 px-4 rounded-full font-semibold text-sm transition-all duration-300 flex items-center justify-center gap-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white hover:shadow-lg shadow-md" onclick="openModal('modalCreateGrup')">
                        <i class="fas fa-plus"></i>
                        Buat Grup
                    </button>
                    <button class="flex-1 py-3 px-4 rounded-full font-semibold text-sm transition-all duration-300 flex items-center justify-center gap-2 bg-gradient-to-r from-blue-500 to-cyan-600 text-white hover:shadow-lg shadow-md" onclick="openModal('modalJoinGrup')">
                        <i class="fas fa-sign-in-alt"></i>
                        Gabung Grup
                    </button>
                </div>

                <!-- Groups List -->
                <div id="groups-container" class="flex flex-col gap-3">
                    <?php if (count($myGroups) > 0): ?>
                        <?php foreach ($myGroups as $group): ?>
                            <div class="bg-white rounded-2xl p-4 border border-gray-200 hover:shadow-md transition-all duration-300">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-800 text-base flex-1"><?= htmlspecialchars($group['NamaGrup']) ?></h4>
                                    <span class="bg-purple-100 text-purple-700 text-xs font-medium px-2 py-1 rounded-full ml-2">
                                        <i class="fas fa-users mr-1"></i><?= $group['jumlah_anggota'] ?> Anggota
                                    </span>
                                </div>
                                
                                <?php if (!empty($group['Deskripsi'])): ?>
                                    <p class="text-gray-600 text-sm mb-3"><?= htmlspecialchars($group['Deskripsi']) ?></p>
                                <?php endif; ?>
                                
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center text-gray-500">
                                        <i class="far fa-calendar mr-1"></i>
                                        <span>Dibuat <?= date('d M Y', strtotime($group['CreatedAt'])) ?></span>
                                    </div>
                                    <a href="<?= BASE_URL ?>/view/group/detail_group.php?kode=<?= $group['KodeGrup'] ?>" class="text-purple-600 hover:text-purple-700 font-medium">
                                        Lihat Detail <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-12 text-gray-400">
                            <i class="fas fa-users text-5xl mb-3"></i>
                            <p class="text-sm font-medium">Belum ada grup</p>
                            <p class="text-xs mt-1">Buat grup baru atau gabung dengan grup yang sudah ada</p>
                        </div>
                    <?php endif; ?>
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
            <form action="<?= BASE_URL ?>/public/proses_tambah.php" method="POST">
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
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">Ruangan</label>
                    <input type="text" name="ruangan" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" placeholder="Contoh: Lab 301">
                </div>
                <div class="mb-5">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">Kelas</label>
                    <input type="text" name="kelas" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" placeholder="Contoh: 3IF-01">
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
            <form action="<?= BASE_URL ?>/public/proses_tambah.php" method="POST">
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
            <form action="../../public/proses_tambah.php" method="POST">
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
            
            <form action="<?= BASE_URL ?>/public/proses_import_ical.php" method="POST" id="formIntegrasLMS">
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

    <!-- Modal Tugas Tanggal Tertentu -->
    <div id="modalTasksDate" class="modal hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-5">
        <div class="modal-content bg-white rounded-2xl p-6 max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <div class="text-lg font-bold text-gray-800" id="modal-date-title">Tugas pada Tanggal</div>
                    <p class="text-xs text-gray-500 mt-1" id="modal-date-subtitle"></p>
                </div>
                <button class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center" onclick="closeModal('modalTasksDate')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Tasks Container -->
            <div id="modal-tasks-container" class="space-y-3">
                <!-- Tasks will be loaded here via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Modal Create Grup -->
    <div id="modalCreateGrup" class="modal hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-5">
        <div class="modal-content bg-white rounded-2xl p-6 max-w-md w-full">
            <div class="flex justify-between items-center mb-5">
                <div class="text-lg font-bold text-gray-800">Buat Grup Baru</div>
                <button class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center" onclick="closeModal('modalCreateGrup')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="<?= BASE_URL ?>/public/proses_tambah.php" method="POST">
                <input type="hidden" name="type" value="grup">
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">
                        Nama Grup <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_grup" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" placeholder="Contoh: Kelompok Tugas IF-47-01" required maxlength="100">
                </div>
                
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">
                        Deskripsi (Opsional)
                    </label>
                    <textarea name="deskripsi" rows="3" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300" placeholder="Deskripsi singkat tentang grup ini..."></textarea>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" class="flex-1 py-2.5 px-4 border border-gray-300 bg-white text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-all duration-300" onclick="closeModal('modalCreateGrup')">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-medium text-sm hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-plus mr-2"></i>Buat Grup
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Join Grup -->
    <div id="modalJoinGrup" class="modal hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-5">
        <div class="modal-content bg-white rounded-2xl p-6 max-w-md w-full">
            <div class="flex justify-between items-center mb-5">
                <div class="text-lg font-bold text-gray-800">Gabung Grup</div>
                <button class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center" onclick="closeModal('modalJoinGrup')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-5">
                <div class="flex items-start gap-2">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                    <div class="text-sm text-blue-900">
                        Masukkan kode undangan yang diberikan oleh admin grup untuk bergabung.
                    </div>
                </div>
            </div>
            
            <form action="<?= BASE_URL ?>/view/group/grup_join.php" method="POST">
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">
                        Kode Undangan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="invite_code" class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-300 uppercase" placeholder="Contoh: ABC12345" required maxlength="20" style="text-transform: uppercase;">
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" class="flex-1 py-2.5 px-4 border border-gray-300 bg-white text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-all duration-300" onclick="closeModal('modalJoinGrup')">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg font-medium text-sm hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i>Gabung
                    </button>
                </div>
            </form>
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
        
        // Auto-switch to tab from URL parameter on page load
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            
            if (tabParam) {
                // Find and click the tab button
                const tabButton = document.querySelector(`.nav-tab[data-tab="${tabParam}"]`);
                if (tabButton) {
                    tabButton.click();
                }
            }
        });

        // Modal Functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.getElementById(modalId).classList.add('flex');
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('flex');
                    this.classList.add('hidden');
                }
            });
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
        
        // Jadwal Filter Handler
        document.getElementById('filter-hari').addEventListener('change', function() {
            const selectedDay = this.value;
            const container = document.getElementById('jadwal-container');
            const countBadge = document.getElementById('jadwal-count');
            const subtitle = document.getElementById('jadwal-subtitle');
            
            // Show loading
            container.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
            
            // Fetch jadwal for selected day
            fetch('<?= BASE_URL ?>/public/get_jadwal.php?hari=' + encodeURIComponent(selectedDay))
                .then(response => response.json())
                .then(data => {
                    // Update count
                    countBadge.textContent = data.count + ' Kelas';
                    
                    // Update subtitle
                    const isToday = selectedDay === '<?= $namaHari ?>';
                    if (isToday) {
                        subtitle.textContent = selectedDay + ', <?= date('d F Y') ?>';
                    } else {
                        subtitle.textContent = selectedDay;
                    }
                    
                    // Update content
                    if (data.count > 0) {
                        let html = '<div class="space-y-3">';
                        data.jadwal.forEach(j => {
                            html += `
                            <div class="p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-200 hover:shadow-md transition-all duration-300">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 text-center">
                                        <div class="bg-blue-500 text-white rounded-lg px-3 py-2">
                                            <div class="text-xs font-semibold">${j.JamMulai}</div>
                                            <div class="text-[10px]">-</div>
                                            <div class="text-xs font-semibold">${j.JamSelesai}</div>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800 text-sm mb-1">${j.NamaMK}</h4>
                                        <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-door-open"></i>
                                                <span>${j.Ruangan}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-users"></i>
                                                <span>${j.Kelas}</span>
                                            </div>
                                            ${j.NamaDosen ? `
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                                <span>${j.NamaDosen}</span>
                                            </div>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        });
                        html += '</div>';
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = `
                        <div class="text-center py-12 text-gray-400">
                            <i class="fas fa-calendar-times text-5xl mb-3"></i>
                            <p class="text-sm font-medium">Tidak ada jadwal kuliah untuk ${selectedDay}.</p>
                        </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<div class="text-center py-8 text-red-500">Error loading jadwal</div>';
                });
        });
        
        // Show Tasks for Selected Date
        function showTasksForDate(day, month, year) {
            const modal = document.getElementById('modalTasksDate');
            const container = document.getElementById('modal-tasks-container');
            const titleEl = document.getElementById('modal-date-title');
            const subtitleEl = document.getElementById('modal-date-subtitle');
            
            // Format date
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const dateStr = day + ' ' + monthNames[parseInt(month) - 1] + ' ' + year;
            
            titleEl.textContent = 'Tugas pada ' + dateStr;
            subtitleEl.textContent = 'Menampilkan semua tugas dengan deadline pada tanggal ini';
            
            // Show loading
            container.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
            
            // Open modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Fetch tasks for this date
            fetch(`<?= BASE_URL ?>/public/get_tasks_by_date.php?day=${day}&month=${month}&year=${year}`)
                .then(response => response.json())
                .then(data => {
                    if (data.count > 0) {
                        let html = '';
                        data.tasks.forEach(task => {
                            // Determine status badge
                            let statusBadge = '';
                            let statusColor = '';
                            if (task.StatusTugas === 'Selesai') {
                                statusBadge = 'Selesai';
                                statusColor = 'bg-green-100 text-green-700';
                            } else if (task.urgency === 'overdue') {
                                statusBadge = 'Terlewat';
                                statusColor = 'bg-red-100 text-red-700';
                            } else if (task.urgency === 'urgent') {
                                statusBadge = 'Mendesak';
                                statusColor = 'bg-orange-100 text-orange-700';
                            } else {
                                statusBadge = 'Aktif';
                                statusColor = 'bg-blue-100 text-blue-700';
                            }
                            
                            html += `
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 hover:shadow-md transition-all duration-300">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-800 text-sm flex-1">${task.JudulTugas}</h4>
                                    <span class="${statusColor} text-xs font-medium px-2 py-1 rounded-full ml-2">${statusBadge}</span>
                                </div>
                                <p class="text-xs text-gray-600 mb-2">${task.NamaMK}</p>
                                ${task.Deskripsi ? `<p class="text-xs text-gray-500 mb-2">${task.Deskripsi}</p>` : ''}
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center text-gray-500">
                                        <i class="far fa-clock mr-1"></i>
                                        <span>${task.DeadlineFormatted}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-400">${task.JenisTugas}</span>
                                    </div>
                                </div>
                            </div>`;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = `
                        <div class="text-center py-12 text-gray-400">
                            <i class="fas fa-calendar-check text-5xl mb-3"></i>
                            <p class="text-sm font-medium">Tidak ada tugas pada tanggal ini</p>
                        </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<div class="text-center py-8 text-red-500">Error loading tasks</div>';
                });
        }
        
        
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const htmlElement = document.documentElement;
        
        // Check for saved dark mode preference or default to light mode
        const currentMode = localStorage.getItem('darkMode') || 'light';
        
        // Apply saved preference on page load
        if (currentMode === 'dark') {
            htmlElement.classList.add('dark');
        }
        
        // Toggle dark mode
        darkModeToggle.addEventListener('click', function() {
            htmlElement.classList.toggle('dark');
            
            // Save preference
            if (htmlElement.classList.contains('dark')) {
                localStorage.setItem('darkMode', 'dark');
            } else {
                localStorage.setItem('darkMode', 'light');
            }
        });
        
        // Load Groups Function
        function loadGroups() {
            const container = document.getElementById('groups-container');
            const loading = document.getElementById('groups-loading');
            const empty = document.getElementById('groups-empty');
            
            // Show loading
            loading.classList.remove('hidden');
            empty.classList.add('hidden');
            container.innerHTML = '';
            container.appendChild(loading);
            
            // Fetch groups
            fetch('grup_list.php')
                .then(response => response.json())
                .then(data => {
                    loading.classList.add('hidden');
                    
                    if (data.error) {
                        // Show specific error message
                        container.innerHTML = `
                            <div class="text-center py-8">
                                <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-3"></i>
                                <p class="text-sm font-medium text-red-600 mb-2">${data.message || 'Error loading groups'}</p>
                                ${data.error === 'Tables not found' ? `
                                    <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-left max-w-md mx-auto">
                                        <p class="text-xs text-yellow-800 font-semibold mb-2">Langkah-langkah:</p>
                                        <ol class="text-xs text-yellow-700 space-y-1 ml-4 list-decimal">
                                            <li>Buka phpMyAdmin</li>
                                            <li>Pilih database db_myremind</li>
                                            <li>Klik tab SQL</li>
                                            <li>Jalankan file migration_grup_tables.sql</li>
                                        </ol>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                        return;
                    }
                    
                    if (data.success && data.count > 0) {
                        let html = '';
                        data.groups.forEach(group => {
                            const roleColors = {
                                'owner': 'bg-yellow-100 text-yellow-700',
                                'admin': 'bg-blue-100 text-blue-700',
                                'member': 'bg-gray-100 text-gray-700'
                            };
                            const roleColor = roleColors[group.Role] || 'bg-gray-100 text-gray-700';
                            
                            html += `
                            <div class="bg-white rounded-lg p-3 flex items-center gap-3 border border-gray-200 transition-all duration-300 hover:shadow-md cursor-pointer" onclick="window.location.href='detail_group.php?kode=${group.KodeGrup}'">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-lg">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-800 text-sm mb-0.5">${group.NamaGrup}</div>
                                    <div class="flex items-center gap-2 text-xs text-gray-400">
                                        <span><i class="fas fa-user"></i> ${group.member_count} anggota</span>
                                        <span>•</span>
                                        <span><i class="fas fa-calendar"></i> ${group.event_count} event</span>
                                    </div>
                                </div>
                                <div class="${roleColor} text-xs font-medium px-2 py-1 rounded-full">
                                    ${group.Role.charAt(0).toUpperCase() + group.Role.slice(1)}
                                </div>
                            </div>`;
                        });
                        container.innerHTML = html;
                    } else {
                        empty.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    loading.classList.add('hidden');
                    container.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-times-circle text-4xl text-red-400 mb-3"></i>
                            <p class="text-sm font-medium text-red-600">Error loading groups</p>
                            <p class="text-xs text-gray-500 mt-1">Periksa console untuk detail error</p>
                        </div>
                    `;
                });
        }
        
        // Load groups when Grup tab is opened
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                if (tabName === 'grup') {
                    loadGroups();
                }
            });
        });
    </script>
</body>
</html>