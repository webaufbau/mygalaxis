ALTER TABLE `offers`
    ADD `platform` varchar(100) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `country`,
    CHANGE `updated_at` `updated_at` datetime NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

UPDATE offers SET checked_at = null;

ALTER TABLE `offers`
    ADD `sub_type` varchar(50) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `original_type`,
    CHANGE `updated_at` `updated_at` datetime NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

DROP TABLE IF EXISTS `offers_move`;
CREATE TABLE `offers_move` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `offer_id` int(10) unsigned NOT NULL,
   `from_object_type` varchar(100) DEFAULT NULL,
   `from_city` varchar(100) DEFAULT NULL,
   `from_room_count` int(11) DEFAULT NULL,
   `to_object_type` varchar(100) DEFAULT NULL,
   `to_city` varchar(100) DEFAULT NULL,
   `to_room_count` int(11) DEFAULT NULL,
   `service_details` text DEFAULT NULL,
   `move_date` date DEFAULT NULL,
   `customer_type` enum('private','company') DEFAULT 'private',
   `created_at` datetime DEFAULT current_timestamp(),
   `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `offer_id` (`offer_id`),
   CONSTRAINT `offers_move_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_cleaning`;
CREATE TABLE `offers_cleaning` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `offer_id` int(10) unsigned NOT NULL,
   `user_role` varchar(50) DEFAULT NULL,
   `business_type` varchar(100) DEFAULT NULL,
   `object_type` varchar(100) DEFAULT NULL,
   `client_role` varchar(100) DEFAULT NULL,
   `apartment_size` varchar(50) DEFAULT NULL,
   `room_count` int(11) DEFAULT NULL,
   `cleaning_area_sqm` int(11) DEFAULT NULL,
   `cleaning_type` varchar(100) DEFAULT NULL,
   `window_shutter_cleaning` varchar(50) DEFAULT NULL,
   `facade_count` int(11) DEFAULT NULL,
   `address_city` varchar(100) DEFAULT NULL,
   `created_at` datetime DEFAULT current_timestamp(),
   `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `offer_id` (`offer_id`),
   CONSTRAINT `offers_cleaning_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_painting`;
CREATE TABLE `offers_painting` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `offer_id` int(10) unsigned NOT NULL,
   `object_type` varchar(100) DEFAULT NULL,
   `business_type` varchar(100) DEFAULT NULL,
   `painting_overview` varchar(255) DEFAULT NULL,
   `service_details` text DEFAULT NULL,
   `address_city` varchar(100) DEFAULT NULL,
   `created_at` datetime DEFAULT current_timestamp(),
   `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `offer_id` (`offer_id`),
   CONSTRAINT `offers_painting_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_gardening`;
CREATE TABLE `offers_gardening` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `offer_id` int(10) unsigned NOT NULL,
    `user_role` varchar(50) DEFAULT NULL,
    `service_details` text DEFAULT NULL,
    `address_city` varchar(100) DEFAULT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `offer_id` (`offer_id`),
    CONSTRAINT `offers_gardening_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_electrician`;
CREATE TABLE `offers_electrician` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` int(10) unsigned NOT NULL,
  `object_type` varchar(100) DEFAULT NULL,
  `service_details` text DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `offer_id` (`offer_id`),
  CONSTRAINT `offers_electrician_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_plumbing`;
CREATE TABLE `offers_plumbing` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `offer_id` int(10) unsigned NOT NULL,
   `object_type` varchar(100) DEFAULT NULL,
   `service_details` text DEFAULT NULL,
   `address_city` varchar(100) DEFAULT NULL,
   `created_at` datetime DEFAULT current_timestamp(),
   `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `offer_id` (`offer_id`),
   CONSTRAINT `offers_plumbing_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_heating`;
CREATE TABLE `offers_heating` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` int(10) unsigned NOT NULL,
  `object_type` varchar(100) DEFAULT NULL,
  `service_details` text DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `offer_id` (`offer_id`),
  CONSTRAINT `offers_heating_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_tiling`;
CREATE TABLE `offers_tiling` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `offer_id` int(10) unsigned NOT NULL,
 `object_type` varchar(100) DEFAULT NULL,
 `service_details` text DEFAULT NULL,
 `address_city` varchar(100) DEFAULT NULL,
 `created_at` datetime DEFAULT current_timestamp(),
 `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
 PRIMARY KEY (`id`),
 KEY `offer_id` (`offer_id`),
 CONSTRAINT `offers_tiling_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_flooring`;
CREATE TABLE `offers_flooring` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `offer_id` int(10) unsigned NOT NULL,
   `object_type` varchar(100) DEFAULT NULL,
   `service_details` text DEFAULT NULL,
   `address_city` varchar(100) DEFAULT NULL,
   `created_at` datetime DEFAULT current_timestamp(),
   `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `offer_id` (`offer_id`),
   CONSTRAINT `offers_flooring_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_move_cleaning`;
CREATE TABLE `offers_move_cleaning` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `offer_id` int(10) unsigned NOT NULL,
-- Move Fields
    `from_object_type` varchar(100) DEFAULT NULL,
    `from_city` varchar(100) DEFAULT NULL,
    `from_room_count` int(11) DEFAULT NULL,
    `to_object_type` varchar(100) DEFAULT NULL,
    `to_city` varchar(100) DEFAULT NULL,
    `to_room_count` int(11) DEFAULT NULL,
    `service_details_move` longtext DEFAULT NULL,
    `move_date` date DEFAULT NULL,
    `customer_type` enum('private','company') DEFAULT 'private',
-- Cleaning Fields
    `user_role` varchar(50) DEFAULT NULL,
    `business_type` varchar(50) DEFAULT NULL,
    `object_type` varchar(50) DEFAULT NULL,
    `client_role` varchar(50) DEFAULT NULL,
    `apartment_size_cleaning` varchar(20) DEFAULT NULL,
    `room_count_cleaning` int(11) DEFAULT NULL,
    `cleaning_area_sqm` decimal(8,2) DEFAULT NULL,
    `cleaning_type` varchar(50) DEFAULT NULL,
    `window_shutter_cleaning` varchar(50) DEFAULT NULL,
    `facade_count` int(11) DEFAULT NULL,
    `address_city` varchar(100) DEFAULT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `offer_id` (`offer_id`),
    CONSTRAINT `offers_move_cleaning_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `offers`
    ADD `discounted_price` decimal(10,2) NULL AFTER `price`,
    CHANGE `updated_at` `updated_at` datetime NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
