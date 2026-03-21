/*
SQLyog Ultimate v13.1.1 (64 bit)
MySQL - 11.8.2-MariaDB : Database - lamtim_biling_mikrotik
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`lamtim_biling_mikrotik` /*!40100 DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci */;

USE `lamtim_biling_mikrotik`;

/*Table structure for table `cache` */

DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `cache` */

/*Table structure for table `cache_locks` */

DROP TABLE IF EXISTS `cache_locks`;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `cache_locks` */

/*Table structure for table `failed_jobs` */

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `failed_jobs` */

/*Table structure for table `job_batches` */

DROP TABLE IF EXISTS `job_batches`;

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `job_batches` */

/*Table structure for table `jobs` */

DROP TABLE IF EXISTS `jobs`;

CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `jobs` */

/*Table structure for table `lamtim_areas` */

DROP TABLE IF EXISTS `lamtim_areas`;

CREATE TABLE `lamtim_areas` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `idProv` varchar(50) DEFAULT NULL,
  `idKab` varchar(50) DEFAULT NULL,
  `idKec` varchar(50) DEFAULT NULL,
  `idKel` varchar(50) DEFAULT NULL,
  `komentar` text DEFAULT NULL,
  `latitude` text DEFAULT NULL,
  `longitude` text DEFAULT NULL,
  `radius` int(11) DEFAULT NULL,
  `public` int(11) DEFAULT NULL,
  `code_area` varchar(20) DEFAULT NULL,
  `coordinates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`coordinates`)),
  `color` varchar(7) NOT NULL DEFAULT '#696cff',
  `create_by` int(11) DEFAULT NULL,
  `mitra` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_areas` */

insert  into `lamtim_areas`(`id`,`name`,`address`,`idProv`,`idKab`,`idKec`,`idKel`,`komentar`,`latitude`,`longitude`,`radius`,`public`,`code_area`,`coordinates`,`color`,`create_by`,`mitra`,`created_at`,`updated_at`) values 
(1,'TJI-vl30','Tulung Julak Kiri','Lampung','Lampung Timur','Way Jepara','Sumberjo','komentar tambahan',NULL,NULL,NULL,NULL,'1',NULL,'#696cff',NULL,0,'2025-06-04 21:53:45','2026-02-04 04:17:28'),
(46,'TJA-vl30','Tulung Julak Kanan',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2',NULL,'#696cff',NULL,0,'2026-02-02 17:27:48','2026-02-04 04:17:36'),
(47,'UMBT1-vl30','Umbul Buntu',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3',NULL,'#696cff',NULL,0,'2026-02-02 17:29:21','2026-02-02 17:29:21'),
(48,'PSKA-vl30','Pulau Sari Kanan',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'4',NULL,'#696cff',NULL,0,'2026-02-02 17:30:20','2026-02-02 17:30:20');

/*Table structure for table `lamtim_diskons` */

DROP TABLE IF EXISTS `lamtim_diskons`;

CREATE TABLE `lamtim_diskons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` bigint(20) unsigned DEFAULT NULL,
  `bulan` int(2) DEFAULT NULL,
  `nominal` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `createBy` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idUser` (`idUser`),
  CONSTRAINT `lamtim_diskons_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_diskons` */

/*Table structure for table `lamtim_fotos` */

DROP TABLE IF EXISTS `lamtim_fotos`;

CREATE TABLE `lamtim_fotos` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `idPelanggan` bigint(11) NOT NULL,
  `idJenis` tinyint(1) DEFAULT NULL COMMENT '1 KTP 2 FOTO RUMAH',
  `foto` varchar(255) DEFAULT NULL,
  `extensi` varchar(5) DEFAULT NULL,
  `ukuran` varchar(100) DEFAULT NULL,
  `ukuran2` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=403 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_fotos` */

/*Table structure for table `lamtim_kategoris` */

DROP TABLE IF EXISTS `lamtim_kategoris`;

CREATE TABLE `lamtim_kategoris` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(125) NOT NULL,
  `description` text NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `create_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_kategoris` */

insert  into `lamtim_kategoris`(`id`,`nama`,`description`,`isActive`,`created_at`,`updated_at`,`create_by`) values 
(10,'Internet 1:1 (Dedicated)','Paket untuk pelanggan umum',1,'2025-06-04 03:30:49','2025-06-04 03:45:01',NULL),
(11,'Internet 1:1 (Khusus)','Paket untuk pelanggan khusus',1,'2025-06-04 03:46:57','2025-06-04 03:46:57',NULL),
(12,'Internet (Gratis)','paket unutk pelanggan geratis',1,'2025-06-04 03:47:16','2025-06-04 03:47:16',NULL);

/*Table structure for table `lamtim_logs` */

DROP TABLE IF EXISTS `lamtim_logs`;

CREATE TABLE `lamtim_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idUser` bigint(20) unsigned NOT NULL,
  `nama` varchar(255) NOT NULL,
  `jenis` varchar(50) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `keterangan` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idUser` (`idUser`),
  CONSTRAINT `lamtim_logs_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_logs` */

insert  into `lamtim_logs`(`id`,`idUser`,`nama`,`jenis`,`date`,`keterangan`,`created_at`,`updated_at`) values 
(1,2,'Batal Pembayaran','PAYMENT','2025-09-20 14:44:37','user2 - No: 15-92025-68CE6DD1C6B91, Total: Rp 172,000','2025-09-20 14:44:37','2025-09-20 14:44:37'),
(2,2,'Batal Pembayaran','PAYMENT','2025-09-20 14:45:05','BINTANG - No: 16-92025-68CE6DD1C7A36, Total: Rp 222,000','2025-09-20 14:45:05','2025-09-20 14:45:05'),
(3,2,'Pembayaran Berhasil','PAYMENT','2025-09-20 14:45:17','user2 - No: 15-92025-68CE6DD1C6B91, Metode: transfer, Total: Rp 172,000','2025-09-20 14:45:17','2025-09-20 14:45:17'),
(4,2,'Pembayaran Berhasil','PAYMENT','2025-09-20 14:46:57','BINTANG - No: 16-92025-68CE6DD1C7A36, Metode: transfer, Total: Rp 222,000','2025-09-20 14:46:57','2025-09-20 14:46:57'),
(5,2,'Batal Pembayaran','PAYMENT','2025-09-20 14:47:15','user2 - No: 15-92025-68CE6DD1C6B91, Total: Rp 172,000','2025-09-20 14:47:15','2025-09-20 14:47:15'),
(6,2,'Pembayaran Berhasil','PAYMENT','2026-02-10 08:02:32','DALINO - No: 71-22026-698AE5EF4F484, Metode: cod, Total: Rp 150,000','2026-02-10 08:02:32','2026-02-10 08:02:32'),
(7,2,'Pembayaran Berhasil','PAYMENT','2026-02-10 08:03:27','SUMILAN - No: 70-22026-698AE5EF4EC4C, Metode: transfer, Total: Rp 200,000','2026-02-10 08:03:27','2026-02-10 08:03:27');

/*Table structure for table `lamtim_mikrotiks` */

DROP TABLE IF EXISTS `lamtim_mikrotiks`;

CREATE TABLE `lamtim_mikrotiks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kode` varchar(100) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `port` int(11) NOT NULL,
  `date_reset` int(11) DEFAULT 0,
  `isActive` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `createBy` bigint(20) DEFAULT NULL COMMENT 'id user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_mikrotiks` */

insert  into `lamtim_mikrotiks`(`id`,`kode`,`nama`,`username`,`password`,`ip`,`port`,`date_reset`,`isActive`,`created_at`,`updated_at`,`deleted_at`,`createBy`) values 
(1,'MK1','MIKROTIK KANGWIFI','kangwifi','kangwifi25','192.168.0.1',8728,0,1,'2025-05-29 17:51:10','2026-02-10 16:16:14',NULL,NULL);

/*Table structure for table `lamtim_odcs` */

DROP TABLE IF EXISTS `lamtim_odcs`;

CREATE TABLE `lamtim_odcs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idOlt` bigint(20) NOT NULL,
  `idOdc` bigint(20) unsigned DEFAULT NULL,
  `kode` varchar(100) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `port` int(5) DEFAULT NULL COMMENT 'jumlah port yg ada',
  `portSisa` int(5) DEFAULT NULL COMMENT 'jml port sisa setelah terpakai',
  `portOlt` int(5) DEFAULT NULL COMMENT 'di lokasi mana port olt nya',
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `route_waypoints` longtext DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'unlocked',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idOlt` (`idOlt`),
  KEY `lamtim_odcs_idodc_index` (`idOdc`),
  CONSTRAINT `lamtim_odcs_ibfk_1` FOREIGN KEY (`idOlt`) REFERENCES `lamtim_olts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_odcs` */

insert  into `lamtim_odcs`(`id`,`idOlt`,`idOdc`,`kode`,`nama`,`port`,`portSisa`,`portOlt`,`latitude`,`longitude`,`route_waypoints`,`keterangan`,`status`,`created_at`,`updated_at`,`deleted_at`) values 
(11,8,NULL,'ODC-I-01','ODC1',2,1,1,'-5.1370801945022','105.66728710848',NULL,NULL,'unlocked','2026-02-10 05:43:01','2026-02-10 07:29:24',NULL),
(12,8,NULL,'ODC-2','ODC-2',2,1,2,'-5.1371362946444','105.66712744481',NULL,NULL,'unlocked','2026-02-10 07:28:42','2026-02-10 07:33:33',NULL),
(13,8,NULL,'UMBT1','ODC UMBULBUNTU',4,3,1,'-5.1282056143469','105.67152902484','[{\"lat\":-5.137440838187023,\"lng\":105.66740681003927},{\"lat\":-5.137033444379959,\"lng\":105.66827809064024},{\"lat\":-5.135467981164162,\"lng\":105.6699169337874},{\"lat\":-5.135200836886695,\"lng\":105.67036236639065},{\"lat\":-5.134693262451122,\"lng\":105.6694285679212},{\"lat\":-5.134450160867812,\"lng\":105.66897508533118},{\"lat\":-5.133873128167151,\"lng\":105.66923751064535},{\"lat\":-5.132566788318714,\"lng\":105.66845085625859},{\"lat\":-5.131046728972116,\"lng\":105.6676565908938},{\"lat\":-5.130603266336269,\"lng\":105.66852128244396},{\"lat\":-5.130012872830775,\"lng\":105.66917869803304},{\"lat\":-5.128810712420968,\"lng\":105.66996759673998},{\"lat\":-5.128418006196513,\"lng\":105.67091749518302}]',NULL,'unlocked','2026-02-10 07:34:46','2026-02-10 08:19:24',NULL),
(14,8,NULL,'PSKA1','PULSAR KANAN1',4,3,2,'-5.1353798249666','105.65571203828','[{\"lat\":-5.13743652997416,\"lng\":105.66738367080688},{\"lat\":-5.137719642484029,\"lng\":105.66671043634416},{\"lat\":-5.137783743412201,\"lng\":105.66559731960298},{\"lat\":-5.138317942230824,\"lng\":105.66364198923112},{\"lat\":-5.1383554067266175,\"lng\":105.66305726766588},{\"lat\":-5.138772062228738,\"lng\":105.66198706626894},{\"lat\":-5.1392848686264845,\"lng\":105.66119849681856},{\"lat\":-5.139867117056862,\"lng\":105.66011756658557},{\"lat\":-5.139776307703669,\"lng\":105.65703839063646},{\"lat\":-5.138042317843711,\"lng\":105.65698206424715},{\"lat\":-5.13692055112506,\"lng\":105.65681576728822},{\"lat\":-5.136572714909134,\"lng\":105.65664410591127},{\"lat\":-5.136162022034476,\"lng\":105.6562900543213},{\"lat\":-5.135654554885411,\"lng\":105.65593600273134}]',NULL,'unlocked','2026-02-10 07:57:19','2026-02-10 08:00:06',NULL);

/*Table structure for table `lamtim_odps` */

DROP TABLE IF EXISTS `lamtim_odps`;

CREATE TABLE `lamtim_odps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idOdc` bigint(20) DEFAULT NULL,
  `idOdp` bigint(20) DEFAULT NULL,
  `idOlt` bigint(20) NOT NULL,
  `kode` varchar(100) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `port` int(5) DEFAULT NULL COMMENT 'jumlah port yg ada',
  `portSisa` int(5) DEFAULT NULL COMMENT 'jml port sisa setelah terpakai',
  `portOdc` int(11) DEFAULT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `route_waypoints` longtext DEFAULT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `fo_a` int(11) DEFAULT 0,
  `fo_b` int(11) DEFAULT 0,
  `kabel` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'unlocked',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_odps` */

insert  into `lamtim_odps`(`id`,`idOdc`,`idOdp`,`idOlt`,`kode`,`nama`,`port`,`portSisa`,`portOdc`,`latitude`,`longitude`,`route_waypoints`,`tipe`,`fo_a`,`fo_b`,`kabel`,`status`,`keterangan`,`created_at`,`updated_at`,`deleted_at`) values 
(34,11,NULL,8,'ODP-TJI-1','TJI-1',9,2,NULL,'-5.1374501882058','105.66740824074',NULL,'Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 05:44:02','2026-02-10 06:01:06',NULL),
(35,NULL,34,8,'ODP-TJI-2','TJI-2',9,5,NULL,'-5.1366968433986','105.66869358106','[{\"lat\":-5.137036115815607,\"lng\":105.66829644837311}]','Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 05:44:46','2026-02-10 06:04:33',NULL),
(36,NULL,35,8,'ODP-TJI-3','TJI-3',9,3,NULL,'-5.135502709912','105.66991298495',NULL,'Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 05:47:01','2026-02-10 06:08:33',NULL),
(37,NULL,36,8,'ODP-TJI-4','TJI-4',9,6,NULL,'-5.1346131190823','105.67134432815',NULL,'Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 05:47:52','2026-02-10 06:10:14',NULL),
(38,NULL,37,8,'ODP-TJI-5','TJI-5',9,6,NULL,'-5.1335752615456','105.67335965097','[{\"lat\":-5.133702155414833,\"lng\":105.67265100854924},{\"lat\":-5.1335752615455865,\"lng\":105.67306197448664}]','Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 05:49:25','2026-02-10 06:12:39',NULL),
(39,NULL,38,8,'ODP-TJI-6','TJI-6',9,4,NULL,'-5.1336941410659','105.67430393917',NULL,'Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 05:50:26','2026-02-10 06:20:42',NULL),
(40,NULL,36,8,'ODP-PSLP1','PSLP1',8,5,NULL,'-5.1302452902488','105.66733037268','[{\"lat\":-5.135200836886695,\"lng\":105.6703849271842},{\"lat\":-5.134442146528325,\"lng\":105.66896812950647},{\"lat\":-5.1338811425138,\"lng\":105.66924719571571},{\"lat\":-5.1325935028618375,\"lng\":105.66845829700877},{\"lat\":-5.131658493186799,\"lng\":105.66801286440553},{\"lat\":-5.131054743354272,\"lng\":105.66765866498608}]','Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 05:51:24','2026-02-10 06:23:23',NULL),
(41,12,NULL,8,'ODP-TJA-1','TJA-1',9,7,1,'-5.1378308674239','105.66559721937','[{\"lat\":-5.1375009454479805,\"lng\":105.66734797334871},{\"lat\":-5.137758738747373,\"lng\":105.66672554294848}]','Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 07:33:33','2026-02-10 07:46:30',NULL),
(42,13,NULL,8,'UMBT1-1','UMBT1-1',8,7,1,'-5.1281468517577','105.67172525223',NULL,'Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 07:38:13','2026-02-10 07:39:06',NULL),
(43,NULL,41,8,'ODP-TJA-2','TJA-2',9,7,NULL,'-5.1383143964888','105.66372916718',NULL,'Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 07:40:40','2026-02-10 07:45:34',NULL),
(44,NULL,43,8,'ODP-TJA-3','TJA-3',9,6,NULL,'-5.1387872394737','105.66202494589','[{\"lat\":-5.138342446506158,\"lng\":105.6636321120944},{\"lat\":-5.138355803656853,\"lng\":105.66345635404917},{\"lat\":-5.138387860817386,\"lng\":105.66308337132718}]','Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 07:42:41','2026-02-10 07:48:02',NULL),
(45,NULL,44,8,'ODP-KRO-1','KRO-1',9,8,NULL,'-5.1398518027625','105.65841413092','[{\"lat\":-5.139309503492622,\"lng\":105.6612107890633},{\"lat\":-5.139886531271437,\"lng\":105.66010525754201},{\"lat\":-5.139891874118802,\"lng\":105.65926805891422}]','Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 07:48:02','2026-02-10 07:50:03',NULL),
(46,NULL,45,8,'ODP-KRO-2','KRO-2',9,5,NULL,'-5.1398945455425','105.65557165795','[{\"lat\":-5.139798374283503,\"lng\":105.65705465747817}]','Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 07:49:57','2026-02-10 07:55:32',NULL),
(47,NULL,46,8,'ODP-LH','LH',8,7,NULL,'-5.1404087943891','105.65024667456',NULL,'Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 07:55:32','2026-02-10 07:56:06',NULL),
(48,14,NULL,8,'ODP-PSKA1-1','PSKA1-1',8,7,NULL,'-5.1352554573886','105.65561950207',NULL,'Splitter',0,0,NULL,'unlocked',NULL,'2026-02-10 08:00:03','2026-02-10 08:00:46',NULL);

/*Table structure for table `lamtim_olts` */

DROP TABLE IF EXISTS `lamtim_olts`;

CREATE TABLE `lamtim_olts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kode` varchar(100) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `ip` varchar(100) DEFAULT NULL COMMENT 'ip olt',
  `port` int(5) DEFAULT NULL COMMENT 'port akses',
  `sfp` int(5) DEFAULT NULL COMMENT 'jml port sft',
  `isActive` tinyint(1) DEFAULT 1 COMMENT '1 on 0 off',
  `latitude` text DEFAULT NULL,
  `longitude` text DEFAULT NULL,
  `teknologi` varchar(50) DEFAULT NULL,
  `port_pon` int(11) DEFAULT NULL,
  `port_uplink` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'unlocked',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_olts` */

insert  into `lamtim_olts`(`id`,`kode`,`nama`,`ip`,`port`,`sfp`,`isActive`,`latitude`,`longitude`,`teknologi`,`port_pon`,`port_uplink`,`status`,`keterangan`,`created_at`,`updated_at`,`deleted_at`) values 
(8,'OLT','OLT','192.168.0.88',80,2,1,'-5.1371029017032','105.66720396448','EPON',2,2,'unlocked',NULL,'2025-10-10 12:07:57','2026-02-10 07:28:45',NULL);

/*Table structure for table `lamtim_pakets` */

DROP TABLE IF EXISTS `lamtim_pakets`;

CREATE TABLE `lamtim_pakets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idKategori` int(11) NOT NULL,
  `kode` varchar(11) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `price` varchar(255) NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `isActive` tinyint(1) NOT NULL DEFAULT 1,
  `create_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`idKategori`),
  KEY `create_by` (`create_by`),
  CONSTRAINT `lamtim_pakets_ibfk_1` FOREIGN KEY (`idKategori`) REFERENCES `lamtim_kategoris` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_pakets` */

insert  into `lamtim_pakets`(`id`,`idKategori`,`kode`,`nama`,`price`,`picture`,`description`,`isActive`,`create_by`,`created_at`,`updated_at`) values 
(51,10,'PAKET-10M','PAKET-10M','200000',NULL,'-',1,NULL,'2025-06-05 08:27:02','2025-10-22 06:09:09'),
(52,10,'PAKET-7M','PAKET-7M','150000',NULL,'-',1,NULL,'2025-06-05 08:27:33','2025-10-22 06:09:28'),
(53,10,'PAKET-5M','PAKET-5M','100000',NULL,'-',1,NULL,'2025-10-22 06:10:00','2025-10-22 06:10:00'),
(54,12,'GRATIS-5M','GRATIS-5M','0',NULL,'Internet (Gratis)',1,NULL,'2026-02-04 04:15:30','2026-02-04 04:15:30'),
(55,12,'GRATIS-7M','GRATIS-7M','0',NULL,'Internet (Gratis)',1,NULL,'2026-02-04 04:15:57','2026-02-04 04:15:57'),
(56,11,'VIP20','VIP-M20','500000',NULL,'Speed On Demand',1,NULL,'2026-02-04 05:13:14','2026-02-04 05:13:14');

/*Table structure for table `lamtim_payment_metodes` */

DROP TABLE IF EXISTS `lamtim_payment_metodes`;

CREATE TABLE `lamtim_payment_metodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noUrut` int(11) DEFAULT NULL,
  `kode` varchar(100) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_payment_metodes` */

insert  into `lamtim_payment_metodes`(`id`,`noUrut`,`kode`,`nama`,`isActive`) values 
(1,3,'TR','Transfer Bank',1),
(2,2,'EW','E-Wallet',1),
(3,4,'COD','Cash On Delivery (COD)',1),
(4,1,'TG','Tunai di Gerai',1),
(5,5,'PY','Payment ',1);

/*Table structure for table `lamtim_tagihans` */

DROP TABLE IF EXISTS `lamtim_tagihans`;

CREATE TABLE `lamtim_tagihans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` bigint(20) unsigned NOT NULL,
  `noTagihan` varchar(255) NOT NULL COMMENT 'uuid',
  `idPaket` int(11) NOT NULL COMMENT 'id paket',
  `bulan` int(5) NOT NULL,
  `tahun` int(5) NOT NULL,
  `statusBayar` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 paid',
  `harga` int(10) NOT NULL,
  `ppn` int(10) NOT NULL,
  `diskon` int(10) NOT NULL,
  `total` int(10) NOT NULL,
  `tglJatuhTempo` date NOT NULL COMMENT 'Tgl jatuh tempo tagihanya',
  `tglBayar` datetime DEFAULT NULL COMMENT 'tanggalBayar',
  `prosesBy` bigint(20) unsigned DEFAULT NULL COMMENT 'id admin yang bayar',
  `prosesNama` varchar(255) DEFAULT NULL COMMENT 'nama admin yang bayar',
  `metode` varchar(50) DEFAULT NULL COMMENT 'metode bayar',
  `aksi` varchar(50) DEFAULT 'created' COMMENT 'created, pembayaranm pembatalan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `create_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `noTagihan` (`noTagihan`),
  KEY `lamtim_tagihans_ibfk_1` (`idUser`),
  KEY `lamtim_tagihans_ibfk_2` (`idPaket`),
  KEY `prosesBy` (`prosesBy`),
  CONSTRAINT `lamtim_tagihans_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lamtim_tagihans_ibfk_2` FOREIGN KEY (`idPaket`) REFERENCES `lamtim_pakets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lamtim_tagihans_ibfk_3` FOREIGN KEY (`prosesBy`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_tagihans` */

insert  into `lamtim_tagihans`(`id`,`idUser`,`noTagihan`,`idPaket`,`bulan`,`tahun`,`statusBayar`,`harga`,`ppn`,`diskon`,`total`,`tglJatuhTempo`,`tglBayar`,`prosesBy`,`prosesNama`,`metode`,`aksi`,`created_at`,`updated_at`,`create_by`) values 
(39,68,'68-22026-698AE5EF4D0ED',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(40,69,'69-22026-698AE5EF4E2A5',52,2,2026,0,150000,0,0,150000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(41,70,'70-22026-698AE5EF4EC4C',51,2,2026,1,200000,0,0,200000,'2026-03-12','2026-02-10 08:03:27',2,'superadmin','transfer','Pembayaran','2026-02-10 08:01:51','2026-02-10 08:03:27',2),
(42,71,'71-22026-698AE5EF4F484',52,2,2026,1,150000,0,0,150000,'2026-03-12','2026-02-10 08:02:32',2,'superadmin','cod','Pembayaran','2026-02-10 08:01:51','2026-02-10 08:02:32',2),
(44,73,'73-22026-698AE5EF506A3',53,2,2026,0,100000,0,0,100000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(45,74,'74-22026-698AE5EF50E91',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(46,75,'75-22026-698AE5EF51669',53,2,2026,0,100000,0,0,100000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(47,76,'76-22026-698AE5EF51F24',52,2,2026,0,150000,0,0,150000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(48,77,'77-22026-698AE5EF52766',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(49,78,'78-22026-698AE5EF53017',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(50,79,'79-22026-698AE5EF53890',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(51,80,'80-22026-698AE5EF540DA',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(52,81,'81-22026-698AE5EF54A76',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(53,82,'82-22026-698AE5EF5568D',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(54,83,'83-22026-698AE5EF55EAB',53,2,2026,0,100000,0,0,100000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(55,84,'84-22026-698AE5EF5663B',53,2,2026,0,100000,0,0,100000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(56,85,'85-22026-698AE5EF56E47',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(57,86,'86-22026-698AE5EF575CB',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(58,87,'87-22026-698AE5EF57D5D',52,2,2026,0,150000,0,0,150000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(59,88,'88-22026-698AE5EF585B0',53,2,2026,0,100000,0,0,100000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(60,89,'89-22026-698AE5EF58DD1',52,2,2026,0,150000,0,0,150000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(61,90,'90-22026-698AE5EF59722',52,2,2026,0,150000,0,0,150000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(62,91,'91-22026-698AE5EF5A56C',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(63,92,'92-22026-698AE5EF5AE0D',52,2,2026,0,150000,0,0,150000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(64,93,'93-22026-698AE5EF5B617',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(65,95,'95-22026-698AE5EF5C135',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(66,96,'96-22026-698AE5EF5C9AF',53,2,2026,0,100000,0,0,100000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(67,97,'97-22026-698AE5EF5D1C8',52,2,2026,0,150000,0,0,150000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(68,98,'98-22026-698AE5EF5D980',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(69,99,'99-22026-698AE5EF5E19B',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(70,100,'100-22026-698AE5EF5E96E',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(71,101,'101-22026-698AE5EF5F42C',51,2,2026,0,200000,0,0,200000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(72,102,'102-22026-698AE5EF5FB8A',52,2,2026,0,150000,0,0,150000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(73,103,'103-22026-698AE5EF6032F',52,2,2026,0,150000,0,0,150000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2),
(74,104,'104-22026-698AE5EF60B98',52,2,2026,0,150000,0,0,150000,'2026-03-12',NULL,NULL,NULL,NULL,'created','2026-02-10 08:01:51','2026-02-10 08:01:51',2);

/*Table structure for table `lamtim_transaksis` */

DROP TABLE IF EXISTS `lamtim_transaksis`;

CREATE TABLE `lamtim_transaksis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idUser` bigint(20) unsigned NOT NULL,
  `idTagihan` bigint(20) unsigned NOT NULL COMMENT 'Id user+tahun+bln+tgl',
  `metodeBayar` varchar(50) DEFAULT NULL COMMENT 'kode payment',
  `namaPaket` varchar(100) DEFAULT NULL COMMENT 'nama paket di bayar',
  `tglBayar` datetime DEFAULT NULL COMMENT 'tanggalBayar',
  `prosesBy` bigint(20) unsigned DEFAULT NULL COMMENT 'id admin yang bayar',
  `prosesNama` varchar(255) DEFAULT NULL COMMENT 'nama admin yang bayar',
  `harga` int(11) DEFAULT NULL,
  `ppn` int(11) DEFAULT NULL,
  `diskon` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idTagihan` (`idTagihan`),
  KEY `lamtim_transaksis_ibfk_1` (`idUser`),
  KEY `prosesBy` (`prosesBy`),
  CONSTRAINT `lamtim_transaksis_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lamtim_transaksis_ibfk_2` FOREIGN KEY (`idTagihan`) REFERENCES `lamtim_tagihans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lamtim_transaksis_ibfk_3` FOREIGN KEY (`prosesBy`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_transaksis` */

insert  into `lamtim_transaksis`(`id`,`idUser`,`idTagihan`,`metodeBayar`,`namaPaket`,`tglBayar`,`prosesBy`,`prosesNama`,`harga`,`ppn`,`diskon`,`total`,`keterangan`,`created_at`,`updated_at`) values 
(30,71,42,'cod','PAKET-7M','2026-02-10 08:02:32',2,'superadmin',150000,0,0,150000,NULL,'2026-02-10 08:02:32','2026-02-10 08:02:32'),
(31,70,41,'transfer','PAKET-10M','2026-02-10 08:03:27',2,'superadmin',200000,0,0,200000,NULL,'2026-02-10 08:03:27','2026-02-10 08:03:27');

/*Table structure for table `lamtim_user_details` */

DROP TABLE IF EXISTS `lamtim_user_details`;

CREATE TABLE `lamtim_user_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idUser` bigint(20) unsigned NOT NULL,
  `idArea` bigint(11) DEFAULT NULL,
  `tglDafatar` date DEFAULT NULL,
  `tglJatuhTempo` date DEFAULT NULL COMMENT 'Tanggal jatuh tempo',
  `statusPpn` tinyint(1) DEFAULT NULL COMMENT '1 aktif atau 0 tidak',
  `statusTagihan` tinyint(1) DEFAULT NULL COMMENT '1 aktif 0 tidak kirim tagihan ke wa',
  `jenisBayar` tinyint(1) DEFAULT NULL COMMENT '1.pascabayar 2.Prabayar',
  `googleMap` varchar(255) DEFAULT NULL COMMENT 'Url google map, lokasinya',
  `js` enum('L','P') DEFAULT NULL,
  `telegram` varchar(100) DEFAULT NULL,
  `fotoProfile` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `qrcode` varchar(255) DEFAULT NULL,
  `identitas` varchar(10) DEFAULT NULL,
  `noIdentitas` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idArea` (`idArea`),
  KEY `idUser` (`idUser`),
  CONSTRAINT `lamtim_user_details_ibfk_1` FOREIGN KEY (`idArea`) REFERENCES `lamtim_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lamtim_user_details_ibfk_2` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_user_details` */

insert  into `lamtim_user_details`(`id`,`idUser`,`idArea`,`tglDafatar`,`tglJatuhTempo`,`statusPpn`,`statusTagihan`,`jenisBayar`,`googleMap`,`js`,`telegram`,`fotoProfile`,`email`,`qrcode`,`identitas`,`noIdentitas`,`alamat`,`keterangan`,`created_at`,`updated_at`) values 
(55,67,NULL,'2025-04-10','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 05:54:54','2026-02-10 05:54:54'),
(56,68,NULL,'2025-05-05','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 05:56:49','2026-02-10 05:56:49'),
(57,69,NULL,'2025-02-10','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 05:57:48','2026-02-10 05:57:48'),
(58,70,NULL,'2025-04-30','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 05:59:27','2026-02-10 05:59:27'),
(59,71,NULL,'2025-02-10','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:00:09','2026-02-10 06:00:09'),
(60,72,NULL,'2025-09-12','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:01:06','2026-02-10 06:01:06'),
(61,73,NULL,'2025-04-30','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:02:15','2026-02-10 06:02:15'),
(62,74,NULL,'2025-04-28','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:03:21','2026-02-10 06:03:21'),
(63,75,NULL,'2025-04-30','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:04:33','2026-02-10 06:04:33'),
(64,76,NULL,'2025-04-28','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:05:42','2026-02-10 06:05:42'),
(65,77,NULL,'2025-05-01','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:07:01','2026-02-10 06:07:01'),
(66,78,NULL,'2025-10-03','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:07:58','2026-02-10 06:07:58'),
(67,79,NULL,'2025-05-04','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:08:33','2026-02-10 06:08:33'),
(68,80,NULL,'2025-04-29','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:09:41','2026-02-10 06:09:41'),
(69,81,NULL,'2025-04-27','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:10:14','2026-02-10 06:10:14'),
(70,82,NULL,'2025-05-04','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:11:22','2026-02-10 06:11:22'),
(71,83,NULL,'2026-01-10','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:12:39','2026-02-10 06:12:39'),
(72,84,NULL,'2025-05-03','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:15:16','2026-02-10 06:15:16'),
(73,85,NULL,'2025-05-02','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:15:53','2026-02-10 06:15:53'),
(74,86,NULL,'2025-05-02','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:16:44','2026-02-10 06:16:44'),
(75,87,NULL,'2026-01-22','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:17:29','2026-02-10 06:17:29'),
(76,88,NULL,'2025-05-31','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:18:17','2026-02-10 06:18:17'),
(77,89,NULL,'2026-01-16','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:18:49','2026-02-10 06:18:49'),
(78,90,NULL,'2026-01-16','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:19:23','2026-02-10 06:19:23'),
(79,91,NULL,'2025-05-10','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:20:42','2026-02-10 06:20:42'),
(80,92,NULL,'2026-01-21','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 06:23:23','2026-02-10 06:23:23'),
(81,93,NULL,'2025-02-10','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 07:39:06','2026-02-10 07:39:06'),
(82,94,NULL,'2025-05-01','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 07:42:04','2026-02-10 07:42:04'),
(83,95,NULL,'2025-05-01','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 07:43:56','2026-02-10 07:43:56'),
(84,96,NULL,'2025-05-01','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 07:44:43','2026-02-10 07:44:43'),
(85,97,NULL,'2025-02-10','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 07:45:34','2026-02-10 07:45:34'),
(86,98,NULL,'2025-06-14','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 07:46:30','2026-02-10 07:46:30'),
(87,99,NULL,'2025-04-29','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 07:47:16','2026-02-10 07:47:16'),
(88,100,NULL,'2025-02-10','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 07:50:55','2026-02-10 07:50:55'),
(89,101,NULL,'2025-02-10','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 07:51:46','2026-02-10 07:51:46'),
(90,102,NULL,'2025-02-10','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 07:52:20','2026-02-10 07:52:20'),
(91,103,NULL,'2025-02-10','2026-03-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 07:56:06','2026-02-10 07:56:06'),
(92,104,NULL,'2025-12-31','2026-02-10',1,1,1,NULL,'L',NULL,NULL,NULL,NULL,'KTP',NULL,NULL,NULL,'2026-02-10 08:00:46','2026-02-10 08:00:46');

/*Table structure for table `lamtim_user_mikrotik_details` */

DROP TABLE IF EXISTS `lamtim_user_mikrotik_details`;

CREATE TABLE `lamtim_user_mikrotik_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idUser` bigint(20) unsigned NOT NULL,
  `idKategori` int(11) DEFAULT NULL,
  `idPaket` int(11) DEFAULT NULL,
  `idMikrotik` bigint(20) DEFAULT NULL COMMENT 'id server mikrotik, di mikrotik mana pelanggan',
  `idMikrotikUser` varchar(255) DEFAULT NULL COMMENT 'id user di mikrotik',
  `namaMikrotikUser` varchar(255) DEFAULT NULL COMMENT 'nama user di mikrotik',
  `serviceMikrotikUser` varchar(255) DEFAULT NULL COMMENT 'service user di mikrotik',
  `profileMikrotikUser` varchar(255) DEFAULT NULL COMMENT 'proifle user di mikrotik',
  `statusIsolir` tinyint(1) DEFAULT 0 COMMENT '1 isolir 0 tidak',
  `password` varchar(255) DEFAULT NULL COMMENT 'password',
  `localAdress` varchar(100) DEFAULT NULL COMMENT 'ip local di mikrotik',
  `remoteAdress` varchar(100) DEFAULT NULL COMMENT 'ip remote di mikrotik',
  `idOlt` bigint(20) DEFAULT NULL,
  `idOdc` bigint(20) DEFAULT NULL,
  `idOdp` bigint(20) DEFAULT NULL,
  `portOdp` int(1) DEFAULT NULL COMMENT 'lokasi port odp',
  `modeIp` varchar(100) DEFAULT NULL COMMENT 'DHCP / STATIC / PPOE / HOTSPOT',
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `route_waypoints` longtext DEFAULT NULL,
  `tglDiIsolir` datetime DEFAULT NULL COMMENT 'catat tanggal di isolir',
  `tglDiBuka` datetime DEFAULT NULL COMMENT 'catata tanggal di buka isolir',
  `tglDiOff` datetime DEFAULT NULL COMMENT 'catat tanggal di off akun',
  `diskon` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Nama paket pada mikrotik',
  `updated_at` timestamp NULL DEFAULT NULL,
  `upload_speed` varchar(50) DEFAULT NULL,
  `download_speed` varchar(50) DEFAULT NULL,
  `wifi_name` varchar(100) DEFAULT NULL,
  `kabel` varchar(100) DEFAULT NULL,
  `keterangan` time DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'online',
  PRIMARY KEY (`id`),
  KEY `idOdc` (`idOdc`),
  KEY `idOdp` (`idOdp`),
  KEY `idUser` (`idUser`),
  KEY `idKategori` (`idKategori`),
  KEY `idPaket` (`idPaket`),
  CONSTRAINT `lamtim_user_mikrotik_details_ibfk_1` FOREIGN KEY (`idOdc`) REFERENCES `lamtim_odcs` (`id`),
  CONSTRAINT `lamtim_user_mikrotik_details_ibfk_2` FOREIGN KEY (`idOdp`) REFERENCES `lamtim_odps` (`id`),
  CONSTRAINT `lamtim_user_mikrotik_details_ibfk_3` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lamtim_user_mikrotik_details_ibfk_4` FOREIGN KEY (`idKategori`) REFERENCES `lamtim_kategoris` (`id`),
  CONSTRAINT `lamtim_user_mikrotik_details_ibfk_5` FOREIGN KEY (`idPaket`) REFERENCES `lamtim_pakets` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `lamtim_user_mikrotik_details` */

insert  into `lamtim_user_mikrotik_details`(`id`,`idUser`,`idKategori`,`idPaket`,`idMikrotik`,`idMikrotikUser`,`namaMikrotikUser`,`serviceMikrotikUser`,`profileMikrotikUser`,`statusIsolir`,`password`,`localAdress`,`remoteAdress`,`idOlt`,`idOdc`,`idOdp`,`portOdp`,`modeIp`,`latitude`,`longitude`,`route_waypoints`,`tglDiIsolir`,`tglDiBuka`,`tglDiOff`,`diskon`,`created_at`,`updated_at`,`upload_speed`,`download_speed`,`wifi_name`,`kabel`,`keterangan`,`status`) values 
(19,67,12,55,1,'*4','rizal@tj-1','pppoe','PAKET-7MB',0,'rizal@tj-1',NULL,NULL,8,11,34,NULL,NULL,'-5.1373112736274','105.66733126694',NULL,NULL,NULL,NULL,NULL,'2026-02-10 05:54:54','2026-02-10 07:29:17',NULL,NULL,NULL,NULL,NULL,'online'),
(20,68,10,51,1,'*13','sringatun@tj-1','pppoe','PAKET-10MB',0,'sringatun@tj-1',NULL,NULL,8,11,34,NULL,NULL,'-5.1373740523349','105.66720545958',NULL,NULL,NULL,NULL,NULL,'2026-02-10 05:56:49','2026-02-10 05:56:49',NULL,NULL,NULL,NULL,NULL,'online'),
(21,69,10,52,1,'*21','sriedi@tj-1','pppoe','PAKET-7MB',0,'sriedi@tj-1',NULL,NULL,8,11,34,NULL,NULL,'-5.137440838187','105.66707129313',NULL,NULL,NULL,NULL,NULL,'2026-02-10 05:57:48','2026-02-10 05:57:48',NULL,NULL,NULL,NULL,NULL,'online'),
(22,70,10,51,1,'*7','sumilan@tj-1','pppoe','PAKET-5MB',0,'sumilan@tj-1',NULL,NULL,8,11,34,NULL,NULL,'-5.1375557098364','105.6668874851',NULL,NULL,NULL,NULL,NULL,'2026-02-10 05:59:27','2026-02-10 05:59:27',NULL,NULL,NULL,NULL,NULL,'online'),
(23,71,10,52,1,'*18','dalijo@tj-1','pppoe','PAKET-7MB',0,'dalijo@tj-1',NULL,NULL,8,11,34,NULL,NULL,'-5.1376224956695','105.66662586053',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:00:09','2026-02-10 06:00:09',NULL,NULL,NULL,NULL,NULL,'online'),
(24,72,12,54,1,'*23','amintelur@tj-1','pppoe','PAKET-5MB',0,'amintelur@tj-1',NULL,NULL,8,11,34,NULL,NULL,'-5.1388540251777','105.66704177651',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:01:06','2026-02-10 06:01:06',NULL,NULL,NULL,NULL,NULL,'online'),
(25,73,10,53,1,'*B','sularmi@tj-2','pppoe','PAKET-5MB',0,'sularmi@tj-2',NULL,NULL,8,NULL,35,NULL,NULL,'-5.1365953287821','105.66918545563',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:02:15','2026-02-10 06:02:15',NULL,NULL,NULL,NULL,NULL,'online'),
(26,74,10,51,1,'*6','hariyanto@tj-2','pppoe','PAKET-10MB',0,'hariyanto@tj-2',NULL,NULL,8,NULL,35,NULL,NULL,'-5.1369052154557','105.66882991455',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:03:21','2026-02-10 06:03:21',NULL,NULL,NULL,NULL,NULL,'online'),
(27,75,10,53,1,'*A','dika@tj-2','pppoe','PAKET-5MB',0,'dika@tj-2',NULL,NULL,8,NULL,35,NULL,NULL,'-5.1367916794074','105.66837240696',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:04:33','2026-02-10 06:04:33',NULL,NULL,NULL,NULL,NULL,'online'),
(28,76,10,52,1,'*5','piko@tj-3','pppoe','PAKET-7MB',0,'piko@tj-3',NULL,NULL,8,NULL,36,NULL,NULL,'-5.1352489228649','105.67011950272',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:05:42','2026-02-10 07:36:33',NULL,NULL,NULL,NULL,NULL,'online'),
(29,77,10,51,1,'*C','beril@tj-3','pppoe','PAKET-10MB',0,'beril@tj-3',NULL,NULL,8,NULL,36,NULL,NULL,'-5.1353237232682','105.66989995692',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:07:01','2026-02-10 06:07:01',NULL,NULL,NULL,NULL,NULL,'online'),
(30,78,10,51,1,'*1E','enikustiwi@tj-3','pppoe','PAKET-7MB',0,'enikustiwi@tj-3',NULL,NULL,8,NULL,36,NULL,NULL,'-5.1355681602394','105.66964623282',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:07:58','2026-02-10 07:30:33',NULL,NULL,NULL,NULL,NULL,'online'),
(31,79,10,51,1,'*12','tumijan@tj-3','pppoe','PAKET-10MB',0,'tumijan@tj-3',NULL,NULL,8,NULL,36,NULL,NULL,'-5.1356750179022','105.67018116945',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:08:33','2026-02-10 07:30:58',NULL,NULL,NULL,NULL,NULL,'online'),
(32,80,10,51,1,'*8','kasanah@tj-3','pppoe','ISOLIR',0,'kasanah@tj-3',NULL,NULL,8,NULL,37,NULL,NULL,'-5.1344060819994','105.67137013559',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:09:41','2026-02-10 06:09:41',NULL,NULL,NULL,NULL,NULL,'offline'),
(33,81,10,51,1,'*3','samsudin@tj-4','pppoe','PAKET-10MB',0,'samsudin@tj-4',NULL,NULL,8,NULL,37,NULL,NULL,'-5.1344688609929','105.67126280244',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:10:14','2026-02-10 06:10:14',NULL,NULL,NULL,NULL,NULL,'online'),
(34,82,10,51,1,'*14','imamblek@tj-6','pppoe','PAKET-10MB',0,'imamblek@tj-6',NULL,NULL,8,NULL,38,NULL,NULL,'-5.1339519359047','105.67401723959',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:11:22','2026-02-10 06:11:22',NULL,NULL,NULL,NULL,NULL,'online'),
(35,83,10,53,1,'*25','endrapln@tj-6','pppoe','PAKET-5MB',0,'endrapln@tj-6',NULL,NULL,8,NULL,38,NULL,NULL,'-5.1337876417967','105.67312369105',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:12:39','2026-02-10 06:12:39',NULL,NULL,NULL,NULL,NULL,'online'),
(36,84,10,53,1,'*F','buari@tj-6','pppoe','PAKET-5MB',0,'buari@tj-6',NULL,NULL,8,NULL,39,NULL,NULL,'-5.1340107077705','105.67435258011',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:15:16','2026-02-10 06:15:16',NULL,NULL,NULL,NULL,NULL,'online'),
(37,85,10,51,1,'*11','katiran@tj-6','pppoe','PAKET-10MB',0,'katiran@tj-6',NULL,NULL,8,NULL,39,NULL,NULL,'-5.134036086529','105.67445588827',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:15:53','2026-02-10 06:15:53',NULL,NULL,NULL,NULL,NULL,'online'),
(38,86,10,51,1,'*E','bagos@tj-6','pppoe','PAKET-10MB',0,'bagos@tj-6',NULL,NULL,8,NULL,39,NULL,NULL,'-5.1336286905462','105.67485838761',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:16:44','2026-02-10 06:16:44',NULL,NULL,NULL,NULL,NULL,'online'),
(39,87,10,52,1,'*2A','solikin@tj-6','pppoe','PAKET-7MB',0,'solikin@tj-6',NULL,NULL,8,NULL,39,NULL,NULL,'-5.133109093325','105.67729701677',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:17:29','2026-02-10 06:17:29',NULL,NULL,NULL,NULL,NULL,'online'),
(40,88,10,53,1,'*15','sujarno@pslp-1','pppoe','PAKET-5MB',0,'sujarno@pslp-1',NULL,NULL,8,NULL,40,NULL,NULL,'-5.1306126164553','105.66724830997',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:18:17','2026-02-10 06:18:17',NULL,NULL,NULL,NULL,NULL,'online'),
(41,89,10,52,1,'*28','dedik@pslp-1','pppoe','PAKET-7MB',0,'dedik@pslp-1',NULL,NULL,8,NULL,40,NULL,NULL,'-5.1305137723332','105.66770179256',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:18:49','2026-02-10 06:18:49',NULL,NULL,NULL,NULL,NULL,'online'),
(42,90,10,52,1,'*27','agossosis@pslp-1','pppoe','PAKET-7MB',0,'agossosis@pslp-1',NULL,NULL,8,NULL,40,NULL,NULL,'-5.1312711316343','105.66662484196',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:19:23','2026-02-10 06:19:31',NULL,NULL,NULL,NULL,NULL,'online'),
(43,91,10,51,1,'*1A','suranti@tj-6','pppoe','PAKET-10MB',0,'suranti@tj-6',NULL,NULL,8,NULL,39,NULL,NULL,'-5.1343660102982','105.67529387309',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:20:42','2026-02-10 06:20:42',NULL,NULL,NULL,NULL,NULL,'online'),
(44,92,10,52,1,'*29','rudenlas@pslp-1','pppoe','PAKET-7MB',0,'rudenlas@pslp-1',NULL,NULL,8,NULL,40,NULL,NULL,'-5.1313272322875','105.66768860829',NULL,NULL,NULL,NULL,NULL,'2026-02-10 06:23:23','2026-02-10 06:23:23',NULL,NULL,NULL,NULL,NULL,'online'),
(45,93,10,51,1,'*20','yoko@umbt1-1','pppoe','PAKET-10MB',0,'yoko@umbt1-1',NULL,NULL,8,13,42,NULL,NULL,'-5.1278089103501','105.67369978307',NULL,NULL,NULL,NULL,NULL,'2026-02-10 07:39:06','2026-02-10 07:39:06',NULL,NULL,NULL,NULL,NULL,'online'),
(47,95,10,51,1,'*D','mohjoko@tj-3','pppoe','PAKET-10MB',0,'mohjoko@tj-3',NULL,NULL,8,NULL,44,NULL,NULL,'-5.1387605251901','105.66276624911',NULL,NULL,NULL,NULL,NULL,'2026-02-10 07:43:56','2026-02-10 07:44:47',NULL,NULL,NULL,NULL,NULL,'online'),
(48,96,10,53,1,'*10','sakom@tj-3','pppoe','PAKET-5MB',0,'sakom@tj-3',NULL,NULL,8,NULL,44,NULL,NULL,'-5.1389168037331','105.66246215548',NULL,NULL,NULL,NULL,NULL,'2026-02-10 07:44:43','2026-02-10 07:44:43',NULL,NULL,NULL,NULL,NULL,'online'),
(49,97,10,52,1,'*22','selamet@tj-2','pppoe','PAKET-7MB',0,'selamet@tj-2',NULL,NULL,8,NULL,43,NULL,NULL,'-5.1385815394612','105.66339980088',NULL,NULL,NULL,NULL,NULL,'2026-02-10 07:45:34','2026-02-10 07:45:34',NULL,NULL,NULL,NULL,NULL,'online'),
(50,98,10,51,1,'*17','suhardoyo@tj-1','pppoe','PAKET-10MB',0,'suhardoyo@tj-1',NULL,NULL,8,12,41,NULL,NULL,'-5.1382235678527','105.6654007568',NULL,NULL,NULL,NULL,NULL,'2026-02-10 07:46:30','2026-02-10 07:46:30',NULL,NULL,NULL,NULL,NULL,'online'),
(51,99,10,51,1,'*9','jemingun@tj-3','pppoe','PAKET-10MB',0,'jemingun@tj-3',NULL,NULL,8,NULL,44,NULL,NULL,'-5.1392560749682','105.66171928326',NULL,NULL,NULL,NULL,NULL,'2026-02-10 07:47:16','2026-02-10 07:47:16',NULL,NULL,NULL,NULL,NULL,'online'),
(52,100,10,51,1,'*1C','sulton@kro-2','pppoe','PAKET-10MB',0,'sulton@kro-2',NULL,NULL,8,NULL,46,NULL,NULL,'-5.1397997099955','105.65591241217',NULL,NULL,NULL,NULL,NULL,'2026-02-10 07:50:55','2026-02-10 07:50:55',NULL,NULL,NULL,NULL,NULL,'online'),
(53,101,10,51,1,'*19','sariani@kro-2','pppoe','PAKET-10MB',0,'sariani@kro-2',NULL,NULL,8,NULL,46,NULL,NULL,'-5.1397102172834','105.65639734492',NULL,NULL,NULL,NULL,NULL,'2026-02-10 07:51:46','2026-02-10 07:52:26',NULL,NULL,NULL,NULL,NULL,'online'),
(54,102,10,52,1,'*1B','msutar@kro-2','pppoe','PAKET-7MB',0,'msutar@kro-2',NULL,NULL,8,NULL,46,NULL,NULL,'-5.1386803823326','105.65730595246',NULL,NULL,NULL,NULL,NULL,'2026-02-10 07:52:20','2026-02-10 07:52:20',NULL,NULL,NULL,NULL,NULL,'online'),
(55,103,10,52,1,'*1F','kosem@lh','pppoe','PAKET-7MB',0,'kosem@lh',NULL,NULL,8,NULL,47,NULL,NULL,'-5.1403006018091','105.65012860808',NULL,NULL,NULL,NULL,NULL,'2026-02-10 07:56:06','2026-02-10 07:56:06',NULL,NULL,NULL,NULL,NULL,'online'),
(56,104,10,52,1,'*24','tianayam@pska1-1','pppoe','PAKET-7MB',0,'tianayam@pska1-1',NULL,NULL,8,14,48,NULL,NULL,'-5.134260128209','105.64823269844',NULL,NULL,NULL,NULL,NULL,'2026-02-10 08:00:46','2026-02-10 08:00:46',NULL,NULL,NULL,NULL,NULL,'online');

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`migration`,`batch`) values 
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_02_04_120139_add_id_odp_to_lamtim_odps_table',2),
(5,'2026_02_04_122647_make_id_odc_nullable_in_lamtim_odps_table',2),
(6,'2026_02_10_100000_add_polygon_columns_to_lamtim_areas_table',3),
(7,'2026_02_10_100001_change_code_area_to_string_on_lamtim_areas_table',3),
(8,'2026_02_10_200000_add_idOdc_to_lamtim_odcs_table',4);

/*Table structure for table `password_reset_tokens` */

DROP TABLE IF EXISTS `password_reset_tokens`;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `password_reset_tokens` */

/*Table structure for table `sessions` */

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `sessions` */

insert  into `sessions`(`id`,`user_id`,`ip_address`,`user_agent`,`payload`,`last_activity`) values 
('5KUYRoXZgvR059PKwvMAk6TZizH9fAJMYqrUuNB3',NULL,'127.0.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/136.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoickZZSzdtOHFXbGlYcW1CTjVIWTY5dW9uU3dGcXZIRThEeWhrQ2VNWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9sYW10aW0tYmlsaW5nLW1pa3JvdGlrLnRlc3QiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1748055618),
('6BRDJLn0vMMzoJ6phKhQzRKQsAiI8xkJSZMh7X0f',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibEc1eXA2aU1OQXlvY1poTXNqU2lSQWlTUEZPdWJIbEFneUhINzNQcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9sYW10aW0tYmlsaW5nLW1pa3JvdGlrLnRlc3QiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1748051587),
('cEbXpwMPUZOgYtvWZRA1lWOtGvU1E5qSJwdtvBQR',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUGVxYTk5UDBhSGs3dkRlckFHNGN0eGhGQVZlNW95c1h2Yzk0aTRCaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9sYW10aW0tYmlsaW5nLW1pa3JvdGlrLnRlc3QiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1748051587),
('NeaHYFyIk5hnXq1HLadmSrqeCvLZeke8rd8eUIjE',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.20.0 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY2JWR2VZa2ZnYUUweXZkVmFXYlY5Y2NLQzZSeWRlTzhISUhnbFEyNyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9sYW10aW0tYmlsaW5nLW1pa3JvdGlrLnRlc3QvP2hlcmQ9cHJldmlldyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1748051587),
('rmLwMKRz6FChLfBY7DA0NYzMbNnneor59k3ezuVn',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieW5ibGl3SGRlUmVTSzdrVjB5eHFkd2s3dWQ1OGRRQTRvSnFvSWdIcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9sYW10aW0tYmlsaW5nLW1pa3JvdGlrLnRlc3QiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1748051587),
('VMQU9NcyRopFXvU0HAGG7OelNFi6qVIsmZPhC9zT',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.20.0 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSWI4NVhPblFuWjR0cWp4Z2xUdTlsalpidDJhaXllUWc0dGR0bjZCRiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9sYW10aW0tYmlsaW5nLW1pa3JvdGlrLnRlc3QvP2hlcmQ9cHJldmlldyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1748056097),
('x8MRHrUccgFnbgT2XVsu98MXEsFQBNQKEn1igdDX',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.20.0 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWFkyWVcyVnpvRjB2Q1RuTnllaGFiU25vNnNOZnQydlpIU3phODhJMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9sYW10aW0tYmlsaW5nLW1pa3JvdGlrLnRlc3QvP2hlcmQ9cHJldmlldyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1748051587);

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idRole` tinyint(1) NOT NULL DEFAULT 5 COMMENT 'role id',
  `name` varchar(255) NOT NULL,
  `julukan` varchar(255) DEFAULT NULL COMMENT 'julukan pelanggan',
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `wa` varchar(25) NOT NULL COMMENT 'no wa bisa untuk login',
  `isActive` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 aktif 0 off',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`idRole`,`name`,`julukan`,`email`,`email_verified_at`,`password`,`remember_token`,`wa`,`isActive`,`created_at`,`updated_at`) values 
(1,1,'server',NULL,'server@server.id',NULL,'$2y$10$4xQ6awgimNk0oJyKtui2TeptwJHRE8Z8WMtE3AMOMoneB9aMaS4Sy',NULL,'1',1,'2025-09-20 21:44:16',NULL),
(2,1,'superadmin',NULL,'codeteam@codeteam.id',NULL,'$2y$10$2VCsbEyCjQckYJ5NPsAUN.sv/ROVgoBZUk8SB3oCCzC7QztVTJ72W',NULL,'2',1,'2025-09-20 21:44:16',NULL),
(3,3,'bendahara1',NULL,'bendahara1@codeteam.id',NULL,'$2y$10$Xc5kISNVe3qXnbu2CYU.Ku5quTbQ7e0fRY0jAushsEbaVMF/wndYu',NULL,'3',1,'2025-09-20 21:44:16',NULL),
(5,2,'admin1',NULL,'admin1@codeteam.id',NULL,'$2y$10$Xc5kISNVe3qXnbu2CYU.Ku5quTbQ7e0fRY0jAushsEbaVMF/wndYu',NULL,'5',1,'2025-09-20 21:44:16',NULL),
(7,4,'teknisi1',NULL,'teknisi1@codeteam.id',NULL,'$2y$10$Xc5kISNVe3qXnbu2CYU.Ku5quTbQ7e0fRY0jAushsEbaVMF/wndYu',NULL,'7',1,'2025-09-20 21:44:16',NULL),
(67,5,'RIZAL',NULL,'client_1770702894_165@local.net',NULL,'$2y$10$c9lzvTSY/Z.DA8DV/ohP4uuwc0qtdvcKczxpNPecS2MptAWvY2gZC',NULL,'-',1,'2026-02-10 05:54:54','2026-02-10 05:54:54'),
(68,5,'SRINGATUN',NULL,'client_1770703009_557@local.net',NULL,'$2y$10$iYCTFXU6N6IntRh/IYoMSuPW7Y6uysXQUHoLBeV0rNVV/F0AKQ6l6',NULL,'-',1,'2026-02-10 05:56:49','2026-02-10 05:56:49'),
(69,5,'SRIEDI',NULL,'client_1770703067_960@local.net',NULL,'$2y$10$nrPJV8n5pNCeYg2qdHkQNektz2/eUpK9lNehVw4s6xM7B0jvv6.hK',NULL,'-',1,'2026-02-10 05:57:48','2026-02-10 05:57:48'),
(70,5,'SUMILAN',NULL,'client_1770703167_286@local.net',NULL,'$2y$10$6NLNcp3aLcMyfNQFq6KW4.ePIwzSWwrjHCs9aLT6iYxKKVd0X90R6',NULL,'-',1,'2026-02-10 05:59:27','2026-02-10 05:59:27'),
(71,5,'DALIJO',NULL,'client_1770703209_620@local.net',NULL,'$2y$10$aluJYDw2N.R4.LoL4er8Z.AGPL/1UNyR1699uf8fJPQ4iWtngbjeO',NULL,'-',1,'2026-02-10 06:00:09','2026-02-10 08:18:31'),
(72,5,'AMIN TELUR',NULL,'client_1770703266_659@local.net',NULL,'$2y$10$VW0x7PtAaxTzB87RoQ2Ls.3O1uRZPXaDpq4fmFpxLj4/0YYKhEVKm',NULL,'-',1,'2026-02-10 06:01:06','2026-02-10 06:01:06'),
(73,5,'SULARMI',NULL,'client_1770703335_509@local.net',NULL,'$2y$10$0Dlj/6jOxuu0qLHVL62IIOGqLD5zWfi0w9NtbV6kOlphMHCv6MWbm',NULL,'-',1,'2026-02-10 06:02:15','2026-02-10 06:02:15'),
(74,5,'HARIYANTO',NULL,'client_1770703401_113@local.net',NULL,'$2y$10$EzSo.Jo3ZZF9CQXj/KtmXO5F/TvgyIuFkKer3AlENm8sopKsXBAqu',NULL,'-',1,'2026-02-10 06:03:21','2026-02-10 06:03:21'),
(75,5,'DIKA',NULL,'client_1770703473_634@local.net',NULL,'$2y$10$dfWaBRVx9Fs3vEXhxCUsa.R6wrob8pH/0TtMP.PYTs85KzAmKX7zm',NULL,'-',1,'2026-02-10 06:04:33','2026-02-10 06:04:33'),
(76,5,'PIKO',NULL,'client_1770703542_800@local.net',NULL,'$2y$10$mU777D4/YERUCfCJdDZFbuTU1X.eOkqaV0MP9nF6lm8ajf4SBB3LG',NULL,'-',1,'2026-02-10 06:05:42','2026-02-10 06:05:42'),
(77,5,'BERIL',NULL,'client_1770703621_475@local.net',NULL,'$2y$10$ooHbawSA6b6/n6l6q4IccONvQUtALfMCH0WZz1kMTSCBQPyNplvuy',NULL,'-',1,'2026-02-10 06:07:01','2026-02-10 06:07:01'),
(78,5,'ENI KUSTIWI',NULL,'client_1770703678_339@local.net',NULL,'$2y$10$yM0A.h1uOoBtDswPinRD8eKPXaxpL/m/t7Ic7enQhCMtrGkYq3nJm',NULL,'-',1,'2026-02-10 06:07:58','2026-02-10 06:07:58'),
(79,5,'TUMIJAN',NULL,'client_1770703713_493@local.net',NULL,'$2y$10$6KrfSbPpU13bw.KAjGCUAOyACjjQjptlo8qeg5au80DrRdF2Z9766',NULL,'-',1,'2026-02-10 06:08:33','2026-02-10 06:08:33'),
(80,5,'KHASANAH',NULL,'client_1770703781_120@local.net',NULL,'$2y$10$p6qeHVgw0qfJRf70tasSgOdbD1.YKS/4cAEr/B9Hdjwc9M1NY7yLG',NULL,'-',1,'2026-02-10 06:09:41','2026-02-10 06:09:41'),
(81,5,'SAMSUDIN',NULL,'client_1770703814_381@local.net',NULL,'$2y$10$wd8PaJOQ2yb9ti05.U.0vekTaRhOKexmK6piMKM9lGPgdsAM8K/fK',NULL,'-',1,'2026-02-10 06:10:14','2026-02-10 06:10:14'),
(82,5,'IMAM BLEK',NULL,'client_1770703882_434@local.net',NULL,'$2y$10$bB6UxmxIsKQgYZ0QcxyKH.lYhuChDlr7N8iOMzfz0h3cl00SxZysW',NULL,'-',1,'2026-02-10 06:11:22','2026-02-10 06:11:22'),
(83,5,'ENDRA PLN',NULL,'client_1770703959_185@local.net',NULL,'$2y$10$0XcEksaPXhaAp0TZWxFOq.E1hYm2pNzN6ZW4pblRmSMPPgQTK9mn.',NULL,'-',1,'2026-02-10 06:12:39','2026-02-10 06:12:39'),
(84,5,'ONIPAN',NULL,'client_1770704115_901@local.net',NULL,'$2y$10$7k4tUMqYlmszpzEqLtV6ZOO0ZfRkGVmUK9FGpzVK4MvAP/yjHlk/O',NULL,'-',1,'2026-02-10 06:15:16','2026-02-10 06:15:16'),
(85,5,'KATIRAN',NULL,'client_1770704153_626@local.net',NULL,'$2y$10$vUxaWmQRFwdWIPEV3AzGmeF9X7ykzTpoOfD0U9U36icdTiWbTyTQ2',NULL,'-',1,'2026-02-10 06:15:53','2026-02-10 06:15:53'),
(86,5,'BAGOS',NULL,'client_1770704204_366@local.net',NULL,'$2y$10$KUBE3YN1Wc9OowRJCvEqwOhTIwC6Dr0IZvcHQTbVogCksmPFQO93a',NULL,'-',1,'2026-02-10 06:16:44','2026-02-10 06:16:44'),
(87,5,'SOLIKIN',NULL,'client_1770704249_345@local.net',NULL,'$2y$10$FUjqb85OlU8X7yisNnqiZeHhynxJkD3moOT1HSzXW/LJusetf3W0C',NULL,'-',1,'2026-02-10 06:17:29','2026-02-10 06:17:29'),
(88,5,'SUJARNO',NULL,'client_1770704297_692@local.net',NULL,'$2y$10$mAQldVgOJyyxVZkNkWeqnOv55oLGuq5MDFE/jyjAAT2741WVcLGc6',NULL,'-',1,'2026-02-10 06:18:17','2026-02-10 06:18:17'),
(89,5,'DEDIK',NULL,'client_1770704329_553@local.net',NULL,'$2y$10$hlrrx3RCW7hGjhd6kOKLMe9hYj5UhEJcJX9iD2FnxsTeOru3isXxe',NULL,'-',1,'2026-02-10 06:18:49','2026-02-10 06:18:49'),
(90,5,'AGOS SOSIS',NULL,'client_1770704362_845@local.net',NULL,'$2y$10$f6DHnS913YxddScprEkCOupR4.g1TRCNC7fI2FDTZvRRLDp8GiP2u',NULL,'-',1,'2026-02-10 06:19:23','2026-02-10 06:19:23'),
(91,5,'SURANTI',NULL,'client_1770704442_888@local.net',NULL,'$2y$10$Z97X2LvmaJfJXQbpRgCWgudflT4FSwgAu0p.HShcXqNu6Y1M.NAyW',NULL,'-',1,'2026-02-10 06:20:42','2026-02-10 06:20:42'),
(92,5,'RUDEN LAS',NULL,'client_1770704603_761@local.net',NULL,'$2y$10$q1zd75J3kgDt9z6dHKmlT.zGrln1PEsHXolvmHPmNk5sy35Bj/MtO',NULL,'-',1,'2026-02-10 06:23:23','2026-02-10 06:23:23'),
(93,5,'YOKO',NULL,'client_1770709146_218@local.net',NULL,'$2y$10$2Uk41W.7FfSyLt7X9yliHua0usZXSXB1C095x8Y5OkolXTdVwxjzy',NULL,'-',1,'2026-02-10 07:39:06','2026-02-10 07:39:06'),
(94,5,'BREKELE',NULL,'client_1770709323_947@local.net',NULL,'$2y$10$PgJmBqPk62vsrEZS0eZZpOj9tjfOe.hSGR5Qq2LK.fMKnkBvSFLri',NULL,'-',1,'2026-02-10 07:42:04','2026-02-10 07:42:04'),
(95,5,'MOH JOKO',NULL,'client_1770709436_897@local.net',NULL,'$2y$10$CqQs0jGVquXKOTrGygixSu7hs/75xzXNiJb0WTAlGGoH35rzkpKNq',NULL,'-',1,'2026-02-10 07:43:56','2026-02-10 07:43:56'),
(96,5,'BREKELE',NULL,'client_1770709483_715@local.net',NULL,'$2y$10$0guA6qIaD3X10/mO5HV4Fe7eBzoBiK8vcFaW8kb3QAkleJuSQKXSG',NULL,'-',1,'2026-02-10 07:44:43','2026-02-10 07:44:43'),
(97,5,'SELAMET',NULL,'client_1770709534_477@local.net',NULL,'$2y$10$v96Y8YiBNHncPBAQzZVbxOQfidsaN1KGSsScYjYdDTPA.k8UG/Z6y',NULL,'-',1,'2026-02-10 07:45:34','2026-02-10 07:45:34'),
(98,5,'SUHARDOYO',NULL,'client_1770709590_614@local.net',NULL,'$2y$10$Ls3JJjUQ7GOP3TvW7RidSeQcoYbw.jG30AT0q4UECxI.wLENmvsMS',NULL,'-',1,'2026-02-10 07:46:30','2026-02-10 07:46:30'),
(99,5,'JEMINGUN',NULL,'client_1770709636_176@local.net',NULL,'$2y$10$LpDb9KWL0S17ejb.JgjPdu1pHAC9PmozryMLrJHvm3alFm1EB.TUa',NULL,'-',1,'2026-02-10 07:47:16','2026-02-10 07:47:16'),
(100,5,'SULTON',NULL,'client_1770709855_578@local.net',NULL,'$2y$10$xpEjDyX0q2JgNO1HJYRgg.gzdFbA2.uaSe5x0bmX/QaSWlP/GpbP.',NULL,'-',1,'2026-02-10 07:50:55','2026-02-10 07:50:55'),
(101,5,'SARIANI',NULL,'client_1770709906_863@local.net',NULL,'$2y$10$LE7oN0b.z/8ZP59eymxG6Or/3rb1DurjfvR/UCOVGUcWKQLW/Rore',NULL,'-',1,'2026-02-10 07:51:46','2026-02-10 07:51:46'),
(102,5,'M. SUTAR',NULL,'client_1770709940_995@local.net',NULL,'$2y$10$Qoh3/anAIzpjecdr/7IM1etEbBrGeYjRa7L5wroB3JMGV.ksi9mii',NULL,'-',1,'2026-02-10 07:52:20','2026-02-10 07:52:20'),
(103,5,'KOSEM',NULL,'client_1770710166_175@local.net',NULL,'$2y$10$yEB.PPFA8d/rfuX4DTJw7.qSqG4hH6kRap3PTWjtCDQ/nOLVjk3nC',NULL,'-',1,'2026-02-10 07:56:06','2026-02-10 07:56:06'),
(104,5,'TIAN AYAM',NULL,'client_1770710446_633@local.net',NULL,'$2y$10$HuPbxV0V2URme6ucv8T9W.qFofQCCuIJS2B1mGDlvkRDWMkn/eqaG',NULL,'-',1,'2026-02-10 08:00:46','2026-02-10 08:00:46');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
