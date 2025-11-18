-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 04:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_myremind`
--

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `KodeDosen` varchar(20) NOT NULL,
  `NamaDosen` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`KodeDosen`, `NamaDosen`, `Email`) VALUES
('DSN001', 'Dr. Budi Santoso', 'budi@telkom.ac.id'),
('DSN1763436368', 'Fatimah', 'dosen.691be750cafa5@telkom.ac.id'),
('DSN1763436413', 'Jawa', 'dosen.691be77d4a89c@telkom.ac.id');

-- --------------------------------------------------------

--
-- Table structure for table `jadwalkuliah`
--

CREATE TABLE `jadwalkuliah` (
  `KodeJadwal` int(11) NOT NULL,
  `KodeMK` varchar(20) NOT NULL,
  `NIM` varchar(20) NOT NULL,
  `Hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `JamMulai` time NOT NULL,
  `JamSelesai` time NOT NULL,
  `Ruangan` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwalkuliah`
--

INSERT INTO `jadwalkuliah` (`KodeJadwal`, `KodeMK`, `NIM`, `Hari`, `JamMulai`, `JamSelesai`, `Ruangan`) VALUES
(1, 'IF101', '1301210001', 'Senin', '08:00:00', '10:00:00', 'GKU-101'),
(2, 'DKV101', '1301210002', 'Selasa', '13:00:00', '15:00:00', 'B-204'),
(3, 'IFX-46-GAB', '1030123123', 'Senin', '09:30:00', '12:30:00', 'KU1.02.16'),
(4, 'IF-123', '1030123123', 'Selasa', '12:30:00', '15:30:00', 'KU1.02.16');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `NIM` varchar(20) NOT NULL,
  `Nama` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Jurusan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`NIM`, `Nama`, `Email`, `Password`, `Jurusan`) VALUES
('1030123123', 'tes', 'tes@student.telkomuniversity.ac.id', '$2y$10$PUFOskQT2hbYeBO49AyE9uChAVUDd5WLmuss8AcL7HUfaq2N70bFS', 'Informatika'),
('1301210001', 'Ahmad Fauzi', 'ahmad@student.telkomuniversity.ac.id', '$2y$10$Md/j.t5.0.X.1.2.3.4.5.6.7.8.9.0.1.2.3.4.5.6.7.8.9.0', 'Informatika'),
('1301210002', 'Gita Gutawa', 'gita@student.telkomuniversity.ac.id', '$2y$10$Md/j.t5.0.X.1.2.3.4.5.6.7.8.9.0.1.2.3.4.5.6.7.8.9.0', 'Desain Komunikasi Visual');

-- --------------------------------------------------------

--
-- Table structure for table `matakuliah`
--

CREATE TABLE `matakuliah` (
  `KodeMK` varchar(20) NOT NULL,
  `NamaMK` varchar(100) NOT NULL,
  `SKS` int(11) NOT NULL,
  `KodeDosen` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matakuliah`
--

INSERT INTO `matakuliah` (`KodeMK`, `NamaMK`, `SKS`, `KodeDosen`) VALUES
('DKV101', 'Nirmana Dwi Matra', 3, 'DSN001'),
('IF-123', 'Pemrograman Web', 3, 'DSN1763436413'),
('IF101', 'Pemrograman Web', 3, 'DSN001'),
('IF102', 'Basis Data', 4, 'DSN001'),
('IFX-46-GAB', 'Tata Tulis', 3, 'DSN1763436368');

-- --------------------------------------------------------

--
-- Table structure for table `tugas`
--

CREATE TABLE `tugas` (
  `KodeTugas` varchar(50) NOT NULL,
  `NIM` varchar(20) NOT NULL,
  `KodeMK` varchar(20) NOT NULL,
  `JudulTugas` varchar(200) NOT NULL,
  `Deskripsi` text DEFAULT NULL,
  `Deadline` datetime NOT NULL,
  `JenisTugas` enum('Individu','Kelompok','Quiz','UTS','UAS') DEFAULT 'Individu',
  `StatusTugas` enum('Aktif','Selesai','Expired') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tugas`
--

INSERT INTO `tugas` (`KodeTugas`, `NIM`, `KodeMK`, `JudulTugas`, `Deskripsi`, `Deadline`, `JenisTugas`, `StatusTugas`) VALUES
('LMS1160', '1030123123', 'IF101', 'd. TUGAS i - Mengukur OO Metric is due', 'Imported from LMS', '2025-12-19 17:00:00', 'Individu', 'Aktif'),
('LMS1601', '1030123123', 'IF101', 'Pengumpulan Presentasi Tugas Proyek Riset is due', 'Imported from LMS', '2025-11-13 16:59:00', 'Individu', 'Aktif'),
('LMS2158', '1030123123', 'IF101', 'c. QUIZ #i - DOKUMENTASI closes', 'Imported from LMS', '2025-11-22 16:59:00', 'Individu', 'Aktif'),
('LMS3345', '1030123123', 'IF101', 'Pengumpulan Tugas PPT is due', 'Imported from LMS', '2025-12-28 08:00:00', 'Individu', 'Aktif'),
('LMS3348', '1030123123', 'IF101', 'QUIZ FINAL - Evaluasi anggota Kelompok is due', 'Imported from LMS', '2025-12-26 17:00:00', 'Individu', 'Aktif'),
('LMS3742', '1030123123', 'IF101', 'c. QUIZ #i - REFACTORING closes', 'Imported from LMS', '2025-11-29 03:57:00', 'Individu', 'Aktif'),
('LMS3870', '1030123123', 'IF101', 'PENGUMPULAN TUGAS PAPPER - PROYEK RISET CLO1 is due', 'Imported from LMS', '2025-11-13 16:59:00', 'Individu', 'Aktif'),
('LMS4036', '1030123123', 'IF101', 'Final Project (Minggu 1-14) is due', 'Imported from LMS', '2025-12-20 16:59:00', 'Individu', 'Aktif'),
('LMS4415', '1030123123', 'IF101', 'd. TUGAS Week #9 -  Ragam uji data masukan error Form IMK is due', 'Imported from LMS', '2025-11-18 06:00:00', 'Individu', 'Aktif'),
('LMS4706', '1030123123', 'IF101', 'SOURCE CODE is due', 'Imported from LMS', '2025-12-26 17:00:00', 'Individu', 'Aktif'),
('LMS4847', '1030123123', 'IF101', 'c. QUIZ #i - REFACTORING opens', 'Imported from LMS', '2025-11-24 03:57:00', 'Individu', 'Aktif'),
('LMS4877', '1030123123', 'IF101', 'UPLOAD DOKUMEN USER MANUAL is due', 'Imported from LMS', '2025-12-26 17:00:00', 'Individu', 'Aktif'),
('LMS5055', '1030123123', 'IF101', 'PENGUMPULAN TUGAS POSTER is due', 'Imported from LMS', '2025-11-13 16:59:00', 'Individu', 'Aktif'),
('LMS5300', '1030123123', 'IF101', 'Submit your Assignment 3  here (submit as pdf file) is due', 'Imported from LMS', '2025-11-28 16:59:00', 'Individu', 'Aktif'),
('LMS6274', '1030123123', 'IF101', 'c. QUIZ #i - DOKUMENTASI opens', 'Imported from LMS', '2025-11-17 04:56:00', 'Individu', 'Aktif'),
('LMS6702', '1030123123', 'IF101', 'UPLOAD DOKUMEN SKPL\\, DPPL\\, DUPL is due', 'Imported from LMS', '2025-12-26 17:00:00', 'Individu', 'Aktif'),
('LMS7563', '1030123123', 'IF101', 'Quiz Review - Manpro 9 - Stakeholder akan berakhir', 'Imported from LMS', '2025-11-21 16:59:00', 'Individu', 'Aktif'),
('LMS8018', '1030123123', 'IF101', 'd. TUGAS #6 - KASUS UJI PROGRAM SEGITIGA is due', 'Imported from LMS', '2025-12-05 17:00:00', 'Individu', 'Aktif'),
('LMS8297', '1030123123', 'IF101', 'Submit your Assignment 4 here is due', 'Imported from LMS', '2025-12-05 16:59:00', 'Individu', 'Aktif'),
('LMS8310', '1030123123', 'IF101', 'UPLOAD SLIDE PRESENTASI is due', 'Imported from LMS', '2025-12-26 17:00:00', 'Individu', 'Aktif'),
('LMS8359', '1030123123', 'IF101', 'c. TUGAS #3 - MENYIAPKAN DUPL dari SKPL &amp\\; DPPL is due', 'Imported from LMS', '2025-12-12 17:00:00', 'Individu', 'Aktif'),
('LMS9358', '1030123123', 'IF101', 'Draft DNA dimunculkan is due', 'Imported from LMS', '2026-01-10 09:00:00', 'Individu', 'Aktif'),
('LMS9379', '1030123123', 'IF101', 'Quiz Review - Manpro 9 - Stakeholder dibuka', 'Imported from LMS', '2025-11-15 03:37:00', 'Individu', 'Aktif'),
('LMS9856', '1030123123', 'IF101', 'Pengumpulan Video is due', 'Imported from LMS', '2025-12-28 08:00:00', 'Individu', 'Aktif'),
('TGS-AHMAD-01', '1301210001', 'IF101', 'Tugas A: Makalah Web', 'Buat makalah tentang HTML5', '2025-11-25 23:59:00', 'Individu', 'Aktif'),
('TGS-AHMAD-02', '1301210001', 'IF102', 'Tugas B: Laporan Basis Data', 'Normalisasi tabel toko buku', '2025-11-28 23:59:00', 'Individu', 'Aktif'),
('TGS-GITA-01', '1301210002', 'IF102', 'Tugas B: Laporan Basis Data', 'Normalisasi tabel (Versi Desain)', '2025-11-28 23:59:00', 'Individu', 'Aktif'),
('TGS-GITA-02', '1301210002', 'DKV101', 'Tugas C: Sketsa Nirmana', 'Buat sketsa 2D menggunakan tinta cina', '2025-11-30 23:59:00', 'Individu', 'Aktif');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`KodeDosen`);

--
-- Indexes for table `jadwalkuliah`
--
ALTER TABLE `jadwalkuliah`
  ADD PRIMARY KEY (`KodeJadwal`),
  ADD KEY `KodeMK` (`KodeMK`),
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
  ADD PRIMARY KEY (`KodeMK`),
  ADD KEY `KodeDosen` (`KodeDosen`);

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`KodeTugas`),
  ADD KEY `KodeMK` (`KodeMK`),
  ADD KEY `NIM` (`NIM`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jadwalkuliah`
--
ALTER TABLE `jadwalkuliah`
  MODIFY `KodeJadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jadwalkuliah`
--
ALTER TABLE `jadwalkuliah`
  ADD CONSTRAINT `fk_jadwal_mhs` FOREIGN KEY (`NIM`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_jadwal_mk` FOREIGN KEY (`KodeMK`) REFERENCES `matakuliah` (`KodeMK`) ON DELETE CASCADE;

--
-- Constraints for table `matakuliah`
--
ALTER TABLE `matakuliah`
  ADD CONSTRAINT `fk_matakuliah_dosen` FOREIGN KEY (`KodeDosen`) REFERENCES `dosen` (`KodeDosen`) ON DELETE SET NULL;

--
-- Constraints for table `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `fk_tugas_mhs` FOREIGN KEY (`NIM`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tugas_mk` FOREIGN KEY (`KodeMK`) REFERENCES `matakuliah` (`KodeMK`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
