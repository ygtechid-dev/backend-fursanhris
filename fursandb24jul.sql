-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: fursan_hris
-- ------------------------------------------------------
-- Server version	8.0.42-0ubuntu0.22.04.1

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
-- Table structure for table `allowances`
--

DROP TABLE IF EXISTS `allowances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `allowances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `type` enum('permanent','monthly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'permanent',
  `month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` int DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `allowances_employee_id_foreign` (`employee_id`),
  KEY `allowances_created_by_foreign` (`created_by`),
  CONSTRAINT `allowances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `allowances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `allowances`
--

LOCK TABLES `allowances` WRITE;
/*!40000 ALTER TABLE `allowances` DISABLE KEYS */;
INSERT INTO `allowances` VALUES (1,1,'Tunjangan Transportasi',500000.00,'permanent',NULL,NULL,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(2,1,'Tunjangan Makan',800000.00,'permanent',NULL,NULL,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(3,1,'Bonus Performa',377414.00,'monthly','07',2025,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(4,1,'Bonus Performa',404331.00,'monthly','06',2025,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(5,2,'Tunjangan Transportasi',500000.00,'permanent',NULL,NULL,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(6,2,'Tunjangan Makan',800000.00,'permanent',NULL,NULL,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(7,2,'Bonus Performa',765324.00,'monthly','07',2025,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(8,2,'Bonus Performa',241315.00,'monthly','06',2025,2,'2025-07-19 03:10:23','2025-07-19 03:10:23');
/*!40000 ALTER TABLE `allowances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warranty_status` enum('On','Off') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Off',
  `buying_date` date DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
INSERT INTO `assets` VALUES (1,1,'Laptop Kerja','Dell','On','2023-01-15','laptop-dell.jpg',2,'2025-07-19 03:10:25','2025-07-19 03:10:25'),(2,2,'Komputer Desktop','HP','On','2023-02-20','desktop-hp.jpg',2,'2025-07-19 03:10:25','2025-07-19 03:10:25'),(3,3,'HP','Techno','On','2025-07-26',NULL,2,'2025-07-22 01:55:09','2025-07-22 01:55:09');
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_employees`
--

DROP TABLE IF EXISTS `attendance_employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `date` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clock_in` time NOT NULL,
  `clock_out` time NOT NULL,
  `late` time NOT NULL,
  `early_leaving` time NOT NULL,
  `overtime` time NOT NULL,
  `total_rest` time NOT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clock_in_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clock_in_latitude` decimal(10,8) DEFAULT NULL,
  `clock_in_longitude` decimal(11,8) DEFAULT NULL,
  `clock_in_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clock_in_notes` text COLLATE utf8mb4_unicode_ci,
  `clock_out_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clock_out_latitude` decimal(10,8) DEFAULT NULL,
  `clock_out_longitude` decimal(11,8) DEFAULT NULL,
  `clock_out_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clock_out_notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_employees_employee_id_date_index` (`employee_id`,`date`),
  KEY `attendance_employees_status_index` (`status`),
  KEY `attendance_employees_created_by_index` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_employees`
--

LOCK TABLES `attendance_employees` WRITE;
/*!40000 ALTER TABLE `attendance_employees` DISABLE KEYS */;
INSERT INTO `attendance_employees` VALUES (1,3,'2025-07-22','Present','13:10:30','13:12:03','11:10:30','00:00:00','02:12:03','00:00:00','Asia/Jakarta','2MR4+22Q Bandung, Jawa Barat, Indonesia',-6.95995950,107.65512560,'http://103.196.155.202:3333/storage/attendance_photos/IQIsSqNhQa8iA2tNEHRIlubps1QevMyFwKYJuoAS.jpg',NULL,'2MR4+22Q Bandung, Jawa Barat, Indonesia',-6.95991720,107.65517940,'http://103.196.155.202:3333/storage/attendance_photos/dOhTIEAkqdKRPsPslJ0pU4U57hGtlFrj6epxWpRY.jpg',NULL,2,'2025-07-22 06:10:30','2025-07-22 06:12:03');
/*!40000 ALTER TABLE `attendance_employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'Indonesia',2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(2,'Malaysia',2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(3,'Malaysia',11,'2025-07-22 01:18:30','2025-07-22 01:18:30'),(4,'FlyFursan',11,'2025-07-22 01:18:42','2025-07-22 01:18:42'),(5,'Fursan Convex',11,'2025-07-22 01:18:53','2025-07-22 01:18:53'),(6,'Irkaz Gourmet',11,'2025-07-22 01:19:02','2025-07-22 01:19:02'),(7,'Cabang Contoh',11,'2025-07-22 05:52:47','2025-07-22 05:52:47');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('spatie.permission.cache','a:3:{s:5:\"alias\";a:5:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";s:1:\"l\";s:10:\"created_by\";}s:11:\"permissions\";a:327:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"Manage User\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:11:\"Create User\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:9:\"Edit User\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:11:\"Delete User\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:11:\"Manage Role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:11:\"Create Role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:11:\"Delete Role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:9:\"Edit Role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:12:\"Manage Award\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"Create Award\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"Delete Award\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:10:\"Edit Award\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:15:\"Manage Transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:15:\"Create Transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:15:\"Delete Transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:13:\"Edit Transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:18:\"Manage Resignation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:18:\"Create Resignation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:16:\"Edit Resignation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:18:\"Delete Resignation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:13:\"Manage Travel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:13:\"Create Travel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:11:\"Edit Travel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:13:\"Delete Travel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:16:\"Manage Promotion\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:16:\"Create Promotion\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:14:\"Edit Promotion\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:16:\"Delete Promotion\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:16:\"Manage Complaint\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:16:\"Create Complaint\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:14:\"Edit Complaint\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:16:\"Delete Complaint\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:14:\"Manage Warning\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:14:\"Create Warning\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:12:\"Edit Warning\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:14:\"Delete Warning\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:18:\"Manage Termination\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:18:\"Create Termination\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:16:\"Edit Termination\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:18:\"Delete Termination\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:17:\"Manage Department\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:17:\"Create Department\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:15:\"Edit Department\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:17:\"Delete Department\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:18:\"Manage Designation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:18:\"Create Designation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:16:\"Edit Designation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:18:\"Delete Designation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:20:\"Manage Document Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:20:\"Create Document Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:18:\"Edit Document Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:20:\"Delete Document Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:13:\"Manage Branch\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:13:\"Create Branch\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:11:\"Edit Branch\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:13:\"Delete Branch\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:17:\"Manage Award Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:17:\"Create Award Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:15:\"Edit Award Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:17:\"Delete Award Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:23:\"Manage Termination Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:23:\"Create Termination Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:21:\"Edit Termination Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:23:\"Delete Termination Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:15:\"Manage Employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:15:\"Create Employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:13:\"Edit Employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:15:\"Delete Employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:13:\"Show Employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:19:\"Manage Payslip Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:19:\"Create Payslip Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:17:\"Edit Payslip Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:19:\"Delete Payslip Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:23:\"Manage Allowance Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:23:\"Create Allowance Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:21:\"Edit Allowance Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:23:\"Delete Allowance Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:18:\"Manage Loan Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:18:\"Create Loan Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:79;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:16:\"Edit Loan Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:80;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:18:\"Delete Loan Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:81;a:4:{s:1:\"a\";i:82;s:1:\"b\";s:23:\"Manage Deduction Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:82;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:23:\"Create Deduction Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:83;a:4:{s:1:\"a\";i:84;s:1:\"b\";s:21:\"Edit Deduction Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:84;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:23:\"Delete Deduction Option\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:85;a:4:{s:1:\"a\";i:86;s:1:\"b\";s:17:\"Manage Set Salary\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:86;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:17:\"Create Set Salary\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:87;a:4:{s:1:\"a\";i:88;s:1:\"b\";s:15:\"Edit Set Salary\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:88;a:4:{s:1:\"a\";i:89;s:1:\"b\";s:17:\"Delete Set Salary\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:89;a:4:{s:1:\"a\";i:90;s:1:\"b\";s:16:\"Manage Allowance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:90;a:4:{s:1:\"a\";i:91;s:1:\"b\";s:16:\"Create Allowance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:91;a:4:{s:1:\"a\";i:92;s:1:\"b\";s:14:\"Edit Allowance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:92;a:4:{s:1:\"a\";i:93;s:1:\"b\";s:16:\"Delete Allowance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:93;a:4:{s:1:\"a\";i:94;s:1:\"b\";s:16:\"Manage Deduction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:94;a:4:{s:1:\"a\";i:95;s:1:\"b\";s:16:\"Create Deduction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:95;a:4:{s:1:\"a\";i:96;s:1:\"b\";s:14:\"Edit Deduction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:96;a:4:{s:1:\"a\";i:97;s:1:\"b\";s:16:\"Delete Deduction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:97;a:4:{s:1:\"a\";i:98;s:1:\"b\";s:17:\"Create Commission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:98;a:4:{s:1:\"a\";i:99;s:1:\"b\";s:11:\"Create Loan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:99;a:4:{s:1:\"a\";i:100;s:1:\"b\";s:27:\"Create Saturation Deduction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:100;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:20:\"Create Other Payment\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:101;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:15:\"Manage Overtime\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:102;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:15:\"Create Overtime\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:103;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:15:\"Edit Commission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:104;a:4:{s:1:\"a\";i:105;s:1:\"b\";s:17:\"Delete Commission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:105;a:4:{s:1:\"a\";i:106;s:1:\"b\";s:9:\"Edit Loan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:106;a:4:{s:1:\"a\";i:107;s:1:\"b\";s:11:\"Delete Loan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:107;a:4:{s:1:\"a\";i:108;s:1:\"b\";s:25:\"Edit Saturation Deduction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:108;a:4:{s:1:\"a\";i:109;s:1:\"b\";s:27:\"Delete Saturation Deduction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:109;a:4:{s:1:\"a\";i:110;s:1:\"b\";s:18:\"Edit Other Payment\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:110;a:4:{s:1:\"a\";i:111;s:1:\"b\";s:20:\"Delete Other Payment\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:111;a:4:{s:1:\"a\";i:112;s:1:\"b\";s:13:\"Edit Overtime\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:112;a:4:{s:1:\"a\";i:113;s:1:\"b\";s:15:\"Delete Overtime\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:113;a:4:{s:1:\"a\";i:114;s:1:\"b\";s:15:\"Manage Pay Slip\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:114;a:4:{s:1:\"a\";i:115;s:1:\"b\";s:15:\"Create Pay Slip\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:115;a:4:{s:1:\"a\";i:116;s:1:\"b\";s:13:\"Edit Pay Slip\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:116;a:4:{s:1:\"a\";i:117;s:1:\"b\";s:15:\"Delete Pay Slip\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:117;a:4:{s:1:\"a\";i:118;s:1:\"b\";s:19:\"Manage Account List\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:118;a:4:{s:1:\"a\";i:119;s:1:\"b\";s:19:\"Create Account List\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:119;a:4:{s:1:\"a\";i:120;s:1:\"b\";s:17:\"Edit Account List\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:120;a:4:{s:1:\"a\";i:121;s:1:\"b\";s:19:\"Delete Account List\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:121;a:4:{s:1:\"a\";i:122;s:1:\"b\";s:25:\"View Balance Account List\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:122;a:4:{s:1:\"a\";i:123;s:1:\"b\";s:12:\"Manage Payee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:123;a:4:{s:1:\"a\";i:124;s:1:\"b\";s:12:\"Create Payee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:124;a:4:{s:1:\"a\";i:125;s:1:\"b\";s:10:\"Edit Payee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:125;a:4:{s:1:\"a\";i:126;s:1:\"b\";s:12:\"Delete Payee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:126;a:4:{s:1:\"a\";i:127;s:1:\"b\";s:12:\"Manage Payer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:127;a:4:{s:1:\"a\";i:128;s:1:\"b\";s:12:\"Create Payer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:128;a:4:{s:1:\"a\";i:129;s:1:\"b\";s:10:\"Edit Payer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:129;a:4:{s:1:\"a\";i:130;s:1:\"b\";s:12:\"Delete Payer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:130;a:4:{s:1:\"a\";i:131;s:1:\"b\";s:19:\"Manage Expense Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:131;a:4:{s:1:\"a\";i:132;s:1:\"b\";s:19:\"Create Expense Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:132;a:4:{s:1:\"a\";i:133;s:1:\"b\";s:17:\"Edit Expense Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:133;a:4:{s:1:\"a\";i:134;s:1:\"b\";s:19:\"Delete Expense Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:134;a:4:{s:1:\"a\";i:135;s:1:\"b\";s:18:\"Manage Income Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:135;a:4:{s:1:\"a\";i:136;s:1:\"b\";s:16:\"Edit Income Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:136;a:4:{s:1:\"a\";i:137;s:1:\"b\";s:18:\"Delete Income Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:137;a:4:{s:1:\"a\";i:138;s:1:\"b\";s:18:\"Create Income Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:138;a:4:{s:1:\"a\";i:139;s:1:\"b\";s:19:\"Manage Payment Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:139;a:4:{s:1:\"a\";i:140;s:1:\"b\";s:19:\"Create Payment Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:140;a:4:{s:1:\"a\";i:141;s:1:\"b\";s:17:\"Edit Payment Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:141;a:4:{s:1:\"a\";i:142;s:1:\"b\";s:19:\"Delete Payment Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:142;a:4:{s:1:\"a\";i:143;s:1:\"b\";s:14:\"Manage Deposit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:143;a:4:{s:1:\"a\";i:144;s:1:\"b\";s:14:\"Create Deposit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:144;a:4:{s:1:\"a\";i:145;s:1:\"b\";s:12:\"Edit Deposit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:145;a:4:{s:1:\"a\";i:146;s:1:\"b\";s:14:\"Delete Deposit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:146;a:4:{s:1:\"a\";i:147;s:1:\"b\";s:14:\"Manage Expense\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:147;a:4:{s:1:\"a\";i:148;s:1:\"b\";s:14:\"Create Expense\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:148;a:4:{s:1:\"a\";i:149;s:1:\"b\";s:12:\"Edit Expense\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:149;a:4:{s:1:\"a\";i:150;s:1:\"b\";s:14:\"Delete Expense\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:150;a:4:{s:1:\"a\";i:151;s:1:\"b\";s:23:\"Manage Transfer Balance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:151;a:4:{s:1:\"a\";i:152;s:1:\"b\";s:23:\"Create Transfer Balance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:152;a:4:{s:1:\"a\";i:153;s:1:\"b\";s:21:\"Edit Transfer Balance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:153;a:4:{s:1:\"a\";i:154;s:1:\"b\";s:23:\"Delete Transfer Balance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:154;a:4:{s:1:\"a\";i:155;s:1:\"b\";s:12:\"Manage Event\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:155;a:4:{s:1:\"a\";i:156;s:1:\"b\";s:12:\"Create Event\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:156;a:4:{s:1:\"a\";i:157;s:1:\"b\";s:10:\"Edit Event\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:157;a:4:{s:1:\"a\";i:158;s:1:\"b\";s:12:\"Delete Event\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:158;a:4:{s:1:\"a\";i:159;s:1:\"b\";s:19:\"Manage Announcement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:159;a:4:{s:1:\"a\";i:160;s:1:\"b\";s:19:\"Create Announcement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:160;a:4:{s:1:\"a\";i:161;s:1:\"b\";s:17:\"Edit Announcement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:161;a:4:{s:1:\"a\";i:162;s:1:\"b\";s:19:\"Delete Announcement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:162;a:4:{s:1:\"a\";i:163;s:1:\"b\";s:17:\"Manage Leave Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:163;a:4:{s:1:\"a\";i:164;s:1:\"b\";s:17:\"Create Leave Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:164;a:4:{s:1:\"a\";i:165;s:1:\"b\";s:15:\"Edit Leave Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:165;a:4:{s:1:\"a\";i:166;s:1:\"b\";s:17:\"Delete Leave Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:166;a:4:{s:1:\"a\";i:167;s:1:\"b\";s:12:\"Manage Leave\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:167;a:4:{s:1:\"a\";i:168;s:1:\"b\";s:12:\"Create Leave\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:168;a:4:{s:1:\"a\";i:169;s:1:\"b\";s:10:\"Edit Leave\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:169;a:4:{s:1:\"a\";i:170;s:1:\"b\";s:12:\"Delete Leave\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:170;a:4:{s:1:\"a\";i:171;s:1:\"b\";s:14:\"Manage Meeting\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:171;a:4:{s:1:\"a\";i:172;s:1:\"b\";s:14:\"Create Meeting\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:172;a:4:{s:1:\"a\";i:173;s:1:\"b\";s:12:\"Edit Meeting\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:173;a:4:{s:1:\"a\";i:174;s:1:\"b\";s:14:\"Delete Meeting\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:174;a:4:{s:1:\"a\";i:175;s:1:\"b\";s:13:\"Manage Ticket\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:175;a:4:{s:1:\"a\";i:176;s:1:\"b\";s:13:\"Create Ticket\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:176;a:4:{s:1:\"a\";i:177;s:1:\"b\";s:11:\"Edit Ticket\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:177;a:4:{s:1:\"a\";i:178;s:1:\"b\";s:13:\"Delete Ticket\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:178;a:4:{s:1:\"a\";i:179;s:1:\"b\";s:17:\"Manage Attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:179;a:4:{s:1:\"a\";i:180;s:1:\"b\";s:17:\"Create Attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:180;a:4:{s:1:\"a\";i:181;s:1:\"b\";s:15:\"Edit Attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:181;a:4:{s:1:\"a\";i:182;s:1:\"b\";s:17:\"Delete Attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:182;a:4:{s:1:\"a\";i:183;s:1:\"b\";s:15:\"Manage Language\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:183;a:3:{s:1:\"a\";i:184;s:1:\"b\";s:15:\"Create Language\";s:1:\"c\";s:3:\"web\";}i:184;a:4:{s:1:\"a\";i:185;s:1:\"b\";s:11:\"Manage Plan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:185;a:3:{s:1:\"a\";i:186;s:1:\"b\";s:11:\"Create Plan\";s:1:\"c\";s:3:\"web\";}i:186;a:3:{s:1:\"a\";i:187;s:1:\"b\";s:9:\"Edit Plan\";s:1:\"c\";s:3:\"web\";}i:187;a:4:{s:1:\"a\";i:188;s:1:\"b\";s:8:\"Buy Plan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:188;a:3:{s:1:\"a\";i:189;s:1:\"b\";s:22:\"Manage System Settings\";s:1:\"c\";s:3:\"web\";}i:189;a:4:{s:1:\"a\";i:190;s:1:\"b\";s:23:\"Manage Company Settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:190;a:4:{s:1:\"a\";i:191;s:1:\"b\";s:16:\"Manage TimeSheet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:191;a:4:{s:1:\"a\";i:192;s:1:\"b\";s:16:\"Create TimeSheet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:192;a:4:{s:1:\"a\";i:193;s:1:\"b\";s:14:\"Edit TimeSheet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:193;a:4:{s:1:\"a\";i:194;s:1:\"b\";s:16:\"Delete TimeSheet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:194;a:4:{s:1:\"a\";i:195;s:1:\"b\";s:12:\"Manage Order\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:195;a:3:{s:1:\"a\";i:196;s:1:\"b\";s:13:\"manage coupon\";s:1:\"c\";s:3:\"web\";}i:196;a:3:{s:1:\"a\";i:197;s:1:\"b\";s:13:\"create coupon\";s:1:\"c\";s:3:\"web\";}i:197;a:3:{s:1:\"a\";i:198;s:1:\"b\";s:11:\"edit coupon\";s:1:\"c\";s:3:\"web\";}i:198;a:3:{s:1:\"a\";i:199;s:1:\"b\";s:13:\"delete coupon\";s:1:\"c\";s:3:\"web\";}i:199;a:4:{s:1:\"a\";i:200;s:1:\"b\";s:13:\"Manage Assets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:200;a:4:{s:1:\"a\";i:201;s:1:\"b\";s:13:\"Create Assets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:201;a:4:{s:1:\"a\";i:202;s:1:\"b\";s:11:\"Edit Assets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:202;a:4:{s:1:\"a\";i:203;s:1:\"b\";s:13:\"Delete Assets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:203;a:4:{s:1:\"a\";i:204;s:1:\"b\";s:15:\"Manage Document\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:204;a:4:{s:1:\"a\";i:205;s:1:\"b\";s:15:\"Create Document\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:205;a:4:{s:1:\"a\";i:206;s:1:\"b\";s:13:\"Edit Document\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:206;a:4:{s:1:\"a\";i:207;s:1:\"b\";s:15:\"Delete Document\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:207;a:4:{s:1:\"a\";i:208;s:1:\"b\";s:23:\"Manage Employee Profile\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:208;a:4:{s:1:\"a\";i:209;s:1:\"b\";s:21:\"Show Employee Profile\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:209;a:4:{s:1:\"a\";i:210;s:1:\"b\";s:26:\"Manage Employee Last Login\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:210;a:4:{s:1:\"a\";i:211;s:1:\"b\";s:16:\"Manage Indicator\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:211;a:4:{s:1:\"a\";i:212;s:1:\"b\";s:16:\"Create Indicator\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:212;a:4:{s:1:\"a\";i:213;s:1:\"b\";s:14:\"Edit Indicator\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:213;a:4:{s:1:\"a\";i:214;s:1:\"b\";s:16:\"Delete Indicator\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:214;a:4:{s:1:\"a\";i:215;s:1:\"b\";s:14:\"Show Indicator\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:215;a:4:{s:1:\"a\";i:216;s:1:\"b\";s:16:\"Manage Appraisal\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:216;a:4:{s:1:\"a\";i:217;s:1:\"b\";s:16:\"Create Appraisal\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:217;a:4:{s:1:\"a\";i:218;s:1:\"b\";s:14:\"Edit Appraisal\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:218;a:4:{s:1:\"a\";i:219;s:1:\"b\";s:16:\"Delete Appraisal\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:219;a:4:{s:1:\"a\";i:220;s:1:\"b\";s:14:\"Show Appraisal\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:220;a:4:{s:1:\"a\";i:221;s:1:\"b\";s:16:\"Manage Goal Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:221;a:4:{s:1:\"a\";i:222;s:1:\"b\";s:16:\"Create Goal Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:222;a:4:{s:1:\"a\";i:223;s:1:\"b\";s:14:\"Edit Goal Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:223;a:4:{s:1:\"a\";i:224;s:1:\"b\";s:16:\"Delete Goal Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:224;a:4:{s:1:\"a\";i:225;s:1:\"b\";s:20:\"Manage Goal Tracking\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:225;a:4:{s:1:\"a\";i:226;s:1:\"b\";s:20:\"Create Goal Tracking\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:226;a:4:{s:1:\"a\";i:227;s:1:\"b\";s:18:\"Edit Goal Tracking\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:227;a:4:{s:1:\"a\";i:228;s:1:\"b\";s:20:\"Delete Goal Tracking\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:228;a:4:{s:1:\"a\";i:229;s:1:\"b\";s:21:\"Manage Company Policy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:229;a:4:{s:1:\"a\";i:230;s:1:\"b\";s:21:\"Create Company Policy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:230;a:4:{s:1:\"a\";i:231;s:1:\"b\";s:19:\"Edit Company Policy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:231;a:4:{s:1:\"a\";i:232;s:1:\"b\";s:21:\"Delete Company Policy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:232;a:4:{s:1:\"a\";i:233;s:1:\"b\";s:14:\"Manage Trainer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:233;a:4:{s:1:\"a\";i:234;s:1:\"b\";s:14:\"Create Trainer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:234;a:4:{s:1:\"a\";i:235;s:1:\"b\";s:12:\"Edit Trainer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:235;a:4:{s:1:\"a\";i:236;s:1:\"b\";s:14:\"Delete Trainer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:236;a:4:{s:1:\"a\";i:237;s:1:\"b\";s:12:\"Show Trainer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:237;a:4:{s:1:\"a\";i:238;s:1:\"b\";s:15:\"Manage Training\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:238;a:4:{s:1:\"a\";i:239;s:1:\"b\";s:15:\"Create Training\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:239;a:4:{s:1:\"a\";i:240;s:1:\"b\";s:13:\"Edit Training\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:240;a:4:{s:1:\"a\";i:241;s:1:\"b\";s:15:\"Delete Training\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:241;a:4:{s:1:\"a\";i:242;s:1:\"b\";s:13:\"Show Training\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:242;a:4:{s:1:\"a\";i:243;s:1:\"b\";s:20:\"Manage Training Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:243;a:4:{s:1:\"a\";i:244;s:1:\"b\";s:20:\"Create Training Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:244;a:4:{s:1:\"a\";i:245;s:1:\"b\";s:18:\"Edit Training Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:245;a:4:{s:1:\"a\";i:246;s:1:\"b\";s:20:\"Delete Training Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:246;a:4:{s:1:\"a\";i:247;s:1:\"b\";s:13:\"Manage Report\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:247;a:4:{s:1:\"a\";i:248;s:1:\"b\";s:14:\"Manage Holiday\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:248;a:4:{s:1:\"a\";i:249;s:1:\"b\";s:14:\"Create Holiday\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:249;a:4:{s:1:\"a\";i:250;s:1:\"b\";s:12:\"Edit Holiday\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:250;a:4:{s:1:\"a\";i:251;s:1:\"b\";s:14:\"Delete Holiday\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:251;a:4:{s:1:\"a\";i:252;s:1:\"b\";s:19:\"Manage Job Category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:252;a:4:{s:1:\"a\";i:253;s:1:\"b\";s:19:\"Create Job Category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:253;a:4:{s:1:\"a\";i:254;s:1:\"b\";s:17:\"Edit Job Category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:254;a:4:{s:1:\"a\";i:255;s:1:\"b\";s:19:\"Delete Job Category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:255;a:4:{s:1:\"a\";i:256;s:1:\"b\";s:16:\"Manage Job Stage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:256;a:4:{s:1:\"a\";i:257;s:1:\"b\";s:16:\"Create Job Stage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:257;a:4:{s:1:\"a\";i:258;s:1:\"b\";s:14:\"Edit Job Stage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:258;a:4:{s:1:\"a\";i:259;s:1:\"b\";s:16:\"Delete Job Stage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:259;a:4:{s:1:\"a\";i:260;s:1:\"b\";s:10:\"Manage Job\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:260;a:4:{s:1:\"a\";i:261;s:1:\"b\";s:10:\"Create Job\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:261;a:4:{s:1:\"a\";i:262;s:1:\"b\";s:8:\"Edit Job\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:262;a:4:{s:1:\"a\";i:263;s:1:\"b\";s:10:\"Delete Job\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:263;a:4:{s:1:\"a\";i:264;s:1:\"b\";s:8:\"Show Job\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:264;a:4:{s:1:\"a\";i:265;s:1:\"b\";s:22:\"Manage Job Application\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:265;a:4:{s:1:\"a\";i:266;s:1:\"b\";s:22:\"Create Job Application\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:266;a:4:{s:1:\"a\";i:267;s:1:\"b\";s:20:\"Edit Job Application\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:267;a:4:{s:1:\"a\";i:268;s:1:\"b\";s:22:\"Delete Job Application\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:268;a:4:{s:1:\"a\";i:269;s:1:\"b\";s:20:\"Show Job Application\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:269;a:4:{s:1:\"a\";i:270;s:1:\"b\";s:20:\"Move Job Application\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:270;a:4:{s:1:\"a\";i:271;s:1:\"b\";s:24:\"Add Job Application Note\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:271;a:4:{s:1:\"a\";i:272;s:1:\"b\";s:27:\"Delete Job Application Note\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:272;a:4:{s:1:\"a\";i:273;s:1:\"b\";s:25:\"Add Job Application Skill\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:273;a:4:{s:1:\"a\";i:274;s:1:\"b\";s:18:\"Manage Job OnBoard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:274;a:4:{s:1:\"a\";i:275;s:1:\"b\";s:22:\"Manage Custom Question\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:275;a:4:{s:1:\"a\";i:276;s:1:\"b\";s:22:\"Create Custom Question\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:276;a:4:{s:1:\"a\";i:277;s:1:\"b\";s:20:\"Edit Custom Question\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:277;a:4:{s:1:\"a\";i:278;s:1:\"b\";s:22:\"Delete Custom Question\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:278;a:4:{s:1:\"a\";i:279;s:1:\"b\";s:25:\"Manage Interview Schedule\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:279;a:4:{s:1:\"a\";i:280;s:1:\"b\";s:25:\"Create Interview Schedule\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:280;a:4:{s:1:\"a\";i:281;s:1:\"b\";s:23:\"Edit Interview Schedule\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:281;a:4:{s:1:\"a\";i:282;s:1:\"b\";s:25:\"Delete Interview Schedule\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:282;a:4:{s:1:\"a\";i:283;s:1:\"b\";s:13:\"Manage Career\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:283;a:4:{s:1:\"a\";i:284;s:1:\"b\";s:19:\"Manage Competencies\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:284;a:4:{s:1:\"a\";i:285;s:1:\"b\";s:19:\"Create Competencies\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:285;a:4:{s:1:\"a\";i:286;s:1:\"b\";s:17:\"Edit Competencies\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:286;a:4:{s:1:\"a\";i:287;s:1:\"b\";s:19:\"Delete Competencies\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:287;a:4:{s:1:\"a\";i:288;s:1:\"b\";s:23:\"Manage Performance Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:288;a:4:{s:1:\"a\";i:289;s:1:\"b\";s:23:\"Create Performance Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:289;a:4:{s:1:\"a\";i:290;s:1:\"b\";s:21:\"Edit Performance Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:290;a:4:{s:1:\"a\";i:291;s:1:\"b\";s:23:\"Delete Performance Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:291;a:4:{s:1:\"a\";i:292;s:1:\"b\";s:20:\"Manage Contract Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:292;a:4:{s:1:\"a\";i:293;s:1:\"b\";s:20:\"Create Contract Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:293;a:4:{s:1:\"a\";i:294;s:1:\"b\";s:18:\"Edit Contract Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:294;a:4:{s:1:\"a\";i:295;s:1:\"b\";s:20:\"Delete Contract Type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:295;a:4:{s:1:\"a\";i:296;s:1:\"b\";s:15:\"Manage Contract\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:296;a:4:{s:1:\"a\";i:297;s:1:\"b\";s:15:\"Create Contract\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:297;a:4:{s:1:\"a\";i:298;s:1:\"b\";s:13:\"Edit Contract\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:298;a:4:{s:1:\"a\";i:299;s:1:\"b\";s:15:\"Delete Contract\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:299;a:4:{s:1:\"a\";i:300;s:1:\"b\";s:10:\"Store Note\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:300;a:4:{s:1:\"a\";i:301;s:1:\"b\";s:11:\"Delete Note\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:301;a:4:{s:1:\"a\";i:302;s:1:\"b\";s:13:\"Store Comment\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:302;a:4:{s:1:\"a\";i:303;s:1:\"b\";s:14:\"Delete Comment\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:303;a:4:{s:1:\"a\";i:304;s:1:\"b\";s:17:\"Delete Attachment\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:304;a:4:{s:1:\"a\";i:305;s:1:\"b\";s:14:\"Create Webhook\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:305;a:4:{s:1:\"a\";i:306;s:1:\"b\";s:12:\"Edit Webhook\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:306;a:4:{s:1:\"a\";i:307;s:1:\"b\";s:14:\"Delete Webhook\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:307;a:4:{s:1:\"a\";i:308;s:1:\"b\";s:19:\"Manage Zoom meeting\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:308;a:4:{s:1:\"a\";i:309;s:1:\"b\";s:19:\"Create Zoom meeting\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:309;a:4:{s:1:\"a\";i:310;s:1:\"b\";s:17:\"Show Zoom meeting\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:310;a:4:{s:1:\"a\";i:311;s:1:\"b\";s:19:\"Delete Zoom meeting\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:311;a:4:{s:1:\"a\";i:312;s:1:\"b\";s:27:\"Manage Biometric Attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:312;a:4:{s:1:\"a\";i:313;s:1:\"b\";s:32:\"Biometric Attendance Synchronize\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:313;a:4:{s:1:\"a\";i:314;s:1:\"b\";s:20:\"Manage Reimbursement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:314;a:4:{s:1:\"a\";i:315;s:1:\"b\";s:20:\"Create Reimbursement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:315;a:4:{s:1:\"a\";i:316;s:1:\"b\";s:18:\"Edit Reimbursement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:316;a:4:{s:1:\"a\";i:317;s:1:\"b\";s:20:\"Delete Reimbursement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:317;a:4:{s:1:\"a\";i:318;s:1:\"b\";s:14:\"Manage Project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:318;a:4:{s:1:\"a\";i:319;s:1:\"b\";s:14:\"Create Project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:319;a:4:{s:1:\"a\";i:320;s:1:\"b\";s:12:\"Edit Project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:320;a:4:{s:1:\"a\";i:321;s:1:\"b\";s:14:\"Delete Project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:321;a:4:{s:1:\"a\";i:322;s:1:\"b\";s:11:\"Manage Task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:322;a:4:{s:1:\"a\";i:323;s:1:\"b\";s:15:\"Manage All Task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:323;a:4:{s:1:\"a\";i:324;s:1:\"b\";s:11:\"Create Task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:324;a:4:{s:1:\"a\";i:325;s:1:\"b\";s:9:\"Edit Task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:325;a:4:{s:1:\"a\";i:326;s:1:\"b\";s:11:\"Delete Task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:326;a:4:{s:1:\"a\";i:327;s:1:\"b\";s:14:\"Manage Rewards\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}}s:5:\"roles\";a:4:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super admin\";s:1:\"c\";s:3:\"web\";s:1:\"l\";i:0;}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:7:\"company\";s:1:\"c\";s:3:\"web\";s:1:\"l\";i:1;}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:13:\"company admin\";s:1:\"c\";s:3:\"web\";s:1:\"l\";i:2;}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:8:\"employee\";s:1:\"c\";s:3:\"web\";s:1:\"l\";i:2;}}}',1753230924);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `complaints` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `complaint_from` int NOT NULL,
  `complaint_against` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complaint_date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaints`
--

LOCK TABLES `complaints` WRITE;
/*!40000 ALTER TABLE `complaints` DISABLE KEYS */;
INSERT INTO `complaints` VALUES (1,1,2,'Unprofessional Behavior','2025-07-14','The employee has been consistently late to team meetings and uses unprofessional language during discussions.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(2,2,1,'Missing Project Deadlines','2025-07-09','Team member has missed three consecutive project deadlines without proper communication.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24');
/*!40000 ALTER TABLE `complaints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deductions`
--

DROP TABLE IF EXISTS `deductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deductions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `type` enum('permanent','monthly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'permanent',
  `month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` int DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deductions_employee_id_foreign` (`employee_id`),
  KEY `deductions_created_by_foreign` (`created_by`),
  CONSTRAINT `deductions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deductions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deductions`
--

LOCK TABLES `deductions` WRITE;
/*!40000 ALTER TABLE `deductions` DISABLE KEYS */;
INSERT INTO `deductions` VALUES (1,1,'BPJS Kesehatan',150000.00,'permanent',NULL,NULL,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(2,1,'BPJS Ketenagakerjaan',100000.00,'permanent',NULL,NULL,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(3,1,'Potongan Keterlambatan',35101.00,'monthly','07',2025,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(4,1,'Potongan Keterlambatan',182969.00,'monthly','06',2025,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(5,2,'BPJS Kesehatan',150000.00,'permanent',NULL,NULL,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(6,2,'BPJS Ketenagakerjaan',100000.00,'permanent',NULL,NULL,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(7,2,'Potongan Keterlambatan',129516.00,'monthly','07',2025,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(8,2,'Potongan Keterlambatan',76278.00,'monthly','06',2025,2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(9,3,'BPJS',120000.00,'permanent','Invalid date',NULL,1,'2025-07-22 06:20:46','2025-07-22 06:20:46');
/*!40000 ALTER TABLE `deductions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,1,'Technology',2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(2,2,'Finance',2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(3,4,'Business Development',11,'2025-07-22 01:19:19','2025-07-22 01:19:19'),(4,5,'Business Development',11,'2025-07-22 01:19:31','2025-07-22 01:19:31'),(5,6,'Business Development',11,'2025-07-22 01:19:41','2025-07-22 01:19:41'),(6,3,'Finance',11,'2025-07-22 01:20:03','2025-07-22 01:20:03'),(7,4,'IT',11,'2025-07-22 01:37:36','2025-07-22 01:37:36'),(8,7,'Staff',11,'2025-07-22 05:56:48','2025-07-22 05:56:48');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designations`
--

DROP TABLE IF EXISTS `designations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int NOT NULL,
  `department_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designations`
--

LOCK TABLES `designations` WRITE;
/*!40000 ALTER TABLE `designations` DISABLE KEYS */;
INSERT INTO `designations` VALUES (1,1,1,'Developers',2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(2,2,2,'Office Boy',2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(3,3,6,'Staff',11,'2025-07-22 01:20:13','2025-07-22 01:20:13'),(4,4,3,'Staff',11,'2025-07-22 01:20:21','2025-07-22 01:20:21'),(5,5,4,'Staff',11,'2025-07-22 01:20:29','2025-07-22 01:20:29'),(6,6,5,'Staff',11,'2025-07-22 01:20:39','2025-07-22 01:20:39'),(7,7,8,'Staff',11,'2025-07-22 05:57:23','2025-07-22 05:57:23');
/*!40000 ALTER TABLE `designations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_documents`
--

DROP TABLE IF EXISTS `employee_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `document_id` int NOT NULL,
  `document_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_documents`
--

LOCK TABLES `employee_documents` WRITE;
/*!40000 ALTER TABLE `employee_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `branch_id` int DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `designation_id` int DEFAULT NULL,
  `company_doj` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documents` json DEFAULT NULL,
  `account_holder_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_identifier_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_payer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salary_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_type` int DEFAULT NULL,
  `salary` float NOT NULL DEFAULT '0',
  `is_active` int NOT NULL DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `family_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `family_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `family_address` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_employee_id_unique` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,5,'employee test','2025-07-19','Male','081392223993','Jakarta, Indonesia','employee.test@example.com','$2y$12$Uhh8jhlzikTFNyS3Eo8ViupPbHT4ID1yXfn9.f4T9CqoA7YIGpPMK','1',1,1,2,'2025-07-19',NULL,'employee test','123443132','Bank Central Asia','CENAIDAJA','Jakarta',NULL,'monthly',NULL,4000000,1,2,'2025-07-19 03:10:15','2025-07-22 06:28:15',NULL,NULL,NULL),(2,6,'employee2 test2','2025-07-19','Male','081392223993','Jakarta, Indonesia','employee.test2@example.com','$2y$12$xk1qG6wOzwqiguuibgUCUuedc6EOlQgZCM3OLDczFt2eD2z84wW0q','2',1,1,1,'2025-07-19',NULL,'employee test','123443132','Bank Central Asia','CENAIDAJA','Jakarta',NULL,'monthly',NULL,4000000,1,2,'2025-07-19 03:10:16','2025-07-19 03:10:16',NULL,NULL,NULL),(3,9,'Nia','1998-07-22','Female','08123456789','Jakarta, Indonesia','nia.hr@workwiseapp.id','$2y$12$wkX5MOcM6WLJ9nwjbGYlZOakIrNwCyw4VgW74ZBoQ739MeJIU5bIO','3',1,1,1,'2025-07-21',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'hourly',NULL,20000,1,2,'2025-07-21 21:25:22','2025-07-22 06:19:15',NULL,NULL,NULL),(6,14,'Contoh','1990-10-05','Female','089246231231','Jakarta, Indonesia','contoh@email.com','$2y$12$1gMSKLCn/V.9TdRxVEAc/.UwWryje9pLahF2sh7B9wvxrEExGnIV2','4',1,1,1,'1998-07-01',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,1,2,'2025-07-22 01:48:17','2025-07-22 01:48:17',NULL,NULL,NULL);
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_employees`
--

DROP TABLE IF EXISTS `event_employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_employees_event_id_user_id_unique` (`event_id`,`user_id`),
  KEY `event_employees_user_id_foreign` (`user_id`),
  CONSTRAINT `event_employees_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_employees`
--

LOCK TABLES `event_employees` WRITE;
/*!40000 ALTER TABLE `event_employees` DISABLE KEYS */;
INSERT INTO `event_employees` VALUES (1,1,3,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(2,2,3,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(3,3,3,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(4,4,3,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(5,5,3,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(6,5,5,'2025-07-19 03:10:16','2025-07-19 03:10:16');
/*!40000 ALTER TABLE `event_employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'Team Meeting','Weekly team sync-up meeting','#4287f5','2025-07-20 10:00:16','2025-07-20 11:30:16',1,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(2,'Project Deadline','Final submission for Q1 project','#f54242','2025-07-24 17:00:16','2025-07-24 17:00:16',1,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(3,'Company Holiday','Annual company holiday','#42f545','2025-08-19 00:00:00','2025-08-19 23:59:59',1,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(4,'Training Session','New software training','#f5a442','2025-07-22 14:00:16','2025-07-22 16:00:16',1,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(5,'Client Meeting','Quarterly review with client','#9042f5','2025-07-26 11:00:16','2025-07-26 12:30:16',1,'2025-07-19 03:10:16','2025-07-19 03:10:16');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_balances`
--

DROP TABLE IF EXISTS `leave_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `leave_type_id` bigint unsigned NOT NULL,
  `total_leaves` int NOT NULL,
  `used_leaves` int NOT NULL,
  `remaining_leaves` int NOT NULL,
  `year` year NOT NULL,
  `carried_forward` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_balances_employee_id_foreign` (`employee_id`),
  KEY `leave_balances_leave_type_id_foreign` (`leave_type_id`),
  CONSTRAINT `leave_balances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_balances_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_balances`
--

LOCK TABLES `leave_balances` WRITE;
/*!40000 ALTER TABLE `leave_balances` DISABLE KEYS */;
/*!40000 ALTER TABLE `leave_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_types`
--

DROP TABLE IF EXISTS `leave_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `days` int NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_types`
--

LOCK TABLES `leave_types` WRITE;
/*!40000 ALTER TABLE `leave_types` DISABLE KEYS */;
INSERT INTO `leave_types` VALUES (1,'Medical Leave',10,2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(2,'Casual Leave',6,2,'2025-07-19 03:10:16','2025-07-19 03:10:16');
/*!40000 ALTER TABLE `leave_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leaves`
--

DROP TABLE IF EXISTS `leaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leaves` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `leave_type_id` int NOT NULL,
  `applied_on` date NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_leave_days` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `leave_reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emergency_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` int DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leaves`
--

LOCK TABLES `leaves` WRITE;
/*!40000 ALTER TABLE `leaves` DISABLE KEYS */;
INSERT INTO `leaves` VALUES (1,3,1,'2025-07-22','2025-07-21','2025-07-23','3','Ggh','082272605775','Leave approved','approved',2,1,'2025-07-22 06:16:25',NULL,NULL,'2025-07-22 06:15:28','2025-07-22 06:16:25',NULL);
/*!40000 ALTER TABLE `leaves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_details`
--

DROP TABLE IF EXISTS `login_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_details`
--

LOCK TABLES `login_details` WRITE;
/*!40000 ALTER TABLE `login_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_12_28_030845_create_employees_table',1),(5,'2024_12_28_033352_create_login_details_table',1),(6,'2024_12_28_035618_create_settings_table',1),(7,'2024_12_28_040311_create_permission_tables',1),(8,'2024_12_28_075115_create_personal_access_tokens_table',1),(9,'2025_01_23_112724_create_branches_table',1),(10,'2025_01_23_112924_create_departments_table',1),(11,'2025_01_23_113129_create_designations_table',1),(12,'2025_01_23_113711_create_employee_documents_table',1),(13,'2025_01_27_121849_create_leave_types_table',1),(14,'2025_01_27_122226_create_leaves_table',1),(15,'2025_01_27_130221_create_leave_balances_table',1),(16,'2025_01_27_132221_create_overtimes_table',1),(17,'2025_01_29_043208_create_attendance_employees_table',1),(18,'2025_03_08_005246_create_events_table',1),(19,'2025_03_08_035115_create_reimbursements_table',1),(20,'2025_03_08_035145_create_reimbursement_categories_table',1),(21,'2025_03_08_232611_create_projects_table',1),(22,'2025_03_08_232946_create_tasks_table',1),(23,'2025_03_08_233134_create_task_attachments_table',1),(24,'2025_03_08_233310_create_task_comments_table',1),(25,'2025_03_08_233437_create_task_employees_table',1),(26,'2025_03_08_233743_create_project_employees_table',1),(27,'2025_03_11_142606_create_payslips_table',1),(28,'2025_03_11_143645_create_allowances_table',1),(29,'2025_03_11_143754_create_deductions_table',1),(30,'2025_03_11_180552_create_terminations_table',1),(31,'2025_03_16_121854_create_rewards_table',1),(32,'2025_03_16_122121_create_award_types_table',1),(33,'2025_03_16_153359_create_resignations_table',1),(34,'2025_03_16_154253_create_trips_table',1),(35,'2025_03_16_161146_create_promotions_table',1),(36,'2025_03_16_162506_create_complaints_table',1),(37,'2025_03_16_163748_create_warnings_table',1),(38,'2025_03_16_170117_create_termination_types_table',1),(39,'2025_04_25_083638_create_assets_table',1),(40,'2025_06_06_061032_create_notifications_table',1),(41,'2025_07_21_034749_add_family_on_employees_table',2),(42,'2025_07_21_051406_add_document_on_leaves_table',2),(43,'2025_07_21_060121_add_document_on_resignation_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(2,'App\\Models\\User',3),(3,'App\\Models\\User',4),(4,'App\\Models\\User',5),(4,'App\\Models\\User',6),(4,'App\\Models\\User',9),(2,'App\\Models\\User',11),(4,'App\\Models\\User',14);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `is_important` tinyint(1) NOT NULL DEFAULT '0',
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_sender_id_foreign` (`sender_id`),
  KEY `notifications_user_id_read_at_index` (`user_id`,`read_at`),
  KEY `notifications_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `notifications_type_created_at_index` (`type`,`created_at`),
  KEY `notifications_category_index` (`category`),
  CONSTRAINT `notifications_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,'leave_approved','Leave Request Approved','Your leave request from 2025-07-21 to 2025-07-23 has been approved','{\"id\": 1, \"remark\": \"Leave approved\", \"status\": \"approved\", \"employee\": {\"id\": 3, \"dob\": \"1998-07-22\", \"name\": \"Nia\", \"user\": {\"id\": 9, \"lang\": \"en\", \"plan\": null, \"type\": \"employee\", \"email\": \"nia.hr@workwiseapp.id\", \"avatar\": \"\", \"dark_mode\": 0, \"is_active\": 1, \"last_name\": \"\", \"company_id\": null, \"created_at\": \"2025-07-22T04:25:21.000000Z\", \"created_by\": 2, \"first_name\": \"Nia\", \"is_disable\": 1, \"last_login\": null, \"updated_at\": \"2025-07-22T04:25:21.000000Z\", \"subscription\": null, \"active_status\": 0, \"storage_limit\": 0, \"is_login_enable\": 1, \"messenger_color\": \"#2180f3\", \"plan_expire_date\": null, \"email_verified_at\": \"2025-07-22T04:25:21.000000Z\"}, \"email\": \"nia.hr@workwiseapp.id\", \"phone\": \"08123456789\", \"gender\": \"Female\", \"salary\": 0, \"address\": \"Jakarta, Indonesia\", \"user_id\": 9, \"bank_name\": null, \"branch_id\": 1, \"documents\": null, \"is_active\": 1, \"created_at\": \"2025-07-22T04:25:22.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-22T04:25:22.000000Z\", \"company_doj\": \"2025-07-21\", \"employee_id\": \"3\", \"family_name\": null, \"salary_type\": null, \"account_type\": null, \"family_phone\": null, \"tax_payer_id\": null, \"department_id\": 1, \"account_number\": null, \"designation_id\": 1, \"family_address\": null, \"branch_location\": null, \"account_holder_name\": null, \"bank_identifier_code\": null}, \"end_date\": \"2025-07-23\", \"applied_on\": \"2025-07-22\", \"created_at\": \"2025-07-22T13:15:28.000000Z\", \"created_by\": 2, \"start_date\": \"2025-07-21\", \"updated_at\": \"2025-07-22T13:16:25.000000Z\", \"approved_at\": \"2025-07-22T13:16:25.000000Z\", \"approved_by\": 1, \"employee_id\": 3, \"rejected_at\": null, \"rejected_by\": null, \"leave_reason\": \"Ggh\", \"document_path\": null, \"leave_type_id\": 1, \"total_leave_days\": \"3\", \"emergency_contact\": \"082272605775\"}',9,NULL,NULL,0,'high','leave',NULL,NULL,'2025-07-22 06:16:26','2025-07-22 06:16:26');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `overtimes`
--

DROP TABLE IF EXISTS `overtimes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `overtimes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number_of_days` int DEFAULT NULL,
  `overtime_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `hours` int NOT NULL,
  `rate` float DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` int DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `overtimes`
--

LOCK TABLES `overtimes` WRITE;
/*!40000 ALTER TABLE `overtimes` DISABLE KEYS */;
/*!40000 ALTER TABLE `overtimes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payslips`
--

DROP TABLE IF EXISTS `payslips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payslips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `payslip_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `month` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` year NOT NULL,
  `salary_type` enum('monthly','hourly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `basic_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_allowance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_deduction` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_overtime` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_work_hours` double DEFAULT NULL,
  `allowance` json NOT NULL,
  `deduction` json NOT NULL,
  `overtime` json NOT NULL,
  `net_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('paid','unpaid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `payment_date` date DEFAULT NULL,
  `payment_method` enum('cash','bank_transfer') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `file_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payslips_payslip_number_unique` (`payslip_number`),
  KEY `payslips_employee_id_foreign` (`employee_id`),
  KEY `payslips_created_by_foreign` (`created_by`),
  CONSTRAINT `payslips_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payslips_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payslips`
--

LOCK TABLES `payslips` WRITE;
/*!40000 ALTER TABLE `payslips` DISABLE KEYS */;
INSERT INTO `payslips` VALUES (1,1,'EMP-72025-2375','7',2025,'monthly',4000000.00,1677414.00,285101.00,0.00,0,'[{\"id\": 1, \"type\": \"permanent\", \"year\": null, \"month\": null, \"title\": \"Tunjangan Transportasi\", \"amount\": \"500000.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 1}, {\"id\": 2, \"type\": \"permanent\", \"year\": null, \"month\": null, \"title\": \"Tunjangan Makan\", \"amount\": \"800000.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 1}, {\"id\": 3, \"type\": \"monthly\", \"year\": 2025, \"month\": \"07\", \"title\": \"Bonus Performa\", \"amount\": \"377414.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 1}]','[{\"id\": 1, \"type\": \"permanent\", \"year\": null, \"month\": null, \"title\": \"BPJS Kesehatan\", \"amount\": \"150000.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 1}, {\"id\": 2, \"type\": \"permanent\", \"year\": null, \"month\": null, \"title\": \"BPJS Ketenagakerjaan\", \"amount\": \"100000.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 1}, {\"id\": 3, \"type\": \"monthly\", \"year\": 2025, \"month\": \"07\", \"title\": \"Potongan Keterlambatan\", \"amount\": \"35101.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 1}]','[]',5392313.00,'unpaid',NULL,NULL,NULL,'http://103.196.155.202:3333/storage/payslips/1/2025/payslip_EMP-72025-2375.pdf',2,'2025-07-22 06:21:17','2025-07-22 06:21:18'),(2,2,'EMP-72025-9767','7',2025,'monthly',4000000.00,2065324.00,379516.00,0.00,0,'[{\"id\": 5, \"type\": \"permanent\", \"year\": null, \"month\": null, \"title\": \"Tunjangan Transportasi\", \"amount\": \"500000.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 2}, {\"id\": 6, \"type\": \"permanent\", \"year\": null, \"month\": null, \"title\": \"Tunjangan Makan\", \"amount\": \"800000.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 2}, {\"id\": 7, \"type\": \"monthly\", \"year\": 2025, \"month\": \"07\", \"title\": \"Bonus Performa\", \"amount\": \"765324.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 2}]','[{\"id\": 5, \"type\": \"permanent\", \"year\": null, \"month\": null, \"title\": \"BPJS Kesehatan\", \"amount\": \"150000.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 2}, {\"id\": 6, \"type\": \"permanent\", \"year\": null, \"month\": null, \"title\": \"BPJS Ketenagakerjaan\", \"amount\": \"100000.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 2}, {\"id\": 7, \"type\": \"monthly\", \"year\": 2025, \"month\": \"07\", \"title\": \"Potongan Keterlambatan\", \"amount\": \"129516.00\", \"created_at\": \"2025-07-19T10:10:23.000000Z\", \"created_by\": 2, \"updated_at\": \"2025-07-19T10:10:23.000000Z\", \"employee_id\": 2}]','[]',5685808.00,'unpaid',NULL,NULL,NULL,'http://103.196.155.202:3333/storage/payslips/2/2025/payslip_EMP-72025-9767.pdf',2,'2025-07-22 06:21:18','2025-07-22 06:21:18'),(3,3,'NIA-72025-7556','7',2025,'hourly',20000.00,0.00,120000.00,0.00,0.03,'[]','[{\"id\": 9, \"type\": \"permanent\", \"year\": null, \"month\": \"Invalid date\", \"title\": \"BPJS\", \"amount\": \"120000.00\", \"created_at\": \"2025-07-22T13:20:46.000000Z\", \"created_by\": 1, \"updated_at\": \"2025-07-22T13:20:46.000000Z\", \"employee_id\": 3}]','[]',-100000.00,'unpaid',NULL,NULL,NULL,'http://103.196.155.202:3333/storage/payslips/3/2025/payslip_NIA-72025-7556.pdf',2,'2025-07-22 06:21:18','2025-07-22 06:21:18');
/*!40000 ALTER TABLE `payslips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=328 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'Manage User','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(2,'Create User','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(3,'Edit User','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(4,'Delete User','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(5,'Manage Role','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(6,'Create Role','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(7,'Delete Role','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(8,'Edit Role','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(9,'Manage Award','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(10,'Create Award','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(11,'Delete Award','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(12,'Edit Award','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(13,'Manage Transfer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(14,'Create Transfer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(15,'Delete Transfer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(16,'Edit Transfer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(17,'Manage Resignation','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(18,'Create Resignation','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(19,'Edit Resignation','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(20,'Delete Resignation','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(21,'Manage Travel','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(22,'Create Travel','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(23,'Edit Travel','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(24,'Delete Travel','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(25,'Manage Promotion','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(26,'Create Promotion','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(27,'Edit Promotion','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(28,'Delete Promotion','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(29,'Manage Complaint','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(30,'Create Complaint','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(31,'Edit Complaint','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(32,'Delete Complaint','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(33,'Manage Warning','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(34,'Create Warning','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(35,'Edit Warning','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(36,'Delete Warning','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(37,'Manage Termination','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(38,'Create Termination','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(39,'Edit Termination','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(40,'Delete Termination','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(41,'Manage Department','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(42,'Create Department','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(43,'Edit Department','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(44,'Delete Department','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(45,'Manage Designation','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(46,'Create Designation','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(47,'Edit Designation','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(48,'Delete Designation','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(49,'Manage Document Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(50,'Create Document Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(51,'Edit Document Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(52,'Delete Document Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(53,'Manage Branch','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(54,'Create Branch','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(55,'Edit Branch','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(56,'Delete Branch','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(57,'Manage Award Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(58,'Create Award Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(59,'Edit Award Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(60,'Delete Award Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(61,'Manage Termination Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(62,'Create Termination Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(63,'Edit Termination Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(64,'Delete Termination Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(65,'Manage Employee','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(66,'Create Employee','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(67,'Edit Employee','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(68,'Delete Employee','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(69,'Show Employee','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(70,'Manage Payslip Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(71,'Create Payslip Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(72,'Edit Payslip Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(73,'Delete Payslip Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(74,'Manage Allowance Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(75,'Create Allowance Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(76,'Edit Allowance Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(77,'Delete Allowance Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(78,'Manage Loan Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(79,'Create Loan Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(80,'Edit Loan Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(81,'Delete Loan Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(82,'Manage Deduction Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(83,'Create Deduction Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(84,'Edit Deduction Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(85,'Delete Deduction Option','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(86,'Manage Set Salary','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(87,'Create Set Salary','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(88,'Edit Set Salary','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(89,'Delete Set Salary','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(90,'Manage Allowance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(91,'Create Allowance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(92,'Edit Allowance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(93,'Delete Allowance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(94,'Manage Deduction','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(95,'Create Deduction','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(96,'Edit Deduction','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(97,'Delete Deduction','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(98,'Create Commission','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(99,'Create Loan','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(100,'Create Saturation Deduction','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(101,'Create Other Payment','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(102,'Manage Overtime','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(103,'Create Overtime','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(104,'Edit Commission','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(105,'Delete Commission','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(106,'Edit Loan','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(107,'Delete Loan','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(108,'Edit Saturation Deduction','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(109,'Delete Saturation Deduction','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(110,'Edit Other Payment','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(111,'Delete Other Payment','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(112,'Edit Overtime','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(113,'Delete Overtime','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(114,'Manage Pay Slip','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(115,'Create Pay Slip','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(116,'Edit Pay Slip','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(117,'Delete Pay Slip','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(118,'Manage Account List','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(119,'Create Account List','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(120,'Edit Account List','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(121,'Delete Account List','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(122,'View Balance Account List','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(123,'Manage Payee','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(124,'Create Payee','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(125,'Edit Payee','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(126,'Delete Payee','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(127,'Manage Payer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(128,'Create Payer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(129,'Edit Payer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(130,'Delete Payer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(131,'Manage Expense Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(132,'Create Expense Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(133,'Edit Expense Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(134,'Delete Expense Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(135,'Manage Income Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(136,'Edit Income Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(137,'Delete Income Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(138,'Create Income Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(139,'Manage Payment Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(140,'Create Payment Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(141,'Edit Payment Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(142,'Delete Payment Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(143,'Manage Deposit','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(144,'Create Deposit','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(145,'Edit Deposit','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(146,'Delete Deposit','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(147,'Manage Expense','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(148,'Create Expense','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(149,'Edit Expense','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(150,'Delete Expense','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(151,'Manage Transfer Balance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(152,'Create Transfer Balance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(153,'Edit Transfer Balance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(154,'Delete Transfer Balance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(155,'Manage Event','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(156,'Create Event','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(157,'Edit Event','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(158,'Delete Event','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(159,'Manage Announcement','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(160,'Create Announcement','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(161,'Edit Announcement','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(162,'Delete Announcement','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(163,'Manage Leave Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(164,'Create Leave Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(165,'Edit Leave Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(166,'Delete Leave Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(167,'Manage Leave','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(168,'Create Leave','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(169,'Edit Leave','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(170,'Delete Leave','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(171,'Manage Meeting','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(172,'Create Meeting','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(173,'Edit Meeting','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(174,'Delete Meeting','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(175,'Manage Ticket','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(176,'Create Ticket','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(177,'Edit Ticket','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(178,'Delete Ticket','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(179,'Manage Attendance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(180,'Create Attendance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(181,'Edit Attendance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(182,'Delete Attendance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(183,'Manage Language','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(184,'Create Language','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(185,'Manage Plan','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(186,'Create Plan','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(187,'Edit Plan','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(188,'Buy Plan','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(189,'Manage System Settings','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(190,'Manage Company Settings','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(191,'Manage TimeSheet','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(192,'Create TimeSheet','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(193,'Edit TimeSheet','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(194,'Delete TimeSheet','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(195,'Manage Order','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(196,'manage coupon','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(197,'create coupon','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(198,'edit coupon','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(199,'delete coupon','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(200,'Manage Assets','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(201,'Create Assets','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(202,'Edit Assets','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(203,'Delete Assets','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(204,'Manage Document','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(205,'Create Document','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(206,'Edit Document','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(207,'Delete Document','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(208,'Manage Employee Profile','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(209,'Show Employee Profile','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(210,'Manage Employee Last Login','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(211,'Manage Indicator','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(212,'Create Indicator','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(213,'Edit Indicator','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(214,'Delete Indicator','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(215,'Show Indicator','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(216,'Manage Appraisal','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(217,'Create Appraisal','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(218,'Edit Appraisal','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(219,'Delete Appraisal','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(220,'Show Appraisal','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(221,'Manage Goal Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(222,'Create Goal Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(223,'Edit Goal Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(224,'Delete Goal Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(225,'Manage Goal Tracking','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(226,'Create Goal Tracking','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(227,'Edit Goal Tracking','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(228,'Delete Goal Tracking','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(229,'Manage Company Policy','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(230,'Create Company Policy','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(231,'Edit Company Policy','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(232,'Delete Company Policy','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(233,'Manage Trainer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(234,'Create Trainer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(235,'Edit Trainer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(236,'Delete Trainer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(237,'Show Trainer','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(238,'Manage Training','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(239,'Create Training','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(240,'Edit Training','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(241,'Delete Training','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(242,'Show Training','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(243,'Manage Training Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(244,'Create Training Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(245,'Edit Training Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(246,'Delete Training Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(247,'Manage Report','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(248,'Manage Holiday','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(249,'Create Holiday','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(250,'Edit Holiday','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(251,'Delete Holiday','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(252,'Manage Job Category','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(253,'Create Job Category','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(254,'Edit Job Category','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(255,'Delete Job Category','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(256,'Manage Job Stage','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(257,'Create Job Stage','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(258,'Edit Job Stage','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(259,'Delete Job Stage','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(260,'Manage Job','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(261,'Create Job','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(262,'Edit Job','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(263,'Delete Job','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(264,'Show Job','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(265,'Manage Job Application','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(266,'Create Job Application','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(267,'Edit Job Application','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(268,'Delete Job Application','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(269,'Show Job Application','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(270,'Move Job Application','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(271,'Add Job Application Note','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(272,'Delete Job Application Note','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(273,'Add Job Application Skill','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(274,'Manage Job OnBoard','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(275,'Manage Custom Question','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(276,'Create Custom Question','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(277,'Edit Custom Question','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(278,'Delete Custom Question','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(279,'Manage Interview Schedule','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(280,'Create Interview Schedule','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(281,'Edit Interview Schedule','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(282,'Delete Interview Schedule','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(283,'Manage Career','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(284,'Manage Competencies','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(285,'Create Competencies','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(286,'Edit Competencies','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(287,'Delete Competencies','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(288,'Manage Performance Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(289,'Create Performance Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(290,'Edit Performance Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(291,'Delete Performance Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(292,'Manage Contract Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(293,'Create Contract Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(294,'Edit Contract Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(295,'Delete Contract Type','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(296,'Manage Contract','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(297,'Create Contract','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(298,'Edit Contract','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(299,'Delete Contract','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(300,'Store Note','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(301,'Delete Note','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(302,'Store Comment','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(303,'Delete Comment','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(304,'Delete Attachment','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(305,'Create Webhook','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(306,'Edit Webhook','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(307,'Delete Webhook','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(308,'Manage Zoom meeting','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(309,'Create Zoom meeting','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(310,'Show Zoom meeting','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(311,'Delete Zoom meeting','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(312,'Manage Biometric Attendance','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(313,'Biometric Attendance Synchronize','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(314,'Manage Reimbursement','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(315,'Create Reimbursement','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(316,'Edit Reimbursement','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(317,'Delete Reimbursement','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(318,'Manage Project','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(319,'Create Project','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(320,'Edit Project','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(321,'Delete Project','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(322,'Manage Task','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(323,'Manage All Task','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(324,'Create Task','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(325,'Edit Task','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(326,'Delete Task','web','2025-07-19 03:10:10','2025-07-19 03:10:10'),(327,'Manage Rewards','web','2025-07-19 03:10:10','2025-07-19 03:10:10');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (23,'App\\Models\\User',2,'web','d6a831865856470b7f8101db1161921660649d77d1c4c083a1d450df9a799526','[\"web-access\"]','2025-07-22 02:34:00',NULL,'2025-07-22 01:58:50','2025-07-22 02:34:00'),(28,'App\\Models\\User',1,'web','2c397dd81024b23a184ae14f88a1d6c5d12a93a81f641d30245015b96e5c4615','[\"web-access\"]','2025-07-22 08:24:16',NULL,'2025-07-22 08:23:58','2025-07-22 08:24:16'),(32,'App\\Models\\User',5,'mobile','01b03520189371bedcc9bbcd047d8553761ffc2e793ffc40547a665fddbcb06a','[\"mobile-access\"]',NULL,NULL,'2025-07-22 20:21:54','2025-07-22 20:21:54');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_members`
--

DROP TABLE IF EXISTS `project_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `assigned_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_members_project_id_user_id_unique` (`project_id`,`user_id`),
  KEY `project_members_user_id_foreign` (`user_id`),
  CONSTRAINT `project_members_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_members`
--

LOCK TABLES `project_members` WRITE;
/*!40000 ALTER TABLE `project_members` DISABLE KEYS */;
INSERT INTO `project_members` VALUES (1,1,4,2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(2,1,5,2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(3,1,6,2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(4,2,3,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(5,2,4,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(6,2,5,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(7,2,6,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(8,3,2,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(9,3,3,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(10,3,4,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(11,3,5,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(12,4,2,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(13,4,6,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(14,5,2,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(15,5,3,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(16,5,4,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(17,6,2,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(18,7,3,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(19,8,3,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(20,8,4,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(21,8,5,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(22,8,6,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(23,9,3,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(24,9,4,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(25,10,6,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(26,11,3,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(27,12,2,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(28,12,4,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(29,12,5,2,'2025-07-19 03:10:17','2025-07-19 03:10:17'),(30,12,6,2,'2025-07-19 03:10:17','2025-07-19 03:10:17');
/*!40000 ALTER TABLE `project_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','on_hold','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'Active Project 1','This is a sample active project created by the seeder.','2025-06-12','2025-08-23','active',2,'2025-07-05 03:10:16','2025-07-15 03:10:16',NULL),(2,'Active Project 2','This is a sample active project created by the seeder.','2025-06-13','2025-07-29','active',2,'2025-06-14 03:10:16','2025-07-11 03:10:16',NULL),(3,'Active Project 3','This is a sample active project created by the seeder.','2025-06-21','2025-09-13','active',2,'2025-06-05 03:10:16','2025-07-11 03:10:16',NULL),(4,'Active Project 4','This is a sample active project created by the seeder.','2025-06-16','2025-08-25','active',2,'2025-06-13 03:10:16','2025-07-18 03:10:16',NULL),(5,'Active Project 5','This is a sample active project created by the seeder.','2025-07-04','2025-09-05','active',2,'2025-07-03 03:10:16','2025-07-19 03:10:16',NULL),(6,'On_hold Project 1','This is a sample on_hold project created by the seeder.','2025-06-20','2025-09-30','on_hold',2,'2025-06-10 03:10:16','2025-07-14 03:10:16',NULL),(7,'On_hold Project 2','This is a sample on_hold project created by the seeder.','2025-06-05','2025-09-14','on_hold',2,'2025-06-14 03:10:16','2025-07-17 03:10:16',NULL),(8,'On_hold Project 3','This is a sample on_hold project created by the seeder.','2025-06-15','2025-10-04','on_hold',2,'2025-05-20 03:10:16','2025-07-10 03:10:16',NULL),(9,'Completed Project 1','This is a sample completed project created by the seeder.','2025-06-02','2025-07-12','completed',2,'2025-06-18 03:10:16','2025-07-13 03:10:16',NULL),(10,'Completed Project 2','This is a sample completed project created by the seeder.','2025-07-03','2025-07-18','completed',2,'2025-06-03 03:10:16','2025-07-15 03:10:16',NULL),(11,'Completed Project 3','This is a sample completed project created by the seeder.','2025-05-23','2025-07-18','completed',2,'2025-05-31 03:10:16','2025-07-16 03:10:16',NULL),(12,'Completed Project 4','This is a sample completed project created by the seeder.','2025-07-06','2025-07-02','completed',2,'2025-05-25 03:10:16','2025-07-18 03:10:16',NULL);
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promotions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `designation_id` int NOT NULL,
  `promotion_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `promotion_date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotions`
--

LOCK TABLES `promotions` WRITE;
/*!40000 ALTER TABLE `promotions` DISABLE KEYS */;
INSERT INTO `promotions` VALUES (1,2,2,'Performance Recognition','2024-09-05','Promoted based on excellent performance and consistent results over the past year.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(2,2,2,'Department Transfer','2025-07-11','Transferred to a new department with higher responsibilities and compensation.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(3,1,2,'Kepala OB','2025-07-22','Bagus',1,'2025-07-22 06:28:15','2025-07-22 06:28:15');
/*!40000 ALTER TABLE `promotions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reimbursement_categories`
--

DROP TABLE IF EXISTS `reimbursement_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reimbursement_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `max_amount` decimal(15,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reimbursement_categories`
--

LOCK TABLES `reimbursement_categories` WRITE;
/*!40000 ALTER TABLE `reimbursement_categories` DISABLE KEYS */;
INSERT INTO `reimbursement_categories` VALUES (1,'Transportation','Costs related to travel and commuting',NULL,1,2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(2,'Office Supplies','Costs for office equipment and consumables',NULL,1,2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(3,'Training','Costs for courses, workshops and learning materials',NULL,1,2,'2025-07-19 03:10:16','2025-07-19 03:10:16'),(4,'Meals','Business meal expenses',NULL,1,2,'2025-07-19 03:10:16','2025-07-19 03:10:16');
/*!40000 ALTER TABLE `reimbursement_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reimbursements`
--

DROP TABLE IF EXISTS `reimbursements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reimbursements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `request_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `receipt_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL,
  `transaction_date` date NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint unsigned DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `paid_by` bigint unsigned DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `category_id` int NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reimbursements_request_number_unique` (`request_number`),
  KEY `reimbursements_employee_id_foreign` (`employee_id`),
  KEY `reimbursements_approved_by_foreign` (`approved_by`),
  KEY `reimbursements_rejected_by_foreign` (`rejected_by`),
  KEY `reimbursements_paid_by_foreign` (`paid_by`),
  CONSTRAINT `reimbursements_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `reimbursements_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reimbursements_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`),
  CONSTRAINT `reimbursements_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reimbursements`
--

LOCK TABLES `reimbursements` WRITE;
/*!40000 ALTER TABLE `reimbursements` DISABLE KEYS */;
INSERT INTO `reimbursements` VALUES (1,1,'REQ-20250719-137','Pending reimbursement for employee test','This is a pending reimbursement request for various expenses',NULL,134087.00,'http://103.196.155.202:3333/storage/receipts/receipt-687b6f08b8a3d.pdf','pending','2025-07-15 03:10:16','2025-07-14',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending approval from finance department',2,2,'2025-07-19 03:10:16','2025-07-19 03:10:16',NULL),(2,2,'REQ-20250719-739','Pending reimbursement for employee2 test2','This is a pending reimbursement request for various expenses',NULL,247143.00,'http://103.196.155.202:3333/storage/receipts/receipt-687b6f08c0988.pdf','pending','2025-07-17 03:10:16','2025-07-11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Pending approval from finance department',4,2,'2025-07-19 03:10:16','2025-07-19 03:10:16',NULL),(3,1,'REQ-20250627-109','Rejected reimbursement for employee test','This reimbursement request was rejected due to insufficient documentation',NULL,132970.00,'http://103.196.155.202:3333/storage/receipts/receipt-687b6f08c6d68.pdf','rejected','2025-06-27 03:10:16','2025-06-22',NULL,NULL,1,'2025-06-30 03:10:16',NULL,NULL,NULL,'Missing original receipt and proper documentation',3,2,'2025-07-19 03:10:16','2025-07-19 03:10:16',NULL),(4,2,'REQ-20250621-511','Rejected reimbursement for employee2 test2','This reimbursement request was rejected due to insufficient documentation',NULL,63885.00,'http://103.196.155.202:3333/storage/receipts/receipt-687b6f08cb820.pdf','rejected','2025-06-21 03:10:16','2025-06-18',NULL,NULL,1,'2025-06-24 03:10:16',NULL,NULL,NULL,'Missing original receipt and proper documentation',3,2,'2025-07-19 03:10:16','2025-07-19 03:10:16',NULL),(5,1,'REQ-20250531-959','Paid reimbursement for employee test','This reimbursement request has been approved and paid',NULL,543095.00,'http://103.196.155.202:3333/storage/receipts/receipt-687b6f08d25d1.pdf','paid','2025-05-31 03:10:16','2025-05-25',2,'2025-06-02 03:10:16',NULL,NULL,3,'2025-06-04 03:10:16','check','Payment processed through Finance department',1,2,'2025-07-19 03:10:16','2025-07-19 03:10:16',NULL),(6,2,'REQ-20250614-875','Paid reimbursement for employee2 test2','This reimbursement request has been approved and paid',NULL,446777.00,'http://103.196.155.202:3333/storage/receipts/receipt-687b6f08d719c.pdf','paid','2025-06-14 03:10:16','2025-06-08',2,'2025-06-15 03:10:16',NULL,NULL,3,'2025-06-17 03:10:16','bank_transfer','Payment processed through Finance department',3,2,'2025-07-19 03:10:16','2025-07-19 03:10:16',NULL),(7,3,'REIM-20250722-ZgmFy','Pembelian',NULL,NULL,200000.00,NULL,'pending','2025-07-22 01:52:20','2025-07-22',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,2,'2025-07-22 01:52:20','2025-07-22 01:52:20',NULL);
/*!40000 ALTER TABLE `reimbursements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resignations`
--

DROP TABLE IF EXISTS `resignations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resignations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `notice_date` date NOT NULL,
  `resignation_date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resignations`
--

LOCK TABLES `resignations` WRITE;
/*!40000 ALTER TABLE `resignations` DISABLE KEYS */;
INSERT INTO `resignations` VALUES (1,1,'2025-05-20','2025-06-19','Moving to another company for better growth opportunities.',2,'2025-07-19 03:10:25','2025-07-19 03:10:25',NULL),(2,2,'2025-06-04','2025-07-04','Relocating to another city due to family reasons.',2,'2025-07-19 03:10:25','2025-07-19 03:10:25',NULL);
/*!40000 ALTER TABLE `resignations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reward_types`
--

DROP TABLE IF EXISTS `reward_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reward_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reward_types`
--

LOCK TABLES `reward_types` WRITE;
/*!40000 ALTER TABLE `reward_types` DISABLE KEYS */;
INSERT INTO `reward_types` VALUES (1,'Employee of the Month','Awarded to employees who demonstrate exceptional performance and dedication.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(2,'Achievement Award','For completing significant milestones or projects.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(3,'Innovation Award','For employees who introduce new ideas or improvements.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(4,'Leadership Award','For demonstrating exceptional leadership qualities.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(5,'Teamwork Award','For outstanding collaboration and team spirit.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23');
/*!40000 ALTER TABLE `reward_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rewards`
--

DROP TABLE IF EXISTS `rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rewards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `reward_type_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `gift` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rewards_employee_id_foreign` (`employee_id`),
  CONSTRAINT `rewards_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rewards`
--

LOCK TABLES `rewards` WRITE;
/*!40000 ALTER TABLE `rewards` DISABLE KEYS */;
INSERT INTO `rewards` VALUES (1,2,3,'2024-09-21','Gift Card - Rp 500.000','Developed a new process that reduced operating costs by 15%.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(2,2,1,'2025-05-19','Weekend Getaway Package','Recognized for perfect attendance and exceptional service quality throughout the month.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(3,2,2,'2025-04-13','Team Lunch','Achieved 150% of quarterly sales target.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(4,1,1,'2025-04-28','Certificate and Trophy','For consistently exceeding targets and maintaining excellent customer satisfaction scores.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(5,2,4,'2024-12-26','Smart Watch','Mentored three junior employees who have now been promoted.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(6,2,3,'2025-01-23','Certificate and Trophy','Developed a new process that reduced operating costs by 15%.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(7,1,5,'2024-11-01','Gift Card - Rp 500.000','Consistently supported team members and fostered a collaborative environment.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(8,2,2,'2025-06-01','Weekend Getaway Package','Successfully completed the project ahead of schedule and under budget.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(9,1,1,'2025-06-04',NULL,'For going above and beyond to assist colleagues and improve team performance.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(10,2,3,'2025-06-20',NULL,'Designed and implemented a new reporting system that saved 10 hours per week.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(11,2,2,'2024-07-19','Additional Day Off','Achieved 150% of quarterly sales target.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(12,1,3,'2025-03-02','Team Lunch','Created an innovative solution to a long-standing customer issue.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(13,1,3,'2025-03-02','Dining Voucher - Rp 1.000.000','Created an innovative solution to a long-standing customer issue.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(14,2,4,'2024-10-27','Weekend Getaway Package','Demonstrated exceptional crisis management during system outage.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(15,1,1,'2024-08-22','Gift Card - Rp 500.000','For consistently exceeding targets and maintaining excellent customer satisfaction scores.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(16,1,1,'2024-12-25','Gift Card - Rp 500.000','Recognized for perfect attendance and exceptional service quality throughout the month.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(17,2,5,'2024-08-07',NULL,'Volunteered to help other departments during peak periods.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(18,1,5,'2025-01-23','Smart Watch','Key contributor to cross-functional project success.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(19,1,2,'2024-09-28',NULL,'Achieved 150% of quarterly sales target.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(20,2,5,'2025-06-06',NULL,'Volunteered to help other departments during peak periods.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(21,1,3,'2024-09-11','Dining Voucher - Rp 1.000.000','Designed and implemented a new reporting system that saved 10 hours per week.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(22,1,1,'2024-09-09','Weekend Getaway Package','Recognized for perfect attendance and exceptional service quality throughout the month.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(23,2,3,'2024-11-13','Smart Watch','Developed a new process that reduced operating costs by 15%.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(24,1,1,'2025-03-13',NULL,'For going above and beyond to assist colleagues and improve team performance.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(25,1,5,'2025-01-27','Certificate and Trophy','Volunteered to help other departments during peak periods.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(26,1,1,'2025-06-30','Smart Watch','For going above and beyond to assist colleagues and improve team performance.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(27,2,3,'2025-03-25','Weekend Getaway Package','Developed a new process that reduced operating costs by 15%.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(28,2,3,'2024-09-19','Additional Day Off','Developed a new process that reduced operating costs by 15%.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(29,2,3,'2024-08-25','Weekend Getaway Package','Designed and implemented a new reporting system that saved 10 hours per week.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23'),(30,1,3,'2025-05-18','Smart Watch','Developed a new process that reduced operating costs by 15%.',2,'2025-07-19 03:10:23','2025-07-19 03:10:23');
/*!40000 ALTER TABLE `rewards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(88,1),(89,1),(90,1),(91,1),(92,1),(93,1),(94,1),(95,1),(96,1),(97,1),(98,1),(99,1),(100,1),(101,1),(102,1),(103,1),(104,1),(105,1),(106,1),(107,1),(108,1),(109,1),(110,1),(111,1),(112,1),(113,1),(114,1),(115,1),(116,1),(117,1),(118,1),(119,1),(120,1),(121,1),(122,1),(123,1),(124,1),(125,1),(126,1),(127,1),(128,1),(129,1),(130,1),(131,1),(132,1),(133,1),(134,1),(135,1),(136,1),(137,1),(138,1),(139,1),(140,1),(141,1),(142,1),(143,1),(144,1),(145,1),(146,1),(147,1),(148,1),(149,1),(150,1),(151,1),(152,1),(153,1),(154,1),(155,1),(156,1),(157,1),(158,1),(159,1),(160,1),(161,1),(162,1),(163,1),(164,1),(165,1),(166,1),(167,1),(168,1),(169,1),(170,1),(171,1),(172,1),(173,1),(174,1),(175,1),(176,1),(177,1),(178,1),(179,1),(180,1),(181,1),(182,1),(183,1),(185,1),(188,1),(190,1),(191,1),(192,1),(193,1),(194,1),(195,1),(200,1),(201,1),(202,1),(203,1),(204,1),(205,1),(206,1),(207,1),(208,1),(209,1),(210,1),(211,1),(212,1),(213,1),(214,1),(215,1),(216,1),(217,1),(218,1),(219,1),(220,1),(221,1),(222,1),(223,1),(224,1),(225,1),(226,1),(227,1),(228,1),(229,1),(230,1),(231,1),(232,1),(233,1),(234,1),(235,1),(236,1),(237,1),(238,1),(239,1),(240,1),(241,1),(242,1),(243,1),(244,1),(245,1),(246,1),(247,1),(248,1),(249,1),(250,1),(251,1),(252,1),(253,1),(254,1),(255,1),(256,1),(257,1),(258,1),(259,1),(260,1),(261,1),(262,1),(263,1),(264,1),(265,1),(266,1),(267,1),(268,1),(269,1),(270,1),(271,1),(272,1),(273,1),(274,1),(275,1),(276,1),(277,1),(278,1),(279,1),(280,1),(281,1),(282,1),(283,1),(284,1),(285,1),(286,1),(287,1),(288,1),(289,1),(290,1),(291,1),(292,1),(293,1),(294,1),(295,1),(296,1),(297,1),(298,1),(299,1),(300,1),(301,1),(302,1),(303,1),(304,1),(305,1),(306,1),(307,1),(308,1),(309,1),(310,1),(311,1),(312,1),(313,1),(314,1),(315,1),(316,1),(317,1),(318,1),(319,1),(320,1),(321,1),(322,1),(323,1),(324,1),(325,1),(326,1),(327,1),(1,2),(2,2),(3,2),(4,2),(5,2),(6,2),(7,2),(8,2),(9,2),(10,2),(11,2),(12,2),(13,2),(14,2),(15,2),(16,2),(17,2),(18,2),(19,2),(20,2),(21,2),(22,2),(23,2),(24,2),(25,2),(26,2),(27,2),(28,2),(29,2),(30,2),(31,2),(32,2),(33,2),(34,2),(35,2),(36,2),(37,2),(38,2),(39,2),(40,2),(41,2),(42,2),(43,2),(44,2),(45,2),(46,2),(47,2),(48,2),(49,2),(50,2),(51,2),(52,2),(53,2),(54,2),(55,2),(56,2),(57,2),(58,2),(59,2),(60,2),(61,2),(62,2),(63,2),(64,2),(65,2),(66,2),(67,2),(68,2),(69,2),(70,2),(71,2),(72,2),(73,2),(74,2),(75,2),(76,2),(77,2),(78,2),(79,2),(80,2),(81,2),(82,2),(83,2),(84,2),(85,2),(86,2),(87,2),(88,2),(89,2),(90,2),(91,2),(92,2),(93,2),(94,2),(95,2),(96,2),(97,2),(98,2),(99,2),(100,2),(101,2),(102,2),(103,2),(104,2),(105,2),(106,2),(107,2),(108,2),(109,2),(110,2),(111,2),(112,2),(113,2),(114,2),(115,2),(116,2),(117,2),(118,2),(119,2),(120,2),(121,2),(122,2),(123,2),(124,2),(125,2),(126,2),(127,2),(128,2),(129,2),(130,2),(131,2),(132,2),(133,2),(134,2),(135,2),(136,2),(137,2),(138,2),(139,2),(140,2),(141,2),(142,2),(143,2),(144,2),(145,2),(146,2),(147,2),(148,2),(149,2),(150,2),(151,2),(152,2),(153,2),(154,2),(155,2),(156,2),(157,2),(158,2),(159,2),(160,2),(161,2),(162,2),(163,2),(164,2),(165,2),(166,2),(167,2),(168,2),(169,2),(170,2),(171,2),(172,2),(173,2),(174,2),(175,2),(176,2),(177,2),(178,2),(179,2),(180,2),(181,2),(182,2),(183,2),(185,2),(188,2),(190,2),(191,2),(192,2),(193,2),(194,2),(195,2),(200,2),(201,2),(202,2),(203,2),(204,2),(205,2),(206,2),(207,2),(208,2),(209,2),(210,2),(211,2),(212,2),(213,2),(214,2),(215,2),(216,2),(217,2),(218,2),(219,2),(220,2),(221,2),(222,2),(223,2),(224,2),(225,2),(226,2),(227,2),(228,2),(229,2),(230,2),(231,2),(232,2),(233,2),(234,2),(235,2),(236,2),(237,2),(238,2),(239,2),(240,2),(241,2),(242,2),(243,2),(244,2),(245,2),(246,2),(247,2),(248,2),(249,2),(250,2),(251,2),(252,2),(253,2),(254,2),(255,2),(256,2),(257,2),(258,2),(259,2),(260,2),(261,2),(262,2),(263,2),(264,2),(265,2),(266,2),(267,2),(268,2),(269,2),(270,2),(271,2),(272,2),(273,2),(274,2),(275,2),(276,2),(277,2),(278,2),(279,2),(280,2),(281,2),(282,2),(283,2),(284,2),(285,2),(286,2),(287,2),(288,2),(289,2),(290,2),(291,2),(292,2),(293,2),(294,2),(295,2),(296,2),(297,2),(298,2),(299,2),(300,2),(301,2),(302,2),(303,2),(304,2),(305,2),(306,2),(307,2),(308,2),(309,2),(310,2),(311,2),(312,2),(313,2),(314,2),(315,2),(316,2),(317,2),(318,2),(319,2),(320,2),(321,2),(322,2),(323,2),(324,2),(325,2),(326,2),(327,2),(1,3),(2,3),(3,3),(4,3),(5,3),(6,3),(7,3),(8,3),(9,3),(10,3),(11,3),(12,3),(13,3),(14,3),(15,3),(16,3),(17,3),(18,3),(19,3),(20,3),(21,3),(22,3),(23,3),(24,3),(25,3),(26,3),(27,3),(28,3),(29,3),(30,3),(31,3),(32,3),(33,3),(34,3),(35,3),(36,3),(37,3),(38,3),(39,3),(40,3),(41,3),(42,3),(43,3),(44,3),(45,3),(46,3),(47,3),(48,3),(49,3),(50,3),(51,3),(52,3),(53,3),(54,3),(55,3),(56,3),(57,3),(58,3),(59,3),(60,3),(61,3),(62,3),(63,3),(64,3),(65,3),(66,3),(67,3),(68,3),(69,3),(70,3),(71,3),(72,3),(73,3),(74,3),(75,3),(76,3),(77,3),(78,3),(79,3),(80,3),(81,3),(82,3),(83,3),(84,3),(85,3),(86,3),(87,3),(88,3),(89,3),(90,3),(91,3),(92,3),(93,3),(94,3),(95,3),(96,3),(97,3),(98,3),(99,3),(100,3),(101,3),(102,3),(103,3),(104,3),(105,3),(106,3),(107,3),(108,3),(109,3),(110,3),(111,3),(112,3),(113,3),(114,3),(115,3),(116,3),(117,3),(118,3),(119,3),(120,3),(121,3),(122,3),(123,3),(124,3),(125,3),(126,3),(127,3),(128,3),(129,3),(130,3),(131,3),(132,3),(133,3),(134,3),(135,3),(136,3),(137,3),(138,3),(139,3),(140,3),(141,3),(142,3),(143,3),(144,3),(145,3),(146,3),(147,3),(148,3),(149,3),(150,3),(151,3),(152,3),(153,3),(154,3),(155,3),(156,3),(157,3),(158,3),(159,3),(160,3),(161,3),(162,3),(163,3),(164,3),(165,3),(166,3),(167,3),(168,3),(169,3),(170,3),(171,3),(172,3),(173,3),(174,3),(175,3),(176,3),(177,3),(178,3),(179,3),(180,3),(181,3),(182,3),(183,3),(185,3),(188,3),(190,3),(191,3),(192,3),(193,3),(194,3),(195,3),(200,3),(201,3),(202,3),(203,3),(204,3),(205,3),(206,3),(207,3),(208,3),(209,3),(210,3),(211,3),(212,3),(213,3),(214,3),(215,3),(216,3),(217,3),(218,3),(219,3),(220,3),(221,3),(222,3),(223,3),(224,3),(225,3),(226,3),(227,3),(228,3),(229,3),(230,3),(231,3),(232,3),(233,3),(234,3),(235,3),(236,3),(237,3),(238,3),(239,3),(240,3),(241,3),(242,3),(243,3),(244,3),(245,3),(246,3),(247,3),(248,3),(249,3),(250,3),(251,3),(252,3),(253,3),(254,3),(255,3),(256,3),(257,3),(258,3),(259,3),(260,3),(261,3),(262,3),(263,3),(264,3),(265,3),(266,3),(267,3),(268,3),(269,3),(270,3),(271,3),(272,3),(273,3),(274,3),(275,3),(276,3),(277,3),(278,3),(279,3),(280,3),(281,3),(282,3),(283,3),(284,3),(285,3),(286,3),(287,3),(288,3),(289,3),(290,3),(291,3),(292,3),(293,3),(294,3),(295,3),(296,3),(297,3),(298,3),(299,3),(300,3),(301,3),(302,3),(303,3),(304,3),(305,3),(306,3),(307,3),(308,3),(309,3),(310,3),(311,3),(312,3),(313,3),(314,3),(315,3),(316,3),(317,3),(318,3),(319,3),(320,3),(321,3),(322,3),(323,3),(324,3),(325,3),(326,3),(327,3),(9,4),(13,4),(17,4),(18,4),(19,4),(20,4),(21,4),(25,4),(29,4),(30,4),(31,4),(32,4),(33,4),(34,4),(35,4),(36,4),(37,4),(45,4),(65,4),(67,4),(69,4),(90,4),(102,4),(103,4),(112,4),(113,4),(114,4),(155,4),(159,4),(163,4),(167,4),(168,4),(169,4),(170,4),(171,4),(175,4),(176,4),(177,4),(178,4),(179,4),(183,4),(191,4),(192,4),(193,4),(194,4),(204,4),(248,4),(283,4),(296,4),(300,4),(301,4),(302,4),(303,4),(304,4),(308,4),(310,4),(314,4),(315,4),(318,4),(322,4),(324,4),(325,4),(327,4);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super admin','web','2025-07-19 03:10:10','2025-07-19 03:10:10',0),(2,'company','web','2025-07-19 03:10:11','2025-07-19 03:10:11',1),(3,'company admin','web','2025-07-19 03:10:12','2025-07-19 03:10:12',2),(4,'employee','web','2025-07-19 03:10:13','2025-07-19 03:10:13',2);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_name_created_by_unique` (`name`,`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'local_storage_validation','jpg,jpeg,png,xlsx,xls,csv,pdf',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(2,'wasabi_storage_validation','jpg,jpeg,png,xlsx,xls,csv,pdf',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(3,'s3_storage_validation','jpg,jpeg,png,xlsx,xls,csv,pdf',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(4,'local_storage_max_upload_size','2048000',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(5,'wasabi_max_upload_size','2048000',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(6,'s3_max_upload_size','2048000',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(7,'storage_setting','local',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(8,'enable_cookie','on',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(9,'cookie_logging','on',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(10,'cookie_title','We use cookies!',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(11,'cookie_description','Hi, this website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(12,'necessary_cookies','on',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(13,'strictly_cookie_title','Strictly necessary cookies',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(14,'strictly_cookie_description','These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(15,'more_information_description','For any queries in relation to our policy on cookies and your choices, please contact us',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(16,'contactus_url','#',1,'2025-07-19 03:10:15','2025-07-19 03:10:15'),(17,'company_timezone','Asia/Jakarta',2,'2025-07-19 03:10:15','2025-07-19 03:10:15');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_assignees`
--

DROP TABLE IF EXISTS `task_assignees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_assignees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `assigned_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_assignees_task_id_user_id_unique` (`task_id`,`user_id`),
  KEY `task_assignees_user_id_foreign` (`user_id`),
  CONSTRAINT `task_assignees_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_assignees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=166 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_assignees`
--

LOCK TABLES `task_assignees` WRITE;
/*!40000 ALTER TABLE `task_assignees` DISABLE KEYS */;
INSERT INTO `task_assignees` VALUES (1,1,3,2,'2025-07-14 03:10:17','2025-07-15 03:10:17'),(2,1,5,2,'2025-06-20 03:10:17','2025-07-15 03:10:17'),(3,1,6,2,'2025-06-29 03:10:17','2025-07-18 03:10:17'),(4,2,3,2,'2025-06-27 03:10:17','2025-07-18 03:10:17'),(5,2,4,2,'2025-06-24 03:10:17','2025-07-17 03:10:17'),(6,2,6,2,'2025-06-26 03:10:17','2025-07-15 03:10:17'),(7,3,4,2,'2025-07-12 03:10:17','2025-07-18 03:10:17'),(8,4,2,2,'2025-06-20 03:10:17','2025-07-18 03:10:17'),(9,4,3,2,'2025-06-24 03:10:17','2025-07-14 03:10:17'),(10,4,6,2,'2025-07-02 03:10:17','2025-07-16 03:10:17'),(11,5,2,2,'2025-06-23 03:10:17','2025-07-18 03:10:17'),(12,5,4,2,'2025-07-08 03:10:17','2025-07-17 03:10:17'),(13,5,5,2,'2025-07-01 03:10:17','2025-07-15 03:10:17'),(14,6,3,2,'2025-07-05 03:10:17','2025-07-19 03:10:17'),(15,6,5,2,'2025-06-27 03:10:17','2025-07-16 03:10:17'),(16,7,4,2,'2025-07-01 03:10:17','2025-07-16 03:10:17'),(17,7,5,2,'2025-06-19 03:10:17','2025-07-17 03:10:17'),(18,8,2,2,'2025-07-06 03:10:17','2025-07-14 03:10:17'),(19,8,3,2,'2025-06-28 03:10:17','2025-07-15 03:10:17'),(20,8,6,2,'2025-07-04 03:10:17','2025-07-18 03:10:17'),(21,9,4,2,'2025-07-15 03:10:17','2025-07-17 03:10:17'),(22,9,5,2,'2025-07-07 03:10:17','2025-07-14 03:10:17'),(23,9,6,2,'2025-06-19 03:10:17','2025-07-16 03:10:17'),(24,10,3,2,'2025-07-02 03:10:17','2025-07-14 03:10:17'),(25,10,5,2,'2025-07-09 03:10:17','2025-07-18 03:10:17'),(26,10,6,2,'2025-06-27 03:10:17','2025-07-17 03:10:17'),(27,11,4,2,'2025-07-09 03:10:17','2025-07-17 03:10:17'),(28,11,5,2,'2025-06-24 03:10:17','2025-07-17 03:10:17'),(29,12,2,2,'2025-06-23 03:10:17','2025-07-17 03:10:17'),(30,13,4,2,'2025-06-25 03:10:17','2025-07-18 03:10:17'),(31,13,6,2,'2025-06-20 03:10:17','2025-07-16 03:10:17'),(32,14,3,2,'2025-07-15 03:10:17','2025-07-14 03:10:17'),(33,14,4,2,'2025-07-11 03:10:17','2025-07-19 03:10:17'),(34,14,5,2,'2025-07-09 03:10:17','2025-07-14 03:10:17'),(35,15,2,2,'2025-06-27 03:10:17','2025-07-18 03:10:17'),(36,16,2,2,'2025-07-04 03:10:17','2025-07-16 03:10:17'),(37,16,4,2,'2025-06-19 03:10:17','2025-07-16 03:10:17'),(38,16,5,2,'2025-06-28 03:10:17','2025-07-18 03:10:17'),(39,17,2,2,'2025-07-17 03:10:17','2025-07-16 03:10:17'),(40,17,4,2,'2025-07-14 03:10:17','2025-07-15 03:10:17'),(41,17,6,2,'2025-07-01 03:10:17','2025-07-18 03:10:17'),(42,18,3,2,'2025-07-06 03:10:17','2025-07-17 03:10:17'),(43,18,4,2,'2025-07-03 03:10:17','2025-07-18 03:10:17'),(44,18,6,2,'2025-06-29 03:10:17','2025-07-16 03:10:17'),(45,19,3,2,'2025-06-29 03:10:17','2025-07-17 03:10:17'),(46,19,6,2,'2025-07-11 03:10:17','2025-07-17 03:10:17'),(47,20,3,2,'2025-06-28 03:10:17','2025-07-14 03:10:17'),(48,21,3,2,'2025-06-24 03:10:17','2025-07-14 03:10:17'),(49,21,4,2,'2025-07-06 03:10:17','2025-07-19 03:10:17'),(50,22,2,2,'2025-07-03 03:10:17','2025-07-16 03:10:17'),(51,22,3,2,'2025-07-01 03:10:17','2025-07-19 03:10:17'),(52,22,6,2,'2025-06-26 03:10:17','2025-07-14 03:10:17'),(53,23,4,2,'2025-06-23 03:10:17','2025-07-14 03:10:17'),(54,23,5,2,'2025-07-09 03:10:17','2025-07-16 03:10:17'),(55,23,6,2,'2025-07-07 03:10:17','2025-07-16 03:10:17'),(56,24,2,2,'2025-07-17 03:10:17','2025-07-17 03:10:17'),(57,24,4,2,'2025-07-12 03:10:17','2025-07-15 03:10:17'),(58,24,6,2,'2025-06-21 03:10:17','2025-07-17 03:10:17'),(59,25,6,2,'2025-06-25 03:10:17','2025-07-17 03:10:17'),(60,26,3,2,'2025-07-05 03:10:17','2025-07-15 03:10:17'),(61,27,2,2,'2025-06-28 03:10:17','2025-07-17 03:10:17'),(62,27,4,2,'2025-06-22 03:10:17','2025-07-14 03:10:17'),(63,28,2,2,'2025-07-15 03:10:17','2025-07-17 03:10:17'),(64,28,4,2,'2025-06-25 03:10:17','2025-07-17 03:10:17'),(65,29,2,2,'2025-07-10 03:10:17','2025-07-14 03:10:17'),(66,29,6,2,'2025-07-11 03:10:17','2025-07-16 03:10:17'),(67,30,4,2,'2025-07-02 03:10:17','2025-07-17 03:10:17'),(68,30,5,2,'2025-07-07 03:10:17','2025-07-19 03:10:17'),(69,30,6,2,'2025-06-21 03:10:17','2025-07-17 03:10:17'),(70,31,2,2,'2025-06-24 03:10:17','2025-07-18 03:10:17'),(71,31,4,2,'2025-07-13 03:10:17','2025-07-16 03:10:17'),(72,32,2,2,'2025-06-23 03:10:17','2025-07-14 03:10:17'),(73,32,5,2,'2025-07-04 03:10:17','2025-07-14 03:10:17'),(74,32,6,2,'2025-06-30 03:10:17','2025-07-16 03:10:17'),(75,33,5,2,'2025-07-18 03:10:17','2025-07-19 03:10:17'),(76,33,6,2,'2025-07-14 03:10:17','2025-07-17 03:10:17'),(77,34,2,2,'2025-07-17 03:10:17','2025-07-19 03:10:17'),(78,35,3,2,'2025-07-09 03:10:17','2025-07-19 03:10:17'),(79,36,2,2,'2025-07-08 03:10:17','2025-07-19 03:10:17'),(80,36,4,2,'2025-06-29 03:10:17','2025-07-19 03:10:17'),(81,37,2,2,'2025-06-27 03:10:17','2025-07-14 03:10:17'),(82,38,3,2,'2025-06-29 03:10:17','2025-07-17 03:10:17'),(83,38,5,2,'2025-07-06 03:10:17','2025-07-15 03:10:17'),(84,38,6,2,'2025-06-27 03:10:17','2025-07-16 03:10:17'),(85,39,3,2,'2025-07-02 03:10:17','2025-07-19 03:10:17'),(86,39,5,2,'2025-07-17 03:10:17','2025-07-17 03:10:17'),(87,40,3,2,'2025-06-28 03:10:17','2025-07-18 03:10:17'),(88,40,6,2,'2025-06-25 03:10:17','2025-07-14 03:10:17'),(89,41,4,2,'2025-07-18 03:10:17','2025-07-19 03:10:17'),(90,41,6,2,'2025-06-30 03:10:17','2025-07-18 03:10:17'),(91,42,3,2,'2025-07-17 03:10:17','2025-07-14 03:10:17'),(92,43,3,2,'2025-07-01 03:10:17','2025-07-18 03:10:17'),(93,44,4,2,'2025-06-25 03:10:17','2025-07-17 03:10:17'),(94,44,5,2,'2025-06-27 03:10:17','2025-07-19 03:10:17'),(95,45,3,2,'2025-06-28 03:10:17','2025-07-16 03:10:17'),(96,45,4,2,'2025-06-27 03:10:17','2025-07-14 03:10:17'),(97,46,2,2,'2025-06-19 03:10:17','2025-07-16 03:10:17'),(98,47,2,2,'2025-07-08 03:10:17','2025-07-16 03:10:17'),(99,47,3,2,'2025-06-19 03:10:17','2025-07-18 03:10:17'),(100,47,4,2,'2025-06-29 03:10:17','2025-07-18 03:10:17'),(101,48,3,2,'2025-06-23 03:10:17','2025-07-19 03:10:17'),(102,49,5,2,'2025-07-04 03:10:17','2025-07-18 03:10:17'),(103,50,3,2,'2025-06-23 03:10:17','2025-07-18 03:10:17'),(104,50,4,2,'2025-06-20 03:10:17','2025-07-15 03:10:17'),(105,50,5,2,'2025-06-23 03:10:17','2025-07-16 03:10:17'),(106,51,2,2,'2025-06-20 03:10:17','2025-07-14 03:10:17'),(107,51,5,2,'2025-06-24 03:10:17','2025-07-18 03:10:17'),(108,52,6,2,'2025-07-03 03:10:17','2025-07-14 03:10:17'),(109,53,3,2,'2025-07-02 03:10:17','2025-07-17 03:10:17'),(110,53,5,2,'2025-07-12 03:10:17','2025-07-15 03:10:17'),(111,54,6,2,'2025-07-16 03:10:17','2025-07-14 03:10:17'),(112,55,2,2,'2025-06-24 03:10:17','2025-07-14 03:10:17'),(113,55,5,2,'2025-07-08 03:10:17','2025-07-14 03:10:17'),(114,56,3,2,'2025-06-22 03:10:17','2025-07-18 03:10:17'),(115,56,5,2,'2025-06-25 03:10:17','2025-07-19 03:10:17'),(116,57,3,2,'2025-07-08 03:10:17','2025-07-19 03:10:17'),(117,57,4,2,'2025-07-18 03:10:17','2025-07-17 03:10:17'),(118,58,4,2,'2025-06-28 03:10:17','2025-07-17 03:10:17'),(119,58,6,2,'2025-07-15 03:10:17','2025-07-18 03:10:17'),(120,59,4,2,'2025-07-03 03:10:17','2025-07-17 03:10:17'),(121,60,2,2,'2025-06-20 03:10:17','2025-07-15 03:10:17'),(122,60,5,2,'2025-07-16 03:10:17','2025-07-14 03:10:17'),(123,60,6,2,'2025-06-28 03:10:17','2025-07-16 03:10:17'),(124,61,2,2,'2025-07-13 03:10:17','2025-07-16 03:10:17'),(125,61,3,2,'2025-07-03 03:10:17','2025-07-17 03:10:17'),(126,62,2,2,'2025-07-09 03:10:17','2025-07-17 03:10:17'),(127,62,4,2,'2025-07-08 03:10:17','2025-07-16 03:10:17'),(128,62,5,2,'2025-07-06 03:10:17','2025-07-17 03:10:17'),(129,63,4,2,'2025-07-11 03:10:17','2025-07-15 03:10:17'),(130,64,2,2,'2025-07-08 03:10:17','2025-07-18 03:10:17'),(131,64,4,2,'2025-07-04 03:10:17','2025-07-18 03:10:17'),(132,64,5,2,'2025-06-22 03:10:17','2025-07-14 03:10:17'),(133,65,5,2,'2025-07-10 03:10:17','2025-07-17 03:10:17'),(134,66,6,2,'2025-07-13 03:10:17','2025-07-15 03:10:17'),(135,67,6,2,'2025-07-05 03:10:17','2025-07-16 03:10:17'),(136,68,5,2,'2025-06-23 03:10:17','2025-07-16 03:10:17'),(137,69,2,2,'2025-07-17 03:10:17','2025-07-15 03:10:17'),(138,69,4,2,'2025-07-04 03:10:17','2025-07-15 03:10:17'),(139,69,6,2,'2025-07-09 03:10:17','2025-07-17 03:10:17'),(140,70,2,2,'2025-07-18 03:10:17','2025-07-14 03:10:17'),(141,71,4,2,'2025-06-21 03:10:17','2025-07-17 03:10:17'),(142,71,5,2,'2025-07-08 03:10:17','2025-07-19 03:10:17'),(143,72,5,2,'2025-06-28 03:10:17','2025-07-18 03:10:17'),(144,73,3,2,'2025-07-05 03:10:17','2025-07-16 03:10:17'),(145,73,4,2,'2025-07-07 03:10:17','2025-07-18 03:10:17'),(146,73,6,2,'2025-07-08 03:10:17','2025-07-15 03:10:17'),(147,74,2,2,'2025-07-08 03:10:17','2025-07-17 03:10:17'),(148,74,6,2,'2025-07-14 03:10:17','2025-07-18 03:10:17'),(149,75,3,2,'2025-06-24 03:10:17','2025-07-19 03:10:17'),(150,76,2,2,'2025-07-17 03:10:17','2025-07-18 03:10:17'),(151,76,3,2,'2025-07-12 03:10:17','2025-07-15 03:10:17'),(152,76,5,2,'2025-06-21 03:10:17','2025-07-14 03:10:17'),(153,77,2,2,'2025-07-10 03:10:17','2025-07-16 03:10:17'),(154,77,3,2,'2025-07-17 03:10:17','2025-07-17 03:10:17'),(155,77,6,2,'2025-07-08 03:10:17','2025-07-15 03:10:17'),(156,78,2,2,'2025-07-17 03:10:17','2025-07-17 03:10:17'),(157,78,6,2,'2025-07-07 03:10:17','2025-07-19 03:10:17'),(158,79,3,2,'2025-07-15 03:10:17','2025-07-18 03:10:17'),(159,79,4,2,'2025-06-29 03:10:17','2025-07-19 03:10:17'),(160,80,2,2,'2025-06-30 03:10:17','2025-07-14 03:10:17'),(161,80,4,2,'2025-06-19 03:10:17','2025-07-16 03:10:17'),(162,80,5,2,'2025-07-06 03:10:17','2025-07-18 03:10:17'),(163,81,2,2,'2025-06-30 03:10:17','2025-07-16 03:10:17'),(164,81,5,2,'2025-07-13 03:10:17','2025-07-19 03:10:17'),(165,82,2,2,'2025-06-22 03:10:17','2025-07-18 03:10:17');
/*!40000 ALTER TABLE `task_assignees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_attachments`
--

DROP TABLE IF EXISTS `task_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_attachments_task_id_foreign` (`task_id`),
  CONSTRAINT `task_attachments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_attachments`
--

LOCK TABLES `task_attachments` WRITE;
/*!40000 ALTER TABLE `task_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_comments`
--

DROP TABLE IF EXISTS `task_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `commented_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_comments_task_id_foreign` (`task_id`),
  CONSTRAINT `task_comments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_comments`
--

LOCK TABLES `task_comments` WRITE;
/*!40000 ALTER TABLE `task_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('todo','in_progress','in_review','done') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'todo',
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `created_by` int NOT NULL,
  `position` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_project_id_foreign` (`project_id`),
  CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,1,'Task 1 for Active Project 1','This is a sample task for the Active Project 1 project.','in_review','high','2025-07-20',2,1,'2025-07-13 03:10:17','2025-07-15 03:10:17',NULL),(2,1,'Task 2 for Active Project 1','This is a sample task for the Active Project 1 project.','in_progress','medium','2025-07-24',2,1,'2025-07-03 03:10:17','2025-07-14 03:10:17',NULL),(3,1,'Task 3 for Active Project 1','This is a sample task for the Active Project 1 project.','in_progress','medium','2025-07-22',2,2,'2025-06-20 03:10:17','2025-07-14 03:10:17',NULL),(4,1,'Task 4 for Active Project 1','This is a sample task for the Active Project 1 project.','done','medium','2025-07-13',2,1,'2025-07-10 03:10:17','2025-07-14 03:10:17',NULL),(5,1,'Task 5 for Active Project 1','This is a sample task for the Active Project 1 project.','in_progress','low','2025-07-28',2,3,'2025-07-08 03:10:17','2025-07-17 03:10:17',NULL),(6,1,'Task 6 for Active Project 1','This is a sample task for the Active Project 1 project.','done','low','2025-07-04',2,2,'2025-07-17 03:10:17','2025-07-16 03:10:17',NULL),(7,1,'Task 7 for Active Project 1','This is a sample task for the Active Project 1 project.','todo','low','2025-08-06',2,1,'2025-06-29 03:10:17','2025-07-17 03:10:17',NULL),(8,1,'Task 8 for Active Project 1','This is a sample task for the Active Project 1 project.','in_progress','medium','2025-07-25',2,4,'2025-06-24 03:10:17','2025-07-19 03:10:17',NULL),(9,2,'Task 1 for Active Project 2','This is a sample task for the Active Project 2 project.','in_progress','medium','2025-07-25',2,1,'2025-07-18 03:10:17','2025-07-17 03:10:17',NULL),(10,2,'Task 2 for Active Project 2','This is a sample task for the Active Project 2 project.','todo','high','2025-07-24',2,1,'2025-06-27 03:10:17','2025-07-18 03:10:17',NULL),(11,2,'Task 3 for Active Project 2','This is a sample task for the Active Project 2 project.','done','low','2025-07-08',2,1,'2025-06-20 03:10:17','2025-07-19 03:10:17',NULL),(12,3,'Task 1 for Active Project 3','This is a sample task for the Active Project 3 project.','todo','high','2025-08-02',2,1,'2025-07-09 03:10:17','2025-07-18 03:10:17',NULL),(13,3,'Task 2 for Active Project 3','This is a sample task for the Active Project 3 project.','todo','medium','2025-08-12',2,2,'2025-06-30 03:10:17','2025-07-14 03:10:17',NULL),(14,3,'Task 3 for Active Project 3','This is a sample task for the Active Project 3 project.','todo','medium','2025-09-02',2,3,'2025-06-26 03:10:17','2025-07-16 03:10:17',NULL),(15,3,'Task 4 for Active Project 3','This is a sample task for the Active Project 3 project.','in_review','medium','2025-07-21',2,1,'2025-06-22 03:10:17','2025-07-16 03:10:17',NULL),(16,3,'Task 5 for Active Project 3','This is a sample task for the Active Project 3 project.','in_progress','low','2025-07-21',2,1,'2025-07-12 03:10:17','2025-07-14 03:10:17',NULL),(17,3,'Task 6 for Active Project 3','This is a sample task for the Active Project 3 project.','done','low','2025-07-16',2,1,'2025-06-29 03:10:17','2025-07-17 03:10:17',NULL),(18,4,'Task 1 for Active Project 4','This is a sample task for the Active Project 4 project.','in_progress','medium','2025-07-20',2,1,'2025-07-14 03:10:17','2025-07-15 03:10:17',NULL),(19,4,'Task 2 for Active Project 4','This is a sample task for the Active Project 4 project.','todo','medium','2025-08-15',2,1,'2025-06-26 03:10:17','2025-07-16 03:10:17',NULL),(20,4,'Task 3 for Active Project 4','This is a sample task for the Active Project 4 project.','in_review','low','2025-07-20',2,1,'2025-07-14 03:10:17','2025-07-17 03:10:17',NULL),(21,4,'Task 4 for Active Project 4','This is a sample task for the Active Project 4 project.','in_progress','high','2025-07-26',2,2,'2025-07-13 03:10:17','2025-07-18 03:10:17',NULL),(22,4,'Task 5 for Active Project 4','This is a sample task for the Active Project 4 project.','todo','medium','2025-08-04',2,2,'2025-06-25 03:10:17','2025-07-18 03:10:17',NULL),(23,4,'Task 6 for Active Project 4','This is a sample task for the Active Project 4 project.','in_progress','medium','2025-07-21',2,3,'2025-06-27 03:10:17','2025-07-14 03:10:17',NULL),(24,5,'Task 1 for Active Project 5','This is a sample task for the Active Project 5 project.','todo','low','2025-07-25',2,1,'2025-07-15 03:10:17','2025-07-18 03:10:17',NULL),(25,5,'Task 2 for Active Project 5','This is a sample task for the Active Project 5 project.','in_review','medium','2025-07-24',2,1,'2025-06-25 03:10:17','2025-07-18 03:10:17',NULL),(26,5,'Task 3 for Active Project 5','This is a sample task for the Active Project 5 project.','in_review','medium','2025-07-22',2,2,'2025-06-21 03:10:17','2025-07-14 03:10:17',NULL),(27,5,'Task 4 for Active Project 5','This is a sample task for the Active Project 5 project.','todo','high','2025-07-25',2,2,'2025-06-27 03:10:17','2025-07-17 03:10:17',NULL),(28,5,'Task 5 for Active Project 5','This is a sample task for the Active Project 5 project.','in_progress','low','2025-07-26',2,1,'2025-07-08 03:10:17','2025-07-17 03:10:17',NULL),(29,5,'Task 6 for Active Project 5','This is a sample task for the Active Project 5 project.','todo','high','2025-08-28',2,3,'2025-07-11 03:10:17','2025-07-16 03:10:17',NULL),(30,5,'Task 7 for Active Project 5','This is a sample task for the Active Project 5 project.','in_review','high','2025-07-19',2,3,'2025-07-11 03:10:17','2025-07-16 03:10:17',NULL),(31,5,'Task 8 for Active Project 5','This is a sample task for the Active Project 5 project.','in_progress','low','2025-07-21',2,2,'2025-07-08 03:10:17','2025-07-16 03:10:17',NULL),(32,5,'Task 9 for Active Project 5','This is a sample task for the Active Project 5 project.','in_progress','low','2025-07-29',2,3,'2025-07-01 03:10:17','2025-07-17 03:10:17',NULL),(33,5,'Task 10 for Active Project 5','This is a sample task for the Active Project 5 project.','in_progress','high','2025-07-29',2,4,'2025-07-18 03:10:17','2025-07-19 03:10:17',NULL),(34,6,'Task 1 for On_hold Project 1','This is a sample task for the On_hold Project 1 project.','todo','low','2025-08-17',2,1,'2025-07-06 03:10:17','2025-07-16 03:10:17',NULL),(35,6,'Task 2 for On_hold Project 1','This is a sample task for the On_hold Project 1 project.','in_progress','high','2025-07-21',2,1,'2025-06-29 03:10:17','2025-07-15 03:10:17',NULL),(36,6,'Task 3 for On_hold Project 1','This is a sample task for the On_hold Project 1 project.','done','high','2025-07-15',2,1,'2025-06-26 03:10:17','2025-07-19 03:10:17',NULL),(37,6,'Task 4 for On_hold Project 1','This is a sample task for the On_hold Project 1 project.','done','low','2025-07-07',2,2,'2025-07-16 03:10:17','2025-07-15 03:10:17',NULL),(38,6,'Task 5 for On_hold Project 1','This is a sample task for the On_hold Project 1 project.','todo','high','2025-08-31',2,2,'2025-07-13 03:10:17','2025-07-15 03:10:17',NULL),(39,7,'Task 1 for On_hold Project 2','This is a sample task for the On_hold Project 2 project.','todo','high','2025-08-07',2,1,'2025-07-09 03:10:17','2025-07-15 03:10:17',NULL),(40,7,'Task 2 for On_hold Project 2','This is a sample task for the On_hold Project 2 project.','in_progress','medium','2025-07-23',2,1,'2025-07-18 03:10:17','2025-07-17 03:10:17',NULL),(41,7,'Task 3 for On_hold Project 2','This is a sample task for the On_hold Project 2 project.','in_progress','high','2025-07-20',2,2,'2025-06-28 03:10:17','2025-07-14 03:10:17',NULL),(42,7,'Task 4 for On_hold Project 2','This is a sample task for the On_hold Project 2 project.','todo','medium','2025-08-10',2,2,'2025-07-15 03:10:17','2025-07-19 03:10:17',NULL),(43,8,'Task 1 for On_hold Project 3','This is a sample task for the On_hold Project 3 project.','todo','high','2025-09-30',2,1,'2025-07-07 03:10:17','2025-07-14 03:10:17',NULL),(44,8,'Task 2 for On_hold Project 3','This is a sample task for the On_hold Project 3 project.','done','low','2025-07-09',2,1,'2025-07-03 03:10:17','2025-07-16 03:10:17',NULL),(45,8,'Task 3 for On_hold Project 3','This is a sample task for the On_hold Project 3 project.','in_review','medium','2025-07-21',2,1,'2025-06-19 03:10:17','2025-07-16 03:10:17',NULL),(46,8,'Task 4 for On_hold Project 3','This is a sample task for the On_hold Project 3 project.','todo','medium','2025-09-03',2,2,'2025-06-24 03:10:17','2025-07-15 03:10:17',NULL),(47,8,'Task 5 for On_hold Project 3','This is a sample task for the On_hold Project 3 project.','todo','low','2025-09-06',2,3,'2025-07-11 03:10:17','2025-07-19 03:10:17',NULL),(48,8,'Task 6 for On_hold Project 3','This is a sample task for the On_hold Project 3 project.','done','low','2025-07-04',2,2,'2025-07-15 03:10:17','2025-07-18 03:10:17',NULL),(49,8,'Task 7 for On_hold Project 3','This is a sample task for the On_hold Project 3 project.','todo','high','2025-09-28',2,4,'2025-06-20 03:10:17','2025-07-15 03:10:17',NULL),(50,8,'Task 8 for On_hold Project 3','This is a sample task for the On_hold Project 3 project.','in_progress','high','2025-07-23',2,1,'2025-07-07 03:10:17','2025-07-18 03:10:17',NULL),(51,8,'Task 9 for On_hold Project 3','This is a sample task for the On_hold Project 3 project.','todo','low','2025-07-27',2,5,'2025-06-23 03:10:17','2025-07-19 03:10:17',NULL),(52,9,'Task 1 for Completed Project 1','This is a sample task for the Completed Project 1 project.','todo','low','2025-07-14',2,0,'2025-06-30 03:10:17','2025-07-22 06:23:43',NULL),(53,9,'Task 2 for Completed Project 1','This is a sample task for the Completed Project 1 project.','in_review','high','2025-07-08',2,1,'2025-06-23 03:10:17','2025-07-22 06:23:43',NULL),(54,9,'Task 3 for Completed Project 1','This is a sample task for the Completed Project 1 project.','done','low','2025-07-13',2,0,'2025-06-27 03:10:17','2025-07-22 06:23:43',NULL),(55,9,'Task 4 for Completed Project 1','This is a sample task for the Completed Project 1 project.','in_review','high','2025-07-16',2,0,'2025-07-07 03:10:17','2025-07-22 06:23:43',NULL),(56,9,'Task 5 for Completed Project 1','This is a sample task for the Completed Project 1 project.','done','high','2025-07-14',2,1,'2025-07-04 03:10:17','2025-07-22 06:23:43',NULL),(57,9,'Task 6 for Completed Project 1','This is a sample task for the Completed Project 1 project.','done','high','2025-07-06',2,2,'2025-07-07 03:10:17','2025-07-22 06:23:43',NULL),(58,9,'Task 7 for Completed Project 1','This is a sample task for the Completed Project 1 project.','done','medium','2025-07-07',2,3,'2025-06-27 03:10:17','2025-07-22 06:23:43',NULL),(59,9,'Task 8 for Completed Project 1','This is a sample task for the Completed Project 1 project.','done','low','2025-07-17',2,4,'2025-07-17 03:10:17','2025-07-22 06:23:43',NULL),(60,9,'Task 9 for Completed Project 1','This is a sample task for the Completed Project 1 project.','done','high','2025-07-08',2,5,'2025-06-25 03:10:17','2025-07-22 06:23:43',NULL),(61,10,'Task 1 for Completed Project 2','This is a sample task for the Completed Project 2 project.','done','high','2025-07-13',2,1,'2025-06-28 03:10:17','2025-07-19 03:10:17',NULL),(62,10,'Task 2 for Completed Project 2','This is a sample task for the Completed Project 2 project.','done','low','2025-07-06',2,2,'2025-07-01 03:10:17','2025-07-18 03:10:17',NULL),(63,10,'Task 3 for Completed Project 2','This is a sample task for the Completed Project 2 project.','done','low','2025-07-17',2,3,'2025-07-02 03:10:17','2025-07-14 03:10:17',NULL),(64,10,'Task 4 for Completed Project 2','This is a sample task for the Completed Project 2 project.','done','medium','2025-07-04',2,4,'2025-07-16 03:10:17','2025-07-18 03:10:17',NULL),(65,11,'Task 1 for Completed Project 3','This is a sample task for the Completed Project 3 project.','done','high','2025-07-17',2,1,'2025-07-09 03:10:17','2025-07-16 03:10:17',NULL),(66,11,'Task 2 for Completed Project 3','This is a sample task for the Completed Project 3 project.','done','high','2025-07-05',2,2,'2025-07-08 03:10:17','2025-07-16 03:10:17',NULL),(67,11,'Task 3 for Completed Project 3','This is a sample task for the Completed Project 3 project.','done','high','2025-07-15',2,3,'2025-06-20 03:10:17','2025-07-16 03:10:17',NULL),(68,11,'Task 4 for Completed Project 3','This is a sample task for the Completed Project 3 project.','done','high','2025-07-17',2,4,'2025-07-18 03:10:17','2025-07-14 03:10:17',NULL),(69,11,'Task 5 for Completed Project 3','This is a sample task for the Completed Project 3 project.','done','low','2025-07-13',2,5,'2025-07-03 03:10:17','2025-07-18 03:10:17',NULL),(70,11,'Task 6 for Completed Project 3','This is a sample task for the Completed Project 3 project.','done','medium','2025-07-11',2,6,'2025-06-29 03:10:17','2025-07-15 03:10:17',NULL),(71,11,'Task 7 for Completed Project 3','This is a sample task for the Completed Project 3 project.','done','high','2025-07-06',2,7,'2025-07-14 03:10:17','2025-07-16 03:10:17',NULL),(72,11,'Task 8 for Completed Project 3','This is a sample task for the Completed Project 3 project.','done','high','2025-07-17',2,8,'2025-07-09 03:10:17','2025-07-17 03:10:17',NULL),(73,11,'Task 9 for Completed Project 3','This is a sample task for the Completed Project 3 project.','done','high','2025-07-14',2,9,'2025-06-28 03:10:17','2025-07-14 03:10:17',NULL),(74,11,'Task 10 for Completed Project 3','This is a sample task for the Completed Project 3 project.','done','low','2025-07-10',2,10,'2025-07-17 03:10:17','2025-07-19 03:10:17',NULL),(75,12,'Task 1 for Completed Project 4','This is a sample task for the Completed Project 4 project.','done','high','2025-07-12',2,1,'2025-06-27 03:10:17','2025-07-17 03:10:17',NULL),(76,12,'Task 2 for Completed Project 4','This is a sample task for the Completed Project 4 project.','done','medium','2025-07-04',2,2,'2025-07-17 03:10:17','2025-07-14 03:10:17',NULL),(77,12,'Task 3 for Completed Project 4','This is a sample task for the Completed Project 4 project.','done','medium','2025-07-04',2,3,'2025-07-15 03:10:17','2025-07-19 03:10:17',NULL),(78,12,'Task 4 for Completed Project 4','This is a sample task for the Completed Project 4 project.','done','low','2025-07-05',2,4,'2025-07-16 03:10:17','2025-07-19 03:10:17',NULL),(79,12,'Task 5 for Completed Project 4','This is a sample task for the Completed Project 4 project.','done','low','2025-07-18',2,5,'2025-07-02 03:10:17','2025-07-19 03:10:17',NULL),(80,12,'Task 6 for Completed Project 4','This is a sample task for the Completed Project 4 project.','done','medium','2025-07-12',2,6,'2025-07-08 03:10:17','2025-07-18 03:10:17',NULL),(81,12,'Task 7 for Completed Project 4','This is a sample task for the Completed Project 4 project.','done','high','2025-07-04',2,7,'2025-06-23 03:10:17','2025-07-14 03:10:17',NULL),(82,12,'Task 8 for Completed Project 4','This is a sample task for the Completed Project 4 project.','done','low','2025-07-15',2,8,'2025-07-03 03:10:17','2025-07-17 03:10:17',NULL);
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `termination_types`
--

DROP TABLE IF EXISTS `termination_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `termination_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `termination_types`
--

LOCK TABLES `termination_types` WRITE;
/*!40000 ALTER TABLE `termination_types` DISABLE KEYS */;
INSERT INTO `termination_types` VALUES (1,'Voluntary Resignation',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(2,'Contract Completion',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(3,'Performance Issues',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(4,'Redundancy',2,'2025-07-19 03:10:25','2025-07-19 03:10:25'),(5,'Misconduct',2,'2025-07-19 03:10:25','2025-07-19 03:10:25'),(6,'Early Retirement',2,'2025-07-19 03:10:25','2025-07-19 03:10:25'),(7,'Abandonment of Position',2,'2025-07-19 03:10:25','2025-07-19 03:10:25'),(8,'Probation Failure',2,'2025-07-19 03:10:25','2025-07-19 03:10:25'),(9,'Mutual Agreement',2,'2025-07-19 03:10:25','2025-07-19 03:10:25'),(10,'Other',2,'2025-07-19 03:10:25','2025-07-19 03:10:25'),(11,'Voluntary Termination',2,'2025-07-19 03:10:25','2025-07-19 03:10:25');
/*!40000 ALTER TABLE `termination_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `terminations`
--

DROP TABLE IF EXISTS `terminations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `terminations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned DEFAULT NULL,
  `termination_type_id` int NOT NULL,
  `termination_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notice_date` date DEFAULT NULL,
  `terminated_by` bigint unsigned DEFAULT NULL,
  `is_mobile_access_allowed` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `company_id` bigint unsigned NOT NULL,
  `documents` json DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `terminations_user_id_foreign` (`user_id`),
  KEY `terminations_employee_id_foreign` (`employee_id`),
  KEY `terminations_company_id_foreign` (`company_id`),
  KEY `terminations_created_by_foreign` (`created_by`),
  KEY `terminations_terminated_by_foreign` (`terminated_by`),
  CONSTRAINT `terminations_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `terminations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `terminations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `terminations_terminated_by_foreign` FOREIGN KEY (`terminated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `terminations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terminations`
--

LOCK TABLES `terminations` WRITE;
/*!40000 ALTER TABLE `terminations` DISABLE KEYS */;
INSERT INTO `terminations` VALUES (1,5,1,11,'2025-07-19','Terminated as part of system testing','Testing termination functionality','2025-07-05',2,1,'active',2,NULL,2,'2025-07-19 03:10:25','2025-07-19 03:10:25');
/*!40000 ALTER TABLE `terminations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trips`
--

DROP TABLE IF EXISTS `trips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `purpose_of_visit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `place_of_visit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trips`
--

LOCK TABLES `trips` WRITE;
/*!40000 ALTER TABLE `trips` DISABLE KEYS */;
INSERT INTO `trips` VALUES (1,1,'2025-07-18','2025-07-21','Branch Inspection','Jakarta','Trip details and agenda for Jakarta visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(2,2,'2025-05-28','2025-06-02','Training','Bali','Trip details and agenda for Surabaya visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(3,1,'2025-05-31','2025-06-01','Branch Inspection','Medan','Trip details and agenda for Jakarta visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(4,1,'2025-07-07','2025-07-11','Business Meeting','Jakarta','Trip details and agenda for Bandung visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(5,2,'2025-06-29','2025-07-04','Business Meeting','Bali','Trip details and agenda for Bali visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(6,2,'2025-07-06','2025-07-08','Branch Inspection','Bali','Trip details and agenda for Bali visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(7,1,'2025-05-22','2025-05-27','Branch Inspection','Makassar','Trip details and agenda for Bandung visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(8,2,'2025-06-06','2025-06-12','Branch Inspection','Bandung','Trip details and agenda for Jakarta visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(9,1,'2025-07-07','2025-07-14','Business Meeting','Medan','Trip details and agenda for Jakarta visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(10,2,'2025-06-08','2025-06-12','Branch Inspection','Yogyakarta','Trip details and agenda for Jakarta visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(11,1,'2025-06-19','2025-06-24','Training','Medan','Trip details and agenda for Bandung visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(12,1,'2025-06-02','2025-06-09','Conference','Medan','Trip details and agenda for Bandung visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(13,1,'2025-05-21','2025-05-22','Training','Jakarta','Trip details and agenda for Bandung visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(14,2,'2025-07-03','2025-07-09','Training','Bali','Trip details and agenda for Makassar visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(15,2,'2025-06-28','2025-07-01','Conference','Surabaya','Trip details and agenda for Makassar visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(16,1,'2025-05-31','2025-06-06','Training','Bandung','Trip details and agenda for Yogyakarta visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(17,1,'2025-06-15','2025-06-19','Business Meeting','Medan','Trip details and agenda for Jakarta visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(18,1,'2025-07-14','2025-07-16','Business Meeting','Surabaya','Trip details and agenda for Makassar visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(19,2,'2025-06-10','2025-06-14','Client Visit','Makassar','Trip details and agenda for Yogyakarta visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(20,2,'2025-06-07','2025-06-11','Branch Inspection','Bali','Trip details and agenda for Bandung visit.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24');
/*!40000 ALTER TABLE `trips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan` int DEFAULT NULL,
  `plan_expire_date` date DEFAULT NULL,
  `storage_limit` float NOT NULL DEFAULT '0',
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  `active_status` tinyint(1) NOT NULL DEFAULT '0',
  `is_login_enable` int NOT NULL DEFAULT '1',
  `dark_mode` tinyint(1) NOT NULL DEFAULT '0',
  `messenger_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#2180f3',
  `is_disable` int NOT NULL DEFAULT '1',
  `created_by` int NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subscription` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super','Admin','superadmin@example.com','2025-07-19 03:10:11','$2y$12$gBNUzbZxi0q9CqRVD1V8jOzSE3cKLK.fopX29s5VjbhfOQ7a18iBW','super admin',NULL,'','en',NULL,NULL,0,NULL,1,0,1,0,'#2180f3',1,0,NULL,'2025-07-19 03:10:11','2025-07-19 03:10:11',NULL),(2,'company','abc','company@example.com','2025-07-19 03:10:12','$2y$12$zNxTkeTLZ/yL/gr2QPKq5.HVi0DpBi0V.MK.KfcLGag3Dw/ZQFDUW','company','CMP-1752919812rPGjv','','en',1,NULL,0,NULL,1,0,1,0,'#2180f3',1,1,NULL,'2025-07-19 03:10:12','2025-07-19 03:10:12',NULL),(3,'company2','abc2','company2@example.com','2025-07-19 03:10:12','$2y$12$QEBL/0cS9rEP.74jHQx9Z.8S2z7jmNBTiEjJDYWV7h6ZtwmpAeHYW','company','CMP-1752919812Ey7sZ','','en',1,NULL,0,NULL,1,0,1,0,'#2180f3',1,1,NULL,'2025-07-19 03:10:12','2025-07-19 03:10:12',NULL),(4,'Company Admin','abc','company.admin@example.com','2025-07-19 03:10:13','$2y$12$3A2ol12g8JXO/TqVd5tbhecFNAyG.il4o1cE8a2uikTyC10YAU1Ma','company admin',NULL,'','en',NULL,NULL,0,NULL,1,0,1,0,'#2180f3',1,2,NULL,'2025-07-19 03:10:13','2025-07-19 03:10:13',NULL),(5,'employee','test','employee.test@example.com','2025-07-19 03:10:14','$2y$12$2tPnZoB9EkODVAGAfYrFoOLZnzJ37C9WkBx6VaG2wTtDR54NktZNe','employee',NULL,'','en',NULL,NULL,0,NULL,1,0,1,0,'#2180f3',1,2,NULL,'2025-07-19 03:10:14','2025-07-19 03:10:25',NULL),(6,'employee2','test2','employee.test2@example.com','2025-07-19 03:10:15','$2y$12$VDfWKf9WGYkiKMmP.k2Y5OrtbKynIT7QrRY8UzNk1dmh0A/s7a8IK','employee',NULL,'','en',NULL,NULL,0,NULL,1,0,1,0,'#2180f3',1,2,NULL,'2025-07-19 03:10:15','2025-07-19 03:10:15',NULL),(7,'tes','zzzt','seillainteg@gmail.comz',NULL,'$2b$10$G4ZBthAy2nkTFspGNt3zi.PYY291g9.Ex.TJ89O/bzrBQcneYU6i2','company','CMP-1753113770228zshirk','','en',0,NULL,0,NULL,1,0,1,0,'#2180f3',1,0,NULL,'2025-07-21 16:02:50','2025-07-21 16:02:50','Basic'),(8,'Seilla','Integ','seilaint@gmail.com',NULL,'$2b$10$ULkvw7uKOxv3AbVRL8IhiOI8d7CllJKYKukxvHfAh1rIzYlBpPoFS','company','CMP-1753114020676na7n2a','','en',0,NULL,0,NULL,1,0,1,0,'#2180f3',1,0,NULL,'2025-07-21 16:07:00','2025-07-21 16:07:00','Basic'),(9,'Nia','','nia.hr@workwiseapp.id','2025-07-21 21:25:21','$2y$12$ZIXmxg/D.ITY05WgwZUskOZwt0ViMIYeI5aN1rpuUaUh5E1/mrXZu','employee',NULL,'','en',NULL,NULL,0,NULL,1,0,1,0,'#2180f3',1,2,NULL,'2025-07-21 21:25:21','2025-07-21 21:25:21',NULL),(10,'Afif','Af','maaa.business@gmail.com',NULL,'$2b$10$wyLqBxfS4gu0TjQEtgDk4eGEx1GKJBLokc0VnHN.6HRIQR7u8rd.m','company','CMP-1753159419983faqva9','','en',0,NULL,0,NULL,1,0,1,0,'#2180f3',1,0,NULL,'2025-07-22 04:43:39','2025-07-22 04:43:39','Basic'),(11,'Fursan','Group','fursan.hr@example.com','2025-07-22 01:18:01','$2y$12$dJ5J.JBQNwF/eUxybLP6meIF3JfKzOFU8myDCbDlQyEkeJL8hV8SG','company',NULL,'','en',0,NULL,0,NULL,1,0,1,0,'#2180f3',1,1,NULL,'2025-07-22 01:18:01','2025-07-22 01:18:01','Basic'),(14,'Contoh','','contoh@email.com','2025-07-22 01:48:16','$2y$12$jnNUJXo8nFaHBPN8WV/KI.B69vf20a7B2v.Nui3PGXJR3jE3mKvhW','employee',NULL,'','en',NULL,NULL,0,NULL,1,0,1,0,'#2180f3',1,2,NULL,'2025-07-22 01:48:17','2025-07-22 01:48:17',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warnings`
--

DROP TABLE IF EXISTS `warnings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `warnings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `warning_to` int NOT NULL,
  `warning_by` int NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warning_date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warnings`
--

LOCK TABLES `warnings` WRITE;
/*!40000 ALTER TABLE `warnings` DISABLE KEYS */;
INSERT INTO `warnings` VALUES (1,2,1,'Poor Performance','2025-05-06','Employee failed to meet performance standards for the third consecutive month.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24'),(2,2,1,'Misuse of Company Property','2025-06-01','Employee was found misusing company equipment for personal purposes.',2,'2025-07-19 03:10:24','2025-07-19 03:10:24');
/*!40000 ALTER TABLE `warnings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-24  6:36:49
