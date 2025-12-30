<?php
/**
 * Proses Tambah - Handler untuk menambah tugas dan jadwal
 * File ini dipanggil dari form tambah tugas/jadwal
 */

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}

// Route ke controller yang sesuai
if (isset($_POST['type'])) {
    if ($_POST['type'] == 'tugas') {
        require_once __DIR__ . '/../controller/TaskController.php';
        handleAddTask();
    } elseif ($_POST['type'] == 'jadwal') {
        require_once __DIR__ . '/../controller/ScheduleController.php';
        handleAddSchedule();
    } elseif ($_POST['type'] == 'grup') {
        require_once __DIR__ . '/../controller/GroupController.php';
        handleCreateGroup();
    }
} else {
    header("Location: index.php");
    exit;
}
?>


