/*
SQLyog Ultimate v13.1.1 (64 bit)
MySQL - 8.0.30 : Database - gogo_trans
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`gogo_trans` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `gogo_trans`;

/*Table structure for table `tb_booking` */

DROP TABLE IF EXISTS `tb_booking`;

CREATE TABLE `tb_booking` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `schedule_id` int NOT NULL,
  `payment_id` int DEFAULT NULL,
  `booking_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','cancelled','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `booking_code` (`booking_code`),
  KEY `idx_user` (`user_id`),
  KEY `idx_schedule` (`schedule_id`),
  KEY `fk_booking_payment` (`payment_id`),
  CONSTRAINT `fk_booking_payment` FOREIGN KEY (`payment_id`) REFERENCES `tb_payment_method` (`payment_id`),
  CONSTRAINT `fk_booking_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `tb_schedule` (`schedule_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `tb_user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tb_booking` */

insert  into `tb_booking`(`booking_id`,`user_id`,`schedule_id`,`payment_id`,`booking_code`,`total_amount`,`status`,`created_at`) values 
(4,3,9,NULL,'GGT-20260107-695',500000.00,'paid','2026-01-07 21:55:35');

/*Table structure for table `tb_booking_details` */

DROP TABLE IF EXISTS `tb_booking_details`;

CREATE TABLE `tb_booking_details` (
  `detail_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `seat_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`detail_id`),
  UNIQUE KEY `unique_booking_seat` (`seat_id`,`booking_id`),
  KEY `idx_booking` (`booking_id`),
  KEY `idx_seat` (`seat_id`),
  CONSTRAINT `fk_detail_booking` FOREIGN KEY (`booking_id`) REFERENCES `tb_booking` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detail_seat` FOREIGN KEY (`seat_id`) REFERENCES `tb_seat` (`seat_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tb_booking_details` */

insert  into `tb_booking_details`(`detail_id`,`booking_id`,`seat_id`,`price`) values 
(6,4,9,250000.00),
(7,4,10,250000.00);

/*Table structure for table `tb_buss` */

DROP TABLE IF EXISTS `tb_buss`;

CREATE TABLE `tb_buss` (
  `bus_id` int NOT NULL AUTO_INCREMENT,
  `bus_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bus_type` enum('big_bus','mini_bus','hiace','innova','avanza') COLLATE utf8mb4_unicode_ci DEFAULT 'big_bus',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `seat_capacity` int NOT NULL,
  `facilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_banner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','maintenance','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`bus_id`),
  UNIQUE KEY `bus_number` (`bus_number`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tb_buss` */

insert  into `tb_buss`(`bus_id`,`bus_number`,`model`,`bus_type`,`description`,`seat_capacity`,`facilities`,`image_url`,`image_banner`,`status`) values 
(1,'DK 1222 BCA','Medium Island','mini_bus','Medium Island merupakan minibus dengan ukuran medium yang berkapasitaskan 8 orang',8,'AC, WiFi, Selimut, Dispenser, Toilet, TV','uploads/6957450fbdf1a_1767326991.jpeg','uploads/6957450fbe5cc_1767326991.jpeg','active'),
(3,'DK 2233 FFD','Jumbo Dumbo','mini_bus','Jumbo Dumbo adalah sebuah minibus yang berkapasitas 15 kursi yang bertipe sleeper',15,'AC, WiFi, Selimut, Dispenser, Toilet, TV, Sleeper','uploads/695744fbd9c99_1767326971.jpeg','uploads/695744fbda2e5_1767326971.jpeg','active'),
(4,'DK 3400 ABD','Reguler Paradise','mini_bus','Reguler Paradise merupakan sebuah minibus yang berkapasitas 6 kursi',6,'AC, WiFi, Selimut, Dispenser, TV','uploads/695744ec84d03_1767326956.jpeg','uploads/695744ec852f4_1767326956.jpeg','active'),
(5,'DK 2222 GAB','Big Bus','big_bus','Big Bus merupakan sebuah Bus dengan berkapasitas 30 Kursi',30,'AC, WiFi, Selimut, Dispenser, Toilet, TV','uploads/6957448381414_1767326851.jpeg','uploads/6957448cb81f0_1767326860.jpeg','active'),
(6,'DK 5043 GAB','Family Hiace','hiace','Family Hiace merupakan Sebuah Mobil untuk family yang berkapasitas 6 orang',6,'AC, WiFi, TV, Bantal, Karaoke','uploads/69576c2a4c5ca_1767337002.jpeg','uploads/69576c2a4ccd2_1767337002.jpeg','active'),
(7,'DK 9923 FAG','Big Top Bus','big_bus','Big Top Bus merupakan sebuah Bus yang memiliki 2 tingkat yang berkapasitas 40 orang',40,'AC, WiFi, Selimut, Dispenser, Toilet, TV, Bantal','uploads/69576c985bee3_1767337112.jpeg','uploads/69576c985c1dc_1767337112.jpeg','active');

/*Table structure for table `tb_passengers` */

DROP TABLE IF EXISTS `tb_passengers`;

CREATE TABLE `tb_passengers` (
  `passenger_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `identity_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`passenger_id`),
  KEY `fk_passenger_booking` (`booking_id`),
  CONSTRAINT `fk_passenger_booking` FOREIGN KEY (`booking_id`) REFERENCES `tb_booking` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tb_passengers` */

insert  into `tb_passengers`(`passenger_id`,`booking_id`,`name`,`identity_number`,`phone`) values 
(6,4,'Ahmad Budiman','0191828182','08122322822'),
(7,4,'Syarifudin','0109299102','08129928182');

/*Table structure for table `tb_payment_method` */

DROP TABLE IF EXISTS `tb_payment_method`;

CREATE TABLE `tb_payment_method` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int DEFAULT NULL,
  `method` enum('transfer','ewallet','cash','qris','credit_card') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_proof` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('pending','success','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tb_payment_method` */

insert  into `tb_payment_method`(`payment_id`,`booking_id`,`method`,`amount`,`payment_proof`,`payment_status`,`paid_at`) values 
(1,2,'cash',1050000.00,NULL,'failed',NULL),
(2,3,'transfer',700000.00,NULL,'success','2025-12-23 06:44:20'),
(3,4,'transfer',500000.00,'uploads/695e73ffe2610_1767797759.png','success','2026-01-07 14:57:30');

/*Table structure for table `tb_refunds` */

DROP TABLE IF EXISTS `tb_refunds`;

CREATE TABLE `tb_refunds` (
  `refund_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `payment_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `refund_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`refund_id`),
  UNIQUE KEY `unique_refund_payment` (`payment_id`),
  KEY `fk_refund_booking` (`booking_id`),
  CONSTRAINT `fk_refund_booking` FOREIGN KEY (`booking_id`) REFERENCES `tb_booking` (`booking_id`),
  CONSTRAINT `fk_refund_payment` FOREIGN KEY (`payment_id`) REFERENCES `tb_payment_method` (`payment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tb_refunds` */

/*Table structure for table `tb_role` */

DROP TABLE IF EXISTS `tb_role`;

CREATE TABLE `tb_role` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tb_role` */

insert  into `tb_role`(`role_id`,`role_name`) values 
(1,'admin'),
(2,'customer');

/*Table structure for table `tb_route` */

DROP TABLE IF EXISTS `tb_route`;

CREATE TABLE `tb_route` (
  `route_id` int NOT NULL AUTO_INCREMENT,
  `departure_city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `arrival_city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `distance_km` int DEFAULT NULL,
  `estimated_duration` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`route_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tb_route` */

insert  into `tb_route`(`route_id`,`departure_city`,`arrival_city`,`distance_km`,`estimated_duration`,`is_active`) values 
(1,'Denpasar','Surabaya',136,'12 Jam',1),
(2,'Denpasar','Yogyakarta',250,'1 Hari',1),
(3,'Denpasar','Malang',130,'10 Jam',1),
(5,'Denpasar','Bandung',350,'2 Hari',0);

/*Table structure for table `tb_schedule` */

DROP TABLE IF EXISTS `tb_schedule`;

CREATE TABLE `tb_schedule` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `route_id` int NOT NULL,
  `bus_id` int NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('available','cancelled','finished') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  PRIMARY KEY (`schedule_id`),
  KEY `idx_route` (`route_id`),
  KEY `idx_bus` (`bus_id`),
  CONSTRAINT `fk_schedule_bus` FOREIGN KEY (`bus_id`) REFERENCES `tb_buss` (`bus_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_schedule_route` FOREIGN KEY (`route_id`) REFERENCES `tb_route` (`route_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tb_schedule` */

insert  into `tb_schedule`(`schedule_id`,`route_id`,`bus_id`,`departure_time`,`arrival_time`,`price`,`status`) values 
(7,1,1,'2026-01-09 12:00:00','2026-01-10 12:00:00',300000.00,'available'),
(8,2,3,'2026-01-09 07:30:00','2026-01-10 00:00:00',550000.00,'available'),
(9,3,4,'2026-01-09 12:30:00','2026-01-10 00:10:00',250000.00,'available'),
(10,2,5,'2026-01-10 12:00:00','2026-01-11 05:00:00',650000.00,'available'),
(11,2,7,'2026-01-10 14:00:00','2026-01-11 07:00:00',700000.00,'available');

/*Table structure for table `tb_seat` */

DROP TABLE IF EXISTS `tb_seat`;

CREATE TABLE `tb_seat` (
  `seat_id` int NOT NULL AUTO_INCREMENT,
  `bus_id` int NOT NULL,
  `seat_number` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`seat_id`),
  UNIQUE KEY `unique_seat_per_bus` (`bus_id`,`seat_number`),
  KEY `idx_bus_seat` (`bus_id`),
  CONSTRAINT `fk_seat_bus` FOREIGN KEY (`bus_id`) REFERENCES `tb_buss` (`bus_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tb_seat` */

insert  into `tb_seat`(`seat_id`,`bus_id`,`seat_number`) values 
(1,1,'1'),
(2,1,'2'),
(3,1,'3'),
(4,1,'4'),
(5,1,'5'),
(6,1,'6'),
(7,1,'7'),
(8,1,'8'),
(15,3,'1'),
(24,3,'10'),
(25,3,'11'),
(26,3,'12'),
(27,3,'13'),
(28,3,'14'),
(29,3,'15'),
(16,3,'2'),
(17,3,'3'),
(18,3,'4'),
(19,3,'5'),
(20,3,'6'),
(21,3,'7'),
(22,3,'8'),
(23,3,'9'),
(9,4,'1'),
(10,4,'2'),
(11,4,'3'),
(12,4,'4'),
(13,4,'5'),
(14,4,'6'),
(30,5,'1'),
(39,5,'10'),
(40,5,'11'),
(41,5,'12'),
(42,5,'13'),
(43,5,'14'),
(44,5,'15'),
(45,5,'16'),
(46,5,'17'),
(47,5,'18'),
(48,5,'19'),
(31,5,'2'),
(49,5,'20'),
(50,5,'21'),
(51,5,'22'),
(52,5,'23'),
(53,5,'24'),
(54,5,'25'),
(55,5,'26'),
(56,5,'27'),
(57,5,'28'),
(58,5,'29'),
(32,5,'3'),
(59,5,'30'),
(33,5,'4'),
(34,5,'5'),
(35,5,'6'),
(36,5,'7'),
(37,5,'8'),
(38,5,'9'),
(60,6,'1'),
(61,6,'2'),
(62,6,'3'),
(63,6,'4'),
(64,6,'5'),
(65,6,'6'),
(66,7,'1'),
(75,7,'10'),
(76,7,'11'),
(77,7,'12'),
(78,7,'13'),
(79,7,'14'),
(80,7,'15'),
(81,7,'16'),
(82,7,'17'),
(83,7,'18'),
(84,7,'19'),
(67,7,'2'),
(85,7,'20'),
(86,7,'21'),
(87,7,'22'),
(88,7,'23'),
(89,7,'24'),
(90,7,'25'),
(91,7,'26'),
(92,7,'27'),
(93,7,'28'),
(94,7,'29'),
(68,7,'3'),
(95,7,'30'),
(96,7,'31'),
(97,7,'32'),
(98,7,'33'),
(99,7,'34'),
(100,7,'35'),
(101,7,'36'),
(102,7,'37'),
(103,7,'38'),
(104,7,'39'),
(69,7,'4'),
(105,7,'40'),
(70,7,'5'),
(71,7,'6'),
(72,7,'7'),
(73,7,'8'),
(74,7,'9');

/*Table structure for table `tb_user` */

DROP TABLE IF EXISTS `tb_user`;

CREATE TABLE `tb_user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_user_role` (`role_id`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `tb_role` (`role_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tb_user` */

insert  into `tb_user`(`user_id`,`role_id`,`username`,`name`,`email`,`phone`,`address`,`pass`,`created_at`,`updated_at`) values 
(1,1,'Juliandika Putra Senjaya','Juliandika Putra Senjaya','juliandika2707@gmail.com','081246028884','Jalan Pancawarna No.21 B, Blok 12, Denpasar Barat, Denpasar','$2y$10$B/Q8AFKKNzqLHLfxBqMPK.Dw1G5knP.KpAFUUB5rh9n0.L7Ae3kOG','2025-12-22 20:40:46','2026-01-02 11:27:23'),
(3,2,'Andika Pontius','Andika Pontius','Pontius2025@gmail.com',NULL,NULL,'$2y$10$CVEm2Wy9v8NLRXVGWgJgMuh0DafZ9jiWjKe7M5KlvO.CPezzPcEzS','2025-12-23 07:13:00','2026-01-07 22:02:02'),
(4,1,'Dena','Gede Dena','Dena2026@gmail.com','08155265516','Jalan Batubulan  Gg.2 No.233','$2y$10$4.x3JwGStUuKYLIi7474oOkSDgq/dCF11o7ofaI3Sh6TtKl9utTh2','2026-01-07 21:43:22','2026-01-07 21:46:00');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
