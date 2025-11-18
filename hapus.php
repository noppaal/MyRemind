<?php
include 'config.php';

// Cek Login & Parameter URL
if (isset($_SESSION['nim']) && isset($_GET['id']) && isset($_GET['type'])) {
    
    $nim = $_SESSION['nim'];
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $type = $_GET['type'];

    if ($type == 'tugas') {
        // Hapus Tugas (Hanya jika milik user yang sedang login)
        $query = "DELETE FROM tugas WHERE KodeTugas = '$id' AND NIM = '$nim'";
        
        if (mysqli_query($conn, $query)) {
            header("Location: index.php?msg=hapus_sukses");
        } else {
            echo "Gagal menghapus tugas: " . mysqli_error($conn);
        }

    } elseif ($type == 'jadwal') {
        // Hapus Jadwal (Hanya jika milik user yang sedang login)
        $query = "DELETE FROM jadwalkuliah WHERE KodeJadwal = '$id' AND NIM = '$nim'";
        
        if (mysqli_query($conn, $query)) {
            header("Location: index.php?msg=hapus_sukses");
        } else {
            echo "Gagal menghapus jadwal: " . mysqli_error($conn);
        }
    }
} else {
    // Jika akses langsung tanpa parameter
    header("Location: index.php");
}
?>