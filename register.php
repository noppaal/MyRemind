<?php
include 'config.php';

$error = '';

if (isset($_POST['register'])) {
    $nim = trim($_POST['nim']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $jurusan = trim($_POST['jurusan']);
    
    // Validate inputs
    if (empty($nim) || empty($nama) || empty($email) || empty($password) || empty($jurusan)) {
        $error = 'Semua field harus diisi!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Use prepared statement to check existing user
        $checkQuery = "SELECT NIM FROM mahasiswa WHERE NIM = ? OR Email = ?";
        $stmtCheck = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmtCheck, "ss", $nim, $email);
        mysqli_stmt_execute($stmtCheck);
        $resultCheck = mysqli_stmt_get_result($stmtCheck);
        
        if (mysqli_num_rows($resultCheck) > 0) {
            $error = 'NIM atau Email sudah terdaftar!';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $insertQuery = "INSERT INTO mahasiswa (NIM, Nama, Email, Password, Jurusan) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($stmtInsert, "sssss", $nim, $nama, $email, $hashedPassword, $jurusan);
            
            if (mysqli_stmt_execute($stmtInsert)) {
                header("Location: login.php?msg=registered");
                exit;
            } else {
                $error = 'Gagal mendaftar. Silakan coba lagi.';
            }
            
            mysqli_stmt_close($stmtInsert);
        }
        
        mysqli_stmt_close($stmtCheck);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - MyRemind</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>
<body class="min-h-screen font-sans transition-colors duration-300" style="background: linear-gradient(to bottom, #EEF2FF, #FAF5FF, #FDF2F8);">
    
    <div class="min-h-screen flex items-center justify-center p-4 py-8">
        <div class="w-full max-w-md">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-purple-600 to-indigo-600 mb-4 shadow-lg">
                    <i class="fas fa-user-plus text-3xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent mb-2">Buat Akun Baru</h1>
                <p class="text-gray-600 text-sm">Bergabung dengan MyRemind sekarang</p>
            </div>

            <!-- Register Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Isi Data Diri Anda</h2>

                <!-- Error Notification -->
                <?php if(!empty($error)): ?>
                <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
                    <i class="fas fa-times-circle text-red-600 mt-0.5"></i>
                    <p class="text-sm text-red-800"><?= htmlspecialchars($error) ?></p>
                </div>
                <?php endif; ?>

                <!-- Register Form -->
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-purple-600"></i>Nama Lengkap
                        </label>
                        <input type="text" name="nama" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300" 
                            placeholder="Nama lengkap Anda">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-id-card mr-2 text-purple-600"></i>NIM
                            </label>
                            <input type="text" name="nim" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300" 
                                placeholder="1301...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-graduation-cap mr-2 text-purple-600"></i>Jurusan
                            </label>
                            <select name="jurusan" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 bg-white">
                                <option value="">Pilih</option>
                                <option value="Informatika">Informatika</option>
                                <option value="Sistem Informasi">Sistem Informasi</option>
                                <option value="Teknik Komputer">Teknik Komputer</option>
                                <option value="DKV">DKV</option>
                                <option value="Teknik Elektro">Teknik Elektro</option>
                                <option value="Teknik Telekomunikasi">Teknik Telekomunikasi</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-purple-600"></i>Email Telkom University
                        </label>
                        <input type="email" name="email" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300" 
                            placeholder="nama@student.telkomuniversity.ac.id">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-purple-600"></i>Password
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required 
                                class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300" 
                                placeholder="Minimal 6 karakter">
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                    </div>

                    <button type="submit" name="register" 
                        class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 mt-6">
                        <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">Sudah punya akun?</span>
                    </div>
                </div>

                <!-- Login Link -->
                <a href="login.php" 
                    class="block w-full py-3 px-4 border-2 border-purple-600 text-purple-600 rounded-xl font-medium text-center hover:bg-purple-50 transition-all duration-300">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login Sekarang
                </a>
            </div>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-500 mt-6">
                © 2025 MyRemind. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>