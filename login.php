<?php
include 'config.php';

// Jika user sudah login, redirect ke dashboard
if (isset($_SESSION['nim'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Cek user berdasarkan Email
    $query = "SELECT * FROM mahasiswa WHERE Email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // --- LOGIKA VERIFIKASI PASSWORD ---
        // 1. Cek apakah password cocok dengan hash database (Cara Aman/Standar)
        // 2. ATAU cek apakah password adalah '123' (Cara Bypass untuk Testing)
        if (password_verify($password, $row['Password']) || $password == '123') { 
            
            // Set Session
            $_SESSION['nim'] = $row['NIM'];
            $_SESSION['nama'] = $row['Nama'];
            
            // Redirect ke Dashboard
            header("Location: index.php");
            exit;
        }
    }
    
    // Jika email tidak ketemu atau password salah
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login - MyRemind</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .btn-primary { background-color: #6f42c1; border: none; } /* Ungu Khas MyRemind */
        .btn-primary:hover { background-color: #5a32a3; }
    </style>
</head>
<body>
    <div class="card login-card p-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold" style="color: #6f42c1;">MyRemind</h3>
            <p class="text-muted">Asisten Cerdas Mahasiswa Telkom University</p>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'registered'): ?>
            <div class="alert alert-success py-2 small text-center">Akun berhasil dibuat! Silakan login.</div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger py-2 small text-center">Email atau Password salah!</div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="nama@student.telkomuniversity.ac.id" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="******" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100 py-2 mb-3">Masuk</button>
        </form>
        <div class="text-center">
            <small class="text-muted">Belum punya akun? <a href="register.php" class="text-decoration-none fw-bold" style="color: #6f42c1;">Daftar disini</a></small>
    </div>
</body>
</html>