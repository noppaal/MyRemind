<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}

$nim = $_SESSION['nim'];

// Get user information
$query = "SELECT * FROM mahasiswa WHERE NIM = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $nim);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: logout.php");
    exit;
}

$user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Profil - MyRemind</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>
<body class="min-h-screen font-sans transition-colors duration-300 dark:bg-gray-900" style="background: linear-gradient(to bottom, #EEF2FF, #FAF5FF, #FDF2F8);">
    
    <!-- Notification Toast -->
    <?php if (isset($_GET['msg'])): ?>
    <?php
    $msg = $_GET['msg'];
    $message = '';
    $bg_color = 'bg-blue-100';
    $text_color = 'text-blue-700';
    
    switch($msg) {
        case 'password_changed':
            $message = 'Password berhasil diubah!';
            $bg_color = 'bg-green-100';
            $text_color = 'text-green-700';
            break;
        case 'wrong_old_password':
            $message = 'Password lama tidak sesuai!';
            $bg_color = 'bg-red-100';
            $text_color = 'text-red-700';
            break;
        case 'password_mismatch':
            $message = 'Password baru dan konfirmasi tidak cocok!';
            $bg_color = 'bg-red-100';
            $text_color = 'text-red-700';
            break;
        case 'password_too_short':
            $message = 'Password minimal 6 karakter!';
            $bg_color = 'bg-red-100';
            $text_color = 'text-red-700';
            break;
        case 'change_failed':
            $message = 'Gagal mengubah password!';
            $bg_color = 'bg-red-100';
            $text_color = 'text-red-700';
            break;
    }
    
    if (!empty($message)):
    ?>
    <div id="toast" class="fixed top-4 right-4 <?= $bg_color ?> <?= $text_color ?> px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-3 animate-slide-in">
        <i class="fas fa-info-circle"></i>
        <span class="font-medium"><?= $message ?></span>
        <button onclick="document.getElementById('toast').remove()" class="ml-2 hover:opacity-70">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.style.animation = 'slide-out 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);
    </script>
    <style>
        @keyframes slide-in {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slide-out {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
    </style>
    <?php endif; ?>
    <?php endif; ?>
    
    <div class="max-w-4xl mx-auto min-h-screen shadow-xl bg-white dark:bg-gray-900 transition-colors duration-300">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-4">
            <div class="flex items-center gap-3">
                <button onclick="window.location.href='index.php'" class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-all">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="flex-1">
                    <h1 class="text-xl font-bold">Pengaturan Profil</h1>
                    <p class="text-sm text-white/80">Kelola informasi akun Anda</p>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4 space-y-4">
            <!-- Profile Information Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-lg border border-gray-200 dark:border-gray-700 transition-colors duration-300">
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold">
                        <?= strtoupper(substr($user['Nama'], 0, 1)) ?>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($user['Nama']) ?></h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($user['NIM']) ?></p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <i class="fas fa-id-card text-purple-600 w-5"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">NIM</p>
                            <p class="font-medium text-gray-800 dark:text-white"><?= htmlspecialchars($user['NIM']) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <i class="fas fa-user text-purple-600 w-5"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Nama Lengkap</p>
                            <p class="font-medium text-gray-800 dark:text-white"><?= htmlspecialchars($user['Nama']) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <i class="fas fa-envelope text-purple-600 w-5"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                            <p class="font-medium text-gray-800 dark:text-white"><?= htmlspecialchars($user['Email']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-lg border border-gray-200 dark:border-gray-700 transition-colors duration-300">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">
                    <i class="fas fa-lock mr-2 text-purple-600"></i>Ganti Password
                </h3>

                <form action="proses_change_password.php" method="POST" id="changePasswordForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Password Lama <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="old_password" id="oldPassword" required 
                                    class="w-full px-4 py-3 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white transition-colors duration-300" 
                                    placeholder="Masukkan password lama">
                                <button type="button" onclick="togglePassword('oldPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Password Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="new_password" id="newPassword" required 
                                    class="w-full px-4 py-3 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white transition-colors duration-300" 
                                    placeholder="Masukkan password baru">
                                <button type="button" onclick="togglePassword('newPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minimal 6 karakter</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Konfirmasi Password Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="confirm_password" id="confirmPassword" required 
                                    class="w-full px-4 py-3 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white transition-colors duration-300" 
                                    placeholder="Ulangi password baru">
                                <button type="button" onclick="togglePassword('confirmPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-medium hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-save mr-2"></i>Simpan Password Baru
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl p-5 border border-red-200 dark:border-red-800 transition-colors duration-300">
                <h3 class="text-lg font-bold text-red-800 dark:text-red-400 mb-2">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Zona Bahaya
                </h3>
                <p class="text-sm text-red-700 dark:text-red-300 mb-4">Tindakan ini tidak dapat dibatalkan</p>
                <button onclick="logout()" class="w-full py-3 px-4 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-all duration-300">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout dari Akun
                </button>
            </div>
        </div>
    </div>

    <script>
        // Dark mode initialization
        const currentMode = localStorage.getItem('darkMode') || 'light';
        if (currentMode === 'dark') {
            document.documentElement.classList.add('dark');
        }

        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form validation
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password baru minimal 6 karakter!');
                return;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password tidak cocok!');
                return;
            }
        });

        // Logout function
        function logout() {
            if (confirm('Yakin ingin logout dari akun Anda?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>
