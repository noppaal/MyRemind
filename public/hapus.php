<?php
/**
 * Hapus Tugas - Handler untuk menghapus tugas
 */

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../controller/TaskController.php';
handleDeleteTask();
?>


