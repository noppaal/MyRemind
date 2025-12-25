<?php
/**
 * ProfileModel - Model untuk Profile User
 * Mengelola data profile dan password user
 */

require_once __DIR__ . '/Database.php';

function getProfile($nim) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    
    $query = "SELECT NIM, Nama, Email FROM mahasiswa WHERE NIM = '$nim'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $profile = mysqli_fetch_assoc($result);
        closeConnection($conn);
        return $profile;
    }
    
    closeConnection($conn);
    return null;
}

function updateProfile($nim, $data) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    $nama = mysqli_real_escape_string($conn, $data['nama']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    
    // Cek apakah email sudah digunakan oleh user lain
    $checkQuery = "SELECT NIM FROM mahasiswa WHERE Email = '$email' AND NIM != '$nim'";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        closeConnection($conn);
        return ['success' => false, 'message' => 'Email sudah digunakan oleh user lain!'];
    }
    
    $query = "UPDATE mahasiswa SET Nama = '$nama', Email = '$email' WHERE NIM = '$nim'";
    
    if (mysqli_query($conn, $query)) {
        closeConnection($conn);
        return ['success' => true, 'message' => 'Profile berhasil diupdate!'];
    }
    
    closeConnection($conn);
    return ['success' => false, 'message' => 'Gagal mengupdate profile!'];
}

function changePassword($nim, $oldPassword, $newPassword) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    
    // Validasi password lama
    $query = "SELECT Password FROM mahasiswa WHERE NIM = '$nim'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if (!password_verify($oldPassword, $user['Password'])) {
            closeConnection($conn);
            return ['success' => false, 'message' => 'Password lama tidak sesuai!'];
        }
        
        // Update password baru
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE mahasiswa SET Password = '$hashedPassword' WHERE NIM = '$nim'";
        
        if (mysqli_query($conn, $updateQuery)) {
            closeConnection($conn);
            return ['success' => true, 'message' => 'Password berhasil diubah!'];
        }
    }
    
    closeConnection($conn);
    return ['success' => false, 'message' => 'Gagal mengubah password!'];
}
?>
