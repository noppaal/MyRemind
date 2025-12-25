-- TROUBLESHOOTING MIGRATION

-- 1. CEK APAKAH MIGRATION SUDAH BERJALAN
-- Jalankan query ini untuk melihat struktur kolom StatusTugas saat ini:
SHOW COLUMNS FROM tugas LIKE 'StatusTugas';

-- Hasilnya harus menunjukkan:
-- Type: enum('Aktif','In Progress','Selesai','Expired')
-- Jika masih: enum('Aktif','Selesai','Expired') maka migration belum berhasil


-- 2. JIKA MIGRATION BELUM BERHASIL, JALANKAN INI:
ALTER TABLE `tugas` 
MODIFY COLUMN `StatusTugas` ENUM('Aktif','In Progress','Selesai','Expired') DEFAULT 'Aktif';


-- 3. VERIFIKASI LAGI:
SHOW COLUMNS FROM tugas LIKE 'StatusTugas';


-- 4. TEST UPDATE MANUAL (opsional):
-- Ganti 'KODE_TUGAS_ANDA' dengan KodeTugas yang sebenarnya
UPDATE tugas SET StatusTugas = 'In Progress' WHERE KodeTugas = 'KODE_TUGAS_ANDA' LIMIT 1;

-- Jika query di atas berhasil tanpa error, berarti migration sudah OK
-- Jika error "Data truncated", berarti migration belum berjalan


-- 5. ALTERNATIF - DROP DAN RECREATE COLUMN (HATI-HATI: DATA AKAN HILANG)
-- JANGAN JALANKAN INI KECUALI ANDA YAKIN!
-- ALTER TABLE tugas DROP COLUMN StatusTugas;
-- ALTER TABLE tugas ADD COLUMN StatusTugas ENUM('Aktif','In Progress','Selesai','Expired') DEFAULT 'Aktif' AFTER JenisTugas;
