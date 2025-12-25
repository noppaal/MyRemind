<?php
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: setting_profile.php?msg=invalid_request");
    exit;
}

$nim = $_SESSION['nim'];
$oldPassword = isset($_POST['old_password']) ? $_POST['old_password'] : '';
$newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validate inputs
if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
    header("Location: setting_profile.php?msg=empty_fields");
    exit;
}

// Validate new password length
if (strlen($newPassword) < 6) {
    header("Location: setting_profile.php?msg=password_too_short");
    exit;
}

// Validate password confirmation
if ($newPassword !== $confirmPassword) {
    header("Location: setting_profile.php?msg=password_mismatch");
    exit;
}

// Get current password from database
$query = "SELECT Password FROM mahasiswa WHERE NIM = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $nim);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: logout.php");
    exit;
}

$user = mysqli_fetch_assoc($result);

// Verify old password
if (!password_verify($oldPassword, $user['Password'])) {
    header("Location: setting_profile.php?msg=wrong_old_password");
    exit;
}

// Hash new password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update password
$updateQuery = "UPDATE mahasiswa SET Password = ? WHERE NIM = ?";
$stmtUpdate = mysqli_prepare($conn, $updateQuery);
mysqli_stmt_bind_param($stmtUpdate, "ss", $hashedPassword, $nim);

if (mysqli_stmt_execute($stmtUpdate)) {
    header("Location: setting_profile.php?msg=password_changed");
} else {
    header("Location: setting_profile.php?msg=change_failed");
}
?>


