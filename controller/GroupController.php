<?php
/**
 * GroupController - Controller untuk Grup
 * Mengelola CRUD grup dan anggota
 */

require_once __DIR__ . '/../model/GroupModel.php';

function handleCreateGroup() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type']) && $_POST['type'] == 'grup') {
        $nim = $_SESSION['nim'];
        $namaGrup = trim($_POST['nama_grup']);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        
        // Generate unique KodeGrup
        $kodeGrup = 'GRP-' . time() . '-' . strtolower(substr(md5(uniqid()), 0, 5));
        
        $data = [
            'kode_grup' => $kodeGrup,
            'nama_grup' => $namaGrup,
            'deskripsi' => $deskripsi,
            'creator_nim' => $nim
        ];
        
        $result = createGroup($data);
        
        if ($result['success']) {
            header("Location: index.php?tab=grup&msg=grup_created");
        } else {
            header("Location: index.php?tab=grup&msg=grup_failed");
        }
        exit;
    }
}

function handleDeleteGroup() {
    if (isset($_GET['action']) && $_GET['action'] == 'delete_group' && isset($_GET['id'])) {
        $groupId = $_GET['id'];
        $nim = $_SESSION['nim'];
        
        // Check if user is admin
        if (isGroupAdmin($groupId, $nim)) {
            if (deleteGroup($groupId)) {
                header("Location: index.php?tab=grup&msg=grup_deleted");
            } else {
                header("Location: index.php?tab=grup&msg=delete_failed");
            }
        } else {
            header("Location: index.php?tab=grup&msg=not_authorized");
        }
        exit;
    }
}

function handleAddMember() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_member') {
        $groupId = $_POST['group_id'];
        $nim = trim($_POST['nim']);
        $currentNIM = $_SESSION['nim'];
        
        // Check if current user is admin
        if (isGroupAdmin($groupId, $currentNIM)) {
            $result = addMember($groupId, $nim, 'member');
            
            if ($result['success']) {
                header("Location: detail_group.php?id=$groupId&msg=member_added");
            } else {
                header("Location: detail_group.php?id=$groupId&msg=add_failed");
            }
        } else {
            header("Location: detail_group.php?id=$groupId&msg=not_authorized");
        }
        exit;
    }
}

function handleRemoveMember() {
    if (isset($_GET['action']) && $_GET['action'] == 'remove_member' && isset($_GET['group_id']) && isset($_GET['nim'])) {
        $groupId = $_GET['group_id'];
        $nimToRemove = $_GET['nim'];
        $currentNIM = $_SESSION['nim'];
        
        // Check if current user is admin
        if (isGroupAdmin($groupId, $currentNIM)) {
            if (removeMember($groupId, $nimToRemove)) {
                header("Location: detail_group.php?id=$groupId&msg=member_removed");
            } else {
                header("Location: detail_group.php?id=$groupId&msg=remove_failed");
            }
        } else {
            header("Location: detail_group.php?id=$groupId&msg=not_authorized");
        }
        exit;
    }
}

function handleCreateInvite() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_invite') {
        $groupId = $_POST['group_id'];
        $nim = $_SESSION['nim'];
        
        // Check if user is admin
        if (isGroupAdmin($groupId, $nim)) {
            $result = createInvite($groupId, $nim);
            
            if ($result['success']) {
                header("Location: detail_group.php?id=$groupId&msg=invite_created&code=" . $result['invite_code']);
            } else {
                header("Location: detail_group.php?id=$groupId&msg=invite_failed");
            }
        } else {
            header("Location: detail_group.php?id=$groupId&msg=not_authorized");
        }
        exit;
    }
}

function handleJoinInvite() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'join_invite') {
        $inviteCode = trim($_POST['invite_code']);
        $nim = $_SESSION['nim'];
        
        $result = joinByInvite($inviteCode, $nim);
        
        if ($result['success']) {
            header("Location: index.php?tab=grup&msg=join_success");
        } else {
            header("Location: index.php?tab=grup&msg=join_failed&error=" . urlencode($result['message']));
        }
        exit;
    }
}

function handleGroupSchedule() {
    // Add group schedule
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_schedule') {
        $groupId = $_POST['group_id'];
        $namaKegiatan = trim($_POST['nama_kegiatan']);
        $hari = $_POST['hari'];
        $jamMulai = $_POST['jam_mulai'];
        $jamSelesai = $_POST['jam_selesai'];
        $lokasi = trim($_POST['lokasi'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');
        
        $data = [
            'group_id' => $groupId,
            'nama_kegiatan' => $namaKegiatan,
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'lokasi' => $lokasi,
            'keterangan' => $keterangan
        ];
        
        if (addGroupSchedule($data)) {
            header("Location: detail_group.php?id=$groupId&msg=schedule_added");
        } else {
            header("Location: detail_group.php?id=$groupId&msg=schedule_failed");
        }
        exit;
    }
    
    // Update group schedule
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_schedule') {
        $scheduleId = $_POST['schedule_id'];
        $groupId = $_POST['group_id'];
        $namaKegiatan = trim($_POST['nama_kegiatan']);
        $hari = $_POST['hari'];
        $jamMulai = $_POST['jam_mulai'];
        $jamSelesai = $_POST['jam_selesai'];
        $lokasi = trim($_POST['lokasi'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');
        
        $data = [
            'nama_kegiatan' => $namaKegiatan,
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'lokasi' => $lokasi,
            'keterangan' => $keterangan
        ];
        
        if (updateGroupSchedule($scheduleId, $data)) {
            header("Location: detail_group.php?id=$groupId&msg=schedule_updated");
        } else {
            header("Location: detail_group.php?id=$groupId&msg=update_failed");
        }
        exit;
    }
    
    // Delete group schedule
    if (isset($_GET['action']) && $_GET['action'] == 'delete_schedule' && isset($_GET['id']) && isset($_GET['group_id'])) {
        $scheduleId = $_GET['id'];
        $groupId = $_GET['group_id'];
        
        if (deleteGroupSchedule($scheduleId)) {
            header("Location: detail_group.php?id=$groupId&msg=schedule_deleted");
        } else {
            header("Location: detail_group.php?id=$groupId&msg=delete_failed");
        }
        exit;
    }
}

function showGroupDetail() {
    if (isset($_GET['id'])) {
        $groupId = $_GET['id'];
        $nim = $_SESSION['nim'];
        
        $group = getGroupDetail($groupId);
        $members = getGroupMembers($groupId);
        $schedules = getGroupSchedules($groupId);
        $isAdmin = isGroupAdmin($groupId, $nim);
        
        require_once __DIR__ . '/../view/groups/detail.php';
    } else {
        header("Location: index.php?tab=grup");
        exit;
    }
}
?>
