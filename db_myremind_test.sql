-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 25, 2025 at 11:59 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_myremind_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `KodeDosen` varchar(20) NOT NULL,
  `NamaDosen` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grup`
--

CREATE TABLE `grup` (
  `KodeGrup` varchar(50) NOT NULL,
  `NamaGrup` varchar(100) NOT NULL,
  `Deskripsi` text,
  `CreatedBy` varchar(20) NOT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grup_anggota`
--

CREATE TABLE `grup_anggota` (
  `ID` int NOT NULL,
  `KodeGrup` varchar(50) NOT NULL,
  `NIM` varchar(20) NOT NULL,
  `Role` enum('owner','admin','member') DEFAULT 'member',
  `JoinedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grup_jadwal`
--

CREATE TABLE `grup_jadwal` (
  `KodeJadwal` varchar(50) NOT NULL,
  `KodeGrup` varchar(50) NOT NULL,
  `JudulKegiatan` varchar(200) NOT NULL,
  `Deskripsi` text,
  `TanggalMulai` datetime NOT NULL,
  `TanggalSelesai` datetime NOT NULL,
  `Lokasi` varchar(200) DEFAULT NULL,
  `CreatedBy` varchar(20) NOT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwalkuliah`
--

CREATE TABLE `jadwalkuliah` (
  `KodeJadwal` varchar(50) NOT NULL,
  `NIM` varchar(20) NOT NULL,
  `KodeMK` varchar(20) DEFAULT NULL,
  `Hari` varchar(20) NOT NULL,
  `JamMulai` time NOT NULL,
  `JamSelesai` time NOT NULL,
  `Ruangan` varchar(50) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `NIM` varchar(20) NOT NULL,
  `Nama` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Jurusan` varchar(100) DEFAULT 'Teknik Informatika',
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`NIM`, `Nama`, `Email`, `Password`, `Jurusan`, `CreatedAt`) VALUES
('1301200001', 'Test User', 'test@example.com', '$2y$12$cM0qNOze8GLzO85Xj7CU7ez75YLnRipOM31/GWUafcgv4Ev5.6q6m', 'Teknik Informatika', '2025-12-25 23:51:57');

-- --------------------------------------------------------

--
-- Table structure for table `matakuliah`
--

CREATE TABLE `matakuliah` (
  `KodeMK` varchar(20) NOT NULL,
  `NamaMK` varchar(100) NOT NULL,
  `SKS` int DEFAULT '3',
  `KodeDosen` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `matakuliah`
--

INSERT INTO `matakuliah` (`KodeMK`, `NamaMK`, `SKS`, `KodeDosen`) VALUES
('GENERAL', 'Tugas LMS (Umum)', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tugas`
--

CREATE TABLE `tugas` (
  `KodeTugas` varchar(50) NOT NULL,
  `NIM` varchar(20) NOT NULL,
  `JudulTugas` varchar(200) NOT NULL,
  `Deskripsi` text,
  `Deadline` datetime NOT NULL,
  `Kategori` varchar(50) DEFAULT 'Tugas',
  `StatusTugas` varchar(20) DEFAULT 'Belum Selesai',
  `KodeMK` varchar(20) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tugas`
--

INSERT INTO `tugas` (`KodeTugas`, `NIM`, `JudulTugas`, `Deskripsi`, `Deadline`, `Kategori`, `StatusTugas`, `KodeMK`, `CreatedAt`, `UpdatedAt`) VALUES
('TSK-1766706717', '1301200001', 'Test Task', 'Test Desc', '2025-12-31 23:59:00', 'Tugas', 'Selesai', NULL, '2025-12-25 23:51:57', '2025-12-25 23:51:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`KodeDosen`);

--
-- Indexes for table `grup`
--
ALTER TABLE `grup`
  ADD PRIMARY KEY (`KodeGrup`),
  ADD KEY `CreatedBy` (`CreatedBy`);

--
-- Indexes for table `grup_anggota`
--
ALTER TABLE `grup_anggota`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `unique_member` (`KodeGrup`,`NIM`),
  ADD KEY `NIM` (`NIM`);

--
-- Indexes for table `grup_jadwal`
--
ALTER TABLE `grup_jadwal`
  ADD PRIMARY KEY (`KodeJadwal`),
  ADD KEY `KodeGrup` (`KodeGrup`),
  ADD KEY `CreatedBy` (`CreatedBy`);

--
-- Indexes for table `jadwalkuliah`
--
ALTER TABLE `jadwalkuliah`
  ADD PRIMARY KEY (`KodeJadwal`),
  ADD KEY `NIM` (`NIM`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`NIM`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `matakuliah`
--
ALTER TABLE `matakuliah`
  ADD PRIMARY KEY (`KodeMK`);

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`KodeTugas`),
  ADD KEY `NIM` (`NIM`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `grup_anggota`
--
ALTER TABLE `grup_anggota`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grup`
--
ALTER TABLE `grup`
  ADD CONSTRAINT `grup_ibfk_1` FOREIGN KEY (`CreatedBy`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE;

--
-- Constraints for table `grup_anggota`
--
ALTER TABLE `grup_anggota`
  ADD CONSTRAINT `grup_anggota_ibfk_1` FOREIGN KEY (`KodeGrup`) REFERENCES `grup` (`KodeGrup`) ON DELETE CASCADE,
  ADD CONSTRAINT `grup_anggota_ibfk_2` FOREIGN KEY (`NIM`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE;

--
-- Constraints for table `grup_jadwal`
--
ALTER TABLE `grup_jadwal`
  ADD CONSTRAINT `grup_jadwal_ibfk_1` FOREIGN KEY (`KodeGrup`) REFERENCES `grup` (`KodeGrup`) ON DELETE CASCADE,
  ADD CONSTRAINT `grup_jadwal_ibfk_2` FOREIGN KEY (`CreatedBy`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE;

--
-- Constraints for table `jadwalkuliah`
--
ALTER TABLE `jadwalkuliah`
  ADD CONSTRAINT `jadwalkuliah_ibfk_1` FOREIGN KEY (`NIM`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE;

--
-- Constraints for table `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`NIM`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
