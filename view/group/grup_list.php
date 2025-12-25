<?php
require_once __DIR__ . '/../../config/config.php';

// Pastikan user login
if (!isset($_SESSION['nim'])) {
    echo json_encode(['error' => 'Not logged in', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$nim = $_SESSION['nim'];

// Check if tables exist
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'grup'");
if (mysqli_num_rows($checkTable) === 0) {
    echo json_encode([
        'error' => 'Tables not found',
        'message' => 'Tabel grup belum dibuat. Silakan jalankan migration SQL terlebih dahulu.',
        'success' => false,
        'count' => 0,
        'groups' => []
    ]);
    exit;
}

// Query groups where user is a member
$query = "SELECT g.*, ga.Role, 
          (SELECT COUNT(*) FROM grup_anggota WHERE KodeGrup = g.KodeGrup) as member_count,
          (SELECT COUNT(*) FROM grup_jadwal WHERE KodeGrup = g.KodeGrup) as event_count
          FROM grup g
          INNER JOIN grup_anggota ga ON g.KodeGrup = ga.KodeGrup
          WHERE ga.NIM = ?
          ORDER BY g.UpdatedAt DESC";

$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    echo json_encode([
        'error' => 'Query error',
        'message' => mysqli_error($conn),
        'success' => false,
        'count' => 0,
        'groups' => []
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $nim);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$groups = [];
while ($row = mysqli_fetch_assoc($result)) {
    $groups[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'count' => count($groups),
    'groups' => $groups
]);
?>




