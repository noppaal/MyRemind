<?php
include 'config.php';

// Pastikan user login
if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}

$nim = $_SESSION['nim'];

// Validasi input
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?msg=invalid_request");
    exit;
}

$namaGrup = isset($_POST['nama_grup']) ? trim($_POST['nama_grup']) : '';
$deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';

// Validasi nama grup
if (empty($namaGrup)) {
    header("Location: index.php?msg=grup_name_required");
    exit;
}

if (strlen($namaGrup) > 100) {
    header("Location: index.php?msg=grup_name_too_long");
    exit;
}

// Generate unique KodeGrup
$kodeGrup = 'GRP-' . time() . '-' . substr(md5(uniqid()), 0, 6);

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Insert grup
    $stmt = mysqli_prepare($conn, "INSERT INTO grup (KodeGrup, NamaGrup, Deskripsi, CreatedBy) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $kodeGrup, $namaGrup, $deskripsi, $nim);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to create group");
    }
    
    // Add creator as owner
    $stmt2 = mysqli_prepare($conn, "INSERT INTO grup_anggota (KodeGrup, NIM, Role) VALUES (?, ?, 'owner')");
    mysqli_stmt_bind_param($stmt2, "ss", $kodeGrup, $nim);
    
    if (!mysqli_stmt_execute($stmt2)) {
        throw new Exception("Failed to add owner");
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Redirect to group detail
    header("Location: index.php?tab=grup&group=$kodeGrup&msg=grup_created");
    
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    header("Location: index.php?msg=grup_create_failed");
}
?>
