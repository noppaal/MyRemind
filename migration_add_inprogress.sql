-- Add 'In Progress' to StatusTugas enum
-- Run this SQL query in your database

ALTER TABLE `tugas` 
MODIFY COLUMN `StatusTugas` ENUM('Aktif','In Progress','Selesai','Expired') DEFAULT 'Aktif';
