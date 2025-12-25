<?php
/**
 * Database Connection Handler
 * Mengelola koneksi ke database MySQL
 */

function getConnection() {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "db_myremind";
    
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
