<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../model/Database.php';

if (!isset($_SESSION['nim'])) {
    header("Location: ../../public/login.php");
    exit;
}

$nim = $_SESSION['nim'];
$kodeJadwal = isset($_POST['kode_jadwal']) ? trim($_POST['kode_jadwal']) : '';
$kodeGrup = isset($_POST['kode_grup']) ? trim($_POST['kode_grup']) : '';
$judulKegiatan = isset($_POST['judul']) ? trim($_POST['judul']) : '';
$deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
$tanggalMulai = isset($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : '';
$tanggalSelesai = isset($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : '';
$lokasi = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : '';

// Validation
if (empty($kodeJadwal) || empty($kodeGrup) || empty($judulKegiatan) || empty($tanggalMulai) || empty($tanggalSelesai)) {
    header("Location: detail_group.php?kode=$kodeGrup&msg=update_failed");
    exit;
}

$conn = getConnection();

// Check if user is member and has permission
$checkQuery = "SELECT Role FROM grup_anggota WHERE KodeGrup = ? AND NIM = ?";
$stmt = mysqli_prepare($conn, $checkQuery);
mysqli_stmt_bind_param($stmt, "ss", $kodeGrup, $nim);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    closeConnection($conn);
    header("Location: detail_group.php?kode=$kodeGrup&msg=not_member");
    exit;
}

$member = mysqli_fetch_assoc($result);
$isAdmin = ($member['Role'] === 'owner' || $member['Role'] === 'admin');

// Check if user is creator of event or admin
$eventQuery = "SELECT CreatedBy FROM grup_jadwal WHERE KodeJadwal = ?";
$stmt2 = mysqli_prepare($conn, $eventQuery);
mysqli_stmt_bind_param($stmt2, "s", $kodeJadwal);
mysqli_stmt_execute($stmt2);
$eventResult = mysqli_stmt_get_result($stmt2);

if (mysqli_num_rows($eventResult) === 0) {
    closeConnection($conn);
    header("Location: detail_group.php?kode=$kodeGrup&msg=event_not_found");
    exit;
}

$event = mysqli_fetch_assoc($eventResult);

if (!$isAdmin && $event['CreatedBy'] !== $nim) {
    closeConnection($conn);
    header("Location: detail_group.php?kode=$kodeGrup&msg=no_permission");
    exit;
}

// Update event
$updateQuery = "UPDATE grup_jadwal 
                SET JudulKegiatan = ?, Deskripsi = ?, TanggalMulai = ?, TanggalSelesai = ?, Lokasi = ?, UpdatedAt = NOW()
                WHERE KodeJadwal = ?";
$stmt3 = mysqli_prepare($conn, $updateQuery);
mysqli_stmt_bind_param($stmt3, "ssssss", $judulKegiatan, $deskripsi, $tanggalMulai, $tanggalSelesai, $lokasi, $kodeJadwal);

if (mysqli_stmt_execute($stmt3)) {
    closeConnection($conn);
    header("Location: detail_group.php?kode=$kodeGrup&msg=event_updated");
} else {
    closeConnection($conn);
    header("Location: detail_group.php?kode=$kodeGrup&msg=update_failed");
}
?>
