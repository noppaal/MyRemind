# Cara Menjalankan Migration Database Grup

## Langkah 1: Buka phpMyAdmin
1. Buka browser
2. Akses: http://localhost/phpmyadmin
3. Login dengan:
   - Username: `root`
   - Password: (kosong, langsung klik Go)

## Langkah 2: Pilih Database
1. Di sidebar kiri, klik database `db_myremind`
2. Pastikan database sudah dipilih (akan terlihat highlight)

## Langkah 3: Buka Tab SQL
1. Klik tab "SQL" di bagian atas
2. Akan muncul text area untuk query SQL

## Langkah 4: Copy SQL Migration
1. Buka file `migration_grup_tables.sql` di folder MyRemind
2. Copy semua isi file (Ctrl+A, Ctrl+C)
3. Paste ke text area SQL di phpMyAdmin (Ctrl+V)

## Langkah 5: Jalankan Query
1. Klik tombol "Go" atau "Kirim" di bawah text area
2. Tunggu hingga proses selesai
3. Jika berhasil, akan muncul pesan sukses

## Langkah 6: Verifikasi
1. Klik tab "Structure" di database db_myremind
2. Pastikan 4 tabel baru sudah muncul:
   - `grup`
   - `grup_anggota`
   - `grup_jadwal`
   - `grup_invite`

## Langkah 7: Test di Aplikasi
1. Refresh browser di halaman MyRemind
2. Klik tab "Grup"
3. Seharusnya tidak ada error lagi
4. Coba klik "Buat Grup" untuk membuat grup pertama

## Troubleshooting

### Error: Table already exists
- Artinya tabel sudah pernah dibuat
- Skip migration, langsung test aplikasi

### Error: Foreign key constraint
- Pastikan tabel `mahasiswa` sudah ada
- Jalankan migration database utama terlebih dahulu

### Error: Access denied
- Pastikan login sebagai root
- Atau user yang memiliki privilege CREATE TABLE

## Isi Migration SQL

File `migration_grup_tables.sql` berisi:
- CREATE TABLE `grup` - Tabel utama grup
- CREATE TABLE `grup_anggota` - Tabel anggota grup
- CREATE TABLE `grup_jadwal` - Tabel jadwal/event grup
- CREATE TABLE `grup_invite` - Tabel kode undangan

Semua tabel memiliki foreign key constraint untuk menjaga integritas data.
