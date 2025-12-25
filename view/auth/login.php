<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MyRemind</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>
<body class="min-h-screen font-sans transition-colors duration-300" style="background: linear-gradient(to bottom, #EEF2FF, #FAF5FF, #FDF2F8);">
    
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-purple-600 to-indigo-600 mb-4 shadow-lg">
                    <i class="fas fa-calendar-check text-3xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent mb-2">MyRemind</h1>
                <p class="text-gray-600 text-sm">Asisten Cerdas Mahasiswa Telkom University</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Masuk ke Akun</h2>

                <!-- Notifications -->
                <?php if(isset($_GET['msg']) && $_GET['msg'] == 'registered'): ?>
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start gap-3">
                    <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-green-800">Akun berhasil dibuat!</p>
                        <p class="text-xs text-green-600 mt-1">Silakan login dengan akun Anda</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['msg']) && $_GET['msg'] == 'logout'): ?>
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-start gap-3">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                    <p class="text-sm text-blue-800">Anda telah logout. Silakan login kembali.</p>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['msg']) && $_GET['msg'] == 'session_expired'): ?>
                <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-yellow-800">Sesi Anda telah berakhir</p>
                        <p class="text-xs text-yellow-600 mt-1">Silakan login kembali untuk melanjutkan</p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(!empty($error)): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
                    <i class="fas fa-times-circle text-red-600 mt-0.5"></i>
                    <p class="text-sm text-red-800"><?= htmlspecialchars($error) ?></p>
                </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-purple-600"></i>Email
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
                                placeholder="••••••••">
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="login" 
                        class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">Belum punya akun?</span>
                    </div>
                </div>

                <!-- Register Link -->
                <a href="register.php" 
                    class="block w-full py-3 px-4 border-2 border-purple-600 text-purple-600 rounded-xl font-medium text-center hover:bg-purple-50 transition-all duration-300">
                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
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
