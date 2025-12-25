<?php
include 'config.php';

// Pastikan user login
if (!isset($_SESSION['nim'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$nim = $_SESSION['nim'];
$kodeGrup = isset($_GET['kode']) ? $_GET['kode'] : '';

if (empty($kodeGrup)) {
    echo json_encode(['error' => 'Group code required']);
    exit;
}

// Check if user is member
$checkQuery = "SELECT Role FROM grup_anggota WHERE KodeGrup = ? AND NIM = ?";
$stmt = mysqli_prepare($conn, $checkQuery);
mysqli_stmt_bind_param($stmt, "ss", $kodeGrup, $nim);
mysqli_stmt_execute($stmt);
$checkResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($checkResult) === 0) {
    echo json_encode(['error' => 'Not a member of this group']);
    exit;
}

$userRole = mysqli_fetch_assoc($checkResult)['Role'];

// Get group details
$grupQuery = "SELECT g.*, m.Nama as CreatorName 
              FROM grup g 
              LEFT JOIN mahasiswa m ON g.CreatedBy = m.NIM 
              WHERE g.KodeGrup = ?";
$stmt2 = mysqli_prepare($conn, $grupQuery);
mysqli_stmt_bind_param($stmt2, "s", $kodeGrup);
mysqli_stmt_execute($stmt2);
$grupResult = mysqli_stmt_get_result($stmt2);
$grupData = mysqli_fetch_assoc($grupResult);

// Get members
$membersQuery = "SELECT ga.*, m.Nama, m.Email 
                 FROM grup_anggota ga 
                 LEFT JOIN mahasiswa m ON ga.NIM = m.NIM 
                 WHERE ga.KodeGrup = ? 
                 ORDER BY 
                   CASE ga.Role 
                     WHEN 'owner' THEN 1 
                     WHEN 'admin' THEN 2 
                     ELSE 3 
                   END, 
                   ga.JoinedAt ASC";
$stmt3 = mysqli_prepare($conn, $membersQuery);
mysqli_stmt_bind_param($stmt3, "s", $kodeGrup);
mysqli_stmt_execute($stmt3);
$membersResult = mysqli_stmt_get_result($stmt3);

$members = [];
while ($row = mysqli_fetch_assoc($membersResult)) {
    $members[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'group' => $grupData,
    'userRole' => $userRole,
    'members' => $members,
    'memberCount' => count($members)
]);
?>
