<?php
/**
 * TaskController - Controller untuk Tugas
 * Mengelola CRUD tugas
 */

require_once __DIR__ . '/../model/TaskModel.php';

function handleAddTask() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type']) && $_POST['type'] == 'tugas') {
        $nim = $_SESSION['nim'];
        $judul = trim($_POST['judul']);
        $namaMKInput = trim($_POST['matakuliah'] ?? '');
        $deadline = $_POST['deadline'];
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        
        // Cari atau buat mata kuliah
        $kodeMK = null;
        if (!empty($namaMKInput)) {
            $kodeMK = getOrCreateMatakuliah($namaMKInput);
        }
        
        $data = [
            'nim' => $nim,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'deadline' => $deadline,
            'kode_mk' => $kodeMK
        ];
        
        if (addTask($data)) {
            header("Location: index.php?tab=tugas&msg=tugas_sukses");
        } else {
            header("Location: index.php?tab=tugas&msg=tugas_gagal");
        }
        exit;
    }
}

function handleEditTask() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $kodeTugas = $_POST['kode_tugas'];
        $judul = trim($_POST['judul']);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $deadline = $_POST['deadline'];
        
        $data = [
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'deadline' => $deadline
        ];
        
        if (updateTask($kodeTugas, $data)) {
            header("Location: index.php?tab=tugas&msg=edit_sukses");
        } else {
            header("Location: index.php?tab=tugas&msg=edit_gagal");
        }
        exit;
    }
}

function handleDeleteTask() {
    if (isset($_GET['kode'])) {
        $kodeTugas = $_GET['kode'];
        
        if (deleteTask($kodeTugas)) {
            header("Location: index.php?tab=tugas&msg=hapus_sukses");
        } else {
            header("Location: index.php?tab=tugas&msg=hapus_gagal");
        }
        exit;
    }
}

function handleMarkCompleted() {
    if (isset($_GET['kode'])) {
        $kodeTugas = $_GET['kode'];
        
        if (markAsCompleted($kodeTugas)) {
            header("Location: index.php?tab=tugas&msg=selesai_sukses");
        } else {
            header("Location: index.php?tab=tugas&msg=selesai_gagal");
        }
        exit;
    }
}

function handleMarkInProgress() {
    if (isset($_GET['kode'])) {
        $kodeTugas = $_GET['kode'];
        
        if (markAsInProgress($kodeTugas)) {
            header("Location: index.php?msg=progress_sukses");
        } else {
            header("Location: index.php?msg=progress_gagal");
        }
        exit;
    }
}

function handleImportIcal() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ical_url'])) {
        $nim = $_SESSION['nim'];
        $icalUrl = trim($_POST['ical_url']);
        
        // Download iCal content
        $icalContent = @file_get_contents($icalUrl);
        
        if ($icalContent === false) {
            header("Location: index.php?msg=import_failed");
            exit;
        }
        
        // Parse iCal
        $imported = 0;
        $skipped = 0;
        
        // Simple iCal parsing
        $lines = explode("\n", $icalContent);
        $event = [];
        $inEvent = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if ($line == 'BEGIN:VEVENT') {
                $inEvent = true;
                $event = [];
            } elseif ($line == 'END:VEVENT') {
                $inEvent = false;
                
                // Process event
                if (isset($event['SUMMARY']) && isset($event['DTSTART'])) {
                    $judul = $event['SUMMARY'];
                    $deadline = $event['DTSTART'];
                    $deskripsi = isset($event['DESCRIPTION']) ? $event['DESCRIPTION'] : '';
                    
                    // Format deadline
                    if (strlen($deadline) == 8) {
                        // Format: YYYYMMDD
                        $deadline = substr($deadline, 0, 4) . '-' . substr($deadline, 4, 2) . '-' . substr($deadline, 6, 2) . ' 23:59:00';
                    } elseif (strlen($deadline) == 15) {
                        // Format: YYYYMMDDTHHMMSS
                        $deadline = substr($deadline, 0, 4) . '-' . substr($deadline, 4, 2) . '-' . substr($deadline, 6, 2) . ' ' .
                                    substr($deadline, 9, 2) . ':' . substr($deadline, 11, 2) . ':' . substr($deadline, 13, 2);
                    }
                    
                    $data = [
                        'nim' => $nim,
                        'judul' => $judul,
                        'deskripsi' => $deskripsi,
                        'deadline' => $deadline,
                        'kode_mk' => null
                    ];
                    
                    if (addTask($data)) {
                        $imported++;
                    } else {
                        $skipped++;
                    }
                }
            } elseif ($inEvent) {
                $parts = explode(':', $line, 2);
                if (count($parts) == 2) {
                    $key = $parts[0];
                    $value = $parts[1];
                    $event[$key] = $value;
                }
            }
        }
        
        header("Location: index.php?msg=import_success&imported=$imported&skipped=$skipped");
        exit;
    }
}
?>
