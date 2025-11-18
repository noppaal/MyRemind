<?php
include 'config.php';

if (isset($_POST['register'])) {
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Enkripsi Password
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);

    // Cek apakah NIM atau Email sudah ada
    $cek = mysqli_query($conn, "SELECT NIM FROM mahasiswa WHERE NIM = '$nim' OR Email = '$email'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "NIM atau Email sudah terdaftar!";
    } else {
        $query = "INSERT INTO mahasiswa (NIM, Nama, Email, Password, Jurusan) 
                  VALUES ('$nim', '$nama', '$email', '$password', '$jurusan')";
        
        if (mysqli_query($conn, $query)) {
            header("Location: login.php?msg=registered");
            exit;
        } else {
            $error = "Gagal mendaftar: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Akun - MyRemind</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 450px; border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .btn-primary { background-color: #6f42c1; border: none; }
        .btn-primary:hover { background-color: #5a32a3; }
    </style>
</head>
<body>
    <div class="card login-card p-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold" style="color: #6f42c1;">Buat Akun Baru</h3>
            <p class="text-muted small">Isi data diri Anda untuk memulai</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger py-2 small"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label small fw-bold">NIM</label>
                    <input type="text" name="nim" class="form-control" placeholder="1301..." required>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold">Jurusan</label>
                    <select name="jurusan" class="form-select">
                        <option>Informatika</option>
                        <option>Sistem Informasi</option>
                        <option>Teknik Komputer</option>
                        <option>DKV</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Email Telkom University</label>
                <input type="email" name="email" class="form-control" placeholder="@student.telkomuniversity.ac.id" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="register" class="btn btn-primary w-100 py-2 mb-3">Daftar Sekarang</button>
        </form>
        <div class="text-center">
            <small>Sudah punya akun? <a href="login.php" class="text-decoration-none fw-bold" style="color: #6f42c1;">Login disini</a></small>
        </div>
    </div>
</body>
</html>