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

// Check if user is member
$roleQuery = "SELECT Role FROM grup_anggota WHERE KodeGrup = ? AND NIM = ?";
$stmt = mysqli_prepare($conn, $roleQuery);
mysqli_stmt_bind_param($stmt, "ss", $kodeGrup, $nim);
mysqli_stmt_execute($stmt);
$roleResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($roleResult) === 0) {
    echo json_encode(['error' => 'Not a member']);
    exit;
}

// Generate unique invite code
$inviteCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

// Set expiration (7 days from now)
$expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

// Insert invite
$insertQuery = "INSERT INTO grup_invite (KodeGrup, InviteCode, CreatedBy, ExpiresAt) VALUES (?, ?, ?, ?)";
$stmt2 = mysqli_prepare($conn, $insertQuery);
mysqli_stmt_bind_param($stmt2, "ssss", $kodeGrup, $inviteCode, $nim, $expiresAt);

if (mysqli_stmt_execute($stmt2)) {
    echo json_encode([
        'success' => true,
        'inviteCode' => $inviteCode,
        'expiresAt' => $expiresAt,
        'inviteLink' => 'index.php?join=' . $inviteCode
    ]);
} else {
    echo json_encode(['error' => 'Failed to create invite']);
}
?>
