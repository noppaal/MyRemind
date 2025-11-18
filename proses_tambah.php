<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $tipe = $_POST['tipe'];
    $nim = $_SESSION['nim']; 

    // ================== TAMBAH JADWAL ==================
    if ($tipe == 'jadwal') {
        // Menerima 'kelas' bukan 'kode_mk'
        $kelas   = mysqli_real_escape_string($conn, $_POST['kelas']); 
        $namaMK  = mysqli_real_escape_string($conn, $_POST['nama_mk']);
        $hari    = $_POST['hari'];
        $ruangan = mysqli_real_escape_string($conn, $_POST['ruangan']);
        $dosen   = mysqli_real_escape_string($conn, $_POST['dosen']);
        
        // Parsing Waktu (08.00 -> 08:00)
        $waktuRaw = str_replace('.', ':', $_POST['waktu']); 
        $parts = explode('-', $waktuRaw);
        $jamMulai   = (isset($parts[0]) && !empty(trim($parts[0]))) ? date('H:i:s', strtotime(trim($parts[0]))) : "00:00:00";
        $jamSelesai = (isset($parts[1]) && !empty(trim($parts[1]))) ? date('H:i:s', strtotime(trim($parts[1]))) : "00:00:00";

        // Logika Dosen
        $kdDosen = null;
        if(!empty($dosen)) {
            $cekDosen = mysqli_query($conn, "SELECT KodeDosen FROM dosen WHERE NamaDosen LIKE '%$dosen%' LIMIT 1");
            if (mysqli_num_rows($cekDosen) > 0) {
                $kdDosen = mysqli_fetch_assoc($cekDosen)['KodeDosen'];
            } else {
                $kdDosen = "DSN" . time(); 
                $emailDosen = "dosen." . uniqid() . "@telkom.ac.id";
                $queryInsertDosen = "INSERT INTO dosen (KodeDosen, NamaDosen, Email) VALUES ('$kdDosen', '$dosen', '$emailDosen')";
                if(!mysqli_query($conn, $queryInsertDosen)) $kdDosen = null; 
            }
        }

        // Logika MK (Cari by Nama, Create if not exists)
        $cekMK = mysqli_query($conn, "SELECT KodeMK FROM matakuliah WHERE NamaMK = '$namaMK' LIMIT 1");
        if (mysqli_num_rows($cekMK) > 0) {
            $kodeMK = mysqli_fetch_assoc($cekMK)['KodeMK'];
        } else {
            $kodeMK = "MK-" . strtoupper(substr(md5($namaMK . time()), 0, 6));
            $sqlKodeDosen = $kdDosen ? "'$kdDosen'" : "NULL";
            $queryMK = "INSERT INTO matakuliah (KodeMK, NamaMK, SKS, KodeDosen) VALUES ('$kodeMK', '$namaMK', 3, $sqlKodeDosen)";
            mysqli_query($conn, $queryMK);
        }

        // Insert Jadwal (Termasuk Kelas)
        $query = "INSERT INTO jadwalkuliah (KodeMK, NIM, Kelas, Hari, JamMulai, JamSelesai, Ruangan) 
                  VALUES ('$kodeMK', '$nim', '$kelas', '$hari', '$jamMulai', '$jamSelesai', '$ruangan')";
        
        if(mysqli_query($conn, $query)) {
            header("Location: index.php?msg=jadwal_sukses");
        } else {
            echo "<h3>Gagal Menambah Jadwal</h3><p>Error: " . mysqli_error($conn) . "</p><a href='index.php'>Kembali</a>";
        }

    // ================== TAMBAH TUGAS ==================
    } elseif ($tipe == 'tugas') {
        $judul = mysqli_real_escape_string($conn, $_POST['judul']);
        $namaMKInput = mysqli_real_escape_string($conn, $_POST['nama_mk']);
        $deadline = $_POST['deadline'] . " 23:59:00"; 
        $desc = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        $prioritas = $_POST['prioritas'] ?? 'Sedang';
        
        // Cari Kode MK dari Nama
        $cariMK = mysqli_query($conn, "SELECT KodeMK FROM matakuliah WHERE NamaMK LIKE '%$namaMKInput%' LIMIT 1");
        $kodeMK = (mysqli_num_rows($cariMK) > 0) ? mysqli_fetch_assoc($cariMK)['KodeMK'] : 'IF101'; // Default IF101 jika tak ada
        $kodeTugas = "MANUAL-" . time(); 

        $query = "INSERT INTO tugas (KodeTugas, NIM, KodeMK, JudulTugas, Deskripsi, Deadline, JenisTugas, StatusTugas)
                  VALUES ('$kodeTugas', '$nim', '$kodeMK', '$judul', '$desc', '$deadline', 'Individu', 'Aktif')";
        
        if(mysqli_query($conn, $query)) header("Location: index.php?msg=tugas_sukses");
        else echo "Error: " . mysqli_error($conn);
    }
}
?>