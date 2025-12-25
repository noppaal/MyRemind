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

// Check if trying to remove last owner
$ownerCount = "SELECT COUNT(*) as count FROM grup_anggota WHERE KodeGrup = ? AND Role = 'owner'";
$stmt2 = mysqli_prepare($conn, $ownerCount);
mysqli_stmt_bind_param($stmt2, "s", $kodeGrup);
mysqli_stmt_execute($stmt2);
$ownerResult = mysqli_stmt_get_result($stmt2);
$owners = mysqli_fetch_assoc($ownerResult)['count'];

$memberRole = "SELECT Role FROM grup_anggota WHERE KodeGrup = ? AND NIM = ?";
$stmt3 = mysqli_prepare($conn, $memberRole);
mysqli_stmt_bind_param($stmt3, "ss", $kodeGrup, $nimAnggota);
mysqli_stmt_execute($stmt3);
$memberResult = mysqli_stmt_get_result($stmt3);

if (mysqli_num_rows($memberResult) > 0) {
    $role = mysqli_fetch_assoc($memberResult)['Role'];
    if ($role === 'owner' && $owners <= 1) {
        echo json_encode(['error' => 'Cannot remove last owner']);
        exit;
    }
}

// Remove member
$deleteQuery = "DELETE FROM grup_anggota WHERE KodeGrup = ? AND NIM = ?";
$stmt4 = mysqli_prepare($conn, $deleteQuery);
mysqli_stmt_bind_param($stmt4, "ss", $kodeGrup, $nimAnggota);

if (mysqli_stmt_execute($stmt4)) {
    header("Location: detail_group.php?kode=$kodeGrup&msg=member_removed");
} else {
    header("Location: detail_group.php?kode=$kodeGrup&msg=member_remove_failed");
}
?>
