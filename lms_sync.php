<?php
include 'config.php';

function parseICS($url) {
    $icsContent = @file_get_contents($url);
    if (!$icsContent) return false;
    $events = explode("BEGIN:VEVENT", $icsContent);
    $parsedEvents = [];
    foreach($events as $event) {
        preg_match('/SUMMARY:(.*?)\n/', $event, $summary);
        preg_match('/DTSTART:(.*?)\n/', $event, $dtstart);
        if(isset($summary[1]) && isset($dtstart[1])) {
            $parsedEvents[] = ['judul' => trim($summary[1]), 'waktu' => trim($dtstart[1])];
        }
    }
    return $parsedEvents;
}

if (isset($_POST['sync_lms']) && isset($_SESSION['nim'])) {
    $lmsUrl = $_POST['lms_url'];
    $nim = $_SESSION['nim'];
    
    $dataTugas = parseICS($lmsUrl);

    if ($dataTugas) {
        foreach ($dataTugas as $tugas) {
            $timeRaw = str_replace(['T', 'Z'], [' ', ''], $tugas['waktu']); 
            $deadline = date('Y-m-d H:i:s', strtotime($timeRaw));
            $kodeTugas = "LMS" . rand(1000,9999); 
            $judul = mysqli_real_escape_string($conn, $tugas['judul']);
            
            // INSERT DENGAN NIM
            $sql = "INSERT INTO tugas (KodeTugas, NIM, KodeMK, JudulTugas, Deskripsi, Deadline, JenisTugas, StatusTugas)
                    VALUES ('$kodeTugas', '$nim', 'IF101', '$judul', 'Imported from LMS', '$deadline', 'Individu', 'Aktif')";
            mysqli_query($conn, $sql);
        }
        header("Location: index.php?msg=sukses_sync");
    } else {
        header("Location: index.php?msg=gagal_sync");
    }
}
?>