-- SQL untuk membuat tabel-tabel Grup
-- Jalankan query ini di phpMyAdmin untuk membuat struktur database grup

-- 1. Tabel Grup
CREATE TABLE IF NOT EXISTS `grup` (
  `IDGrup` int(11) NOT NULL AUTO_INCREMENT,
  `NamaGrup` varchar(100) NOT NULL,
  `Deskripsi` text,
  `TanggalDibuat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`IDGrup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Anggota Grup
CREATE TABLE IF NOT EXISTS `grup_members` (
  `IDMember` int(11) NOT NULL AUTO_INCREMENT,
  `IDGrup` int(11) NOT NULL,
  `NIM` varchar(20) NOT NULL,
  `Role` enum('admin','member') NOT NULL DEFAULT 'member',
  `TanggalBergabung` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`IDMember`),
  KEY `IDGrup` (`IDGrup`),
  KEY `NIM` (`NIM`),
  FOREIGN KEY (`IDGrup`) REFERENCES `grup` (`IDGrup`) ON DELETE CASCADE,
  FOREIGN KEY (`NIM`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Invite Code Grup
CREATE TABLE IF NOT EXISTS `grup_invites` (
  `IDInvite` int(11) NOT NULL AUTO_INCREMENT,
  `IDGrup` int(11) NOT NULL,
  `InviteCode` varchar(20) NOT NULL,
  `CreatedBy` varchar(20) NOT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ExpiresAt` datetime NOT NULL,
  PRIMARY KEY (`IDInvite`),
  UNIQUE KEY `InviteCode` (`InviteCode`),
  KEY `IDGrup` (`IDGrup`),
  FOREIGN KEY (`IDGrup`) REFERENCES `grup` (`IDGrup`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Jadwal Grup
CREATE TABLE IF NOT EXISTS `grup_jadwal` (
  `IDJadwal` int(11) NOT NULL AUTO_INCREMENT,
  `IDGrup` int(11) NOT NULL,
  `NamaKegiatan` varchar(100) NOT NULL,
  `Hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `JamMulai` time NOT NULL,
  `JamSelesai` time NOT NULL,
  `Lokasi` varchar(100),
  `Keterangan` text,
  PRIMARY KEY (`IDJadwal`),
  KEY `IDGrup` (`IDGrup`),
  FOREIGN KEY (`IDGrup`) REFERENCES `grup` (`IDGrup`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
