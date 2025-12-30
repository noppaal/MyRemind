# MyRemind - MVC Procedural Refactoring

## 📋 Deskripsi

Refactoring aplikasi MyRemind dari struktur monolitik menjadi arsitektur **MVC (Model-View-Controller) Procedural** yang sederhana. Struktur baru memisahkan logika bisnis, akses data, dan presentasi ke dalam 3 folder utama: `model`, `view`, dan `controller`.

## 🗂️ Struktur Folder

```
MyRemind/
├── model/              # Data Access Layer
├── view/               # Presentation Layer  
├── controller/         # Business Logic Layer
├── config.php          # Konfigurasi session & timezone
└── *.php               # Entry point files
```

## 🚀 Cara Mengaktifkan MVC Baru

File-file baru dibuat dengan suffix `_new` untuk keamanan. Untuk mengaktifkan:

### Opsi 1: Rename File Baru (Recommended)

```powershell
# Backup file lama terlebih dahulu
Move-Item login.php login_old.php
Move-Item register.php register_old.php
Move-Item logout.php logout_old.php
Move-Item index.php index_old.php
Move-Item proses_tambah.php proses_tambah_old.php
Move-Item hapus.php hapus_old.php
Move-Item proses_selesai.php proses_selesai_old.php
Move-Item proses_progress.php proses_progress_old.php
Move-Item setting_profile.php setting_profile_old.php

# Rename file baru (hapus suffix _new)
Rename-Item login_new.php login.php
Rename-Item register_new.php register.php
Rename-Item logout_new.php logout.php
Rename-Item index_new.php index.php
Rename-Item proses_tambah_new.php proses_tambah.php
Rename-Item hapus_new.php hapus.php
Rename-Item proses_selesai_new.php proses_selesai.php
Rename-Item proses_progress_new.php proses_progress.php
Rename-Item setting_profile_new.php setting_profile.php
```

### Opsi 2: Update Semua Link

Ubah semua link di aplikasi dari `login.php` menjadi `login_new.php`, dst.

## 📁 File-File yang Dibuat

### Model (6 files)
- `model/Database.php` - Koneksi database
- `model/AuthModel.php` - Authentication
- `model/TaskModel.php` - Tugas
- `model/ScheduleModel.php` - Jadwal
- `model/GroupModel.php` - Grup
- `model/ProfileModel.php` - Profile

### Controller (6 files)
- `controller/AuthController.php` - Login, Register, Logout
- `controller/DashboardController.php` - Dashboard
- `controller/TaskController.php` - CRUD Tugas
- `controller/ScheduleController.php` - CRUD Jadwal
- `controller/GroupController.php` - CRUD Grup
- `controller/ProfileController.php` - Profile & Password

### View (2 files)
- `view/auth/login.php` - Halaman login
- `view/auth/register.php` - Halaman register

### Entry Points (5 files)
- `login_new.php` - Entry point login
- `register_new.php` - Entry point register
- `logout_new.php` - Entry point logout
- `index_new.php` - Entry point dashboard (main)
- `setting_profile_new.php` - Entry point settings

### Helper Routers (4 files)
- `proses_tambah_new.php` - Router tambah tugas/jadwal
- `hapus_new.php` - Router hapus tugas
- `proses_selesai_new.php` - Router tandai selesai
- `proses_progress_new.php` - Router tandai in progress

## 🔄 Cara Kerja MVC

### Flow Login
```
User → login.php 
    → AuthController::showLoginPage()
    → AuthModel::validateLogin()
    → Database::getConnection()
    → view/auth/login.php
```

### Flow Tambah Tugas
```
User → proses_tambah.php
    → TaskController::handleAddTask()
    → TaskModel::addTask()
    → Database::getConnection()
    → Redirect ke index.php
```

### Flow Dashboard
```
User → index.php
    → DashboardController::showDashboard()
    → TaskModel::getActiveTasks()
    → ScheduleModel::getSchedulesByDay()
    → view/dashboard/index.php
```

## 📝 Penggunaan `require` Antar File

Semua pemanggilan antar file menggunakan `require` atau `require_once`:

```php
// Di entry point
require_once 'config.php';
require_once 'controller/AuthController.php';

// Di controller
require_once __DIR__ . '/../model/AuthModel.php';

// Di model
require_once __DIR__ . '/Database.php';
```

## ✅ Testing

Setelah mengaktifkan MVC baru, test fitur-fitur berikut:

- [ ] Login dengan kredensial yang benar
- [ ] Register user baru
- [ ] Dashboard menampilkan data dengan benar
- [ ] Kalender menampilkan deadline tugas
- [ ] Tambah, edit, hapus tugas
- [ ] Tandai tugas selesai dan in progress
- [ ] Tambah, edit, hapus jadwal
- [ ] Filter jadwal berdasarkan hari
- [ ] Buat dan kelola grup
- [ ] Join grup dengan invite code
- [ ] Update profile
- [ ] Change password
- [ ] Import iCal dari LMS

## 📚 Dokumentasi Lengkap

Lihat file [walkthrough.md](file:///C:/Users/MEGABUNAWI/.gemini/antigravity/brain/93960b02-bbe6-448f-a92f-4c632724f683/walkthrough.md) untuk dokumentasi lengkap tentang:
- Struktur folder detail
- Fungsi-fungsi di setiap Model
- Handler di setiap Controller
- Flow request untuk setiap fitur
- Keuntungan refactoring

## ⚠️ Catatan Penting

1. **File lama masih ada** sebagai backup (tanpa suffix `_new`)
2. **Database connection** sekarang di `model/Database.php`, bukan di `config.php`
3. **Session management** tetap di `config.php`
4. **View untuk dashboard** belum dibuat lengkap, masih menggunakan logic di controller
5. Untuk view yang belum dibuat (dashboard, tasks, groups, profile), bisa copy HTML dari file lama

## 🎯 Next Steps

1. **Buat view lengkap** untuk dashboard, tasks, groups, dan profile
2. **Testing menyeluruh** semua fitur
3. **Cleanup** file-file lama setelah yakin MVC baru berfungsi
4. **Optimasi** query database jika diperlukan

## 📞 Support

Jika ada masalah atau pertanyaan, silakan hubungi developer.

---

© 2025 MyRemind - Refactored to MVC Procedural
