<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../model/Database.php';
require_once __DIR__ . '/../../model/GroupModel.php';

if (!isset($_SESSION['nim'])) {
    header("Location: ../../public/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_SESSION['nim'];
    $inviteCode = strtoupper(trim($_POST['invite_code']));
    
    // Debug log
    error_log("JOIN GROUP ATTEMPT - NIM: $nim, InviteCode: $inviteCode");
    
    if (empty($inviteCode)) {
        error_log("JOIN GROUP FAILED - Empty invite code");
        header("Location: ../../public/index.php?tab=grup&msg=invite_empty");
        exit;
    }
    
    $result = joinByInvite($inviteCode, $nim);
    
    // Debug log result
    error_log("JOIN GROUP RESULT - " . print_r($result, true));
    
    if ($result['success']) {
        error_log("JOIN GROUP SUCCESS - User $nim joined group via code $inviteCode");
        header("Location: ../../public/index.php?tab=grup&msg=join_success");
    } else {
        error_log("JOIN GROUP FAILED - " . $result['message']);
        header("Location: ../../public/index.php?tab=grup&msg=join_failed&error=" . urlencode($result['message']));
    }
    exit;
}

header("Location: ../../public/index.php?tab=grup");
exit;
?>
