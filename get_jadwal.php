<?php
include 'config.php';

// Pastikan user login
if (!isset($_SESSION['nim'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$nim = $_SESSION['nim'];
$hari = isset($_GET['hari']) ? $_GET['hari'] : '';

if (empty($hari)) {
    echo json_encode(['error' => 'Hari not specified']);
    exit;
}

// Query jadwal for selected day
$query = "SELECT j.*, m.NamaMK, d.NamaDosen 
          FROM jadwalkuliah j 
          LEFT JOIN matakuliah m ON j.KodeMK = m.KodeMK 
          LEFT JOIN dosen d ON m.KodeDosen = d.KodeDosen
          WHERE j.NIM = ? AND j.Hari = ? 
          ORDER BY j.JamMulai ASC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $nim, $hari);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$jadwal = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Format time
    $row['JamMulai'] = date('H:i', strtotime($row['JamMulai']));
    $row['JamSelesai'] = date('H:i', strtotime($row['JamSelesai']));
    $jadwal[] = $row;
}

$response = [
    'count' => count($jadwal),
    'jadwal' => $jadwal,
    'hari' => $hari
];

header('Content-Type: application/json');
echo json_encode($response);
?>
