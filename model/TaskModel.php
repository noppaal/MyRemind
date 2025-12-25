<?php
/**
 * TaskModel - Model untuk Tugas
 * Mengelola data tugas mahasiswa
 */

require_once __DIR__ . '/Database.php';

function getAllTasks($nim) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    
    $query = "SELECT t.*, m.NamaMK 
              FROM tugas t 
              LEFT JOIN matakuliah m ON t.KodeMK = m.KodeMK 
              WHERE t.NIM = '$nim' 
              ORDER BY t.Deadline ASC";
    
    $result = mysqli_query($conn, $query);
    $tasks = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        if (empty($row['NamaMK'])) {
            $row['NamaMK'] = "Tugas Umum (LMS)";
        }
        $tasks[] = $row;
    }
    
    closeConnection($conn);
    return $tasks;
}

function getActiveTasks($nim) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    
    $query = "SELECT t.*, m.NamaMK 
              FROM tugas t 
              LEFT JOIN matakuliah m ON t.KodeMK = m.KodeMK 
              WHERE t.StatusTugas = 'Aktif' AND t.NIM = '$nim' 
              ORDER BY t.Deadline ASC";
    
    $result = mysqli_query($conn, $query);
    $tasks = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        if (empty($row['NamaMK'])) {
            $row['NamaMK'] = "Tugas Umum (LMS)";
        }
        $tasks[] = $row;
    }
    
    closeConnection($conn);
    return $tasks;
}

function getCompletedTasks($nim, $limit = 20) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    
    $query = "SELECT t.*, m.NamaMK 
              FROM tugas t 
              LEFT JOIN matakuliah m ON t.KodeMK = m.KodeMK 
              WHERE t.StatusTugas = 'Selesai' AND t.NIM = '$nim' 
              ORDER BY t.Deadline DESC 
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $tasks = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        if (empty($row['NamaMK'])) {
            $row['NamaMK'] = "Tugas Umum (LMS)";
        }
        $tasks[] = $row;
    }
    
    closeConnection($conn);
    return $tasks;
}

function getInProgressTasks($nim) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    
    $query = "SELECT t.*, m.NamaMK 
              FROM tugas t 
              LEFT JOIN matakuliah m ON t.KodeMK = m.KodeMK 
              WHERE t.StatusTugas = 'In Progress' AND t.NIM = '$nim' 
              ORDER BY t.Deadline ASC";
    
    $result = mysqli_query($conn, $query);
    $tasks = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        if (empty($row['NamaMK'])) {
            $row['NamaMK'] = "Tugas Umum (LMS)";
        }
        $tasks[] = $row;
    }
    
    closeConnection($conn);
    return $tasks;
}

function getTasksByDate($nim, $month, $year) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    $month = mysqli_real_escape_string($conn, $month);
    $year = mysqli_real_escape_string($conn, $year);
    
    $query = "SELECT DAY(Deadline) as hari, JudulTugas, StatusTugas, Deadline 
              FROM tugas 
              WHERE NIM = '$nim' 
              AND MONTH(Deadline) = '$month' 
              AND YEAR(Deadline) = '$year'
              ORDER BY Deadline ASC";
    
    $result = mysqli_query($conn, $query);
    $tasks = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $tasks[] = $row;
    }
    
    closeConnection($conn);
    return $tasks;
}

function getTaskStatistics($nim) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    
    $queryAktif = "SELECT COUNT(*) as total FROM tugas WHERE StatusTugas = 'Aktif' AND NIM = '$nim'";
    $resultAktif = mysqli_query($conn, $queryAktif);
    $countAktif = mysqli_fetch_assoc($resultAktif)['total'];
    
    $querySelesai = "SELECT COUNT(*) as total FROM tugas WHERE StatusTugas = 'Selesai' AND NIM = '$nim'";
    $resultSelesai = mysqli_query($conn, $querySelesai);
    $countSelesai = mysqli_fetch_assoc($resultSelesai)['total'];
    
    $queryInProgress = "SELECT COUNT(*) as total FROM tugas WHERE StatusTugas = 'In Progress' AND NIM = '$nim'";
    $resultInProgress = mysqli_query($conn, $queryInProgress);
    $countInProgress = mysqli_fetch_assoc($resultInProgress)['total'];
    
    closeConnection($conn);
    
    return [
        'aktif' => $countAktif,
        'selesai' => $countSelesai,
        'in_progress' => $countInProgress,
        'total' => $countAktif + $countSelesai + $countInProgress
    ];
}

function addTask($data) {
    $conn = getConnection();
    
    $nim = mysqli_real_escape_string($conn, $data['nim']);
    $judul = mysqli_real_escape_string($conn, $data['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $data['deskripsi'] ?? '');
    $deadline = mysqli_real_escape_string($conn, $data['deadline']);
    $kodeMK = isset($data['kode_mk']) ? mysqli_real_escape_string($conn, $data['kode_mk']) : null;
    $kodeTugas = "MANUAL-" . time();
    
    $kodeMKValue = $kodeMK ? "'$kodeMK'" : "NULL";
    
    $query = "INSERT INTO tugas (KodeTugas, NIM, KodeMK, JudulTugas, Deskripsi, Deadline, JenisTugas, StatusTugas)
              VALUES ('$kodeTugas', '$nim', $kodeMKValue, '$judul', '$deskripsi', '$deadline', 'Individu', 'Aktif')";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function updateTask($kodeTugas, $data) {
    $conn = getConnection();
    
    $kodeTugas = mysqli_real_escape_string($conn, $kodeTugas);
    $judul = mysqli_real_escape_string($conn, $data['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $data['deskripsi'] ?? '');
    $deadline = mysqli_real_escape_string($conn, $data['deadline']);
    
    $query = "UPDATE tugas 
              SET JudulTugas = '$judul', Deskripsi = '$deskripsi', Deadline = '$deadline' 
              WHERE KodeTugas = '$kodeTugas'";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function deleteTask($kodeTugas) {
    $conn = getConnection();
    $kodeTugas = mysqli_real_escape_string($conn, $kodeTugas);
    
    $query = "DELETE FROM tugas WHERE KodeTugas = '$kodeTugas'";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function markAsCompleted($kodeTugas) {
    $conn = getConnection();
    $kodeTugas = mysqli_real_escape_string($conn, $kodeTugas);
    
    $query = "UPDATE tugas SET StatusTugas = 'Selesai' WHERE KodeTugas = '$kodeTugas'";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function markAsInProgress($kodeTugas) {
    $conn = getConnection();
    $kodeTugas = mysqli_real_escape_string($conn, $kodeTugas);
    
    $query = "UPDATE tugas SET StatusTugas = 'In Progress' WHERE KodeTugas = '$kodeTugas'";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function getOrCreateMatakuliah($namaMK) {
    $conn = getConnection();
    $namaMK = mysqli_real_escape_string($conn, $namaMK);
    
    // Cari mata kuliah yang sudah ada
    $query = "SELECT KodeMK FROM matakuliah WHERE NamaMK LIKE '%$namaMK%' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        closeConnection($conn);
        return $row['KodeMK'];
    }
    
    // Buat mata kuliah baru jika tidak ada
    $kodeMK = "MK-" . strtoupper(substr(md5($namaMK . time()), 0, 6));
    $queryInsert = "INSERT INTO matakuliah (KodeMK, NamaMK, SKS) VALUES ('$kodeMK', '$namaMK', 3)";
    mysqli_query($conn, $queryInsert);
    
    closeConnection($conn);
    return $kodeMK;
}
?>
