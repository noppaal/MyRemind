<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_myremind";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}

// Session configuration for security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Session timeout (30 minutes of inactivity)
$session_timeout = 1800; // 30 minutes in seconds

// Check if session is expired
if (isset($_SESSION['login_time'])) {
    $elapsed_time = time() - $_SESSION['login_time'];
    
    if ($elapsed_time > $session_timeout) {
        // Session expired
        session_unset();
        session_destroy();
        header("Location: login.php?msg=session_expired");
        exit;
    }
    
    // Update last activity time
    $_SESSION['login_time'] = time();
}

// Validate user agent to prevent session hijacking
if (isset($_SESSION['user_agent'])) {
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        // Possible session hijacking
        session_unset();
        session_destroy();
        header("Location: login.php?msg=session_expired");
        exit;
    }
}

// Set timezone to Indonesia Jakarta
date_default_timezone_set('Asia/Jakarta');
?>