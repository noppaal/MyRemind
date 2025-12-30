<?php
/**
 * Database Connection Handler
 * Mengelola koneksi ke database MySQL
 */

function getConnection() {
    $host = getenv('DB_HOST') ?: "localhost";
    $user = getenv('DB_USER') ?: "root";
    $pass = getenv('DB_PASS') ?: "";
    $db   = getenv('DB_NAME') ?: "db_myremind";
    
    $conn = mysqli_connect($host, $user, $pass, $db);
    
    if (!$conn) {
        die("Koneksi Gagal: " . mysqli_connect_error());
    }
    
    return $conn;
}

function closeConnection($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}
?>
