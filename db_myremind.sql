-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 07:58 AM
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
('LMS-691c0fd520f3d', '103012300399', 'GENERAL', 'Tugas 4a is due', 'Anggaplah kelompok kalian sedang melakukan penelitian dan telah', '2025-11-13 07:00:00', 'Individu', 'Selesai'),
('LMS-691c0fd521dfd', '103012300399', 'GENERAL', 'PENGUMPULAN TUGAS POSTER is due', '', '2025-11-13 16:59:00', 'Individu', 'Selesai'),
('LMS-691c0fd523993', '103012300399', 'GENERAL', 'PENGUMPULAN TUGAS PAPPER - PROYEK RISET CLO1 is due', 'Silakan mengumpulkan artikel penelitian tinjauan literatur pada', '2025-11-13 16:59:00', 'Individu', 'Selesai'),
('LMS-691c0fd52436a', '103012300399', 'GENERAL', 'Pengumpulan Presentasi Tugas Proyek Riset is due', 'Setiap kelompok diharapkan mengunggah video presentasi dengan d', '2025-11-13 16:59:00', 'Individu', 'Selesai'),
('LMS-691c0fd524ff4', '103012300399', 'GENERAL', '[IF-48-01] Jurnal Modul 9 - Syscall Xinu', '', '2025-11-14 23:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd5258e7', '103012300399', 'GENERAL', '[IF-48-01] Jurnal Modul 9 - Syscall Xinu', '', '2025-11-14 23:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd526237', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-06', '', '2025-11-14 23:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd527569', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-08 opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd52809a', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-09 opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd528a0f', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-10 opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd5293f9', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-11 opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd529ec5', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-12 opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd52a9be', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IFX-48-GAB opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd52b3ea', '103012300399', 'GENERAL', 'Preliminary Quiz Module 10 IF-48-INT opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd52bef0', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-01 opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd52cb57', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-05 opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd52d55a', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-04 opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd52ddf8', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-03 opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd52e643', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-02 opens', '', '2025-11-14 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd52eec2', '103012300399', 'GENERAL', '[IF-48-05] Jurnal Modul 9 - Syscall Xinu opens', '', '2025-11-15 00:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd52f712', '103012300399', 'GENERAL', '[IF-48-05] Jurnal Modul 9 - Syscall Xinu closes', '', '2025-11-15 02:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd52ff7a', '103012300399', 'GENERAL', '[IF-48-11] Jurnal Modul 9 -Syscall Xinu opens', '', '2025-11-15 03:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd530838', '103012300399', 'GENERAL', '[IF-48-11] Jurnal Modul 9 -Syscall Xinu closes', '', '2025-11-15 05:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd5310b8', '103012300399', 'GENERAL', 'Tugas Devensive Programming is due', '', '2025-11-15 06:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd5318dd', '103012300399', 'GENERAL', 'Tugas 7 - Konsistensi dan Fault Tolerance is due', '', '2025-11-15 06:30:00', 'Individu', 'Aktif'),
('LMS-691c0fd532f09', '103012300399', 'GENERAL', '[IF-48-03] Jurnal Modul 9 - Syscall Xinu opens', '', '2025-11-15 06:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd533809', '103012300399', 'GENERAL', '[IF-48-03] Jurnal Modul 9 - Syscall Xinu closes', '', '2025-11-15 08:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd5341e1', '103012300399', 'GENERAL', 'Kuis 9 - Dasar Komputasi Awan opens', '', '2025-11-15 09:30:00', 'Individu', 'Aktif'),
('LMS-691c0fd534c83', '103012300399', 'GENERAL', '[IF-48-01PJJ] JURNAL MODUL 2 - INSTALASI XINU closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd535609', '103012300399', 'GENERAL', '[IF-48-02PJJ] JURNAL MODUL 2 - INSTALASI XINU closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd536197', '103012300399', 'GENERAL', '[IF-48-03PJJ] JURNAL MODUL 2 - INSTALASI XINU closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd536b93', '103012300399', 'GENERAL', '[IF-48-01PJJ] JURNAL MODUL 3 - EKSPLORASI XINU closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd5374cb', '103012300399', 'GENERAL', '[IF-48-02PJJ] JURNAL MODUL 3 - EKSPLORASI XINU closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd537de5', '103012300399', 'GENERAL', '[IF-48-01PJJ] JURNAL MODUL 4 - MEMBACA SOURCE CODE XINU closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd53869a', '103012300399', 'GENERAL', '[IF-48-02PJJ] JURNAL MODUL 4 - MEMBACA SOURCE CODE XINU closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd538fa6', '103012300399', 'GENERAL', '[IF-48-03PJJ] JURNAL MODUL 4 - MEMBACA SOURCE CODE XINU closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd5398e0', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 5 IF-48-01PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd53a249', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 5 IF-48-02PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd53ab59', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 5 IF-48-03PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd53b405', '103012300399', 'GENERAL', '[IF-48-03PJJ] JURNAL MODUL 3 - EKSPLORASI XINU closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd53bd20', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 6 IF-48-01PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd53c617', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 6 IF-48-02PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd53d3b1', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 6 IF-48-03PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd53e200', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 7 IF-48-01PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd53ec39', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 7 IF-48-03PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd53f574', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 7 IF-48-02PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd53ff3d', '103012300399', 'GENERAL', '[IF-48-01PJJ] JURNAL MODUL 5 - EKSPLORASI PROSES closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd540883', '103012300399', 'GENERAL', '[IF-48-02PJJ] JURNAL MODUL 5 - EKSPLORASI PROSES closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd54120f', '103012300399', 'GENERAL', '[IF-48-03PJJ] JURNAL MODUL 5 - EKSPLORASI PROSES closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd541ba2', '103012300399', 'GENERAL', '[IF-48-01PJJ] JURNAL MODUL 6 - SEKUENSIAL &amp\\; KONKUREN closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd54241d', '103012300399', 'GENERAL', '[IF-48-02PJJ] JURNAL MODUL 6 - SEKUENSIAL &amp\\; KONKUREN closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd542ca3', '103012300399', 'GENERAL', '[IF-48-03PJJ] JURNAL MODUL 6 - SEKUENSIAL &amp\\; KONKUREN closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd543548', '103012300399', 'GENERAL', '[IF-48-03PJJ] JURNAL MODUL 7 - SEMAPHORE closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd543e4d', '103012300399', 'GENERAL', '[IF-48-01PJJ] JURNAL MODUL 7 - SEMAPHORE closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd5447bb', '103012300399', 'GENERAL', '[IF-48-02PJJ] JURNAL MODUL 7 - SEMAPHORE closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd5450dd', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 8 IF-48-01PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd545a85', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 8 IF-48-02PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd54633c', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 8 IF-48-03PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd546c7e', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 9 IF-48-01PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd547575', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 9 IF-48-02PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd547f4d', '103012300399', 'GENERAL', 'QUIZ AWAL MODUL 9 IF-48-03PJJ closes', '', '2025-11-15 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd54883e', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-06 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd5490eb', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-07 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd5499cb', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-08 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd54a29b', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-09 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd54ab4a', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-10 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd54b3c4', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-11 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd54bc0b', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-12 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd54c78f', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IFX-48-GAB closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd54d1f1', '103012300399', 'GENERAL', 'Preliminary Quiz Module 10 IF-48-INT closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd54db8d', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-01 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd54e45a', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-05 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd54eca6', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-04 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd54f584', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-03 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd550104', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-02 closes', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd550d80', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-06 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd5516dc', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-07 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd551f64', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-08 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd5527db', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-09 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd5530c8', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-10 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd55396b', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-11 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd554270', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-12 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd554bd2', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IFX-48-GAB should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd5556f0', '103012300399', 'GENERAL', 'Preliminary Quiz Module 10 IF-48-INT should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd556125', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-01 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd556ab1', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-05 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd55741c', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-04 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd557cf6', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-03 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd558596', '103012300399', 'GENERAL', 'Quiz Awal Modul 10 IF-48-02 should be completed', '', '2025-11-16 23:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd558e24', '103012300399', 'GENERAL', '[IF-48-02] Jurnal Modul 10 - Shell opens', '', '2025-11-16 23:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd559646', '103012300399', 'GENERAL', '[IF-48-02] Jurnal Modul 10 - Shell closes', '', '2025-11-17 01:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd559e8f', '103012300399', 'GENERAL', 'c. QUIZ #i - DOKUMENTASI opens', 'Boleh diulang sampai mendapat nilai 10.\\n\\nNilai (yang berbeda)', '2025-11-17 04:56:00', 'Individu', 'Aktif'),
('LMS-691c0fd55a6f4', '103012300399', 'GENERAL', '[IF-48-09] Jurnal Modul 10 - Shell opens', '', '2025-11-17 05:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd55bc60', '103012300399', 'GENERAL', '[IF-48-09] Jurnal Modul 10 - Shell closes', '', '2025-11-17 07:20:00', 'Individu', 'Selesai'),
('LMS-691c0fd55c55f', '103012300399', 'GENERAL', '[IF-48-10] Jurnal Modul 10 - Shell dibuka', '', '2025-11-17 08:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd55ce5a', '103012300399', 'GENERAL', '[IF-48-10] Jurnal Modul 10 - Shell akan berakhir', '', '2025-11-17 10:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd55d6de', '103012300399', 'GENERAL', '[IF-48-08] Jurnal Modul 10 - Shell opens', '', '2025-11-17 23:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd55df86', '103012300399', 'GENERAL', '[IF-48-12] Jurnal Modul 10 - Shell opens', '', '2025-11-17 23:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd55e7fd', '103012300399', 'GENERAL', '[IF-48-08] Jurnal Modul 10 - Shell closes', '', '2025-11-18 01:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd55f072', '103012300399', 'GENERAL', '[IF-48-12] Jurnal Modul 10 - Shell closes', '', '2025-11-18 01:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd55fa52', '103012300399', 'GENERAL', '[IF-48-07] Jurnal Modul 10 - Shell opens', '', '2025-11-18 02:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd560326', '103012300399', 'GENERAL', '[IFX-48-GAB] Jurnal Modul 10 - Shell opens', '', '2025-11-18 02:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd560bf1', '103012300399', 'GENERAL', '[IF-48-07] Jurnal Modul 10 - Shell closes', '', '2025-11-18 04:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd561499', '103012300399', 'GENERAL', '[IFX-48-GAB] Jurnal Modul 10 - Shell closes', '', '2025-11-18 04:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd561e3d', '103012300399', 'GENERAL', '[IF-48-INT] Module 10 Journal - Shell opens', '', '2025-11-18 23:40:00', 'Individu', 'Selesai'),
('LMS-691c0fd5626f8', '103012300399', 'GENERAL', '[IF-48-INT] Module 10 Journal - Shell closes', '', '2025-11-19 01:20:00', 'Individu', 'Selesai'),
('LMS-691c0fd563131', '103012300399', 'GENERAL', '[IF-48-04] Jurnal Modul 10 - Shell opens', '', '2025-11-19 23:40:00', 'Individu', 'Selesai'),
('LMS-691c0fd563ae8', '103012300399', 'GENERAL', '[IF-48-04] Jurnal Modul 10 - Shell closes', '', '2025-11-20 01:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd56446f', '103012300399', 'GENERAL', '[IF-48-06] Jurnal Modul 10 - Shell opens', '', '2025-11-20 02:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd564f0f', '103012300399', 'GENERAL', '[IF-48-06] Jurnal Modul 10 - Shell closes', '', '2025-11-20 04:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd5659ce', '103012300399', 'GENERAL', '[IF-48-03PJJ] JURNAL MODUL 9 - SYSCALL closes', '', '2025-11-20 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd5662cf', '103012300399', 'GENERAL', '[IF-48-02PJJ] JURNAL MODUL 9 - SYSCALL closes', '', '2025-11-20 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd566bea', '103012300399', 'GENERAL', '[IF-48-01PJJ] JURNAL MODUL 9 - SYSCALL closes', '', '2025-11-20 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd5675be', '103012300399', 'GENERAL', '[IF-48-01] Jurnal Modul 10 - Shell opens', '', '2025-11-21 00:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd567efa', '103012300399', 'GENERAL', '[IF-48-01] Jurnal Modul 10 - Shell closes', '', '2025-11-21 02:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd5687fa', '103012300399', 'GENERAL', '[IF-48-05] Jurnal Modul 10 - Shell opens', '', '2025-11-22 00:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd569023', '103012300399', 'GENERAL', '[IF-48-05] Jurnal Modul 10 - Shell closes', '', '2025-11-22 02:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd56997c', '103012300399', 'GENERAL', '[IF-48-11] Jurnal Modul 10 - Shell opens', '', '2025-11-22 03:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd56a253', '103012300399', 'GENERAL', '[IF-48-11] Jurnal Modul 10 - Shell closes', '', '2025-11-22 05:20:00', 'Individu', 'Aktif'),
('LMS-691c0fd56ab3d', '103012300399', 'GENERAL', 'Tugas 9 - Dasar Komputasi Awan is due', '', '2025-11-22 06:30:00', 'Individu', 'Aktif'),
('LMS-691c0fd56b398', '103012300399', 'GENERAL', 'Kuis 9 - Dasar Komputasi Awan closes', '', '2025-11-22 06:30:00', 'Individu', 'Aktif'),
('LMS-691c0fd56bc0c', '103012300399', 'GENERAL', '[IF-48-03] Jurnal Modul 10 - Shell opens', '', '2025-11-22 06:40:00', 'Individu', 'Aktif'),
('LMS-691c0fd56c4a7', '103012300399', 'GENERAL', '[IF-48-03] Jurnal Modul 10 - Shell closes', '', '2025-11-21 23:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd56cd6e', '103012300399', 'GENERAL', 'Kuis 10 - Arsitektur Komputasi Awan opens', '', '2025-11-20 23:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd56dc60', '103012300399', 'GENERAL', 'c. QUIZ #i - DOKUMENTASI closes', 'Boleh diulang sampai mendapat nilai 10.\\n\\nNilai (yang berbeda)', '2025-11-22 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd56e61a', '103012300399', 'GENERAL', 'TUGAS DECISION TREE CLASSIFICATION is due', 'DARI DATASET BERIKUT\\, HITUNG ENTROPY DAN INFORMATION GAIN SEPE', '2025-11-22 17:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd56effc', '103012300399', 'GENERAL', 'c. QUIZ #i - REFACTORING opens', 'Boleh diulang sampai dapat 10.\\nNilai (yang berbeda) akan dirat', '2025-11-24 03:57:00', 'Individu', 'Aktif'),
('LMS-691c0fd56f925', '103012300399', 'GENERAL', 'c. QUIZ #i - REFACTORING closes', 'Boleh diulang sampai dapat 10.\\nNilai (yang berbeda) akan dirat', '2025-11-29 03:57:00', 'Individu', 'Aktif'),
('LMS-691c0fd5701a8', '103012300399', 'GENERAL', 'Tugas 10 - Arsitektur Komputasi Awan is due', '', '2025-11-29 06:30:00', 'Individu', 'Aktif'),
('LMS-691c0fd570a52', '103012300399', 'GENERAL', 'Kuis 10 - Arsitektur Komputasi Awan closes', '', '2025-11-29 06:30:00', 'Individu', 'Aktif'),
('LMS-691c0fd571393', '103012300399', 'GENERAL', 'Tugas 5 is due', '* Tulis makalah lengkap (mulai judul\\, penulis\\, abstrak\\, pen', '2025-12-07 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd571da1', '103012300399', 'GENERAL', 'Final Project (Minggu 1-14) is due', '* Final Project dikerjakan secara berkelompok\\, 1 kelompok ter', '2025-12-20 16:59:00', 'Individu', 'Aktif'),
('LMS-691c0fd572689', '103012300399', 'GENERAL', 'Pengumpulan Tugas PPT is due', 'Silahkan untuk upload tugas beruupa PPT\\n', '2025-12-28 08:00:00', 'Individu', 'Aktif'),
('LMS-691c0fd573170', '103012300399', 'GENERAL', 'Pengumpulan Video is due', 'Silahkan uplaod link video proyek sosial\\n\\n', '2025-12-28 08:00:00', 'Individu', 'Aktif'),
('LMS-691c17fa0a63b', '103012300239', 'GENERAL', 'c. QUIZ #i - DOKUMENTASI opens', 'Boleh diulang sampai mendapat nilai 10.\\n\\nNilai (yang berbeda)', '2025-11-17 04:56:00', 'Individu', 'Selesai'),
('LMS-691c17fa0b257', '103012300239', 'GENERAL', 'd. TUGAS Week #9 -  Ragam uji data masukan error Form IMK is due', 'Tugas Kelompok\\n\\nUntuk sejumlah form yang setara dengan aplika', '2025-11-18 06:00:00', 'Individu', 'Selesai'),
('LMS-691c17fa0bbc6', '103012300239', 'GENERAL', 'Quiz Review - Manpro 9 - Stakeholder akan berakhir', 'Merupakan kuis yang ditujukan untuk mereview konten per pokok b', '2025-11-21 16:59:00', 'Individu', 'Selesai'),
('LMS-691c17fa0c5e8', '103012300239', 'GENERAL', 'c. QUIZ #i - DOKUMENTASI closes', 'Boleh diulang sampai mendapat nilai 10.\\n\\nNilai (yang berbeda)', '2025-11-22 16:59:00', 'Individu', 'Selesai'),
('LMS-691c17fa0cf7c', '103012300239', 'GENERAL', 'c. QUIZ #i - REFACTORING opens', 'Boleh diulang sampai dapat 10.\\nNilai (yang berbeda) akan dirat', '2025-11-24 03:57:00', 'Individu', 'Aktif'),
('LMS-691c17fa0d8f7', '103012300239', 'GENERAL', 'Submit your Assignment 3  here (submit as pdf file) is due', '•Deskripsi: Merancang sebuah artikel ilmiah\\n•Tujuan: Mahasiswa', '2025-11-28 16:59:00', 'Individu', 'Aktif'),
('LMS-691c17fa0e29c', '103012300239', 'GENERAL', 'c. QUIZ #i - REFACTORING closes', 'Boleh diulang sampai dapat 10.\\nNilai (yang berbeda) akan dirat', '2025-11-29 03:57:00', 'Individu', 'Aktif'),
('LMS-691c17fa0eb58', '103012300239', 'GENERAL', 'Submit your Assignment 4 here is due', '•Deskripsi: Menulis sebuah artikel ilmiah sesuai draft yang sud', '2025-12-05 16:59:00', 'Individu', 'Aktif'),
('LMS-691c17fa0f4d5', '103012300239', 'GENERAL', 'd. TUGAS #6 - KASUS UJI PROGRAM SEGITIGA is due', '', '2025-12-05 17:00:00', 'Individu', 'Aktif'),
('LMS-691c17fa10023', '103012300239', 'GENERAL', 'c. TUGAS #3 - MENYIAPKAN DUPL dari SKPL &amp\\; DPPL is due', '', '2025-12-12 17:00:00', 'Individu', 'Aktif'),
('LMS-691c17fa10af9', '103012300239', 'GENERAL', 'd. TUGAS i - Mengukur OO Metric is due', '', '2025-12-19 17:00:00', 'Individu', 'Aktif'),
('LMS-691c17fa11414', '103012300239', 'GENERAL', 'Final Project (Minggu 1-14) is due', '* Final Project dikerjakan secara berkelompok\\, 1 kelompok ter', '2025-12-20 16:59:00', 'Individu', 'Aktif'),
('LMS-691c17fa11e42', '103012300239', 'GENERAL', 'UPLOAD SLIDE PRESENTASI is due', '', '2025-12-26 17:00:00', 'Individu', 'Aktif'),
('LMS-691c17fa13833', '103012300239', 'GENERAL', 'SOURCE CODE is due', 'Dokumen Source Code [1] atau link GitHub\\n\\n\\nLinks:\\n------\\n[', '2025-12-26 17:00:00', 'Individu', 'Aktif'),
('LMS-691c17fa144f6', '103012300239', 'GENERAL', 'UPLOAD DOKUMEN USER MANUAL is due', '', '2025-12-26 17:00:00', 'Individu', 'Aktif'),
('LMS-691c17fa1500e', '103012300239', 'GENERAL', 'UPLOAD DOKUMEN SKPL\\, DPPL\\, DUPL is due', '', '2025-12-26 17:00:00', 'Individu', 'Aktif'),
('LMS-691c17fa15b5f', '103012300239', 'GENERAL', 'QUIZ FINAL - Evaluasi anggota Kelompok is due', '', '2025-12-26 17:00:00', 'Individu', 'Aktif'),
('LMS-691c17fa164eb', '103012300239', 'GENERAL', 'Pengumpulan Tugas PPT is due', 'Silahkan untuk upload tugas beruupa PPT\\n', '2025-12-28 08:00:00', 'Individu', 'Aktif'),
('LMS-691c17fa16df4', '103012300239', 'GENERAL', 'Pengumpulan Video is due', 'Silahkan uplaod link video proyek sosial\\n\\n', '2025-12-28 08:00:00', 'Individu', 'Aktif'),
('LMS-691c17fa176b8', '103012300239', 'GENERAL', 'Draft DNA dimunculkan is due', '', '2026-01-10 09:00:00', 'Individu', 'Aktif');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`KodeTugas`),
  ADD KEY `KodeMK` (`KodeMK`),
  ADD KEY `NIM` (`NIM`);

--
-- Constraints for dumped tables
--

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
