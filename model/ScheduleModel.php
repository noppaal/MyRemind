<?php
/**
 * ScheduleModel - Model untuk Jadwal Kuliah
 * Mengelola data jadwal kuliah mahasiswa
 */

require_once __DIR__ . '/Database.php';

function getAllSchedules($nim) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    
    $query = "SELECT j.*, m.NamaMK, d.NamaDosen 
              FROM jadwalkuliah j 
              LEFT JOIN matakuliah m ON j.KodeMK = m.KodeMK 
              LEFT JOIN dosen d ON m.KodeDosen = d.KodeDosen
              WHERE j.NIM = '$nim' 
              ORDER BY FIELD(j.Hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), j.JamMulai ASC";
    
    $result = mysqli_query($conn, $query);
    $schedules = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $schedules[] = $row;
    }
    
    closeConnection($conn);
    return $schedules;
}

function getSchedulesByDay($nim, $day) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    $day = mysqli_real_escape_string($conn, $day);
    
    $query = "SELECT j.*, m.NamaMK, d.NamaDosen 
              FROM jadwalkuliah j 
              LEFT JOIN matakuliah m ON j.KodeMK = m.KodeMK 
              LEFT JOIN dosen d ON m.KodeDosen = d.KodeDosen
              WHERE j.NIM = '$nim' AND j.Hari = '$day' 
              ORDER BY j.JamMulai ASC";
    
    $result = mysqli_query($conn, $query);
    $schedules = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $schedules[] = $row;
    }
    
    closeConnection($conn);
    return $schedules;
}

function getScheduleCount($nim, $day) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    $day = mysqli_real_escape_string($conn, $day);
    
    $query = "SELECT COUNT(*) as total FROM jadwalkuliah WHERE NIM = '$nim' AND Hari = '$day'";
    $result = mysqli_query($conn, $query);
    $count = mysqli_fetch_assoc($result)['total'];
    
    closeConnection($conn);
    return $count;
}

function addSchedule($data) {
    $conn = getConnection();
    
    $nim = mysqli_real_escape_string($conn, $data['nim']);
    $namaMK = mysqli_real_escape_string($conn, $data['matakuliah']);
    $hari = mysqli_real_escape_string($conn, $data['hari']);
    $ruangan = mysqli_real_escape_string($conn, $data['ruangan'] ?? '');
    $kelas = mysqli_real_escape_string($conn, $data['kelas'] ?? '');
    $jamMulai = mysqli_real_escape_string($conn, $data['jam_mulai']) . ':00';
    $jamSelesai = mysqli_real_escape_string($conn, $data['jam_selesai']) . ':00';
    
    // Cari atau buat mata kuliah
    $kodeMK = getOrCreateMatakuliahForSchedule($namaMK);
    
    $query = "INSERT INTO jadwalkuliah (KodeMK, NIM, Hari, JamMulai, JamSelesai, Ruangan, Kelas) 
              VALUES ('$kodeMK', '$nim', '$hari', '$jamMulai', '$jamSelesai', '$ruangan', '$kelas')";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function updateSchedule($id, $data) {
    $conn = getConnection();
    
    $id = mysqli_real_escape_string($conn, $id);
    $hari = mysqli_real_escape_string($conn, $data['hari']);
    $ruangan = mysqli_real_escape_string($conn, $data['ruangan'] ?? '');
    $jamMulai = mysqli_real_escape_string($conn, $data['jam_mulai']) . ':00';
    $jamSelesai = mysqli_real_escape_string($conn, $data['jam_selesai']) . ':00';
    
    $query = "UPDATE jadwalkuliah 
              SET Hari = '$hari', JamMulai = '$jamMulai', JamSelesai = '$jamSelesai', Ruangan = '$ruangan' 
              WHERE IDJadwal = '$id'";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function deleteSchedule($id) {
    $conn = getConnection();
    $id = mysqli_real_escape_string($conn, $id);
    
    $query = "DELETE FROM jadwalkuliah WHERE IDJadwal = '$id'";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function getOrCreateMatakuliahForSchedule($namaMK) {
    $conn = getConnection();
    $namaMK = mysqli_real_escape_string($conn, $namaMK);
    
    // Cari mata kuliah yang sudah ada
    $query = "SELECT KodeMK FROM matakuliah WHERE NamaMK = '$namaMK' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        closeConnection($conn);
        return $row['KodeMK'];
    }
    
    // Buat mata kuliah baru jika tidak ada
    $kodeMK = "MK-" . strtoupper(substr(md5($namaMK . time()), 0, 6));
    $queryInsert = "INSERT INTO matakuliah (KodeMK, NamaMK, SKS, KodeDosen) VALUES ('$kodeMK', '$namaMK', 3, NULL)";
    mysqli_query($conn, $queryInsert);
    
    closeConnection($conn);
    return $kodeMK;
}
?>
