<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../model/Database.php';

if (!isset($_SESSION['nim'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$nim = $_SESSION['nim'];
$kodeGrup = isset($_POST['kode_grup']) ? $_POST['kode_grup'] : '';
$judulKegiatan = isset($_POST['judul']) ? trim($_POST['judul']) : '';
$deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
$tanggalMulai = isset($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : '';
$tanggalSelesai = isset($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : '';
$lokasi = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : '';

// Validation
if (empty($kodeGrup) || empty($judulKegiatan) || empty($tanggalMulai) || empty($tanggalSelesai)) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$conn = getConnection();

// Check if user is member
$memberQuery = "SELECT Role FROM grup_anggota WHERE KodeGrup = ? AND NIM = ?";
$stmt = mysqli_prepare($conn, $memberQuery);
mysqli_stmt_bind_param($stmt, "ss", $kodeGrup, $nim);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_get_result($stmt)->num_rows === 0) {
    echo json_encode(['error' => 'Not a member']);
    exit;
}

// Validate dates
if (strtotime($tanggalSelesai) < strtotime($tanggalMulai)) {
    echo json_encode(['error' => 'End date must be after start date']);
    exit;
}

// Generate unique KodeJadwal
$kodeJadwal = 'EVT-' . time() . '-' . substr(md5(uniqid()), 0, 6);

// Insert event
$insertQuery = "INSERT INTO grup_jadwal (KodeJadwal, KodeGrup, JudulKegiatan, Deskripsi, TanggalMulai, TanggalSelesai, Lokasi, CreatedBy) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt2 = mysqli_prepare($conn, $insertQuery);
mysqli_stmt_bind_param($stmt2, "ssssssss", $kodeJadwal, $kodeGrup, $judulKegiatan, $deskripsi, $tanggalMulai, $tanggalSelesai, $lokasi, $nim);

if (mysqli_stmt_execute($stmt2)) {
    echo json_encode([
        'success' => true,
        'kodeJadwal' => $kodeJadwal,
        'message' => 'Event created successfully'
    ]);
    // Redirect back to detail page
    header("Location: detail_group.php?kode=$kodeGrup&msg=event_created");
} else {
    echo json_encode(['error' => 'Failed to create event']);
    header("Location: detail_group.php?kode=$kodeGrup&msg=event_failed");
}

closeConnection($conn);
?>




