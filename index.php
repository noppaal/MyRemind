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
$bulanIni = date('m');
$tahunIni = date('Y');
$jumlahHari = date('t');
$hariPertama = date('N', strtotime("$tahunIni-$bulanIni-01"));

$deadlinesMap = [];
$queryKalender = mysqli_query($conn, "SELECT DAY(Deadline) as hari FROM tugas WHERE NIM = '$nim' AND StatusTugas = 'Aktif' AND MONTH(Deadline) = '$bulanIni' AND YEAR(Deadline) = '$tahunIni'");
while($row = mysqli_fetch_assoc($queryKalender)) {
    $deadlinesMap[] = $row['hari'];
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

                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <div class="font-semibold text-gray-800"><?= $bulanSekarang ?> <?= $tahunIni ?></div>
                        <div class="flex gap-2">
                            <button class="w-7 h-7 rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </button>
                            <button class="w-7 h-7 rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-1">
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Min</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Sen</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Sel</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Rab</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Kam</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Jum</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Sab</div>

                        <?php
                        // Empty cells before first day
                        for ($i = 1; $i < $hariPertama; $i++) {
                            echo '<div class="aspect-square flex items-center justify-center rounded-md text-sm text-gray-300"></div>';
                        }

                        // Days of the month
                        $hariIniAngka = (int)date('d');
                        for ($day = 1; $day <= $jumlahHari; $day++) {
                            $classes = 'aspect-square flex items-center justify-center rounded-md text-sm text-gray-700 cursor-pointer transition-all duration-200 hover:bg-gray-100 relative';
                            
                            if ($day == $hariIniAngka) {
                                $classes = 'aspect-square flex items-center justify-center rounded-md text-sm font-semibold cursor-pointer transition-all duration-200 relative bg-purple-600 text-white';
                            }
                            
                            $hasDeadline = in_array($day, $deadlinesMap);
                            echo '<div class="' . $classes . '">';
                            echo $day;
                            if ($hasDeadline) {
                                echo '<span class="absolute bottom-0.5 w-1 h-1 rounded-full bg-red-500"></span>';
                            }
                            echo '</div>';
                        }
                        ?>
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
                // For now, we'll use this as a placeholder - you can add logic to track "in progress" status
                $inProgressTasks = [];
                $inProgressCount = count($inProgressTasks);
                ?>
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">In Progress</h3>
                        <span class="bg-blue-100 text-blue-700 text-sm font-medium px-3 py-1 rounded-full"><?= $inProgressCount ?></span>
                    </div>
                    <?php if ($inProgressCount == 0): ?>
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
    </script>
</body>
</html>
