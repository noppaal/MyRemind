<?php
/**
 * PHPUnit Bootstrap File
 * Setup test environment
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load application config
require_once __DIR__ . '/../config/config.php';

// Override database config for testing
define('DB_HOST_TEST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME_TEST', getenv('DB_NAME') ?: 'db_myremind_test');
define('DB_USER_TEST', getenv('DB_USER') ?: 'root');
define('DB_PASS_TEST', getenv('DB_PASS') ?: '');

/**
 * Get test database connection
 */
function getTestConnection() {
    $conn = mysqli_connect(DB_HOST_TEST, DB_USER_TEST, DB_PASS_TEST, DB_NAME_TEST);
    
    if (!$conn) {
        die("Test database connection failed: " . mysqli_connect_error());
    }
    
    return $conn;
}

/**
 * Reset test database
 */
function resetTestDatabase() {
    $conn = getTestConnection();
    
    // Clear all tables
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
    mysqli_query($conn, "TRUNCATE TABLE tugas");
    mysqli_query($conn, "TRUNCATE TABLE jadwalkuliah");
    mysqli_query($conn, "TRUNCATE TABLE grup");
    mysqli_query($conn, "TRUNCATE TABLE grup_anggota");
    mysqli_query($conn, "TRUNCATE TABLE grup_jadwal");
    mysqli_query($conn, "TRUNCATE TABLE mahasiswa");
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
    
    // Ensure default matakuliah GENERAL exists
    mysqli_query($conn, "INSERT INTO matakuliah (KodeMK, NamaMK, SKS) VALUES ('GENERAL', 'Tugas LMS (Umum)', 0) ON DUPLICATE KEY UPDATE NamaMK = VALUES(NamaMK)");
    
    mysqli_close($conn);
}

/**
 * Create test user
 */
function createTestUser($nim = '1301200001', $nama = 'Test User', $email = 'test@example.com') {
    $conn = getTestConnection();
    
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $query = "INSERT INTO mahasiswa (NIM, Nama, Email, Password, Jurusan) 
              VALUES (?, ?, ?, ?, 'Teknik Informatika')
              ON DUPLICATE KEY UPDATE Email = VALUES(Email)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $nim, $nama, $email, $password);
    mysqli_stmt_execute($stmt);
    
    mysqli_close($conn);
    
    return $nim;
}
