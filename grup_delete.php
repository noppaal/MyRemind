<?php
include 'config.php';

if (!isset($_SESSION['nim'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$nim = $_SESSION['nim'];
$kodeGrup = isset($_POST['kode_grup']) ? $_POST['kode_grup'] : '';

if (empty($kodeGrup)) {
    echo json_encode(['error' => 'Group code required']);
    exit;
}

// Check if user is owner
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
if ($userRole !== 'owner') {
    echo json_encode(['error' => 'Only owner can delete group']);
    exit;
}

// Delete group (cascade will delete members, events, invites)
$deleteQuery = "DELETE FROM grup WHERE KodeGrup = ?";
$stmt2 = mysqli_prepare($conn, $deleteQuery);
mysqli_stmt_bind_param($stmt2, "s", $kodeGrup);

if (mysqli_stmt_execute($stmt2)) {
    header("Location: index.php?tab=grup&msg=group_deleted");
} else {
    header("Location: detail_group.php?kode=$kodeGrup&msg=group_delete_failed");
}
?>
