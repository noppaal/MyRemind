<?php
include 'config.php';

// Pastikan user login & ada ID yang dikirim
if (isset($_GET['id']) && isset($_SESSION['nim'])) {
    $id_tugas = mysqli_real_escape_string($conn, $_GET['id']);
    $nim = $_SESSION['nim'];

    // Update status tugas menjadi 'Selesai'
    // Kita tambahkan filter NIM agar user tidak bisa menyelesaikan tugas orang lain
    $query = "UPDATE tugas SET StatusTugas = 'Selesai' WHERE KodeTugas = '$id_tugas' AND NIM = '$nim'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php?tab=tugas&msg=selesai_sukses");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php?tab=tugas");
}
?>