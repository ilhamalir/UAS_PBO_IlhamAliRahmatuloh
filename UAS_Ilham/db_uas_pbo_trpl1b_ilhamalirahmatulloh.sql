-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 23, 2026 at 01:26 AM
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
-- Database: `db_uas_pbo_trpl1b_ilhamalirahmatulloh`
--

-- --------------------------------------------------------

--
-- Table structure for table `tabel_karyawan`
--

CREATE TABLE `tabel_karyawan` (
  `id_karyawan` int NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `departemen` varchar(100) NOT NULL,
  `hari_kerja_masuk` int NOT NULL,
  `gaji_dasar_per_hari` double NOT NULL,
  `jenis_karyawan` enum('Tetap','Kontrak','Magang') NOT NULL,
  `durasi_kontrak_bulan` int DEFAULT NULL,
  `uang_saku_bulanan` double DEFAULT NULL,
  `sertifikat_kampus_merdeka` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tabel_karyawan`
--

INSERT INTO `tabel_karyawan` (`id_karyawan`, `nama_karyawan`, `departemen`, `hari_kerja_masuk`, `gaji_dasar_per_hari`, `jenis_karyawan`, `durasi_kontrak_bulan`, `uang_saku_bulanan`, `sertifikat_kampus_merdeka`) VALUES
(1, 'Andi Saputra', 'IT', 22, 250000, 'Tetap', NULL, NULL, NULL),
(2, 'Budi Santoso', 'Keuangan', 20, 240000, 'Tetap', NULL, NULL, NULL),
(3, 'Citra Lestari', 'HRD', 22, 230000, 'Tetap', NULL, NULL, NULL),
(4, 'Dewi Anggraini', 'Marketing', 21, 235000, 'Tetap', NULL, NULL, NULL),
(5, 'Eko Prasetyo', 'Produksi', 20, 245000, 'Tetap', NULL, NULL, NULL),
(6, 'Fajar Nugroho', 'Gudang', 22, 220000, 'Tetap', NULL, NULL, NULL),
(7, 'Gina Maharani', 'IT', 21, 250000, 'Tetap', NULL, NULL, NULL),
(8, 'Hendra Wijaya', 'IT', 20, 180000, 'Kontrak', 12, NULL, NULL),
(9, 'Indah Permata', 'HRD', 22, 175000, 'Kontrak', 6, NULL, NULL),
(10, 'Joko Susilo', 'Marketing', 21, 185000, 'Kontrak', 12, NULL, NULL),
(11, 'Kevin Setiawan', 'Keuangan', 20, 180000, 'Kontrak', 6, NULL, NULL),
(12, 'Lina Oktavia', 'Produksi', 22, 190000, 'Kontrak', 12, NULL, NULL),
(13, 'Maya Sari', 'Gudang', 20, 170000, 'Kontrak', 6, NULL, NULL),
(14, 'Nanda Putra', 'IT', 21, 180000, 'Kontrak', 12, NULL, NULL),
(15, 'Olivia Putri', 'IT', 20, 100000, 'Magang', NULL, 1500000, 'Ya'),
(16, 'Pandu Wijaya', 'HRD', 20, 100000, 'Magang', NULL, 1500000, 'Tidak'),
(17, 'Qori Aulia', 'Marketing', 20, 100000, 'Magang', NULL, 1500000, 'Ya'),
(18, 'Rizky Ramadhan', 'Keuangan', 20, 100000, 'Magang', NULL, 1500000, 'Ya'),
(19, 'Salsa Nabila', 'Produksi', 20, 100000, 'Magang', NULL, 1500000, 'Tidak'),
(20, 'Tegar Saputra', 'Gudang', 20, 100000, 'Magang', NULL, 1500000, 'Ya');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tabel_karyawan`
--
ALTER TABLE `tabel_karyawan`
  ADD PRIMARY KEY (`id_karyawan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tabel_karyawan`
--
ALTER TABLE `tabel_karyawan`
  MODIFY `id_karyawan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
