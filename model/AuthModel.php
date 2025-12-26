<?php
/**
 * AuthModel - Model untuk Authentication
 * Mengelola data user, login, dan registrasi
 */

require_once __DIR__ . '/Database.php';

function getUserByEmail($email) {
    $conn = getConnection();
    $email = mysqli_real_escape_string($conn, $email);
    
    $query = "SELECT NIM, Nama, Email, Password FROM mahasiswa WHERE Email = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            closeConnection($conn);
            return $user;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    closeConnection($conn);
    return null;
}

function getUserByNIM($nim) {
    $conn = getConnection();
    $nim = mysqli_real_escape_string($conn, $nim);
    
    $query = "SELECT NIM, Nama, Email FROM mahasiswa WHERE NIM = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $nim);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            closeConnection($conn);
            return $user;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    closeConnection($conn);
    return null;
}

function validateLogin($email, $password) {
    $conn = getConnection();
    $email = mysqli_real_escape_string($conn, $email);
    
    $query = "SELECT * FROM mahasiswa WHERE Email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 0) {
        closeConnection($conn);
        return ['success' => false, 'message' => 'Email tidak terdaftar!'];
    }
    
    $user = mysqli_fetch_assoc($result);
    
    if (!password_verify($password, $user['Password'])) {
        closeConnection($conn);
        return ['success' => false, 'message' => 'Password salah!'];
    }
    
    unset($user['Password']); // Remove password from return
    closeConnection($conn);
    return ['success' => true, 'user' => $user];
}

function registerUser($data) {
    $conn = getConnection();
    
    $nim = mysqli_real_escape_string($conn, $data['nim']);
    $nama = mysqli_real_escape_string($conn, $data['nama']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Validasi email domain (disabled for testing)
    // if (!str_ends_with($email, '@student.telkomuniversity.ac.id')) {
    //     closeConnection($conn);
    //     return ['success' => false, 'message' => 'Email harus menggunakan domain @student.telkomuniversity.ac.id!'];
    // }
    
    // Cek apakah email sudah terdaftar
    $checkEmail = getUserByEmail($email);
    if ($checkEmail) {
        closeConnection($conn);
        return ['success' => false, 'message' => 'Email sudah terdaftar!'];
    }
    
    // Cek apakah NIM sudah terdaftar
    $checkNIM = getUserByNIM($nim);
    if ($checkNIM) {
        closeConnection($conn);
        return ['success' => false, 'message' => 'NIM sudah terdaftar!'];
    }
    
    // FIX: Added 'Jurusan' field with a default value
    $query = "INSERT INTO mahasiswa (NIM, Nama, Email, Password, Jurusan) VALUES (?, ?, ?, ?, 'Teknik Informatika')";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $nim, $nama, $email, $password);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            closeConnection($conn);
            return ['success' => true, 'message' => 'Registrasi berhasil!'];
        }
        
        mysqli_stmt_close($stmt);
    }
    
    closeConnection($conn);
    return ['success' => false, 'message' => 'Terjadi kesalahan sistem!'];
}
?>
