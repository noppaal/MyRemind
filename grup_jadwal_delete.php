<?php
include 'config.php';

if (!isset($_SESSION['nim'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$nim = $_SESSION['nim'];
$kodeJadwal = isset($_POST['kode_jadwal']) ? $_POST['kode_jadwal'] : '';

if (empty($kodeJadwal)) {
    echo json_encode(['error' => 'Event code required']);
    exit;
}

// Get event and check permissions
$eventQuery = "SELECT gj.*, ga.Role 
               FROM grup_jadwal gj
               LEFT JOIN grup_anggota ga ON gj.KodeGrup = ga.KodeGrup AND ga.NIM = ?
               WHERE gj.KodeJadwal = ?";
$stmt = mysqli_prepare($conn, $eventQuery);
mysqli_stmt_bind_param($stmt, "ss", $nim, $kodeJadwal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['error' => 'Event not found or not a member']);
    exit;
}

$event = mysqli_fetch_assoc($result);

// Only creator or admin/owner can delete
if ($event['CreatedBy'] !== $nim && $event['Role'] !== 'admin' && $event['Role'] !== 'owner') {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Delete event
$deleteQuery = "DELETE FROM grup_jadwal WHERE KodeJadwal = ?";
$stmt2 = mysqli_prepare($conn, $deleteQuery);
mysqli_stmt_bind_param($stmt2, "s", $kodeJadwal);

if (mysqli_stmt_execute($stmt2)) {
    header("Location: detail_group.php?kode=" . $event['KodeGrup'] . "&msg=event_deleted");
} else {
    header("Location: detail_group.php?kode=" . $event['KodeGrup'] . "&msg=event_delete_failed");
}
?>
