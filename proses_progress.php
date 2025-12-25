<?php
include 'config.php';

// Pastikan user login & ada ID yang dikirim
if (isset($_GET['id']) && isset($_SESSION['nim'])) {
    $id_tugas = mysqli_real_escape_string($conn, $_GET['id']);
    $nim = $_SESSION['nim'];

    // Update status tugas menjadi 'In Progress'
    $query = "UPDATE tugas SET StatusTugas = 'In Progress' WHERE KodeTugas = '$id_tugas' AND NIM = '$nim'";

    if (mysqli_query($conn, $query)) {
        // Cek apakah ada row yang ter-update
        if (mysqli_affected_rows($conn) > 0) {
            header("Location: index.php?tab=tugas&msg=progress_sukses");
        } else {
            header("Location: index.php?tab=tugas&msg=progress_no_change");
        }
    } else {
        // Jika error, kemungkinan migration belum dijalankan
        $error = mysqli_error($conn);
        if (strpos($error, 'Data truncated') !== false || strpos($error, 'StatusTugas') !== false) {
            header("Location: index.php?tab=tugas&msg=migration_required");
        } else {
            header("Location: index.php?tab=tugas&msg=progress_error");
        }
    }
} else {
    header("Location: index.php?tab=tugas");
}
?>

