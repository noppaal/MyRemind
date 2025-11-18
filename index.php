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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard - MyRemind</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #6f42c1; --secondary: #0d6efd; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .navbar-brand { font-weight: bold; color: var(--primary) !important; }
        
        /* Cards & UI */
        .stat-card { border: none; border-radius: 12px; border-left: 5px solid; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .card-blue { border-left-color: var(--secondary); }
        .card-red { border-left-color: #dc3545; }
        .card-green { border-left-color: #198754; }
        .list-card { background: white; border: 1px solid #e9ecef; border-radius: 10px; padding: 20px; margin-bottom: 15px; transition: all 0.2s; cursor: pointer; }
        .list-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-color: var(--primary); transform: translateX(2px); }
        .accent-bar { width: 4px; height: 40px; background-color: var(--secondary); border-radius: 2px; margin-right: 15px; }
        
        /* Tabs */
        .nav-pills .nav-link { color: #6c757d; border-radius: 20px; padding: 8px 20px; font-weight: 600; }
        .nav-pills .nav-link.active { background-color: #e9ecef; color: #000; }
        
        /* Filter Tabs (Sub-tabs) */
        .nav-filter .nav-link { border-radius: 20px; padding: 6px 18px; font-size: 0.85rem; font-weight: 600; margin-right: 5px; background-color: #fff; border: 1px solid #e9ecef; color: #6c757d; }
        .nav-filter .nav-link.active { background-color: var(--primary); color: white; border-color: var(--primary); }

        /* Calendar */
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; text-align: center; font-size: 0.9rem; }
        .calendar-day-head { font-weight: bold; color: #666; font-size: 0.8rem; margin-bottom: 5px; }
        .calendar-date { padding: 5px; border-radius: 10px; width: 100%; min-height: 40px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: default; }
        .calendar-date.today { background-color: var(--primary); color: white; font-weight: bold; }
        .calendar-dot { height: 6px; width: 6px; background-color: #dc3545; border-radius: 50%; display: block; margin-top: 2px; }
        .calendar-date.today .calendar-dot { background-color: white; }
        .calendar-date.empty { background: transparent; }
        
        /* Forms & Badges */
        .form-control, .form-select { background-color: #f8f9fa; border: 1px solid #dee2e6; }
        .badge-overdue { background-color: #212529; color: #fff; }
        .task-done h6 { text-decoration: line-through; color: #adb5bd; }
        
        /* Scrollable Widget */
        .task-scroll-box { max-height: 320px; overflow-y: auto; padding-right: 5px; }
        .task-scroll-box::-webkit-scrollbar { width: 6px; }
        .task-scroll-box::-webkit-scrollbar-track { background: #f1f1f1; }
        .task-scroll-box::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
        
        /* Widget Tabs */
        .nav-tabs-deadline { border-bottom: none; }
        .nav-tabs-deadline .nav-link { border: none; color: #6c757d; font-size: 0.9rem; font-weight: 600; padding: 5px 15px; }
        .nav-tabs-deadline .nav-link.active { color: var(--primary); border-bottom: 2px solid var(--primary); background: transparent; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fa-solid fa-graduation-cap me-2"></i>MyRemind</a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-md-block">Halo, <b><?= htmlspecialchars($_SESSION['nama']) ?></b></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm ms-2 rounded-pill">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card card-blue p-3 h-100">
                    <div class="d-flex justify-content-between">
                        <div><small class="text-muted">Jadwal Hari Ini</small><h3 class="fw-bold mt-2 text-primary"><?= $countJadwal ?> Kelas</h3></div>
                        <i class="fa-regular fa-calendar text-primary opacity-50 fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card card-red p-3 h-100">
                    <div class="d-flex justify-content-between">
                        <div><small class="text-muted">Tugas Mendesak</small><h3 class="fw-bold mt-2 text-danger"><?= count($tugasMendesak) ?> Tugas</h3></div>
                        <i class="fa-solid fa-circle-exclamation text-danger opacity-50 fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card card-green p-3 h-100">
                    <div class="d-flex justify-content-between">
                        <div><small class="text-muted">Tugas Selesai</small><h3 class="fw-bold mt-2 text-success"><?= $countSelesai ?> / <?= $countTotal ?></h3></div>
                        <i class="fa-solid fa-book-open text-success opacity-50 fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-pill p-1 mb-4 shadow-sm d-flex justify-content-between" style="max-width: 800px; margin: 0 auto;">
            <ul class="nav nav-pills nav-fill w-100" id="pills-tab" role="tablist">
                <li class="nav-item"><button class="nav-link active" id="pills-overview-tab" data-bs-toggle="pill" data-bs-target="#pills-overview" type="button">Overview</button></li>
                <li class="nav-item"><button class="nav-link" id="pills-jadwal-tab" data-bs-toggle="pill" data-bs-target="#pills-jadwal" type="button">Jadwal Kuliah</button></li>
                <li class="nav-item"><button class="nav-link" id="pills-tugas-tab" data-bs-toggle="pill" data-bs-target="#pills-tugas" type="button">Tugas & Deadline</button></li>
            </ul>
        </div>

        <div class="tab-content" id="pills-tabContent">
            
            <div class="tab-pane fade show active" id="pills-overview">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">Kalender Akademik</h5>
                                <div class="p-3 bg-light rounded mt-3">
                                    <h6 class="fw-bold text-center mb-3"><?= date('F Y') ?></h6>
                                    <div class="calendar-grid mb-2"><div class="calendar-day-head">Sen</div><div class="calendar-day-head">Sel</div><div class="calendar-day-head">Rab</div><div class="calendar-day-head">Kam</div><div class="calendar-day-head">Jum</div><div class="calendar-day-head">Sab</div><div class="calendar-day-head text-danger">Min</div></div>
                                    <div class="calendar-grid">
                                        <?php 
                                        for($i=1; $i < $hariPertama; $i++) echo '<div class="calendar-date empty"></div>';
                                        for($d=1; $d <= $jumlahHari; $d++) {
                                            $cls = ($d == date('d')) ? 'today' : '';
                                            $marker = in_array($d, $deadlinesMap) ? '<span class="calendar-dot"></span>' : '';
                                            echo "<div class='calendar-date $cls'><span>$d</span>$marker</div>";
                                        } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                         <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                                <div class="d-flex justify-content-between align-items-center mb-2"><h5 class="card-title fw-bold mb-0">Deadline Tugas</h5></div>
                                <ul class="nav nav-tabs nav-tabs-deadline" id="deadlineTab" role="tablist">
                                    <li class="nav-item"><button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming-content" type="button">Akan Datang (<?= count($tugasUpcomingWidget) ?>)</button></li>
                                    <li class="nav-item"><button class="nav-link" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue-content" type="button">Terlewat (<?= count($tugasOverdueWidget) ?>)</button></li>
                                </ul>
                            </div>
                            <div class="card-body pt-2">
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="upcoming-content">
                                        <div class="task-scroll-box">
                                            <?php if(count($tugasUpcomingWidget) > 0): foreach($tugasUpcomingWidget as $t): ?>
                                            <div class="border rounded p-3 mb-2 bg-white btn-view-detail" 
                                                 data-judul="<?= htmlspecialchars($t['JudulTugas']) ?>" 
                                                 data-mk="<?= htmlspecialchars($t['NamaMK']) ?>" 
                                                 data-deadline="<?= date('d M Y, H:i', strtotime($t['Deadline'])) ?>" 
                                                 data-desc="<?= htmlspecialchars($t['Deskripsi'] ?? '-') ?>" 
                                                 data-status="Akan Datang">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div><h6 class="mb-1 fw-bold"><?= htmlspecialchars($t['JudulTugas']) ?></h6><small class="text-muted"><?= $t['NamaMK'] ?></small></div>
                                                    <span class="badge bg-primary rounded-pill">H-<?= (new DateTime())->diff(new DateTime($t['Deadline']))->days ?></span>
                                                </div>
                                                <div class="mt-2 small text-muted"><i class="fa-regular fa-clock me-1"></i> <?= date('d M Y, H:i', strtotime($t['Deadline'])) ?></div>
                                            </div>
                                            <?php endforeach; else: ?><div class="text-center py-5 text-muted small">Tidak ada tugas.</div><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="overdue-content">
                                        <div class="task-scroll-box">
                                            <?php if(count($tugasOverdueWidget) > 0): foreach($tugasOverdueWidget as $t): ?>
                                            <div class="border rounded p-3 mb-2 bg-light border-danger btn-view-detail" 
                                                 data-judul="<?= htmlspecialchars($t['JudulTugas']) ?>" 
                                                 data-mk="<?= htmlspecialchars($t['NamaMK']) ?>" 
                                                 data-deadline="<?= date('d M Y, H:i', strtotime($t['Deadline'])) ?>" 
                                                 data-desc="<?= htmlspecialchars($t['Deskripsi'] ?? '-') ?>" 
                                                 data-status="Terlewat">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div><h6 class="mb-1 fw-bold text-danger"><?= htmlspecialchars($t['JudulTugas']) ?></h6><small class="text-muted"><?= $t['NamaMK'] ?></small></div>
                                                    <span class="badge badge-overdue">Terlewat</span>
                                                </div>
                                                <div class="mt-2 small text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Deadline: <?= date('d M Y, H:i', strtotime($t['Deadline'])) ?></div>
                                            </div>
                                            <?php endforeach; else: ?><div class="text-center py-5 text-muted small">Tidak ada tugas terlewat.</div><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="card-title fw-bold mb-0">Jadwal Kuliah Hari Ini</h5><p class="text-muted small mb-0"><?= $hariIni ?>, <?= date('d F Y') ?></p></div><span class="badge bg-primary rounded-pill px-3"><?= mysqli_num_rows($resultJadwalToday) ?> Kelas</span></div>
                        <?php if(mysqli_num_rows($resultJadwalToday) > 0): ?>
                            <div class="row g-3"><?php while($jt = mysqli_fetch_assoc($resultJadwalToday)): ?><div class="col-md-6"><div class="p-3 border rounded bg-light h-100"><div class="d-flex align-items-center mb-2"><span class="badge bg-primary me-2"><?= substr($jt['JamMulai'], 0, 5) ?></span><h6 class="fw-bold mb-0"><?= $jt['NamaMK'] ?></h6></div><div class="text-muted small ps-1"><div><i class="fa-solid fa-chalkboard-user me-2"></i> <?= $jt['Kelas'] ?> (<?= $jt['Ruangan'] ?>)</div><div><i class="fa-regular fa-user me-2"></i> <?= $jt['NamaDosen'] ?? '-' ?></div></div></div></div><?php endwhile; ?></div>
                        <?php else: ?><div class="text-center p-4 bg-light rounded text-muted">Tidak ada jadwal kuliah hari ini.</div><?php endif; ?>
                    </div>
                </div>
                
                <div class="card bg-light border-primary border-opacity-25 mb-5">
                    <div class="card-body d-flex align-items-center gap-3"><div class="bg-primary text-white rounded p-3"><i class="fa-solid fa-graduation-cap fs-4"></i></div><div class="flex-grow-1"><h6 class="fw-bold text-primary mb-1">Integrasi LMS Telkom University</h6><p class="small text-muted mb-2">Hubungkan dengan iGracias/CeLOE untuk impor otomatis.</p><form action="lms_sync.php" method="POST" class="d-flex gap-2"><input type="url" name="lms_url" class="form-control form-control-sm" placeholder="Tempel URL Calendar Export (.ics)..." required><button type="submit" name="sync_lms" class="btn btn-primary btn-sm text-nowrap">Hubungkan</button></form></div></div>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-jadwal">
                 <div class="d-flex justify-content-between align-items-center mb-4"><div><h5 class="fw-bold mb-0">Jadwal Kuliah</h5></div><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalJadwal"><i class="fa-solid fa-plus me-2"></i>Tambah</button></div>
                 <?php while($jadwal = mysqli_fetch_assoc($resultJadwal)): ?>
                    <div class="list-card d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center"><div class="accent-bar bg-primary"></div><div><h6 class="fw-bold mb-1"><?= $jadwal['NamaMK'] ?></h6><small class="text-muted"><?= $jadwal['Kelas'] ?> • <?= $jadwal['Hari'] ?>, <?= substr($jadwal['JamMulai'],0,5) ?></small></div></div>
                        <div class="d-flex gap-2">
                             <div class="small text-muted me-3 align-self-center"><i class="fa-solid fa-location-dot me-2"></i><?= $jadwal['Ruangan'] ?></div>
                             <button class="btn btn-sm btn-light text-primary btn-edit-jadwal" data-id="<?= $jadwal['KodeJadwal'] ?>" data-mk="<?= htmlspecialchars($jadwal['NamaMK']) ?>" data-kelas="<?= $jadwal['Kelas'] ?>" data-hari="<?= $jadwal['Hari'] ?>" data-waktu="<?= substr($jadwal['JamMulai'],0,5).'-'.substr($jadwal['JamSelesai'],0,5) ?>" data-ruangan="<?= $jadwal['Ruangan'] ?>"><i class="fa-solid fa-pen"></i></button>
                             <a href="hapus.php?id=<?= $jadwal['KodeJadwal'] ?>&type=jadwal" class="btn btn-sm btn-light text-danger border-0" onclick="return confirm('Yakin hapus jadwal ini?')"><i class="fa-solid fa-trash"></i></a>
                        </div>
                    </div>
                 <?php endwhile; ?>
            </div>
            
            <div class="tab-pane fade" id="pills-tugas">
                 <div class="d-flex justify-content-between align-items-center mb-4">
                    <div><h5 class="fw-bold mb-0">Daftar Tugas</h5><p class="text-muted small mb-0">Kelola semua tugas Anda</p></div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTugas"><i class="fa-solid fa-plus me-2"></i>Tambah</button>
                 </div>

                 <ul class="nav nav-pills nav-filter mb-4" id="filterTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" id="tab-semua" data-bs-toggle="pill" data-bs-target="#content-semua">Semua (<?= count($tugasSemua) ?>)</button></li>
                    <li class="nav-item"><button class="nav-link" id="tab-mendesak" data-bs-toggle="pill" data-bs-target="#content-mendesak">Mendesak (<?= count($tugasMendesak) ?>)</button></li>
                    <li class="nav-item"><button class="nav-link" id="tab-terlewat" data-bs-toggle="pill" data-bs-target="#content-terlewat">Terlewat (<?= count($tugasTerlewat) ?>)</button></li>
                    <li class="nav-item"><button class="nav-link" id="tab-selesai" data-bs-toggle="pill" data-bs-target="#content-selesai">Selesai (<?= count($tugasSelesaiList) ?>)</button></li>
                 </ul>

                 <div class="tab-content" id="filterTabContent">
                    <div class="tab-pane fade show active" id="content-semua">
                        <?php if(count($tugasSemua) > 0): foreach($tugasSemua as $t): include 'item_tugas.php'; endforeach; else: echo '<div class="text-center p-5 text-muted bg-light rounded">Tidak ada tugas aktif.</div>'; endif; ?>
                    </div>
                    <div class="tab-pane fade" id="content-mendesak">
                         <?php if(count($tugasMendesak) > 0): foreach($tugasMendesak as $t): include 'item_tugas.php'; endforeach; else: echo '<div class="text-center p-5 text-muted bg-light rounded">Tidak ada tugas mendesak.</div>'; endif; ?>
                    </div>
                    <div class="tab-pane fade" id="content-terlewat">
                         <?php if(count($tugasTerlewat) > 0): foreach($tugasTerlewat as $t): include 'item_tugas.php'; endforeach; else: echo '<div class="text-center p-5 text-muted bg-light rounded">Hore! Tidak ada tugas terlewat.</div>'; endif; ?>
                    </div>
                    <div class="tab-pane fade" id="content-selesai">
                         <?php if(count($tugasSelesaiList) > 0): foreach($tugasSelesaiList as $t): include 'item_tugas.php'; endforeach; else: echo '<div class="text-center p-5 text-muted bg-light rounded">Belum ada tugas selesai.</div>'; endif; ?>
                    </div>
                 </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetailTugas" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0"><div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold" id="detail_judul">Judul Tugas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><span class="badge bg-light text-dark border" id="detail_mk">Mata Kuliah</span> <span class="badge bg-primary" id="detail_status">Status</span></div><div class="d-flex align-items-center text-muted small mb-4"><i class="fa-regular fa-clock me-2"></i> <span id="detail_deadline">Deadline</span></div><h6 class="fw-bold small text-muted">DESKRIPSI</h6><p class="bg-light p-3 rounded small text-secondary" id="detail_deskripsi" style="white-space: pre-wrap;">...</p></div><div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Tutup</button></div></div></div></div>

    <div class="modal fade" id="modalTugas" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0"><div class="modal-header border-0 pb-0"><div><h5 class="modal-title fw-bold">Tambah Tugas</h5></div><button type="button" class="btn-close mb-auto" data-bs-dismiss="modal"></button></div><div class="modal-body pt-3"><form action="proses_tambah.php" method="POST"><input type="hidden" name="tipe" value="tugas"><div class="mb-3"><label class="form-label fw-bold small mb-1">Judul</label><input type="text" name="judul" class="form-control" required></div><div class="mb-3"><label class="form-label fw-bold small mb-1">Mata Kuliah</label><input type="text" name="nama_mk" class="form-control" required></div><div class="row mb-3"><div class="col-6"><label class="form-label fw-bold small mb-1">Deadline</label><input type="date" name="deadline" class="form-control" required></div><div class="col-6"><label class="form-label fw-bold small mb-1">Prioritas</label><select name="prioritas" class="form-select"><option>Sedang</option><option>Tinggi</option></select></div></div><div class="mb-4"><label class="form-label fw-bold small mb-1">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="3"></textarea></div><div class="d-flex justify-content-end"><button type="submit" class="btn btn-primary px-3">Simpan</button></div></form></div></div></div></div>
    
    <div class="modal fade" id="modalJadwal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0"><div class="modal-header border-0 pb-0"><div><h5 class="modal-title fw-bold">Tambah Jadwal</h5></div><button type="button" class="btn-close mb-auto" data-bs-dismiss="modal"></button></div><div class="modal-body pt-3"><form action="proses_tambah.php" method="POST"><input type="hidden" name="tipe" value="jadwal"><div class="mb-3"><label class="form-label fw-bold small mb-1">Nama Mata Kuliah</label><input type="text" name="nama_mk" class="form-control" placeholder="Pemrograman Web" required></div><div class="mb-3"><label class="form-label fw-bold small mb-1">Kode Kelas</label><input type="text" name="kelas" class="form-control" placeholder="IF-47-02" required></div><div class="row mb-3"><div class="col-5"><label class="form-label fw-bold small mb-1">Hari</label><select name="hari" class="form-select"><option>Senin</option><option>Selasa</option><option>Rabu</option><option>Kamis</option><option>Jumat</option><option>Sabtu</option></select></div><div class="col-7"><label class="form-label fw-bold small mb-1">Waktu</label><input type="text" name="waktu" class="form-control" placeholder="08:00-10:00" required></div></div><div class="mb-3"><label class="form-label fw-bold small mb-1">Ruangan</label><input type="text" name="ruangan" class="form-control" required></div><div class="mb-4"><label class="form-label fw-bold small mb-1">Dosen</label><input type="text" name="dosen" class="form-control"></div><div class="d-flex justify-content-end"><button type="submit" class="btn btn-primary px-3">Simpan</button></div></form></div></div></div></div>
    
    <div class="modal fade" id="modalEditJadwal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0"><div class="modal-header border-0 pb-0"><div><h5 class="modal-title fw-bold">Edit Jadwal</h5></div><button type="button" class="btn-close mb-auto" data-bs-dismiss="modal"></button></div><div class="modal-body pt-3"><form action="proses_edit.php" method="POST"><input type="hidden" name="tipe" value="jadwal"><input type="hidden" name="id_jadwal" id="edit_id_jadwal"><div class="mb-3"><label class="form-label fw-bold small mb-1">Mata Kuliah</label><input type="text" id="edit_nama_mk" class="form-control bg-light" readonly></div><div class="mb-3"><label class="form-label fw-bold small mb-1">Kode Kelas</label><input type="text" name="kelas" id="edit_kelas" class="form-control" required></div><div class="row mb-3"><div class="col-5"><label class="form-label fw-bold small mb-1">Hari</label><select name="hari" id="edit_hari" class="form-select"><option>Senin</option><option>Selasa</option><option>Rabu</option><option>Kamis</option><option>Jumat</option><option>Sabtu</option></select></div><div class="col-7"><label class="form-label fw-bold small mb-1">Waktu</label><input type="text" name="waktu" id="edit_waktu" class="form-control" required></div></div><div class="mb-3"><label class="form-label fw-bold small mb-1">Ruangan</label><input type="text" name="ruangan" id="edit_ruangan" class="form-control" required></div><div class="d-flex justify-content-end"><button type="submit" class="btn btn-primary px-3">Simpan Perubahan</button></div></form></div></div></div></div>

    <div class="modal fade" id="modalEditTugas" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0"><div class="modal-header border-0 pb-0"><div><h5 class="modal-title fw-bold">Edit Tugas</h5></div><button type="button" class="btn-close mb-auto" data-bs-dismiss="modal"></button></div><div class="modal-body pt-3"><form action="proses_edit.php" method="POST"><input type="hidden" name="tipe" value="tugas"><input type="hidden" name="id_tugas" id="edit_id_tugas"><div class="mb-3"><label class="form-label fw-bold small mb-1">Judul Tugas</label><input type="text" name="judul" id="edit_judul" class="form-control" required></div><div class="mb-3"><label class="form-label fw-bold small mb-1">Deadline</label><input type="date" name="deadline" id="edit_deadline" class="form-control" required></div><div class="mb-4"><label class="form-label fw-bold small mb-1">Deskripsi</label><textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea></div><div class="d-flex justify-content-end"><button type="submit" class="btn btn-primary px-3">Simpan Perubahan</button></div></form></div></div></div></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // View Detail
            $('.btn-view-detail').on('click', function(e) {
                if ($(e.target).closest('button').length || $(e.target).closest('a').length) return;
                $('#detail_judul').text($(this).data('judul'));
                $('#detail_mk').text($(this).data('mk'));
                $('#detail_deadline').text($(this).data('deadline'));
                $('#detail_status').text($(this).data('status'));
                var desc = $(this).data('desc');
                if(desc == "" || desc == "-") desc = "Tidak ada deskripsi.";
                $('#detail_deskripsi').text(desc);
                if($(this).data('status') == 'Terlewat') $('#detail_status').removeClass('bg-primary').addClass('bg-dark');
                else if($(this).data('status') == 'Mendesak') $('#detail_status').removeClass('bg-primary').addClass('bg-danger');
                else $('#detail_status').addClass('bg-primary').removeClass('bg-danger bg-dark');
                $('#modalDetailTugas').modal('show');
            });
            // Populate Edit Jadwal
            $('.btn-edit-jadwal').on('click', function() {
                $('#edit_id_jadwal').val($(this).data('id')); $('#edit_nama_mk').val($(this).data('mk')); $('#edit_kelas').val($(this).data('kelas')); $('#edit_hari').val($(this).data('hari')); $('#edit_waktu').val($(this).data('waktu')); $('#edit_ruangan').val($(this).data('ruangan')); $('#modalEditJadwal').modal('show');
            });
            // Populate Edit Tugas
            $('.btn-edit-tugas').on('click', function() {
                $('#edit_id_tugas').val($(this).data('id')); $('#edit_judul').val($(this).data('judul')); $('#edit_deadline').val($(this).data('deadline')); $('#edit_deskripsi').val($(this).data('deskripsi')); $('#modalEditTugas').modal('show');
            });
        });
    </script>
</body>
</html>