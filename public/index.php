<?php
/**
 * Dashboard - Entry Point Utama
 * Memanggil DashboardController untuk menampilkan dashboard
 */

require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}

// Route to appropriate controller based on action
if (isset($_GET['action']) || isset($_POST['action']) || isset($_POST['type'])) {
    // Task actions
    if (isset($_POST['type']) && $_POST['type'] == 'tugas') {
        require_once __DIR__ . '/../controller/TaskController.php';
        handleAddTask();
    }
    
    // Schedule actions
    if (isset($_POST['type']) && $_POST['type'] == 'jadwal') {
        require_once __DIR__ . '/../controller/ScheduleController.php';
        handleAddSchedule();
    }
    
    // Group actions
    $groupActions = ['create_group', 'delete_group', 'add_member', 'remove_member', 'create_invite', 'join_invite', 'add_schedule', 'update_schedule', 'delete_schedule'];
    if (isset($_POST['action']) && in_array($_POST['action'], $groupActions)) {
        require_once __DIR__ . '/../controller/GroupController.php';
        handleCreateGroup();
        handleAddMember();
        handleRemoveMember();
        handleCreateInvite();
        handleJoinInvite();
        handleGroupSchedule();
    }
    
    if (isset($_GET['action']) && in_array($_GET['action'], $groupActions)) {
        require_once __DIR__ . '/../controller/GroupController.php';
        handleDeleteGroup();
        handleRemoveMember();
        handleGroupSchedule();
    }
}

// Show dashboard
require_once __DIR__ . '/../controller/DashboardController.php';
showDashboard();
?>


