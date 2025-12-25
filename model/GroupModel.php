<?php
/**
 * GroupModel - Model untuk Grup
 * Mengelola data grup dan anggota grup
 */

require_once __DIR__ . '/Database.php';

function getAllGroups($nim) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    
    $query = "SELECT g.*, 
              (SELECT COUNT(*) FROM grup_anggota WHERE KodeGrup = g.KodeGrup) as jumlah_anggota
              FROM grup g
              INNER JOIN grup_anggota ga ON g.KodeGrup = ga.KodeGrup
              WHERE ga.NIM = '$nim'
              ORDER BY g.CreatedAt DESC";
    
    $result = mysqli_query($conn, $query);
    $groups = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $groups[] = $row;
    }
    
    closeConnection($conn);
    return $groups;
}

function getGroupDetail($kodeGrup) {
    $conn = getConnection();
    $kodeGrup = mysqli_real_escape_string($conn, $kodeGrup);
    
    $query = "SELECT * FROM grup WHERE KodeGrup = '$kodeGrup'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $group = mysqli_fetch_assoc($result);
        closeConnection($conn);
        return $group;
    }
    
    closeConnection($conn);
    return null;
}

function getGroupMembers($kodeGrup) {
    $conn = getConnection();
    $kodeGrup = mysqli_real_escape_string($conn, $kodeGrup);
    
    $query = "SELECT ga.*, m.Nama, m.Email 
              FROM grup_anggota ga
              INNER JOIN mahasiswa m ON ga.NIM = m.NIM
              WHERE ga.KodeGrup = '$kodeGrup'
              ORDER BY ga.Role DESC, m.Nama ASC";
    
    $result = mysqli_query($conn, $query);
    $members = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
    
    closeConnection($conn);
    return $members;
}

function createGroup($data) {
    $conn = getConnection();
    
    $kodeGrup = mysqli_real_escape_string($conn, $data['kode_grup']);
    $namaGrup = mysqli_real_escape_string($conn, $data['nama_grup']);
    $deskripsi = mysqli_real_escape_string($conn, $data['deskripsi'] ?? '');
    $creatorNIM = mysqli_real_escape_string($conn, $data['creator_nim']);
    
    // Insert grup
    $query = "INSERT INTO grup (KodeGrup, NamaGrup, Deskripsi, CreatedBy, CreatedAt) 
              VALUES ('$kodeGrup', '$namaGrup', '$deskripsi', '$creatorNIM', NOW())";
    
    if (mysqli_query($conn, $query)) {
        // Tambahkan creator sebagai owner
        $queryMember = "INSERT INTO grup_anggota (KodeGrup, NIM, Role, JoinedAt) 
                        VALUES ('$kodeGrup', '$creatorNIM', 'owner', NOW())";
        mysqli_query($conn, $queryMember);
        
        closeConnection($conn);
        return ['success' => true, 'kode_grup' => $kodeGrup];
    }
    
    closeConnection($conn);
    return ['success' => false];
}

function deleteGroup($groupId) {
    $conn = getConnection();
    $groupId = mysqli_real_escape_string($conn, $groupId);
    
    // Hapus jadwal grup
    mysqli_query($conn, "DELETE FROM grup_jadwal WHERE IDGrup = '$groupId'");
    
    // Hapus anggota grup
    mysqli_query($conn, "DELETE FROM grup_members WHERE IDGrup = '$groupId'");
    
    // Hapus invite codes
    mysqli_query($conn, "DELETE FROM grup_invites WHERE IDGrup = '$groupId'");
    
    // Hapus grup
    $query = "DELETE FROM grup WHERE IDGrup = '$groupId'";
    $result = mysqli_query($conn, $query);
    
    closeConnection($conn);
    return $result;
}

function addMember($groupId, $nim, $role = 'member') {
    $conn = getConnection();
    $groupId = mysqli_real_escape_string($conn, $groupId);
    $nim = mysqli_real_escape_string($conn, $nim);
    $role = mysqli_real_escape_string($conn, $role);
    
    // Cek apakah sudah menjadi anggota
    $checkQuery = "SELECT * FROM grup_members WHERE IDGrup = '$groupId' AND NIM = '$nim'";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        closeConnection($conn);
        return ['success' => false, 'message' => 'Sudah menjadi anggota grup'];
    }
    
    $query = "INSERT INTO grup_members (IDGrup, NIM, Role, TanggalBergabung) 
              VALUES ('$groupId', '$nim', '$role', NOW())";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return ['success' => $result, 'message' => $result ? 'Berhasil menambahkan anggota' : 'Gagal menambahkan anggota'];
}

function removeMember($groupId, $nim) {
    $conn = getConnection();
    $groupId = mysqli_real_escape_string($conn, $groupId);
    $nim = mysqli_real_escape_string($conn, $nim);
    
    $query = "DELETE FROM grup_members WHERE IDGrup = '$groupId' AND NIM = '$nim'";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function createInvite($kodeGrup, $creatorNIM) {
    $conn = getConnection();
    $kodeGrup = mysqli_real_escape_string($conn, $kodeGrup);
    $creatorNIM = mysqli_real_escape_string($conn, $creatorNIM);
    
    // Generate kode invite unik
    $inviteCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    
    // Check if grup_invites table exists, if not use simple approach
    $query = "SELECT KodeGrup FROM grup WHERE KodeGrup = '$kodeGrup'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        closeConnection($conn);
        return ['success' => true, 'invite_code' => $inviteCode, 'kode_grup' => $kodeGrup];
    }
    
    closeConnection($conn);
    return ['success' => false];
}

function joinByInvite($inviteCode, $nim) {
    $conn = getConnection();
    $inviteCode = mysqli_real_escape_string($conn, strtoupper($inviteCode));
    $nim = mysqli_real_escape_string($conn, $nim);
    
    // For simple implementation, use invite code as KodeGrup directly
    // Check if group exists (case-insensitive)
    $query = "SELECT KodeGrup, NamaGrup FROM grup WHERE UPPER(KodeGrup) = '$inviteCode'";
    $result = mysqli_query($conn, $query);
    
    // Debug log
    error_log("JOIN BY INVITE - Query: $query");
    error_log("JOIN BY INVITE - Result count: " . mysqli_num_rows($result));
    
    if (mysqli_num_rows($result) > 0) {
        $group = mysqli_fetch_assoc($result);
        $kodeGrup = $group['KodeGrup'];
        
        error_log("JOIN BY INVITE - Group found: " . $group['NamaGrup']);
        
        // Check if already member
        $checkQuery = "SELECT * FROM grup_anggota WHERE KodeGrup = '$kodeGrup' AND NIM = '$nim'";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        error_log("JOIN BY INVITE - Already member check: " . mysqli_num_rows($checkResult));
        
        if (mysqli_num_rows($checkResult) > 0) {
            closeConnection($conn);
            return ['success' => false, 'message' => 'Anda sudah menjadi anggota grup ini'];
        }
        
        // Add as member
        $insertQuery = "INSERT INTO grup_anggota (KodeGrup, NIM, Role, JoinedAt) 
                        VALUES ('$kodeGrup', '$nim', 'member', NOW())";
        
        error_log("JOIN BY INVITE - Insert query: $insertQuery");
        
        if (mysqli_query($conn, $insertQuery)) {
            error_log("JOIN BY INVITE - SUCCESS! User $nim joined group $kodeGrup");
            closeConnection($conn);
            return ['success' => true, 'message' => 'Berhasil bergabung ke grup: ' . $group['NamaGrup']];
        } else {
            error_log("JOIN BY INVITE - Insert failed: " . mysqli_error($conn));
        }
    } else {
        error_log("JOIN BY INVITE - Group not found with code: $inviteCode");
    }
    
    closeConnection($conn);
    return ['success' => false, 'message' => 'Kode invite tidak valid'];
}

function getGroupSchedules($groupId) {
    $conn = getConnection();
    $groupId = mysqli_real_escape_string($conn, $groupId);
    
    $query = "SELECT * FROM grup_jadwal 
              WHERE IDGrup = '$groupId' 
              ORDER BY FIELD(Hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), JamMulai ASC";
    
    $result = mysqli_query($conn, $query);
    $schedules = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $schedules[] = $row;
    }
    
    closeConnection($conn);
    return $schedules;
}

function addGroupSchedule($data) {
    $conn = getConnection();
    
    $groupId = mysqli_real_escape_string($conn, $data['group_id']);
    $namaKegiatan = mysqli_real_escape_string($conn, $data['nama_kegiatan']);
    $hari = mysqli_real_escape_string($conn, $data['hari']);
    $jamMulai = mysqli_real_escape_string($conn, $data['jam_mulai']) . ':00';
    $jamSelesai = mysqli_real_escape_string($conn, $data['jam_selesai']) . ':00';
    $lokasi = mysqli_real_escape_string($conn, $data['lokasi'] ?? '');
    $keterangan = mysqli_real_escape_string($conn, $data['keterangan'] ?? '');
    
    $query = "INSERT INTO grup_jadwal (IDGrup, NamaKegiatan, Hari, JamMulai, JamSelesai, Lokasi, Keterangan) 
              VALUES ('$groupId', '$namaKegiatan', '$hari', '$jamMulai', '$jamSelesai', '$lokasi', '$keterangan')";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function updateGroupSchedule($id, $data) {
    $conn = getConnection();
    
    $id = mysqli_real_escape_string($conn, $id);
    $namaKegiatan = mysqli_real_escape_string($conn, $data['nama_kegiatan']);
    $hari = mysqli_real_escape_string($conn, $data['hari']);
    $jamMulai = mysqli_real_escape_string($conn, $data['jam_mulai']) . ':00';
    $jamSelesai = mysqli_real_escape_string($conn, $data['jam_selesai']) . ':00';
    $lokasi = mysqli_real_escape_string($conn, $data['lokasi'] ?? '');
    $keterangan = mysqli_real_escape_string($conn, $data['keterangan'] ?? '');
    
    $query = "UPDATE grup_jadwal 
              SET NamaKegiatan = '$namaKegiatan', Hari = '$hari', JamMulai = '$jamMulai', 
                  JamSelesai = '$jamSelesai', Lokasi = '$lokasi', Keterangan = '$keterangan' 
              WHERE IDJadwal = '$id'";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function deleteGroupSchedule($id) {
    $conn = getConnection();
    $id = mysqli_real_escape_string($conn, $id);
    
    $query = "DELETE FROM grup_jadwal WHERE IDJadwal = '$id'";
    
    $result = mysqli_query($conn, $query);
    closeConnection($conn);
    
    return $result;
}

function isGroupAdmin($groupId, $nim) {
    $conn = getConnection();
    $groupId = mysqli_real_escape_string($conn, $groupId);
    $nim = mysqli_real_escape_string($conn, $nim);
    
    $query = "SELECT Role FROM grup_members WHERE IDGrup = '$groupId' AND NIM = '$nim'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        closeConnection($conn);
        return $row['Role'] === 'admin';
    }
    
    closeConnection($conn);
    return false;
}
?>
