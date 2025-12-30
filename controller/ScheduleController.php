<?php
/**
 * ScheduleController - Controller untuk Jadwal
 * Mengelola CRUD jadwal kuliah
 */

require_once __DIR__ . '/../model/ScheduleModel.php';

function handleAddSchedule() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type']) && $_POST['type'] == 'jadwal') {
        $nim = $_SESSION['nim'];
        $matakuliah = trim($_POST['matakuliah']);
        $hari = $_POST['hari'];
        $ruangan = trim($_POST['ruangan'] ?? '');
        $jamMulai = $_POST['jam_mulai'];
        $jamSelesai = $_POST['jam_selesai'];
        
        $data = [
            'nim' => $nim,
            'matakuliah' => $matakuliah,
            'hari' => $hari,
            'ruangan' => $ruangan,
            'kelas' => trim($_POST['kelas'] ?? ''),
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai
        ];
        
        if (addSchedule($data)) {
            header("Location: index.php?tab=kalender&msg=jadwal_sukses");
        } else {
            header("Location: index.php?tab=kalender&msg=jadwal_gagal");
        }
        exit;
    }
}

function handleUpdateSchedule() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id = $_POST['id_jadwal'];
        $hari = $_POST['hari'];
        $ruangan = trim($_POST['ruangan'] ?? '');
        $jamMulai = $_POST['jam_mulai'];
        $jamSelesai = $_POST['jam_selesai'];
        
        $data = [
            'hari' => $hari,
            'ruangan' => $ruangan,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai
        ];
        
        if (updateSchedule($id, $data)) {
            header("Location: index.php?tab=kalender&msg=update_sukses");
        } else {
            header("Location: index.php?tab=kalender&msg=update_gagal");
        }
        exit;
    }
}

function handleDeleteSchedule() {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        
        if (deleteSchedule($id)) {
            header("Location: index.php?tab=kalender&msg=hapus_sukses");
        } else {
            header("Location: index.php?tab=kalender&msg=hapus_gagal");
        }
        exit;
    }
}

function getSchedulesByDayAjax() {
    if (isset($_GET['hari']) && isset($_GET['nim'])) {
        $nim = $_GET['nim'];
        $hari = $_GET['hari'];
        
        $schedules = getSchedulesByDay($nim, $hari);
        
        header('Content-Type: application/json');
        echo json_encode($schedules);
        exit;
    }
}
?>
