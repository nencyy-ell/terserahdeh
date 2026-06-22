-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: prambanan_beton
-- ------------------------------------------------------
-- Server version	8.4.6

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int DEFAULT NULL,
  `admin_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `action` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,2,'Nency','Membuat pesanan baru #INV-2026-001','2026-04-27 04:26:29'),(2,3,'Indah','Mengupdate stok Semen Portland','2026-04-27 04:26:29'),(3,4,'Nadya','Menambahkan laporan marketing','2026-04-27 04:26:29'),(4,1,'Ardhan','Mengupdate data pelanggan','2026-04-27 04:26:29'),(5,2,'Nency','Login ke sistem','2026-04-27 04:37:16'),(6,2,'Nency','Menambahkan laporan marketing - Pembangunan Jembatan','2026-04-27 04:46:07'),(7,2,'Nency','Menambahkan laporan marketing - Pembangunan Jembatan','2026-04-27 04:47:28'),(8,3,'Indah','Login ke sistem','2026-04-29 03:02:52'),(9,3,'Indah','Menambahkan laporan marketing - pembangunan panti asuhan','2026-04-29 03:08:24'),(10,3,'Indah','Login ke sistem internal','2026-05-06 01:00:54'),(11,1,'Super Admin','Login ke sistem internal','2026-05-06 07:24:31'),(12,1,'Super Admin','Login ke sistem internal','2026-05-06 22:00:54'),(13,1,'Super Admin','Logout dari sistem internal','2026-05-06 22:07:11'),(14,1,'Super Admin','Login ke sistem internal','2026-05-06 22:16:38'),(15,1,'Super Admin','Logout dari sistem internal','2026-05-06 22:20:54'),(16,1,'Super Admin','Login ke sistem','2026-05-06 22:23:13'),(17,1,'Super Admin','Login ke sistem','2026-05-07 00:32:09'),(18,1,'Super Admin','Logout dari sistem internal','2026-05-07 00:36:51'),(19,1,'Super Admin','Login ke sistem','2026-05-07 01:56:29'),(20,1,'Super Admin','Login ke sistem','2026-05-08 01:05:05'),(21,1,'Super Admin','Menambahkan laporan marketing - Bangun gedung tambahan','2026-05-08 01:27:59'),(22,1,'Super Admin','Logout dari sistem internal','2026-05-08 02:08:04'),(23,1,'Super Admin','Login ke sistem','2026-05-08 02:22:27'),(24,1,'Super Admin','Logout dari sistem internal','2026-05-08 02:22:44'),(25,4,'Nadya','Login ke sistem','2026-05-08 02:23:38'),(26,4,'Nadya','Logout dari sistem internal','2026-05-08 03:15:23'),(27,3,'Indah','Login ke sistem','2026-05-08 03:54:15'),(28,3,'Indah','Logout dari sistem internal','2026-05-08 03:55:33'),(29,1,'Super Admin','Login ke sistem','2026-05-08 03:55:38'),(30,1,'Super Admin','Membuat pesanan baru #INV-2026-004','2026-05-08 05:42:38'),(31,1,'Super Admin','Login ke sistem','2026-05-09 03:04:47'),(32,1,'Super Admin','Login ke sistem','2026-05-09 05:21:17');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('superadmin','admin','marketing','gudang') COLLATE utf8mb4_general_ci DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'Super Admin','admin','admin@prambanan-beton.co.id','$2y$10$vd/fDWpJX80NZ9ao4/jKLuteu3mF4vnS5y4drECCYxzqVcT5wbyLa','superadmin',1,'2026-05-09 05:21:17','2026-04-27 04:26:29'),(2,'Nency','nency','nency@prambanan-beton.co.id','$2y$10$vd/fDWpJX80NZ9ao4/jKLuteu3mF4vnS5y4drECCYxzqVcT5wbyLa','admin',1,'2026-04-27 04:37:16','2026-04-27 04:26:29'),(3,'Indah','indah','indah@prambanan-beton.co.id','$2y$10$vd/fDWpJX80NZ9ao4/jKLuteu3mF4vnS5y4drECCYxzqVcT5wbyLa','gudang',1,'2026-05-08 03:54:15','2026-04-27 04:26:29'),(4,'Nadya','nadya','nadya@prambanan-beton.co.id','$2y$10$vd/fDWpJX80NZ9ao4/jKLuteu3mF4vnS5y4drECCYxzqVcT5wbyLa','marketing',1,'2026-05-08 02:23:38','2026-04-27 04:26:29');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pesan` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marketing_reports`
--

DROP TABLE IF EXISTS `marketing_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketing_reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_marketing` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_kontraktor` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_proyek` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat_proyek` text COLLATE utf8mb4_general_ci,
  `jenis_proyek` enum('Pemerintah','BUMN','Swasta','Retail','Lainnya') COLLATE utf8mb4_general_ci NOT NULL,
  `status_proyek` enum('Follow Up','Nego','Deal','Loss') COLLATE utf8mb4_general_ci NOT NULL,
  `estimasi_volume` decimal(10,2) DEFAULT NULL,
  `contact_person` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wilayah` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_aktivitas` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hasil_promosi` text COLLATE utf8mb4_general_ci,
  `tanggal` date NOT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketing_reports`
--

LOCK TABLES `marketing_reports` WRITE;
/*!40000 ALTER TABLE `marketing_reports` DISABLE KEYS */;
INSERT INTO `marketing_reports` VALUES (1,'Budi Santoso','PT Developer Jember','Perumahan Grand Jember','Jl. Gajah Mada, Jember','Swasta','Follow Up',500.00,'Pak Hendra','Jember Kota','Promosi ke Developer','09:00:00','12:00:00',NULL,NULL,NULL,'Tertarik, follow up minggu depan','2026-02-21',1,'2026-04-27 04:26:29'),(2,'Siti Aminah','CV. Bondowoso Jaya','Gudang Logistik','Jl. Ahmad Yani, Bondowoso','Swasta','Deal',100.00,'Bu Sari','Bondowoso','Kunjungan Proyek','08:00:00','11:30:00',NULL,NULL,NULL,'Berhasil closing 100m┬│','2026-02-20',1,'2026-04-27 04:26:29'),(3,'Ahmad Yani','Dinas PU Lumajang','Jembatan Desa Lumajang','Jl. Diponegoro, Lumajang','Pemerintah','Nego',800.00,'Pak Bambang','Lumajang','Survey Lokasi','10:00:00','15:00:00',NULL,NULL,NULL,'Potensi proyek jembatan','2026-02-19',1,'2026-04-27 04:26:29'),(4,'Abimanyu','Budi Santoso','Pembangunan Jembatan','Jalan jalannn','Lainnya','Follow Up',185.00,'belum nemu','Jalan jalannn','Kunjungan Proyek','09:40:00','11:40:00',-8.19626262,113.51065860,NULL,'','2026-04-27',0,'2026-04-27 04:46:07'),(5,'Abimanyu','Budi Santoso','Pembangunan Jembatan','Jalan jalannn','Lainnya','Follow Up',185.00,'belum nemu','Jalan jalannn','Kunjungan Proyek','09:40:00','11:40:00',-8.28450000,113.60430000,NULL,'','2026-04-27',0,'2026-04-27 04:47:28'),(6,'Indah sari','bapak yogiswara','pembangunan panti asuhan','jember kidul','Lainnya','Nego',250.00,'pak asep','jember kidul','Survey Lokasi','07:15:00','10:01:00',-8.29375654,113.60451218,NULL,'nego tipis tipis','2026-04-29',0,'2026-04-29 03:08:24'),(7,'nency','nadya','Bangun gedung tambahan','Perumahan puri bunga nirwana','Lainnya','Nego',250.00,'0881036052926','Perumahan puri bunga nirwana','Kunjungan Proyek','08:20:00','12:30:00',-8.16916403,113.73449381,NULL,'Menunggu negosiasi lanjutan','2026-05-08',0,'2026-05-08 01:27:59');
/*!40000 ALTER TABLE `marketing_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `materials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `satuan` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `stok_tersedia` decimal(10,2) DEFAULT '0.00',
  `stok_minimum` decimal(10,2) DEFAULT '0.00',
  `harga_terakhir` decimal(15,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materials`
--

LOCK TABLES `materials` WRITE;
/*!40000 ALTER TABLE `materials` DISABLE KEYS */;
INSERT INTO `materials` VALUES (1,'Semen Portland','ton',45.00,20.00,850000.00,'2026-04-27 04:26:29'),(2,'Pasir','m┬│',120.00,50.00,250000.00,'2026-04-27 04:26:29'),(3,'Kerikil','m┬│',15.00,30.00,300000.00,'2026-04-27 04:26:29'),(4,'Air','m┬│',80.00,40.00,50000.00,'2026-04-27 04:26:29'),(5,'Additif','liter',250.00,100.00,150000.00,'2026-04-27 04:26:29'),(6,'Batu','ton',85.00,25.00,112000.00,'2026-05-08 05:44:06');
/*!40000 ALTER TABLE `materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembayaran`
--

DROP TABLE IF EXISTS `pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pesanan_id` int NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pesanan_id` (`pesanan_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayaran`
--

LOCK TABLES `pembayaran` WRITE;
/*!40000 ALTER TABLE `pembayaran` DISABLE KEYS */;
INSERT INTO `pembayaran` VALUES (1,3,15000000.00,'2026-04-27',NULL,2,'2026-04-27 04:50:44'),(2,2,110000000.00,'2026-04-27',NULL,2,'2026-04-27 04:51:04'),(3,2,110000000.00,'2026-04-27',NULL,2,'2026-04-27 04:51:19');
/*!40000 ALTER TABLE `pembayaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permintaan_material`
--

DROP TABLE IF EXISTS `permintaan_material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permintaan_material` (
  `id` int NOT NULL AUTO_INCREMENT,
  `material_id` int NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `diminta_oleh` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Pending','Disetujui','Ditolak') COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `material_id` (`material_id`),
  CONSTRAINT `permintaan_material_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permintaan_material`
--

LOCK TABLES `permintaan_material` WRITE;
/*!40000 ALTER TABLE `permintaan_material` DISABLE KEYS */;
INSERT INTO `permintaan_material` VALUES (1,1,10.00,'Produksi','2026-02-21','Pending',NULL,'2026-04-27 04:26:29'),(2,2,25.00,'Produksi','2026-02-20','Pending',NULL,'2026-04-27 04:26:29'),(3,3,15.00,'Produksi','2026-02-19','Pending',NULL,'2026-04-27 04:26:29');
/*!40000 ALTER TABLE `permintaan_material` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesanan`
--

DROP TABLE IF EXISTS `pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pesanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_invoice` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_pelanggan` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_proyek` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_beton` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `volume` decimal(10,2) NOT NULL,
  `harga_per_m3` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `ppn_aktif` tinyint(1) DEFAULT '0',
  `ppn_persen` decimal(5,2) DEFAULT '11.00',
  `ppn_nominal` decimal(15,2) DEFAULT '0.00',
  `total_tagihan` decimal(15,2) NOT NULL,
  `uang_muka` decimal(15,2) DEFAULT '0.00',
  `total_terbayar` decimal(15,2) DEFAULT '0.00',
  `sisa_tagihan` decimal(15,2) NOT NULL,
  `status` enum('Pending','DP 50%','Lunas') COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `tanggal` date NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_invoice` (`no_invoice`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesanan`
--

LOCK TABLES `pesanan` WRITE;
/*!40000 ALTER TABLE `pesanan` DISABLE KEYS */;
INSERT INTO `pesanan` VALUES (1,'INV-2026-001','PT. Jaya Konstruksi','Hotel Jember','K-300',150.00,950000.00,142500000.00,1,11.00,15675000.00,158175000.00,0.00,158175000.00,0.00,'Lunas','2026-02-15',1,'2026-04-27 04:26:29'),(2,'INV-2026-002','CV. Mitra Bangun','Gedung Perkantoran','K-400',200.00,1100000.00,220000000.00,0,11.00,0.00,220000000.00,110000000.00,220000000.00,0.00,'Lunas','2026-02-18',1,'2026-04-27 04:26:29'),(3,'INV-2026-003','Koperasi Merah Putih','Kantor Koperasi','K-225',80.00,850000.00,68000000.00,0,11.00,0.00,68000000.00,0.00,15000000.00,53000000.00,'DP 50%','2026-02-20',1,'2026-04-27 04:26:29'),(4,'INV-2026-004','Prama','Koperasi Hutan','K-225',135.00,780000.00,105300000.00,1,11.00,11583000.00,116883000.00,0.00,0.00,116883000.00,'Pending','2026-05-08',1,'2026-05-08 05:42:38');
/*!40000 ALTER TABLE `pesanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portfolio`
--

DROP TABLE IF EXISTS `portfolio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `portfolio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `klien` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lokasi` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tahun` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `volume` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio`
--

LOCK TABLES `portfolio` WRITE;
/*!40000 ALTER TABLE `portfolio` DISABLE KEYS */;
INSERT INTO `portfolio` VALUES (1,'Hotel Jember','Pemasok Beton Ready mix untuk hotel Jember','PT. Jaya Konstruksi','Jl Trunojoyo, Jember, Jawa Timur','2025','2.500m┬│',NULL,1,1,'2026-04-27 04:26:29'),(2,'Koperasi Merah Putih','Pemasok Beton Ready mix untuk Koperasi Merah Putih','Koperasi Merah Putih','Jl. GajahMada, Rambipuji, Jember','2025-2026','1.750m┬│',NULL,1,1,'2026-04-27 04:26:29');
/*!40000 ALTER TABLE `portfolio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kekuatan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `harga_per_m3` decimal(15,2) DEFAULT '0.00',
  `cocok_untuk` text COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'K-225','Beton Ready Mix K-225','225 kg/cm┬▓',780000.00,'Jalan raya dan jalan lingkungan|Lantai rumah tinggal|Trotoar dan jalan setapak|Area parkir kendaraan ringan',1,'2026-04-27 04:26:29'),(2,'K-300','Beton Ready Mix K-300','300 kg/cm┬▓',820000.00,'Kolom dan balok bangunan|Lantai gedung bertingkat|Plat lantai dan tangga|Struktur bangunan umum',1,'2026-04-27 04:26:29'),(3,'K-400','Beton Ready Mix K-400','400 kg/cm┬▓',875000.00,'Gedung bertingkat tinggi|Struktur jembatan|Konstruksi berat|Fondasi proyek besar',1,'2026-04-27 04:26:29'),(4,'K-500','Beton Ready Mix K-500','500 kg/cm┬▓',995000.00,'Struktur khusus ekstra kuat|Jembatan layang|Bendungan dan infrastruktur besar|Proyek dengan beban sangat tinggi',1,'2026-04-27 04:26:29'),(5,'B0','Beton B0',NULL,715000.00,NULL,1,'2026-05-08 05:08:39'),(6,'K-125','Beton K-125',NULL,720000.00,NULL,1,'2026-05-08 05:08:39'),(7,'K-150','Beton K-150',NULL,735000.00,NULL,1,'2026-05-08 05:08:39'),(8,'K-175','Beton K-175',NULL,750000.00,NULL,1,'2026-05-08 05:08:39'),(9,'K-200','Beton K-200',NULL,770000.00,NULL,1,'2026-05-08 05:08:39'),(10,'K-250','Beton K-250',NULL,800000.00,NULL,1,'2026-05-08 05:08:39'),(11,'K-275','Beton K-275',NULL,810000.00,NULL,1,'2026-05-08 05:08:39'),(12,'K-350','Beton K-350',NULL,850000.00,NULL,1,'2026-05-08 05:08:39'),(13,'K-450','Beton K-450',NULL,895000.00,NULL,1,'2026-05-08 05:08:39'),(14,'K-475','Beton K-475',NULL,950000.00,NULL,1,'2026-05-08 05:08:39');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sertifikasi`
--

DROP TABLE IF EXISTS `sertifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sertifikasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `penerbit` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tahun` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sertifikasi`
--

LOCK TABLES `sertifikasi` WRITE;
/*!40000 ALTER TABLE `sertifikasi` DISABLE KEYS */;
INSERT INTO `sertifikasi` VALUES (1,'ISO 9001:2015','Sistem Manajemen Mutu','Badan Sertifikasi Internasional','2023',1),(2,'SNI Beton Ready Mix','Standar Nasional Indonesia','Badan Standardisasi Nasional','2022',1),(3,'Sertifikat K3L','Keselamatan Kerja & Lingkungan','Kementerian Ketenagakerjaan','2024',1),(4,'SIUJK Konstruksi','Surat Izin Usaha Jasa Konstruksi','Kementerian PUPR','2023',1);
/*!40000 ALTER TABLE `sertifikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ulasan`
--

DROP TABLE IF EXISTS `ulasan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ulasan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `rating` int NOT NULL DEFAULT '5',
  `komentar` text COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ulasan`
--

LOCK TABLES `ulasan` WRITE;
/*!40000 ALTER TABLE `ulasan` DISABLE KEYS */;
INSERT INTO `ulasan` VALUES (1,'Pak Hendra - PT. Jaya Konstruksi',5,'Kualitas beton sangat baik, pengiriman tepat waktu. Sangat puas dengan layanan PT Prambanan Beton!',1,'2026-04-27 04:26:29'),(2,'Bu Sari - CV. Mitra Bangun',4,'Produk bagus dan tim profesional. Harga cukup kompetitif untuk kualitas yang diberikan.',1,'2026-04-27 04:26:29'),(3,'Pak Bambang - Dinas PU',5,'Sudah bekerja sama beberapa proyek, selalu memuaskan. Rekomendasi untuk proyek pemerintah.',1,'2026-04-27 04:26:29'),(4,'Ahmad - Kontraktor Swasta',5,'Respon cepat dan beton sesuai spesifikasi. Akan terus menggunakan jasa PT Prambanan Beton.',1,'2026-04-27 04:26:29');
/*!40000 ALTER TABLE `ulasan` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-09 14:54:22
