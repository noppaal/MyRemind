<?php
include 'config.php';

if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}

$nim = $_SESSION['nim'];

// --- 1. LOGIKA UMUM & STATISTIK (Hanya Data Milik User Login) ---
// Update: Tambah filter NIM='$nim'
$resTugasCount = mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE StatusTugas = 'Aktif' AND NIM = '$nim'");
$countTugas = mysqli_fetch_assoc($resTugasCount)['total'];

$hariIniIndo = [
    'Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 
    'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
];
$hariInggris = date('D');
$hariIni = $hariIniIndo[$hariInggris];

$resJadwalHariIni = mysqli_query($conn, "SELECT COUNT(*) as total FROM jadwalkuliah WHERE NIM = '$nim' AND Hari = '$hariIni'");
$countJadwal = mysqli_fetch_assoc($resJadwalHariIni)['total'];

// --- 2. QUERY OVERVIEW: JADWAL HARI INI ---
$queryJadwalToday = "SELECT j.*, m.NamaMK, d.NamaDosen 
                     FROM jadwalkuliah j 
                     JOIN matakuliah m ON j.KodeMK = m.KodeMK 
                     LEFT JOIN dosen d ON m.KodeDosen = d.KodeDosen
                     WHERE j.NIM = '$nim' AND j.Hari = '$hariIni' 
                     ORDER BY j.JamMulai ASC";
$resultJadwalToday = mysqli_query($conn, $queryJadwalToday);

// --- 3. QUERY OVERVIEW: DEADLINE TERDEKAT (Update: Filter NIM) ---
$queryDeadlineOverview = "SELECT t.*, m.NamaMK FROM tugas t 
                          JOIN matakuliah m ON t.KodeMK = m.KodeMK 
                          WHERE t.StatusTugas='Aktif' 
                          AND t.NIM = '$nim' 
                          ORDER BY t.Deadline ASC LIMIT 2";
$resDeadlineOverview = mysqli_query($conn, $queryDeadlineOverview);

// --- 4. QUERY TAB JADWAL (MINGGUAN) ---
$queryListJadwal = "SELECT j.*, m.NamaMK, d.NamaDosen 
                    FROM jadwalkuliah j 
                    JOIN matakuliah m ON j.KodeMK = m.KodeMK 
                    LEFT JOIN dosen d ON m.KodeDosen = d.KodeDosen
                    WHERE j.NIM = '$nim' 
                    ORDER BY FIELD(j.Hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), j.JamMulai ASC";
$resultJadwal = mysqli_query($conn, $queryListJadwal);

// --- 5. QUERY TAB TUGAS (SEMUA) (Update: Filter NIM) ---
$queryListTugas = "SELECT t.*, m.NamaMK 
                   FROM tugas t 
                   JOIN matakuliah m ON t.KodeMK = m.KodeMK 
                   WHERE t.StatusTugas = 'Aktif' 
                   AND t.NIM = '$nim'
                   ORDER BY t.Deadline ASC";
$resultTugas = mysqli_query($conn, $queryListTugas);

// --- 6. LOGIKA KALENDER ---
$bulanIni = date('m');
$tahunIni = date('Y');
$jumlahHari = date('t');
$hariPertama = date('N', strtotime("$tahunIni-$bulanIni-01"));
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
        .stat-card { border: none; border-radius: 12px; border-left: 5px solid; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .card-blue { border-left-color: var(--secondary); }
        .card-red { border-left-color: #dc3545; }
        .card-green { border-left-color: #198754; }
        .list-card { background: white; border: 1px solid #e9ecef; border-radius: 10px; padding: 20px; margin-bottom: 15px; }
        .accent-bar { width: 4px; height: 40px; background-color: var(--secondary); border-radius: 2px; margin-right: 15px; }
        .nav-pills .nav-link { color: #6c757d; border-radius: 20px; padding: 8px 20px; font-weight: 600; }
        .nav-pills .nav-link.active { background-color: #e9ecef; color: #000; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; text-align: center; font-size: 0.9rem; }
        .calendar-day-head { font-weight: bold; color: #666; font-size: 0.8rem; margin-bottom: 5px; }
        .calendar-date { padding: 5px; border-radius: 50%; width: 32px; height: 32px; line-height: 22px; margin: 0 auto; cursor: default; }
        .calendar-date.today { background-color: var(--primary); color: white; font-weight: bold; }
        .calendar-date.empty { background: transparent; }
        .form-control, .form-select { background-color: #f8f9fa; border: 1px solid #dee2e6; }
        .form-control:focus { background-color: #fff; border-color: var(--primary); box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.1); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fa-solid fa-graduation-cap me-2"></i>MyRemind
                <div style="font-size: 12px; font-weight: normal; color: #666;">Asisten Cerdas Mahasiswa Telkom University</div>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-md-block">Halo, <b><?= $_SESSION['nama'] ?></b></span>
                <button class="btn btn-primary btn-sm px-3 rounded-pill"><i class="fa-regular fa-bell me-2"></i>Pengingat Aktif</button>
                <a href="logout.php" class="btn btn-outline-danger btn-sm ms-2 rounded-pill">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <div class="row mb-4">
            <div class="col-md-4"><div class="card stat-card card-blue p-3 h-100"><div class="d-flex justify-content-between"><div><small class="text-muted">Jadwal Hari Ini</small><h3 class="fw-bold mt-2 text-primary"><?= $countJadwal ?> Kelas</h3></div><i class="fa-regular fa-calendar text-primary opacity-50 fs-2"></i></div></div></div>
            <div class="col-md-4"><div class="card stat-card card-red p-3 h-100"><div class="d-flex justify-content-between"><div><small class="text-muted">Tugas Mendesak</small><h3 class="fw-bold mt-2 text-danger"><?= $countTugas ?> Tugas</h3></div><i class="fa-solid fa-circle-exclamation text-danger opacity-50 fs-2"></i></div></div></div>
            <div class="col-md-4"><div class="card stat-card card-green p-3 h-100"><div class="d-flex justify-content-between"><div><small class="text-muted">Tugas Selesai</small><h3 class="fw-bold mt-2 text-success">0 / 1</h3></div><i class="fa-solid fa-book-open text-success opacity-50 fs-2"></i></div></div></div>
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
                                <p class="text-muted small">Pilih tanggal untuk melihat jadwal & deadline</p>
                                <div class="p-3 bg-light rounded">
                                    <h6 class="fw-bold text-center mb-3"><?= date('F Y') ?></h6>
                                    <div class="calendar-grid mb-2">
                                        <div class="calendar-day-head">Sen</div><div class="calendar-day-head">Sel</div><div class="calendar-day-head">Rab</div><div class="calendar-day-head">Kam</div><div class="calendar-day-head">Jum</div><div class="calendar-day-head">Sab</div><div class="calendar-day-head text-danger">Min</div>
                                    </div>
                                    <div class="calendar-grid">
                                        <?php 
                                        for($i=1; $i < $hariPertama; $i++) echo '<div class="calendar-date empty"></div>';
                                        for($d=1; $d <= $jumlahHari; $d++) {
                                            $cls = ($d == date('d')) ? 'today' : '';
                                            echo "<div class='calendar-date $cls'>$d</div>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                         <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">Deadline Terdekat</h5>
                                <p class="text-muted small">Tugas yang perlu segera diselesaikan</p>
                                <?php while($t = mysqli_fetch_assoc($resDeadlineOverview)): ?>
                                <div class="border rounded p-3 mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div><h6 class="mb-1 fw-bold"><?= htmlspecialchars($t['JudulTugas']) ?></h6><small class="text-muted"><?= $t['KodeMK'] ?></small></div>
                                        <span class="badge bg-danger">Mendesak</span>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div><h5 class="card-title fw-bold mb-0">Jadwal Kuliah Hari Ini</h5><p class="text-muted small mb-0"><?= $hariIni ?>, <?= date('d F Y') ?></p></div>
                            <span class="badge bg-primary rounded-pill px-3"><?= mysqli_num_rows($resultJadwalToday) ?> Kelas</span>
                        </div>
                        <?php if(mysqli_num_rows($resultJadwalToday) > 0): ?>
                            <div class="row g-3">
                                <?php while($jt = mysqli_fetch_assoc($resultJadwalToday)): ?>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-light h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-primary me-2"><?= substr($jt['JamMulai'], 0, 5) ?></span>
                                            <h6 class="fw-bold mb-0"><?= $jt['NamaMK'] ?></h6>
                                        </div>
                                        <div class="text-muted small ps-1">
                                            <div class="mb-1"><i class="fa-solid fa-chalkboard-user me-2" style="width:15px"></i> <?= $jt['KodeMK'] ?></div>
                                            <div class="mb-1"><i class="fa-solid fa-location-dot me-2" style="width:15px"></i> <?= $jt['Ruangan'] ?></div>
                                            <div><i class="fa-regular fa-user me-2" style="width:15px"></i> <?= $jt['NamaDosen'] ?? '-' ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-4 bg-light rounded"><p class="text-muted mb-0">Tidak ada jadwal kuliah hari ini.</p></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card bg-light border-primary border-opacity-25 mb-5">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded p-3"><i class="fa-solid fa-graduation-cap fs-4"></i></div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold text-primary mb-1">Integrasi LMS Telkom University</h6>
                            <p class="small text-muted mb-2">Hubungkan dengan iGracias/CeLOE untuk impor otomatis.</p>
                            <form action="lms_sync.php" method="POST" class="d-flex gap-2">
                                <input type="url" name="lms_url" class="form-control form-control-sm" placeholder="Tempel URL Calendar Export (.ics)..." required>
                                <button type="submit" name="sync_lms" class="btn btn-primary btn-sm text-nowrap">Hubungkan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-jadwal">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div><h5 class="fw-bold mb-0">Jadwal Kuliah Mingguan</h5><p class="text-muted small mb-0">Semua jadwal kuliah semester ini</p></div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalJadwal"><i class="fa-solid fa-plus me-2"></i>Tambah Jadwal</button>
                </div>
                <?php while($jadwal = mysqli_fetch_assoc($resultJadwal)): ?>
                <div class="list-card d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center"><div class="accent-bar bg-primary"></div><div><h6 class="fw-bold mb-1"><?= $jadwal['NamaMK'] ?></h6><small class="text-muted"><?= $jadwal['KodeMK'] ?> • <?= $jadwal['Hari'] ?></small></div></div>
                    <div class="d-flex gap-4 text-muted small">
                        <div><i class="fa-regular fa-clock me-2"></i><?= date('H:i', strtotime($jadwal['JamMulai'])) ?> - <?= date('H:i', strtotime($jadwal['JamSelesai'])) ?></div>
                        <div><i class="fa-solid fa-location-dot me-2"></i><?= $jadwal['Ruangan'] ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="tab-pane fade" id="pills-tugas">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div><h5 class="fw-bold mb-0">Daftar Tugas & Deadline</h5><p class="text-muted small mb-0">Kelola semua tugas dan deadline Anda</p></div>
                    <button class="btn btn-primary btn-sm" style="background-color: #8a2be2; border:none;" data-bs-toggle="modal" data-bs-target="#modalTugas"><i class="fa-solid fa-plus me-2"></i>Tambah Tugas</button>
                </div>
                <?php while($tugas = mysqli_fetch_assoc($resultTugas)): ?>
                <div class="list-card d-flex justify-content-between align-items-start">
                    <div><h6 class="fw-bold mb-1"><?= $tugas['JudulTugas'] ?></h6><p class="text-muted small mb-1"><?= $tugas['NamaMK'] ?></p><small class="text-muted"><i class="fa-regular fa-clock me-1"></i> Deadline: <?= date('d M Y', strtotime($tugas['Deadline'])) ?></small></div>
                    <button class="btn btn-light btn-sm text-danger"><i class="fa-solid fa-trash"></i></button>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTugas" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0"><div><h5 class="modal-title fw-bold">Tambah Tugas Baru</h5><p class="text-muted small mb-0">Tambahkan tugas atau deadline baru</p></div><button type="button" class="btn-close mb-auto" data-bs-dismiss="modal"></button></div>
                <div class="modal-body pt-3">
                    <form action="proses_tambah.php" method="POST">
                        <input type="hidden" name="tipe" value="tugas">
                        <div class="mb-3"><label class="form-label fw-bold small mb-1">Judul Tugas</label><input type="text" name="judul" class="form-control" placeholder="Contoh: Tugas Besar Web" required></div>
                        <div class="mb-3"><label class="form-label fw-bold small mb-1">Mata Kuliah</label><input type="text" name="nama_mk" class="form-control" placeholder="Nama mata kuliah" required></div>
                        <div class="row mb-3">
                            <div class="col-6"><label class="form-label fw-bold small mb-1">Deadline</label><input type="date" name="deadline" class="form-control" required></div>
                            <div class="col-6"><label class="form-label fw-bold small mb-1">Prioritas</label><select name="prioritas" class="form-select"><option value="Sedang">Sedang</option><option value="Tinggi">Tinggi</option><option value="Rendah">Rendah</option></select></div>
                        </div>
                        <div class="mb-4"><label class="form-label fw-bold small mb-1">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="3" placeholder="Detail tugas..."></textarea></div>
                        <div class="d-flex justify-content-end gap-2"><button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary px-3" style="background-color: #8a2be2; border-color: #8a2be2;">Simpan Tugas</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalJadwal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0"><div><h5 class="modal-title fw-bold">Tambah Jadwal Kuliah</h5><p class="text-muted small mb-0">Tambahkan jadwal kuliah baru ke kalender Anda</p></div><button type="button" class="btn-close mb-auto" data-bs-dismiss="modal"></button></div>
                <div class="modal-body pt-3">
                    <form action="proses_tambah.php" method="POST">
                        <input type="hidden" name="tipe" value="jadwal">
                        <div class="mb-3"><label class="form-label fw-bold small mb-1">Nama Mata Kuliah</label><input type="text" name="nama_mk" class="form-control" placeholder="Contoh: Pemrograman Web" required></div>
                        <div class="mb-3"><label class="form-label fw-bold small mb-1">Kode Mata Kuliah</label><input type="text" name="kode_mk" class="form-control" placeholder="Contoh: IF-3028" required></div>
                        <div class="row mb-3">
                            <div class="col-5"><label class="form-label fw-bold small mb-1">Hari</label><select name="hari" class="form-select"><option>Senin</option><option>Selasa</option><option>Rabu</option><option>Kamis</option><option>Jumat</option><option>Sabtu</option></select></div>
                            <div class="col-7"><label class="form-label fw-bold small mb-1">Waktu</label><input type="text" name="waktu" class="form-control" placeholder="08:00 - 10:30" required></div>
                        </div>
                        <div class="mb-3"><label class="form-label fw-bold small mb-1">Ruangan</label><input type="text" name="ruangan" class="form-control" placeholder="Contoh: GKU 102" required></div>
                        <div class="mb-4"><label class="form-label fw-bold small mb-1">Dosen</label><input type="text" name="dosen" class="form-control" placeholder="Nama dosen"></div>
                        <div class="d-flex justify-content-end gap-2"><button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary px-3 bg-primary border-0">Simpan Jadwal</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>