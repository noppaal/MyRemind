<?php
/**
 * ProfileController - Controller untuk Profile
 * Mengelola profile dan password user
 */

require_once __DIR__ . '/../model/ProfileModel.php';

function showSettingsPage() {
    $nim = $_SESSION['nim'];
    $profile = getProfile($nim);
    
    $error = '';
    $success = '';
    
    // Handle update profile
    if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
        $result = handleUpdateProfile();
        if ($result['success']) {
            $success = $result['message'];
            // Update session
            $_SESSION['nama'] = $_POST['nama'];
            $_SESSION['email'] = $_POST['email'];
            // Refresh profile data
            $profile = getProfile($nim);
        } else {
            $error = $result['message'];
        }
    }
    
    // Handle change password
    if (isset($_POST['action']) && $_POST['action'] == 'change_password') {
        $result = handleChangePassword();
        if ($result['success']) {
            header("Location: setting_profile.php?msg=password_changed");
            exit;
        } else {
            header("Location: setting_profile.php?msg=" . urlencode($result['message']));
            exit;
        }
    }
    
    // View will be loaded by setting_profile.php
    return compact('profile', 'error', 'success');
}

function handleUpdateProfile() {
    $nim = $_SESSION['nim'];
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    
    if (empty($nama) || empty($email)) {
        return ['success' => false, 'message' => 'Nama dan email harus diisi!'];
    }
    
    $data = [
        'nama' => $nama,
        'email' => $email
    ];
    
    return updateProfile($nim, $data);
}

function handleChangePassword() {
    $nim = $_SESSION['nim'];
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        return ['success' => false, 'message' => 'Semua field password harus diisi!'];
    }
    
    if ($newPassword !== $confirmPassword) {
        return ['success' => false, 'message' => 'password_mismatch'];
    }
    
    if (strlen($newPassword) < 6) {
        return ['success' => false, 'message' => 'password_too_short'];
    }
    
    $result = changePassword($nim, $oldPassword, $newPassword);
    
    // Format message for URL parameter
    if (!$result['success']) {
        if ($result['message'] == 'Password lama tidak sesuai!') {
            $result['message'] = 'wrong_old_password';
        } elseif ($result['message'] == 'Gagal mengubah password!') {
            $result['message'] = 'change_failed';
        }
    }
    
    return $result;
}
?>
