<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $tipe = $_POST['tipe'];
    $nim = $_SESSION['nim'];

    // ================== EDIT JADWAL ==================
    if ($tipe == 'jadwal') {
        $id_jadwal = $_POST['id_jadwal'];
        $kelas     = mysqli_real_escape_string($conn, $_POST['kelas']);
        $hari      = $_POST['hari'];
        $ruangan   = mysqli_real_escape_string($conn, $_POST['ruangan']);
        
        // Parsing Waktu
        $waktuRaw = str_replace('.', ':', $_POST['waktu']); 
        $parts = explode('-', $waktuRaw);
        $jamMulai   = (isset($parts[0])) ? date('H:i:s', strtotime(trim($parts[0]))) : "00:00:00";
        $jamSelesai = (isset($parts[1])) ? date('H:i:s', strtotime(trim($parts[1]))) : "00:00:00";

        // Update Query
        $query = "UPDATE jadwalkuliah SET 
                  Kelas = '$kelas', 
                  Hari = '$hari', 
                  JamMulai = '$jamMulai', 
                  JamSelesai = '$jamSelesai', 
                  Ruangan = '$ruangan' 
                  WHERE KodeJadwal = '$id_jadwal' AND NIM = '$nim'";
        
        if(mysqli_query($conn, $query)) {
            header("Location: index.php?msg=edit_sukses");
        } else {
            echo "Gagal edit: " . mysqli_error($conn);
        }

    // ================== EDIT TUGAS ==================
    } elseif ($tipe == 'tugas') {
        $id_tugas = $_POST['id_tugas'];
        $judul    = mysqli_real_escape_string($conn, $_POST['judul']);
        $deadline = $_POST['deadline'] . " 23:59:00"; 
        $desc     = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        
        // Update Query
        $query = "UPDATE tugas SET 
                  JudulTugas = '$judul', 
                  Deskripsi = '$desc', 
                  Deadline = '$deadline' 
                  WHERE KodeTugas = '$id_tugas' AND NIM = '$nim'";
        
        if(mysqli_query($conn, $query)) {
            header("Location: index.php?msg=edit_sukses");
        } else {
            echo "Gagal edit: " . mysqli_error($conn);
        }
    }
}
?>