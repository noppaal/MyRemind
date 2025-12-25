<?php
include 'config.php';

if (!isset($_SESSION['nim'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$nim = $_SESSION['nim'];
$kodeGrup = isset($_POST['kode_grup']) ? $_POST['kode_grup'] : '';
$nimAnggota = isset($_POST['nim_anggota']) ? $_POST['nim_anggota'] : '';

if (empty($kodeGrup) || empty($nimAnggota)) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// Check if user is admin or owner
$roleQuery = "SELECT Role FROM grup_anggota WHERE KodeGrup = ? AND NIM = ?";
$stmt = mysqli_prepare($conn, $roleQuery);
mysqli_stmt_bind_param($stmt, "ss", $kodeGrup, $nim);
mysqli_stmt_execute($stmt);
$roleResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($roleResult) === 0) {
    echo json_encode(['error' => 'Not a member']);
    exit;
}

$userRole = mysqli_fetch_assoc($roleResult)['Role'];
if ($userRole !== 'owner' && $userRole !== 'admin') {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Check if member exists
$checkMember = "SELECT NIM FROM mahasiswa WHERE NIM = ?";
$stmt2 = mysqli_prepare($conn, $checkMember);
mysqli_stmt_bind_param($stmt2, "s", $nimAnggota);
mysqli_stmt_execute($stmt2);
if (mysqli_stmt_get_result($stmt2)->num_rows === 0) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Check if already member
$checkExisting = "SELECT ID FROM grup_anggota WHERE KodeGrup = ? AND NIM = ?";
$stmt3 = mysqli_prepare($conn, $checkExisting);
mysqli_stmt_bind_param($stmt3, "ss", $kodeGrup, $nimAnggota);
mysqli_stmt_execute($stmt3);
if (mysqli_stmt_get_result($stmt3)->num_rows > 0) {
    echo json_encode(['error' => 'Already a member']);
    exit;
}

// Add member
$insertQuery = "INSERT INTO grup_anggota (KodeGrup, NIM, Role) VALUES (?, ?, 'member')";
$stmt4 = mysqli_prepare($conn, $insertQuery);
mysqli_stmt_bind_param($stmt4, "ss", $kodeGrup, $nimAnggota);

if (mysqli_stmt_execute($stmt4)) {
    header("Location: detail_group.php?kode=$kodeGrup&msg=member_added");
} else {
    header("Location: detail_group.php?kode=$kodeGrup&msg=member_add_failed");
}
?>
