# Cara Menambahkan Status "In Progress"

## Langkah-langkah:

### 1. Buka phpMyAdmin
- Akses: http://localhost/phpmyadmin
- Login dengan user: `root`, password: (kosong)

### 2. Pilih Database
- Klik database `db_myremind` di sidebar kiri

### 3. Jalankan SQL Query
- Klik tab "SQL" di bagian atas
- Copy dan paste query berikut:

```sql
ALTER TABLE `tugas` 
MODIFY COLUMN `StatusTugas` ENUM('Aktif','In Progress','Selesai','Expired') DEFAULT 'Aktif';
```

- Klik tombol "Go" atau "Kirim"

### 4. Verifikasi
- Klik tabel `tugas` di sidebar
- Klik tab "Structure"
- Pastikan kolom `StatusTugas` sekarang memiliki nilai: 'Aktif', 'In Progress', 'Selesai', 'Expired'

## Setelah Migration

Setelah menjalankan query di atas:
- ✅ Tombol "Mulai" akan mengubah status tugas ke "In Progress"
- ✅ Tidak akan ada error lagi
- ✅ Tugas akan terlihat dengan status yang berbeda di UI

## Alternatif: Via MySQL Command Line

Jika Anda prefer menggunakan command line:

```bash
# Masuk ke direktori MySQL
cd C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin

# Jalankan MySQL
.\mysql.exe -u root

# Di MySQL prompt:
USE db_myremind;
ALTER TABLE tugas MODIFY COLUMN StatusTugas ENUM('Aktif','In Progress','Selesai','Expired') DEFAULT 'Aktif';
EXIT;
```

## File yang Sudah Diupdate

1. ✅ `proses_progress.php` - Sudah menggunakan status 'In Progress'
2. ✅ `migration_add_inprogress.sql` - File SQL untuk migration

Setelah migration selesai, refresh browser dan coba tombol "Mulai" lagi!
