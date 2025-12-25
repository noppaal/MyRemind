<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $tipe = $_POST['type'];
    $nim = $_SESSION['nim']; 

    // ================== TAMBAH JADWAL ==================
    if ($tipe == 'jadwal') {
        // Ambil data dari form
        $namaMK  = mysqli_real_escape_string($conn, $_POST['matakuliah']);
        $hari    = $_POST['hari'];
        $ruangan = mysqli_real_escape_string($conn, $_POST['ruangan'] ?? '');
        $jamMulai   = $_POST['jam_mulai'] . ':00';
        $jamSelesai = $_POST['jam_selesai'] . ':00';

        // Logika Dosen (set default atau null)
        $kdDosen = null;

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

        // Insert Jadwal
        $query = "INSERT INTO jadwalkuliah (KodeMK, NIM, Hari, JamMulai, JamSelesai, Ruangan) 
                  VALUES ('$kodeMK', '$nim', '$hari', '$jamMulai', '$jamSelesai', '$ruangan')";
        
        if(mysqli_query($conn, $query)) {
            header("Location: index.php?tab=kalender&msg=jadwal_sukses");
        } else {
            echo "<h3>Gagal Menambah Jadwal</h3><p>Error: " . mysqli_error($conn) . "</p><a href='index.php'>Kembali</a>";
        }

    // ================== TAMBAH TUGAS ==================
    } elseif ($tipe == 'tugas') {
        $judul = mysqli_real_escape_string($conn, $_POST['judul']);
        $namaMKInput = mysqli_real_escape_string($conn, $_POST['matakuliah'] ?? '');
        $deadline = $_POST['deadline']; 
        $desc = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
        $prioritas = 'Sedang';
        
        // Cari Kode MK dari Nama atau buat baru
        if (!empty($namaMKInput)) {
            $cariMK = mysqli_query($conn, "SELECT KodeMK FROM matakuliah WHERE NamaMK LIKE '%$namaMKInput%' LIMIT 1");
            if (mysqli_num_rows($cariMK) > 0) {
                $kodeMK = mysqli_fetch_assoc($cariMK)['KodeMK'];
            } else {
                // Buat MK baru jika tidak ada
                $kodeMK = "MK-" . strtoupper(substr(md5($namaMKInput . time()), 0, 6));
                $queryMK = "INSERT INTO matakuliah (KodeMK, NamaMK, SKS) VALUES ('$kodeMK', '$namaMKInput', 3)";
                mysqli_query($conn, $queryMK);
            }
        } else {
            $kodeMK = null; // Tugas tanpa mata kuliah
        }
        $kodeTugas = "MANUAL-" . time(); 

        $kodeMKValue = $kodeMK ? "'$kodeMK'" : "NULL";
        $query = "INSERT INTO tugas (KodeTugas, NIM, KodeMK, JudulTugas, Deskripsi, Deadline, JenisTugas, StatusTugas)
                  VALUES ('$kodeTugas', '$nim', $kodeMKValue, '$judul', '$desc', '$deadline', 'Individu', 'Aktif')";
        
        if(mysqli_query($conn, $query)) header("Location: index.php?tab=tugas&msg=tugas_sukses");
        else echo "Error: " . mysqli_error($conn);
    }
}
?>