<?php
/**
 * Proses Selesai - Handler untuk menandai tugas selesai
 */

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../controller/TaskController.php';
handleMarkCompleted();
?>


