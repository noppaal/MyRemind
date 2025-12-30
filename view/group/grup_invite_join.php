<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../model/Database.php';

$conn = getConnection();

if (!isset($_SESSION['nim'])) {
    header("Location: ../../public/login.php");
    exit;
}

$nim = $_SESSION['nim'];
$inviteCode = isset($_POST['invite_code']) ? trim(strtoupper($_POST['invite_code'])) : '';

if (empty($inviteCode)) {
    header("Location: ../../public/index.php?msg=invite_code_required");
    exit;
}

// Get invite details
$inviteQuery = "SELECT * FROM grup_invite WHERE InviteCode = ?";
$stmt = mysqli_prepare($conn, $inviteQuery);
mysqli_stmt_bind_param($stmt, "s", $inviteCode);
mysqli_stmt_execute($stmt);
$inviteResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($inviteResult) === 0) {
    header("Location: ../../public/index.php?msg=invite_invalid");
    exit;
}

$invite = mysqli_fetch_assoc($inviteResult);

// Check expiration
if ($invite['ExpiresAt'] && strtotime($invite['ExpiresAt']) < time()) {
    header("Location: ../../public/index.php?msg=invite_expired");
    exit;
}

// Check max uses
if ($invite['MaxUses'] && $invite['UsedCount'] >= $invite['MaxUses']) {
    header("Location: ../../public/index.php?msg=invite_limit_reached");
    exit;
}

$kodeGrup = $invite['KodeGrup'];

// Check if already member
$checkQuery = "SELECT ID FROM grup_anggota WHERE KodeGrup = ? AND NIM = ?";
$stmt2 = mysqli_prepare($conn, $checkQuery);
mysqli_stmt_bind_param($stmt2, "ss", $kodeGrup, $nim);
mysqli_stmt_execute($stmt2);

if (mysqli_stmt_get_result($stmt2)->num_rows > 0) {
    header("Location: ../../public/index.php?tab=grup&group=$kodeGrup&msg=already_member");
    exit;
}

// Add member
$insertQuery = "INSERT INTO grup_anggota (KodeGrup, NIM, Role) VALUES (?, ?, 'member')";
$stmt3 = mysqli_prepare($conn, $insertQuery);
mysqli_stmt_bind_param($stmt3, "ss", $kodeGrup, $nim);

if (mysqli_stmt_execute($stmt3)) {
    // Increment usage count
    $updateQuery = "UPDATE grup_invite SET UsedCount = UsedCount + 1 WHERE ID = ?";
    $stmt4 = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt4, "i", $invite['ID']);
    mysqli_stmt_execute($stmt4);
    
    header("Location: ../../public/index.php?tab=grup&group=$kodeGrup&msg=joined_group");
} else {
    header("Location: ../../public/index.php?msg=join_failed");
}
?>




