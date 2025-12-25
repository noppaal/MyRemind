<?php
include 'config.php';

if (!isset($_SESSION['nim'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$nim = $_SESSION['nim'];
$kodeJadwal = isset($_POST['kode_jadwal']) ? $_POST['kode_jadwal'] : '';
$judulKegiatan = isset($_POST['judul']) ? trim($_POST['judul']) : '';
$deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
$tanggalMulai = isset($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : '';
$tanggalSelesai = isset($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : '';
$lokasi = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : '';

if (empty($kodeJadwal)) {
    echo json_encode(['error' => 'Event code required']);
    exit;
}

// Get event and check permissions
$eventQuery = "SELECT gj.*, ga.Role 
               FROM grup_jadwal gj
               LEFT JOIN grup_anggota ga ON gj.KodeGrup = ga.KodeGrup AND ga.NIM = ?
               WHERE gj.KodeJadwal = ?";
$stmt = mysqli_prepare($conn, $eventQuery);
mysqli_stmt_bind_param($stmt, "ss", $nim, $kodeJadwal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['error' => 'Event not found or not a member']);
    exit;
}

$event = mysqli_fetch_assoc($result);

// Only creator or admin/owner can edit
if ($event['CreatedBy'] !== $nim && $event['Role'] !== 'admin' && $event['Role'] !== 'owner') {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Build update query
$updates = [];
$params = [];
$types = '';

if (!empty($judulKegiatan)) {
    $updates[] = "JudulKegiatan = ?";
    $params[] = $judulKegiatan;
    $types .= 's';
}
if (!empty($deskripsi)) {
    $updates[] = "Deskripsi = ?";
    $params[] = $deskripsi;
    $types .= 's';
}
if (!empty($tanggalMulai)) {
    $updates[] = "TanggalMulai = ?";
    $params[] = $tanggalMulai;
    $types .= 's';
}
if (!empty($tanggalSelesai)) {
    $updates[] = "TanggalSelesai = ?";
    $params[] = $tanggalSelesai;
    $types .= 's';
}
if (!empty($lokasi)) {
    $updates[] = "Lokasi = ?";
    $params[] = $lokasi;
    $types .= 's';
}

if (empty($updates)) {
    echo json_encode(['error' => 'No fields to update']);
    exit;
}

$params[] = $kodeJadwal;
$types .= 's';

$updateQuery = "UPDATE grup_jadwal SET " . implode(', ', $updates) . " WHERE KodeJadwal = ?";
$stmt2 = mysqli_prepare($conn, $updateQuery);
mysqli_stmt_bind_param($stmt2, $types, ...$params);

if (mysqli_stmt_execute($stmt2)) {
    echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
} else {
    echo json_encode(['error' => 'Failed to update event']);
}
?>
