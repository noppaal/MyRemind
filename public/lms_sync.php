<?php
require_once __DIR__ . '/../config/config.php';

// Fungsi Parsing iCalendar (ICS)
function parseICS($url) {
    // Gunakan @ untuk menyembunyikan warning jika URL mati/kosong
    $icsContent = @file_get_contents($url);
    
    if (!$icsContent) return false;

    // Memecah berdasarkan event
    $events = explode("BEGIN:VEVENT", $icsContent);
    $parsedEvents = [];

    foreach($events as $event) {
        // Regex untuk mengambil Summary (Judul) dan DTSTART (Waktu)
        preg_match('/SUMMARY:(.*?)\n/', $event, $summary);
        preg_match('/DTSTART:(.*?)\n/', $event, $dtstart);
        preg_match('/DESCRIPTION:(.*?)\n/', $event, $desc);

        if(isset($summary[1]) && isset($dtstart[1])) {
            $parsedEvents[] = [
                'judul' => trim($summary[1]),
                'waktu' => trim($dtstart[1]), // Format ICS: YYYYMMDDTHHMMSS
                'deskripsi' => isset($desc[1]) ? trim($desc[1]) : ''
            ];
        }
    }
    return $parsedEvents;
}

if (isset($_POST['sync_lms']) && isset($_SESSION['nim'])) {
    $lmsUrl = $_POST['lms_url'];
    $nim = $_SESSION['nim'];
    
    $dataTugas = parseICS($lmsUrl);

    if ($dataTugas) {
        $countSukses = 0;
        
        // --- 1. AMBIL DAFTAR MATA KULIAH DARI DB UTK PENCOCOKAN ---
        $listMK = [];
        $queryMK = mysqli_query($conn, "SELECT KodeMK, NamaMK FROM matakuliah");
        while($row = mysqli_fetch_assoc($queryMK)){
            $listMK[$row['KodeMK']] = strtolower($row['NamaMK']);
        }

        foreach ($dataTugas as $tugas) {
            // Konversi Waktu
            $timeRaw = str_replace(['T', 'Z'], [' ', ''], $tugas['waktu']); 
            $deadline = date('Y-m-d H:i:s', strtotime($timeRaw));
            
            $judul = mysqli_real_escape_string($conn, $tugas['judul']);
            $deskripsi = mysqli_real_escape_string($conn, $tugas['deskripsi']);
            $judulLower = strtolower($judul);

            // --- 2. LOGIKA SMART MATCH (Menebak MK) ---
            $kodeMKSelected = 'GENERAL'; // Default ke 'Umum' (Sesuai SQL diatas)
            
            foreach($listMK as $kode => $nama) {
                // Pecah nama MK jadi kata kunci (misal: "Pemrograman Web" -> "Pemrograman", "Web")
                $keywords = explode(' ', $nama);
                foreach($keywords as $word) {
                    // Abaikan kata pendek (dan, di, ke, dll)
                    if(strlen($word) < 3) continue; 
                    
                    // Jika Judul Tugas mengandung kata kunci MK
                    if (strpos($judulLower, $word) !== false) {
                        $kodeMKSelected = $kode;
                        break 2; // Ketemu! Keluar dari loop
                    }
                }
            }

            // --- 3. CEK DUPLIKAT ---
            $cekDuplikat = mysqli_query($conn, "SELECT KodeTugas FROM tugas 
                                                WHERE NIM = '$nim' 
                                                AND JudulTugas = '$judul' 
                                                AND Deadline = '$deadline'");

            if (mysqli_num_rows($cekDuplikat) == 0) {
                // Generate ID Unik
                $kodeTugas = "LMS-" . uniqid(); 
                
                // Insert dengan Kode MK hasil tebakan
                $sql = "INSERT INTO tugas (KodeTugas, NIM, KodeMK, JudulTugas, Deskripsi, Deadline, JenisTugas, StatusTugas)
                        VALUES ('$kodeTugas', '$nim', '$kodeMKSelected', '$judul', '$deskripsi', '$deadline', 'Individu', 'Aktif')";
                
                if(mysqli_query($conn, $sql)) {
                    $countSukses++;
                }
            }
        }
        header("Location: index.php?msg=sukses_sync&count=$countSukses");
    } else {
        header("Location: index.php?msg=gagal_sync");
    }
} else {
    header("Location: index.php");
}
?>

