-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2025 at 12:06 PM
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
-- Database: `penjualan_qrcode`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama_lengkap`, `email`, `created_at`, `updated_at`) VALUES
(2, 'Admin2', '$2y$10$VkCXGApXDiu0B1SzhtUU9emffgQH3jKBFFM5AYEdjYKesP4dvvVIS', 'Seseorang Lain', 'someone@gmail.com', '2025-07-03 09:35:30', '2025-07-03 09:35:30'),
(3, 'admin', '$2y$10$nODKQkN9ZEzsy3asbzlbw.0mYrtZr1RET.t50HZsQW9x8jjAwgSX2', 'Asministrator', 'tariiitariii333@gmail.com', '2025-07-03 09:45:52', '2025-07-03 09:45:52');

-- --------------------------------------------------------

--
-- Table structure for table `bukti_transfer`
--

CREATE TABLE `bukti_transfer` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `path_file` varchar(500) NOT NULL,
  `ukuran_file` int(11) DEFAULT NULL,
  `tipe_file` varchar(50) DEFAULT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_verifikasi` enum('menunggu','diterima','ditolak') DEFAULT 'menunggu',
  `catatan_admin` text DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bukti_transfer`
--

INSERT INTO `bukti_transfer` (`id`, `pesanan_id`, `nama_file`, `path_file`, `ukuran_file`, `tipe_file`, `tanggal_upload`, `status_verifikasi`, `catatan_admin`, `verified_by`, `verified_at`) VALUES
(4, 9, '68663c5417a30.png', '../assets/uploads/bukti-transfer/68663c5417a30.png', 11941, 'image/png', '2025-07-03 08:16:20', 'diterima', NULL, NULL, '2025-07-03 08:54:36');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `produk_id`, `jumlah`, `harga_satuan`, `subtotal`, `created_at`) VALUES
(9, 9, 3, 5, 400000.00, 2000000.00, '2025-07-03 08:16:05');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `deskripsi`, `status`, `created_at`) VALUES
(1, 'Paint & Coating', 'For wall and surface protection or waterproofing.', 'aktif', '2025-06-26 14:20:54'),
(2, 'Pipes & Plumbing', 'Used for water distribution or drainage.', 'aktif', '2025-06-26 14:20:54'),
(3, 'Construction Materials', 'Basic building materials.', 'aktif', '2025-06-26 14:20:54'),
(4, 'Adhesives & Sealants', 'Used to bond or seal materials.', 'aktif', '2025-06-26 14:20:54'),
(5, 'Others', 'Lain-lainnya', 'aktif', '2025-07-03 09:49:58');

-- --------------------------------------------------------

--
-- Table structure for table `log_stok`
--

CREATE TABLE `log_stok` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jenis_transaksi` enum('masuk','keluar','koreksi') NOT NULL,
  `jumlah` int(11) NOT NULL,
  `stok_sebelum` int(11) NOT NULL,
  `stok_sesudah` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_stok`
--

INSERT INTO `log_stok` (`id`, `produk_id`, `jenis_transaksi`, `jumlah`, `stok_sebelum`, `stok_sesudah`, `keterangan`, `admin_id`, `created_at`) VALUES
(9, 3, 'keluar', 5, 15, 10, 'Pesanan #ORD-20250703-5482', NULL, '2025-07-03 08:16:05');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `nama_toko` varchar(100) NOT NULL,
  `alamat_toko` text DEFAULT NULL,
  `no_hp_toko` varchar(20) DEFAULT NULL,
  `email_toko` varchar(100) DEFAULT NULL,
  `no_rekening` varchar(50) DEFAULT NULL,
  `nama_bank` varchar(50) DEFAULT NULL,
  `atas_nama` varchar(100) DEFAULT NULL,
  `logo_toko` varchar(255) DEFAULT NULL,
  `deskripsi_toko` text DEFAULT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_toko`, `alamat_toko`, `no_hp_toko`, `email_toko`, `no_rekening`, `nama_bank`, `atas_nama`, `logo_toko`, `deskripsi_toko`, `whatsapp_number`, `updated_at`) VALUES
(1, 'MJS', 'Batam, Kepulauan Riau', '6282269343968', 'info@megajayasakti.com', '1234567890', 'Bank Mandiri', 'PT. Mega Jaya Sakti', 'assets/uploads/settings/logo-1750998348.png', '', '6282269343968', '2025-07-02 07:30:01');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `nomor_pesanan` varchar(50) NOT NULL,
  `nama_pembeli` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `total_harga` decimal(15,2) NOT NULL,
  `status_pesanan` enum('pending','dikonfirmasi','diproses','dikirim','selesai','dibatalkan') DEFAULT 'pending',
  `status_pembayaran` enum('belum_bayar','menunggu_verifikasi','lunas','gagal') DEFAULT 'belum_bayar',
  `tanggal_pesanan` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_bayar` timestamp NULL DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `nomor_pesanan`, `nama_pembeli`, `no_hp`, `alamat`, `total_harga`, `status_pesanan`, `status_pembayaran`, `tanggal_pesanan`, `tanggal_bayar`, `qr_code`, `created_at`, `updated_at`) VALUES
(9, 'ORD-20250703-5482', 'User', '08666666999', 'abc', 2000000.00, 'selesai', 'lunas', '2025-07-03 08:16:05', '2025-07-03 08:54:36', 'order_9_1751530565.png', '2025-07-03 08:16:05', '2025-07-03 08:54:49');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `kode_produk` varchar(50) NOT NULL,
  `nama_produk` varchar(200) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `kode_produk`, `nama_produk`, `kategori_id`, `harga`, `stok`, `deskripsi`, `gambar`, `status`, `created_at`, `updated_at`) VALUES
(3, 'PROD-6865EFC228867', 'test2', 1, 400000.00, 15, '2', '6866561c1fb72.png', 'aktif', '2025-07-03 02:49:38', '2025-07-03 10:06:20'),
(5, 'PROD-65B8F4A1C0A1E', 'APP 37 Waterproofing', 1, 150000.00, 20, 'High-quality waterproofing coating for walls and surfaces', 'APP 37.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(6, 'PROD-65B8F4A1C0A30', 'Aquaproof Waterproofing', 1, 180000.00, 25, 'Premium waterproofing solution for bathrooms and wet areas', 'AQUAPROOF.webp', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(7, 'PROD-65B8F4A1C0A35', 'No Drop Waterproofing', 1, 165000.00, 30, 'Reliable waterproofing paint for exterior and interior use', 'NODROP.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(8, 'PROD-65B8F4A1C0A39', 'Qiluc Paint', 1, 120000.00, 15, 'High-quality paint for interior and exterior applications', 'QILUC.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(9, 'PROD-65B8F4A1C0A3D', 'Q-TON Paint', 1, 140000.00, 18, 'Premium quality paint with excellent coverage', 'Q-TON.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(10, 'PROD-65B8F4A1C0A41', 'Lem Fox Putih', 1, 25000.00, 50, 'White adhesive glue for various applications', 'lem fox putih.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(11, 'PROD-65B8F4A1C0A45', 'Keran Air On-Off', 2, 75000.00, 12, 'Standard water faucet with on-off function', 'keran air on-off.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(12, 'PROD-65B8F4A1C0A49', 'Keran Air Shower', 2, 85000.00, 10, 'Shower faucet for bathroom applications', 'Keran air showy.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(13, 'PROD-65B8F4A1C0A4D', 'Kran Air DVD', 2, 95000.00, 8, 'Modern water faucet with DVD design', 'kran air dvd.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(14, 'PROD-65B8F4A1C0A51', 'Kran Air Onda', 2, 90000.00, 14, 'Onda series water faucet', 'Kran Air Onda.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(15, 'PROD-65B8F4A1C0A55', 'Kran Air STK', 2, 80000.00, 16, 'STK series water faucet', 'Kran Air Stk.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(16, 'PROD-65B8F4A1C0A59', 'Baja Ringan', 3, 45000.00, 100, 'Lightweight steel for construction framework', 'bajaringan.webp', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(17, 'PROD-65B8F4A1C0A5D', 'Fortuna Building Material', 3, 35000.00, 80, 'Quality building material for construction', 'fortuna.webp', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(18, 'PROD-65B8F4A1C0A61', 'Kayu Construction', 3, 25000.00, 60, 'Quality wood for construction purposes', 'kayu.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(19, 'PROD-65B8F4A1C0A65', 'Palem Wood', 3, 28000.00, 45, 'Palem wood for construction and furniture', 'palem.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(20, 'PROD-65B8F4A1C0A69', 'Spandek Roofing', 3, 55000.00, 40, 'Corrugated metal roofing sheets', 'spandek.webp', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(21, 'PROD-65B8F4A1C0A6D', 'PVC Kaleg Adhesive', 4, 35000.00, 25, 'Specialized adhesive for PVC pipes and fittings', 'pvc kaleg.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(22, 'PROD-65B8F4A1C0A71', 'PVC Odol Adhesive', 4, 32000.00, 22, 'PVC pipe adhesive for plumbing applications', 'pvc odol.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(23, 'PROD-65B8F4A1C0A75', 'RG Product', 5, 45000.00, 20, 'Multi-purpose construction product', 'RG.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(24, 'PROD-65B8F4A1C0A79', 'Rucika Abu', 5, 38000.00, 35, 'Rucika grey pipe fittings', 'rucika abu abu.jpg', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00'),
(25, 'PROD-65B8F4A1C0A7D', 'Rucika Putih', 5, 40000.00, 30, 'Rucika white pipe fittings', 'rucika putih.webp', 'aktif', '2025-07-03 10:01:00', '2025-07-03 10:01:00');

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `qr_code_data` text NOT NULL,
  `qr_code_image` varchar(255) DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qr_codes`
--

INSERT INTO `qr_codes` (`id`, `pesanan_id`, `qr_code_data`, `qr_code_image`, `is_used`, `used_at`, `created_at`) VALUES
(9, 9, '{\"order_id\":\"9\",\"order_number\":\"ORD-20250703-5482\",\"total\":\"2000000\",\"date\":\"2025-07-03 15:16:05\"}', 'order_9_1751530565.png', 0, NULL, '2025-07-03 08:16:06');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_pesanan_detail`
-- (See below for the actual view)
--
CREATE TABLE `view_pesanan_detail` (
`id` int(11)
,`nomor_pesanan` varchar(50)
,`nama_pembeli` varchar(100)
,`no_hp` varchar(20)
,`alamat` text
,`total_harga` decimal(15,2)
,`status_pesanan` enum('pending','dikonfirmasi','diproses','dikirim','selesai','dibatalkan')
,`status_pembayaran` enum('belum_bayar','menunggu_verifikasi','lunas','gagal')
,`tanggal_pesanan` timestamp
,`produk_dipesan` mediumtext
,`status_bukti_transfer` enum('menunggu','diterima','ditolak')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_stok_produk`
-- (See below for the actual view)
--
CREATE TABLE `view_stok_produk` (
`id` int(11)
,`kode_produk` varchar(50)
,`nama_produk` varchar(200)
,`nama_kategori` varchar(100)
,`harga` decimal(15,2)
,`stok` int(11)
,`status` enum('aktif','nonaktif')
,`total_terjual` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Structure for view `view_pesanan_detail`
--
DROP TABLE IF EXISTS `view_pesanan_detail`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_pesanan_detail`  AS SELECT `p`.`id` AS `id`, `p`.`nomor_pesanan` AS `nomor_pesanan`, `p`.`nama_pembeli` AS `nama_pembeli`, `p`.`no_hp` AS `no_hp`, `p`.`alamat` AS `alamat`, `p`.`total_harga` AS `total_harga`, `p`.`status_pesanan` AS `status_pesanan`, `p`.`status_pembayaran` AS `status_pembayaran`, `p`.`tanggal_pesanan` AS `tanggal_pesanan`, group_concat(concat(`pr`.`nama_produk`,' (',`dp`.`jumlah`,')') separator ', ') AS `produk_dipesan`, `bt`.`status_verifikasi` AS `status_bukti_transfer` FROM (((`pesanan` `p` left join `detail_pesanan` `dp` on(`p`.`id` = `dp`.`pesanan_id`)) left join `produk` `pr` on(`dp`.`produk_id` = `pr`.`id`)) left join `bukti_transfer` `bt` on(`p`.`id` = `bt`.`pesanan_id`)) GROUP BY `p`.`id` ;

-- --------------------------------------------------------

--
-- Structure for view `view_stok_produk`
--
DROP TABLE IF EXISTS `view_stok_produk`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_stok_produk`  AS SELECT `p`.`id` AS `id`, `p`.`kode_produk` AS `kode_produk`, `p`.`nama_produk` AS `nama_produk`, `k`.`nama_kategori` AS `nama_kategori`, `p`.`harga` AS `harga`, `p`.`stok` AS `stok`, `p`.`status` AS `status`, coalesce(sum(`dp`.`jumlah`),0) AS `total_terjual` FROM (((`produk` `p` left join `kategori` `k` on(`p`.`kategori_id` = `k`.`id`)) left join `detail_pesanan` `dp` on(`p`.`id` = `dp`.`produk_id`)) left join `pesanan` `ps` on(`dp`.`pesanan_id` = `ps`.`id` and `ps`.`status_pembayaran` = 'lunas')) WHERE `p`.`status` = 'aktif' GROUP BY `p`.`id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bukti_transfer`
--
ALTER TABLE `bukti_transfer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `idx_bukti_transfer_pesanan` (`pesanan_id`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`),
  ADD KEY `idx_detail_pesanan` (`pesanan_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_stok`
--
ALTER TABLE `log_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_pesanan` (`nomor_pesanan`),
  ADD KEY `idx_pesanan_status` (`status_pesanan`),
  ADD KEY `idx_pesanan_pembayaran` (`status_pembayaran`),
  ADD KEY `idx_pesanan_tanggal` (`tanggal_pesanan`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`),
  ADD KEY `idx_produk_status` (`status`),
  ADD KEY `idx_produk_kategori` (`kategori_id`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qr_codes_pesanan` (`pesanan_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bukti_transfer`
--
ALTER TABLE `bukti_transfer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `log_stok`
--
ALTER TABLE `log_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bukti_transfer`
--
ALTER TABLE `bukti_transfer`
  ADD CONSTRAINT `bukti_transfer_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bukti_transfer_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `admin` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `log_stok`
--
ALTER TABLE `log_stok`
  ADD CONSTRAINT `log_stok_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `log_stok_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `qr_codes_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
