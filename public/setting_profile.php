<?php
/**
 * Setting Profile - Entry Point untuk halaman settings
 */

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../controller/ProfileController.php';
$data = showSettingsPage();
extract($data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Profile - MyRemind</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-purple-50 via-blue-50 to-pink-50 min-h-screen">
    
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-user-cog mr-3 text-purple-600"></i>Pengaturan Profile
                </h1>
                <p class="text-gray-600 mt-2">Kelola informasi akun dan keamanan Anda</p>
            </div>
            <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($success)): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
            <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
            <p class="text-sm text-green-800"><?= htmlspecialchars($success) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
            <i class="fas fa-times-circle text-red-600 mt-0.5"></i>
            <p class="text-sm text-red-800"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg'])): ?>
        <div class="mb-6 p-4 <?= $_GET['msg'] == 'password_changed' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' ?> border rounded-lg">
            <p class="text-sm <?= $_GET['msg'] == 'password_changed' ? 'text-green-800' : 'text-red-800' ?>">
                <?php
                $messages = [
                    'password_changed' => 'Password berhasil diubah!',
                    'password_mismatch' => 'Password baru dan konfirmasi tidak cocok!',
                    'password_too_short' => 'Password minimal 6 karakter!',
                    'wrong_old_password' => 'Password lama tidak sesuai!',
                    'change_failed' => 'Gagal mengubah password!'
                ];
                echo $messages[$_GET['msg']] ?? 'Terjadi kesalahan!';
                ?>
            </p>
        </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Update Profile Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-user mr-3 text-blue-600"></i>Informasi Profile
                </h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NIM</label>
                        <input type="text" value="<?= htmlspecialchars($profile['NIM']) ?>" disabled 
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($profile['Nama']) ?>" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($profile['Email']) ?>" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jurusan</label>
                        <input type="text" value="<?= htmlspecialchars($profile['Jurusan'] ?? 'Teknik Informatika') ?>" disabled
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    
                    <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg font-medium hover:shadow-lg transition-all">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </form>
            </div>

            <!-- Change Password Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-lock mr-3 text-purple-600"></i>Ubah Password
                </h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Lama</label>
                        <input type="password" name="old_password" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                        <input type="password" name="new_password" required minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" required minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-medium hover:shadow-lg transition-all">
                        <i class="fas fa-key mr-2"></i>Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
