<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $tipe = $_POST['tipe'];
    $nim = $_SESSION['nim']; // Pastikan sesi aktif

    // ================== TAMBAH JADWAL ==================
    if ($tipe == 'jadwal') {
        $kodeMK  = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['kode_mk']))); 
        $namaMK  = mysqli_real_escape_string($conn, $_POST['nama_mk']);
        $hari    = $_POST['hari'];
        $ruangan = mysqli_real_escape_string($conn, $_POST['ruangan']);
        $dosen   = mysqli_real_escape_string($conn, $_POST['dosen']);
        
        // 1. PARSING WAKTU (08.00 -> 08:00)
        $waktuRaw = str_replace('.', ':', $_POST['waktu']); 
        $parts = explode('-', $waktuRaw);
        $jamMulai   = (isset($parts[0]) && !empty(trim($parts[0]))) ? date('H:i:s', strtotime(trim($parts[0]))) : "00:00:00";
        $jamSelesai = (isset($parts[1]) && !empty(trim($parts[1]))) ? date('H:i:s', strtotime(trim($parts[1]))) : "00:00:00";

        // 2. LOGIKA DOSEN (LEBIH AMAN)
        $kdDosen = null; // Default NULL (Bukan "DSN-UNK" agar tidak error FK)

        if(!empty($dosen)) {
            // Cek apakah dosen sudah ada (berdasarkan Nama)
            $cekDosen = mysqli_query($conn, "SELECT KodeDosen FROM dosen WHERE NamaDosen LIKE '%$dosen%' LIMIT 1");
            
            if (mysqli_num_rows($cekDosen) > 0) {
                // Jika ada, ambil kodenya
                $kdDosen = mysqli_fetch_assoc($cekDosen)['KodeDosen'];
            } else {
                // Jika tidak ada, Buat Baru
                $kdDosen = "DSN" . time(); // Pakai timestamp agar Kode unik
                $emailDosen = "dosen." . uniqid() . "@telkom.ac.id"; // Pakai uniqid agar Email unik
                
                $queryInsertDosen = "INSERT INTO dosen (KodeDosen, NamaDosen, Email) VALUES ('$kdDosen', '$dosen', '$emailDosen')";
                if(!mysqli_query($conn, $queryInsertDosen)){
                    // Jika gagal insert dosen, biarkan NULL agar aplikasi tidak crash
                    $kdDosen = null; 
                }
            }
        }

        // 3. LOGIKA MATA KULIAH
        // Cek apakah MK sudah ada
        $cekMK = mysqli_query($conn, "SELECT KodeMK FROM matakuliah WHERE KodeMK = '$kodeMK'");
        
        if (mysqli_num_rows($cekMK) == 0) {
            // Siapkan value untuk SQL (NULL atau 'KODE')
            $sqlKodeDosen = $kdDosen ? "'$kdDosen'" : "NULL";
            
            // Insert MK Baru
            $queryMK = "INSERT INTO matakuliah (KodeMK, NamaMK, SKS, KodeDosen) VALUES ('$kodeMK', '$namaMK', 3, $sqlKodeDosen)";
            
            if (!mysqli_query($conn, $queryMK)) {
                die("<h3>Error Sistem</h3><p>Gagal membuat Mata Kuliah: " . mysqli_error($conn) . "</p><a href='index.php'>Kembali</a>");
            }
        }

        // 4. INSERT JADWAL
        $query = "INSERT INTO jadwalkuliah (KodeMK, NIM, Hari, JamMulai, JamSelesai, Ruangan) 
                  VALUES ('$kodeMK', '$nim', '$hari', '$jamMulai', '$jamSelesai', '$ruangan')";
        
        if(mysqli_query($conn, $query)) {
            header("Location: index.php?msg=jadwal_sukses");
        } else {
            echo "<h3>Gagal Menambah Jadwal</h3>";
            echo "<p>Error: " . mysqli_error($conn) . "</p>";
            echo "<a href='index.php'>Kembali</a>";
        }

    // ================== TAMBAH TUGAS ==================
    } elseif ($tipe == 'tugas') {
        $judul = mysqli_real_escape_string($conn, $_POST['judul']);
        $namaMKInput = mysqli_real_escape_string($conn, $_POST['nama_mk']);
        $deadline = $_POST['deadline'] . " 23:59:00"; 
        $desc = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        $prioritas = $_POST['prioritas'] ?? 'Sedang'; // Default Sedang
        
        // Cari Kode MK
        $cariMK = mysqli_query($conn, "SELECT KodeMK FROM matakuliah WHERE NamaMK LIKE '%$namaMKInput%' LIMIT 1");
        if(mysqli_num_rows($cariMK) > 0){
            $kodeMK = mysqli_fetch_assoc($cariMK)['KodeMK'];
        } else {
            $kodeMK = 'IF101'; // Fallback ke default jika MK tidak ditemukan
        }

        $kodeTugas = "MANUAL-" . time(); 

        $query = "INSERT INTO tugas (KodeTugas, NIM, KodeMK, JudulTugas, Deskripsi, Deadline, JenisTugas, StatusTugas)
                  VALUES ('$kodeTugas', '$nim', '$kodeMK', '$judul', '$desc', '$deadline', 'Individu', 'Aktif')";
        
        if(mysqli_query($conn, $query)) {
            header("Location: index.php?msg=tugas_sukses");
        } else {
            echo "<h3>Gagal Menambah Tugas</h3>";
            echo "<p>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>