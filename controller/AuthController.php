<?php
/**
 * AuthController - Controller untuk Authentication
 * Mengelola login, register, dan logout
 */

require_once __DIR__ . '/../model/AuthModel.php';

function showLoginPage() {
    $error = '';
    
    if (isset($_POST['login'])) {
        $error = handleLogin();
    }
    
    require_once __DIR__ . '/../view/auth/login.php';
}

function handleLogin() {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        return 'Email dan password harus diisi!';
    }
    
    // Validate login
    $result = validateLogin($email, $password);
    
    if ($result['success']) {
        $user = $result['user'];
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['nim'] = $user['NIM'];
        $_SESSION['nama'] = $user['Nama'];
        $_SESSION['email'] = $user['Email'];
        $_SESSION['login_time'] = time();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        // Redirect to dashboard
        header("Location: index.php");
        exit;
    } else {
        return $result['message'];
    }
}

function showRegisterPage() {
    $error = '';
    $success = '';
    
    if (isset($_POST['register'])) {
        $result = handleRegister();
        if ($result['success']) {
            header("Location: login.php?msg=registered");
            exit;
        } else {
            $error = $result['message'];
        }
    }
    
    require_once __DIR__ . '/../view/auth/register.php';
}

function handleRegister() {
    $nim = trim($_POST['nim']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($nim) || empty($nama) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Semua field harus diisi!'];
    }
    
    if ($password !== $confirmPassword) {
        return ['success' => false, 'message' => 'Password dan konfirmasi password tidak cocok!'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password minimal 6 karakter!'];
    }
    
    // Register user
    $data = [
        'nim' => $nim,
        'nama' => $nama,
        'email' => $email,
        'password' => $password
    ];
    
    return registerUser($data);
}

function handleLogout() {
    session_unset();
    session_destroy();
    header("Location: login.php?msg=logout");
    exit;
}
?>
