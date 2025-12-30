<?php
/**
 * Login Page - Entry Point
 * Memanggil AuthController untuk menampilkan halaman login
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controller/AuthController.php';

// Jika user sudah login, redirect ke dashboard
if (isset($_SESSION['nim'])) {
    header("Location: index.php");
    exit;
}

// Tampilkan halaman login
showLoginPage();
?>


