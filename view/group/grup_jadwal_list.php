<?php
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['nim'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$nim = $_SESSION['nim'];
$kodeGrup = isset($_GET['kode_grup']) ? $_GET['kode_grup'] : '';

if (empty($kodeGrup)) {
    echo json_encode(['error' => 'Group code required']);
    exit;
}

// Check if user is member
$memberQuery = "SELECT Role FROM grup_anggota WHERE KodeGrup = ? AND NIM = ?";
$stmt = mysqli_prepare($conn, $memberQuery);
mysqli_stmt_bind_param($stmt, "ss", $kodeGrup, $nim);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_get_result($stmt)->num_rows === 0) {
    echo json_encode(['error' => 'Not a member']);
    exit;
}

// Get events
$query = "SELECT gj.*, m.Nama as CreatorName 
          FROM grup_jadwal gj 
          LEFT JOIN mahasiswa m ON gj.CreatedBy = m.NIM 
          WHERE gj.KodeGrup = ? 
          ORDER BY gj.TanggalMulai ASC";

$stmt2 = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt2, "s", $kodeGrup);
mysqli_stmt_execute($stmt2);
$result = mysqli_stmt_get_result($stmt2);

$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $events[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'count' => count($events),
    'events' => $events
]);
?>




