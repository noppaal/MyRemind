-- Migration: Add Group and Collaborative Scheduling Tables
-- Run this SQL in phpMyAdmin or MySQL CLI

-- 1. Create grup table
CREATE TABLE `grup` (
  `KodeGrup` VARCHAR(50) NOT NULL PRIMARY KEY,
  `NamaGrup` VARCHAR(100) NOT NULL,
  `Deskripsi` TEXT,
  `CreatedBy` VARCHAR(20) NOT NULL,
  `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`CreatedBy`) REFERENCES `mahasiswa`(`NIM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Create grup_anggota table
CREATE TABLE `grup_anggota` (
  `ID` INT AUTO_INCREMENT PRIMARY KEY,
  `KodeGrup` VARCHAR(50) NOT NULL,
  `NIM` VARCHAR(20) NOT NULL,
  `Role` ENUM('owner','admin','member') DEFAULT 'member',
  `JoinedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`KodeGrup`) REFERENCES `grup`(`KodeGrup`) ON DELETE CASCADE,
  FOREIGN KEY (`NIM`) REFERENCES `mahasiswa`(`NIM`) ON DELETE CASCADE,
  UNIQUE KEY `unique_member` (`KodeGrup`, `NIM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Create grup_jadwal table
CREATE TABLE `grup_jadwal` (
  `KodeJadwal` VARCHAR(50) NOT NULL PRIMARY KEY,
  `KodeGrup` VARCHAR(50) NOT NULL,
  `JudulKegiatan` VARCHAR(200) NOT NULL,
  `Deskripsi` TEXT,
  `TanggalMulai` DATETIME NOT NULL,
  `TanggalSelesai` DATETIME NOT NULL,
  `Lokasi` VARCHAR(100),
  `CreatedBy` VARCHAR(20) NOT NULL,
  `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`KodeGrup`) REFERENCES `grup`(`KodeGrup`) ON DELETE CASCADE,
  FOREIGN KEY (`CreatedBy`) REFERENCES `mahasiswa`(`NIM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Create grup_invite table
CREATE TABLE `grup_invite` (
  `ID` INT AUTO_INCREMENT PRIMARY KEY,
  `KodeGrup` VARCHAR(50) NOT NULL,
  `InviteCode` VARCHAR(20) UNIQUE NOT NULL,
  `CreatedBy` VARCHAR(20) NOT NULL,
  `ExpiresAt` DATETIME,
  `MaxUses` INT DEFAULT NULL,
  `UsedCount` INT DEFAULT 0,
  `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`KodeGrup`) REFERENCES `grup`(`KodeGrup`) ON DELETE CASCADE,
  FOREIGN KEY (`CreatedBy`) REFERENCES `mahasiswa`(`NIM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Verify tables created successfully
SHOW TABLES LIKE 'grup%';
