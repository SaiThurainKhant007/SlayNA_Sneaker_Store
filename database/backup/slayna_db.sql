-- =========================================================
-- SlayNa Sneaker Store (Laravel build) - Database Backup
-- Information Systems Project - Sai Thurein Khant (202635)
-- Schema + demo seed data (admin/staff/customer accounts,
-- catalogue, one sample completed order+payment+review).
-- Import with: mysql -u root slayna_db < slayna_db.sql
-- (create the database first, see command below)
-- =========================================================

CREATE DATABASE IF NOT EXISTS slayna_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE slayna_db;

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: slayna_test
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'System Admin','admin@slayna.com','$2y$12$o/3K.IiPudnCYF9z6nL3nek7BAXOFxI7NoHsE49iz6RbtVjbR8pGy',NULL,'active',NULL,'2026-11-03 00:07:44','2026-11-03 00:07:44');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `show_in_marquee` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (1,'Nike',NULL,1,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(2,'Adidas',NULL,1,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(3,'Puma',NULL,1,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(4,'New Balance',NULL,1,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(5,'Jordan',NULL,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(6,'Converse',NULL,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(7,'Vans',NULL,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(8,'Asics',NULL,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(9,'Onitsuka Tiger',NULL,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(10,'Reebok',NULL,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(11,'Hoka',NULL,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(12,'Under Armour',NULL,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(13,'Skechers',NULL,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(14,'Fila',NULL,1,'2026-11-03 00:07:46','2026-11-03 00:07:46');
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
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
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `product_size_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carts_customer_id_product_size_id_unique` (`customer_id`,`product_size_id`),
  KEY `carts_product_size_id_foreign` (`product_size_id`),
  CONSTRAINT `carts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `carts_product_size_id_foreign` FOREIGN KEY (`product_size_id`) REFERENCES `product_sizes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Running','2026-11-03 00:07:44','2026-11-03 00:07:44'),(2,'Basketball','2026-11-03 00:07:44','2026-11-03 00:07:44'),(3,'Lifestyle','2026-11-03 00:07:44','2026-11-03 00:07:44'),(4,'Skateboarding','2026-11-03 00:07:44','2026-11-03 00:07:44');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'Aung Customer','customer@slayna.com','$2y$12$o/3K.IiPudnCYF9z6nL3nek7BAXOFxI7NoHsE49iz6RbtVjbR8pGy',NULL,'No. 12, Bo Aung Kyaw Street, Yangon','active',NULL,'2026-11-03 00:07:44','2026-11-03 00:07:44');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
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
-- Table structure for table `gallery_images`
--

DROP TABLE IF EXISTS `gallery_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) NOT NULL,
  `title` varchar(80) DEFAULT NULL,
  `subtitle` varchar(120) DEFAULT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gallery_images_product_id_foreign` (`product_id`),
  CONSTRAINT `gallery_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery_images`
--

LOCK TABLES `gallery_images` WRITE;
/*!40000 ALTER TABLE `gallery_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `gallery_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
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
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_23_051545_create_brands_table',1),(5,'2026_08_23_051546_create_categories_table',1),(6,'2026_08_23_051547_create_sizes_table',1),(7,'2026_08_23_051548_create_products_table',1),(8,'2026_08_23_051549_create_product_variants_table',1),(9,'2026_08_23_051550_create_cart_items_table',1),(10,'2026_08_23_051551_create_orders_table',1),(11,'2026_08_23_051552_create_order_items_table',1),(12,'2026_08_23_051553_create_payments_table',1),(13,'2026_08_23_051554_create_saved_cards_table',1),(14,'2026_08_23_051555_create_reviews_table',1),(15,'2026_10_22_000000_add_logo_to_brands_table',1),(16,'2026_10_22_000001_create_promotions_table',1),(17,'2026_10_22_000002_add_promotion_to_order_items_table',1),(18,'2026_10_22_000003_drop_starts_on_from_promotions_table',1),(19,'2026_10_22_000004_add_mobile_wallets_to_payments_table',1),(20,'2026_10_22_000006_create_gallery_images_table',1),(21,'2026_10_23_000000_create_staff_table',1),(22,'2026_10_23_000001_create_product_images_table',1),(23,'2026_11_03_000000_split_users_into_admins_staff_customers',1),(24,'2026_11_03_000001_rename_cart_items_and_product_variants_tables',1),(25,'2026_11_03_000002_create_payment_methods_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_size_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `discount_percent` tinyint(3) unsigned DEFAULT NULL,
  `promotion_id` bigint(20) unsigned DEFAULT NULL,
  `promotion_title` varchar(120) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_promotion_id_foreign` (`promotion_id`),
  KEY `order_items_product_size_id_foreign` (`product_size_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_size_id_foreign` FOREIGN KEY (`product_size_id`) REFERENCES `product_sizes` (`id`),
  CONSTRAINT `order_items_promotion_id_foreign` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,1,145000.00,NULL,NULL,NULL,NULL,'2026-11-03 00:07:44','2026-11-03 00:07:44');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipping_address` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('pending','confirmed','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_customer_id_foreign` (`customer_id`),
  KEY `orders_processed_by_foreign` (`processed_by`),
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,'2026-11-03 06:37:44','No. 12, Bo Aung Kyaw Street, Yangon',145000.00,'completed',1,'2026-11-03 00:07:44','2026-11-03 00:07:44');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
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
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(60) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_methods_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_methods`
--

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
INSERT INTO `payment_methods` VALUES (1,'kpay','KPay','2026-11-03 00:07:43','2026-11-03 00:07:43'),(2,'ayapay','AYA Pay','2026-11-03 00:07:43','2026-11-03 00:07:43'),(3,'cod','Cash on Delivery','2026-11-03 00:07:43','2026-11-03 00:07:43'),(4,'saved_card','Saved Card','2026-11-03 00:07:43','2026-11-03 00:07:43'),(5,'bank_transfer','Bank Transfer','2026-11-03 00:07:43','2026-11-03 00:07:43');
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `payment_method_id` bigint(20) unsigned NOT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `transaction_ref` varchar(40) DEFAULT NULL,
  `payment_status` enum('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
  `confirmed_by` bigint(20) unsigned DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_foreign` (`order_id`),
  KEY `payments_confirmed_by_foreign` (`confirmed_by`),
  KEY `payments_payment_method_id_foreign` (`payment_method_id`),
  CONSTRAINT `payments_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,5,NULL,NULL,'confirmed',1,'2026-11-03 00:07:44','2026-11-03 00:07:44','2026-11-03 00:07:44');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_sizes`
--

DROP TABLE IF EXISTS `product_sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_sizes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `size_id` bigint(20) unsigned NOT NULL,
  `stock_quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_sizes_product_id_size_id_unique` (`product_id`,`size_id`),
  KEY `product_sizes_size_id_foreign` (`size_id`),
  CONSTRAINT `product_sizes_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_sizes_size_id_foreign` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=274 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_sizes`
--

LOCK TABLES `product_sizes` WRITE;
/*!40000 ALTER TABLE `product_sizes` DISABLE KEYS */;
INSERT INTO `product_sizes` VALUES (1,1,1,7,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(2,1,2,6,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(3,1,3,6,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(4,1,4,14,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(5,1,5,12,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(6,1,6,6,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(7,1,7,10,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(8,2,1,11,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(9,2,2,8,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(10,2,3,4,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(11,2,4,5,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(12,2,5,6,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(13,2,6,7,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(14,2,7,6,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(15,3,1,5,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(16,3,2,11,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(17,3,3,15,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(18,3,4,5,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(19,3,5,11,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(20,3,6,8,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(21,3,7,12,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(22,4,1,3,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(23,4,2,14,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(24,4,3,13,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(25,4,4,6,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(26,4,5,7,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(27,4,6,11,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(28,4,7,8,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(29,5,1,10,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(30,5,2,4,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(31,5,3,15,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(32,5,4,13,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(33,5,5,9,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(34,5,6,15,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(35,5,7,14,'2026-11-03 00:07:44','2026-11-03 00:07:44'),(36,6,1,0,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(37,6,2,12,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(38,6,3,2,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(39,6,4,14,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(40,6,5,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(41,6,6,0,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(42,6,7,8,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(43,7,1,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(44,7,2,11,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(45,7,3,8,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(46,7,4,15,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(47,7,5,4,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(48,7,6,13,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(49,7,7,7,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(50,8,1,8,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(51,8,2,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(52,8,3,4,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(53,8,4,10,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(54,8,5,8,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(55,8,6,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(56,8,7,0,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(57,9,1,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(58,9,2,0,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(59,9,3,7,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(60,9,4,15,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(61,9,5,12,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(62,9,6,6,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(63,9,7,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(64,10,1,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(65,10,2,11,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(66,10,3,6,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(67,10,4,4,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(68,10,5,4,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(69,10,6,2,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(70,10,7,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(71,11,1,15,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(72,11,2,4,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(73,11,3,13,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(74,11,4,0,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(75,11,5,15,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(76,11,6,10,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(77,11,7,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(78,12,1,10,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(79,12,2,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(80,12,3,14,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(81,12,4,11,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(82,12,5,13,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(83,12,6,5,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(84,12,7,8,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(85,13,1,5,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(86,13,2,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(87,13,3,10,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(88,13,4,0,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(89,13,5,8,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(90,13,6,15,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(91,13,7,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(92,14,1,14,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(93,14,2,11,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(94,14,3,9,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(95,14,4,8,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(96,14,5,5,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(97,14,6,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(98,14,7,4,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(99,15,1,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(100,15,2,10,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(101,15,3,0,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(102,15,4,8,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(103,15,5,15,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(104,15,6,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(105,15,7,0,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(106,16,1,5,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(107,16,2,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(108,16,3,0,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(109,16,4,6,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(110,16,5,9,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(111,16,6,2,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(112,16,7,14,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(113,17,1,7,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(114,17,2,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(115,17,3,11,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(116,17,4,12,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(117,17,5,7,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(118,17,6,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(119,17,7,8,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(120,18,1,10,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(121,18,2,6,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(122,18,3,14,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(123,18,4,9,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(124,18,5,14,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(125,18,6,12,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(126,18,7,12,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(127,19,1,9,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(128,19,2,8,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(129,19,3,11,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(130,19,4,0,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(131,19,5,7,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(132,19,6,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(133,19,7,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(134,20,1,13,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(135,20,2,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(136,20,3,14,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(137,20,4,12,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(138,20,5,3,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(139,20,6,6,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(140,20,7,11,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(141,21,1,2,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(142,21,2,14,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(143,21,3,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(144,21,4,14,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(145,21,5,15,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(146,21,6,5,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(147,21,7,11,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(148,22,1,13,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(149,22,2,7,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(150,22,3,9,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(151,22,4,8,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(152,22,5,6,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(153,22,6,1,'2026-11-03 00:07:45','2026-11-03 00:07:45'),(154,22,7,3,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(155,23,1,4,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(156,23,2,15,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(157,23,3,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(158,23,4,4,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(159,23,5,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(160,23,6,12,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(161,23,7,3,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(162,24,1,15,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(163,24,2,0,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(164,24,3,15,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(165,24,4,9,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(166,24,5,11,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(167,24,6,5,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(168,24,7,13,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(169,25,1,13,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(170,25,2,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(171,25,3,6,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(172,25,4,13,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(173,25,5,7,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(174,25,6,3,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(175,25,7,11,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(176,26,1,4,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(177,26,2,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(178,26,3,5,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(179,26,4,12,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(180,26,5,9,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(181,26,6,8,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(182,26,7,0,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(183,27,1,14,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(184,27,2,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(185,27,3,4,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(186,27,4,0,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(187,27,5,5,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(188,27,6,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(189,27,7,12,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(190,28,1,4,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(191,28,2,14,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(192,28,3,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(193,28,4,11,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(194,28,5,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(195,28,6,12,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(196,28,7,2,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(197,29,1,7,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(198,29,2,9,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(199,29,3,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(200,29,4,9,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(201,29,5,15,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(202,29,6,8,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(203,29,7,0,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(204,30,1,13,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(205,30,2,6,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(206,30,3,14,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(207,30,4,13,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(208,30,5,0,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(209,30,6,4,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(210,30,7,2,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(211,31,1,8,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(212,31,2,7,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(213,31,3,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(214,31,4,8,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(215,31,5,8,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(216,31,6,8,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(217,31,7,2,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(218,32,1,3,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(219,32,2,15,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(220,32,3,0,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(221,32,4,11,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(222,32,5,14,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(223,32,6,15,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(224,32,7,9,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(225,33,1,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(226,33,2,5,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(227,33,3,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(228,33,4,3,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(229,33,5,12,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(230,33,6,6,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(231,33,7,8,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(232,34,1,7,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(233,34,2,15,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(234,34,3,13,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(235,34,4,9,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(236,34,5,14,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(237,34,6,3,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(238,34,7,5,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(239,35,1,2,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(240,35,2,9,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(241,35,3,2,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(242,35,4,5,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(243,35,5,5,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(244,35,6,9,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(245,35,7,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(246,36,1,11,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(247,36,2,14,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(248,36,3,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(249,36,4,7,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(250,36,5,2,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(251,36,6,9,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(252,36,7,8,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(253,37,1,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(254,37,2,12,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(255,37,3,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(256,37,4,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(257,37,5,13,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(258,37,6,3,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(259,37,7,1,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(260,38,1,14,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(261,38,2,10,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(262,38,3,6,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(263,38,4,6,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(264,38,5,3,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(265,38,6,15,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(266,38,7,9,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(267,39,1,7,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(268,39,2,12,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(269,39,3,5,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(270,39,4,2,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(271,39,5,6,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(272,39,6,9,'2026-11-03 00:07:46','2026-11-03 00:07:46'),(273,39,7,6,'2026-11-03 00:07:46','2026-11-03 00:07:46');
/*!40000 ALTER TABLE `product_sizes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `brand_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL DEFAULT 'img/no-image.png',
  `low_stock_threshold` int(10) unsigned NOT NULL DEFAULT 5,
  `status` enum('active','discontinued') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_brand_id_foreign` (`brand_id`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Air Zoom Pegasus','Lightweight everyday running shoe with responsive cushioning.',145000.00,1,1,'img/no-image.png',5,'active','2026-11-03 00:07:44','2026-11-03 00:07:44'),(2,'Ultraboost Light','Energy-returning running shoe with Boost midsole.',210000.00,2,1,'img/no-image.png',5,'active','2026-11-03 00:07:44','2026-11-03 00:07:44'),(3,'Dunk Low Retro','Classic low-top basketball-inspired lifestyle sneaker.',165000.00,1,3,'img/no-image.png',5,'active','2026-11-03 00:07:44','2026-11-03 00:07:44'),(4,'Suede Classic','Iconic Puma suede sneaker for everyday street style.',98000.00,3,3,'img/no-image.png',5,'active','2026-11-03 00:07:44','2026-11-03 00:07:44'),(5,'574 Core','New Balance heritage running silhouette, breathable mesh upper.',125000.00,4,1,'img/no-image.png',5,'active','2026-11-03 00:07:44','2026-11-03 00:07:44'),(6,'Air Force 1 \'07','The legendary white-on-white court classic with crisp leather overlays.',155000.00,1,3,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(7,'Air Max 90','Visible Max Air cushioning and waffle outsole in an iconic 90s silhouette.',185000.00,1,3,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(8,'Vomero 17','Plush ZoomX foam for long, comfortable training miles.',175000.00,1,1,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(9,'SB Dunk Low Pro','Padded tongue and grippy cupsole tuned for the skatepark.',160000.00,1,4,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(10,'Air Jordan 1 Mid','The shoe that started it all, in a versatile mid-top cut.',195000.00,5,2,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(11,'Air Jordan 4 Retro','Mesh panels, visible Air and the famous wing eyelets.',260000.00,5,2,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(12,'Jordan Luka 3','Responsive cushioning and lockdown support for quick cuts.',205000.00,5,2,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(13,'Samba OG','Terrace-ready leather upper with a gum rubber sole.',150000.00,2,3,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(14,'Gazelle Indoor','Soft suede and a translucent outsole straight from the archive.',145000.00,2,3,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(15,'Adizero Adios Pro 3','Carbon-infused race-day shoe built for personal bests.',280000.00,2,1,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(16,'Forum Low','80s basketball heritage with the signature ankle strap.',140000.00,2,2,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(17,'Palermo','Retro terrace sneaker with a suede upper and T-toe overlay.',115000.00,3,3,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(18,'Velocity Nitro 3','Nitrogen-infused foam for a light, springy everyday run.',160000.00,3,1,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(19,'MB.03','LaMelo Ball\'s signature shoe with bold, out-of-this-world styling.',195000.00,3,2,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(20,'550','Clean late-80s basketball design reborn for the street.',165000.00,4,3,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(21,'9060','Chunky, sculpted sole with ABZORB and SBS cushioning.',210000.00,4,3,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(22,'Fresh Foam X 1080v13','Ultra-soft Fresh Foam X for daily training comfort.',225000.00,4,1,'img/no-image.png',5,'active','2026-11-03 00:07:45','2026-11-03 00:07:45'),(23,'Chuck Taylor All Star Hi','The timeless canvas high-top worn by every generation.',85000.00,6,3,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(24,'Chuck 70 Low','Premium canvas and extra cushioning on a vintage profile.',105000.00,6,3,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(25,'One Star Pro','Suede skate shoe with a durable, grippy vulcanised sole.',110000.00,6,4,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(26,'Old Skool','The original side-stripe skate shoe with a waffle outsole.',90000.00,7,4,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(27,'Sk8-Hi','Padded high-top collar for support on and off the board.',100000.00,7,4,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(28,'Authentic','Simple, low-profile canvas lace-up from Vans\' early days.',80000.00,7,3,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(29,'Gel-Kayano 31','Stability running shoe with FF Blast+ cushioning.',215000.00,8,1,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(30,'Gel-1130','Y2K-inspired runner with breathable mesh and GEL comfort.',135000.00,8,3,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(31,'Novablast 4','Bouncy trampoline-inspired midsole for energetic runs.',175000.00,8,1,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(32,'Mexico 66','Slim, iconic profile with the famous Tiger stripes.',160000.00,9,3,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(33,'Club C 85','Minimal leather tennis sneaker with a soft terry lining.',110000.00,10,3,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(34,'Classic Leather','Soft garment leather upper and EVA midsole since 1983.',105000.00,10,3,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(35,'Clifton 9','Lightweight, max-cushioned trainer for every distance.',200000.00,11,1,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(36,'Bondi 8','Hoka\'s most cushioned road shoe for ultimate comfort.',225000.00,11,1,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(37,'Curry 11','Stephen Curry\'s signature shoe with UA Flow grip.',210000.00,12,2,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(38,'D\'Lites','Chunky, comfortable sneaker with a memory foam insole.',95000.00,13,3,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46'),(39,'Disruptor II','Bold, serrated chunky sole that defined 90s street style.',98000.00,14,3,'img/no-image.png',5,'active','2026-11-03 00:07:46','2026-11-03 00:07:46');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promotions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `discount_percent` tinyint(3) unsigned NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `hover_image_path` varchar(255) DEFAULT NULL,
  `ends_on` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promotions_product_id_foreign` (`product_id`),
  CONSTRAINT `promotions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotions`
--

LOCK TABLES `promotions` WRITE;
/*!40000 ALTER TABLE `promotions` DISABLE KEYS */;
/*!40000 ALTER TABLE `promotions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_product_id_foreign` (`product_id`),
  KEY `reviews_order_id_foreign` (`order_id`),
  KEY `reviews_customer_id_foreign` (`customer_id`),
  CONSTRAINT `reviews_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,1,1,1,5,'Very comfortable for daily runs, true to size!','2026-11-03 00:07:44','2026-11-03 00:07:44');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_cards`
--

DROP TABLE IF EXISTS `saved_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saved_cards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `card_holder` varchar(100) NOT NULL,
  `card_last4` char(4) NOT NULL,
  `card_type` varchar(20) NOT NULL,
  `expiry_month` tinyint(3) unsigned NOT NULL,
  `expiry_year` smallint(5) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `saved_cards_customer_id_foreign` (`customer_id`),
  CONSTRAINT `saved_cards_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_cards`
--

LOCK TABLES `saved_cards` WRITE;
/*!40000 ALTER TABLE `saved_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sizes`
--

DROP TABLE IF EXISTS `sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sizes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sizes_label_unique` (`label`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sizes`
--

LOCK TABLES `sizes` WRITE;
/*!40000 ALTER TABLE `sizes` DISABLE KEYS */;
INSERT INTO `sizes` VALUES (1,'38','2026-11-03 00:07:44','2026-11-03 00:07:44'),(2,'39','2026-11-03 00:07:44','2026-11-03 00:07:44'),(3,'40','2026-11-03 00:07:44','2026-11-03 00:07:44'),(4,'41','2026-11-03 00:07:44','2026-11-03 00:07:44'),(5,'42','2026-11-03 00:07:44','2026-11-03 00:07:44'),(6,'43','2026-11-03 00:07:44','2026-11-03 00:07:44'),(7,'44','2026-11-03 00:07:44','2026-11-03 00:07:44');
/*!40000 ALTER TABLE `sizes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `managed_by` bigint(20) unsigned DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `hired_on` date DEFAULT NULL,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_email_unique` (`email`),
  KEY `staff_managed_by_foreign` (`managed_by`),
  CONSTRAINT `staff_managed_by_foreign` FOREIGN KEY (`managed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,'Su Su Staff','staff@slayna.com','$2y$12$o/3K.IiPudnCYF9z6nL3nek7BAXOFxI7NoHsE49iz6RbtVjbR8pGy',NULL,1,'Sales Associate','2026-11-03','active',NULL,'2026-11-03 00:07:44','2026-11-03 00:07:44');
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
