
SET NAMES utf8mb4;

CREATE TABLE `users` (
                         `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                         `username` varchar(30) NOT NULL,
                         `email` varchar(255) NOT NULL UNIQUE,
                         `password` varchar(255) NOT NULL,
                         `status` varchar(255) DEFAULT NULL,
                         `status_message` varchar(255) DEFAULT NULL,
                         `active` tinyint(1) NOT NULL DEFAULT 0,
                         `last_active` datetime DEFAULT NULL,
                         `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         `deleted_at` datetime DEFAULT NULL,
                         PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE users
    ADD COLUMN company_name VARCHAR(255),
    ADD COLUMN contact_person VARCHAR(255),
    ADD COLUMN company_uid VARCHAR(50),
    ADD COLUMN company_street VARCHAR(255),
    ADD COLUMN company_zip VARCHAR(10),
    ADD COLUMN company_city VARCHAR(100),
    ADD COLUMN company_website VARCHAR(255) NOT NULL,
    ADD COLUMN company_email VARCHAR(255) DEFAULT NULL,
    ADD COLUMN company_phone VARCHAR(255) NOT NULL,
    ADD COLUMN filter_address TEXT DEFAULT NULL,
    ADD COLUMN filter_cantons JSON DEFAULT '[]',
    ADD COLUMN filter_languages JSON DEFAULT '[]',
    ADD COLUMN filter_absences JSON DEFAULT '[]',
    ADD COLUMN account_balance DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN auto_purchase TINYINT(1) DEFAULT 0;

DROP TABLE IF EXISTS `auth_groups_users`;
CREATE TABLE `auth_groups_users` (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
     `user_id` int(11) unsigned NOT NULL,
     `group` varchar(255) NOT NULL,
     `created_at` datetime NOT NULL,
     PRIMARY KEY (`id`),
     KEY `auth_groups_users_user_id_foreign` (`user_id`),
     CONSTRAINT `auth_groups_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `auth_identities`;
CREATE TABLE `auth_identities` (
   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
   `user_id` int(11) unsigned NOT NULL,
   `type` varchar(255) NOT NULL,
   `name` varchar(255) DEFAULT NULL,
   `secret` varchar(255) NOT NULL,
   `secret2` varchar(255) DEFAULT NULL,
   `expires` datetime DEFAULT NULL,
   `extra` text DEFAULT NULL,
   `force_reset` tinyint(1) NOT NULL DEFAULT 0,
   `last_used_at` datetime DEFAULT NULL,
   `created_at` datetime DEFAULT NULL,
   `updated_at` datetime DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `type_secret` (`type`,`secret`),
   KEY `user_id` (`user_id`),
   CONSTRAINT `auth_identities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `auth_logins`;
CREATE TABLE `auth_logins` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`ip_address` varchar(255) NOT NULL,
`user_agent` varchar(255) DEFAULT NULL,
`id_type` varchar(255) NOT NULL,
`identifier` varchar(255) NOT NULL,
`user_id` int(11) unsigned DEFAULT NULL,
`date` datetime NOT NULL,
`success` tinyint(1) NOT NULL,
PRIMARY KEY (`id`),
KEY `id_type_identifier` (`id_type`,`identifier`),
KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `auth_permissions_users`;
CREATE TABLE `auth_permissions_users` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int(11) unsigned NOT NULL,
          `permission` varchar(255) NOT NULL,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `auth_permissions_users_user_id_foreign` (`user_id`),
          CONSTRAINT `auth_permissions_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `auth_remember_tokens`;
CREATE TABLE `auth_remember_tokens` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `selector` varchar(255) NOT NULL,
        `hashedValidator` varchar(255) NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        `expires` datetime NOT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `selector` (`selector`),
        KEY `auth_remember_tokens_user_id_foreign` (`user_id`),
        CONSTRAINT `auth_remember_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `auth_token_logins`;
CREATE TABLE `auth_token_logins` (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
     `ip_address` varchar(255) NOT NULL,
     `user_agent` varchar(255) DEFAULT NULL,
     `id_type` varchar(255) NOT NULL,
     `identifier` varchar(255) NOT NULL,
     `user_id` int(11) unsigned DEFAULT NULL,
     `date` datetime NOT NULL,
     `success` tinyint(1) NOT NULL,
     PRIMARY KEY (`id`),
     KEY `id_type_identifier` (`id_type`,`identifier`),
     KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Balance Top-ups
CREATE TABLE balance_transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('paypal', 'creditcard', 'manual') NOT NULL,
  transaction_id VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE `credits` (
   `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `user_id` INT UNSIGNED NOT NULL,
   `amount` DECIMAL(10,2) NOT NULL, -- positive oder negative Beträge
   `type` ENUM('manual_credit', 'purchase', 'refund', 'auto_charge', 'initial_credit') NOT NULL,
   `description` VARCHAR(255) DEFAULT NULL,
   `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

   FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`version` varchar(255) NOT NULL,
`class` varchar(255) NOT NULL,
`group` varchar(255) NOT NULL,
`namespace` varchar(255) NOT NULL,
`time` int(11) NOT NULL,
`batch` int(11) unsigned NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
   (1,	'2020-12-28-223112',	'CodeIgniter\\Shield\\Database\\Migrations\\CreateAuthTables',	'default',	'CodeIgniter\\Shield',	1750171964,	1),
   (2,	'2021-07-04-041948',	'CodeIgniter\\Settings\\Database\\Migrations\\CreateSettingsTable',	'default',	'CodeIgniter\\Settings',	1750171964,	1),
   (3,	'2021-11-14-143905',	'CodeIgniter\\Settings\\Database\\Migrations\\AddContextColumn',	'default',	'CodeIgniter\\Settings',	1750171964,	1);


DROP TABLE IF EXISTS `offers`;
CREATE TABLE `offers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `form_name` varchar(255) DEFAULT NULL,
  `form_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`form_fields`)),
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`headers`)),
  `referer` varchar(512) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `verify_type` varchar(50) DEFAULT NULL,
  `uuid` varchar(64) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'new',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `buyers` int(10) unsigned NOT NULL DEFAULT 0,
  `bought_by` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bought_by`)),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Purchased Offers
CREATE TABLE offer_purchases (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT UNSIGNED NOT NULL,
     offer_id INT UNSIGNED NOT NULL,
     price DECIMAL(10,2) NOT NULL,
     payment_method ENUM('balance', 'paypal', 'creditcard') NOT NULL,
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
     FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE
);

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
`id` int(9) NOT NULL AUTO_INCREMENT,
`class` varchar(255) NOT NULL,
`key` varchar(255) NOT NULL,
`value` text DEFAULT NULL,
`type` varchar(31) NOT NULL DEFAULT 'string',
`context` varchar(255) DEFAULT NULL,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- UPDATE zipcodes SET canton = REPLACE(REPLACE(REPLACE(state, 'Canton du ', ''), 'Canton de ', ''), 'Kanton ', '');

CREATE TABLE bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('purchase', 'topup', 'credit', 'subscription') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  description TEXT,
  reference_id INT DEFAULT NULL, -- z.B. Offer ID bei Kauf
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO bookings (user_id, type, amount, description, reference_id, created_at) VALUES
    (11, 'topup', 100.00, 'Guthaben via TWINT', NULL, '2025-06-10 14:12:00'),
    (11, 'purchase', -29.00, 'Kauf Anfrage: Umzug Zürich', 123, '2025-06-11 08:00:00'),
    (11, 'credit', 29.00, 'Gutschrift wegen Storno Anfrage: Umzug Zürich', 123, '2025-06-12 10:30:00'),
    (11, 'subscription', -9.90, 'Monatliches Abo', NULL, '2025-06-15 00:00:00'),
    (11, 'purchase', -49.00, 'Kauf Anfrage: Reinigung Bern', 124, '2025-06-17 17:45:00'),
    (11, 'purchase', -39.00, 'Kauf Anfrage: Malerarbeiten Basel', 125, '2025-06-18 13:20:00'),
    (11, 'topup', 200.00, 'Guthaben via Kreditkarte', NULL, '2025-06-18 14:00:00'),
    (11, 'subscription', -9.90, 'Monatliches Abo', NULL, '2025-07-15 00:00:00'),
    (11, 'credit', 15.00, 'Gutschrift für Ausfall Anfrage: Malerarbeiten Basel', 125, '2025-07-16 09:00:00');

INSERT INTO bookings (user_id, type, amount, description, reference_id, created_at) VALUES
    (11, 'topup', 50.00, 'Manuelle Gutschrift durch Admin', NULL, '2025-05-01 09:30:00'),
    (11, 'purchase', -25.00, 'Kauf Anfrage: Gärtner Luzern', 110, '2025-05-02 11:45:00'),
    (11, 'subscription', -9.90, 'Monatliches Abo', NULL, '2025-05-15 00:00:00'),
    (11, 'purchase', -45.00, 'Kauf Anfrage: Reinigung St. Gallen', 111, '2025-05-16 13:15:00'),
    (11, 'credit', 25.00, 'Teilrückerstattung wegen Reklamation', 110, '2025-05-17 10:10:00'),
    (11, 'topup', 80.00, 'Guthaben per Überweisung', NULL, '2025-04-20 14:00:00'),
    (11, 'purchase', -60.00, 'Kauf Anfrage: Umzug Lausanne', 105, '2025-04-21 08:45:00'),
    (11, 'subscription', -9.90, 'Monatliches Abo', NULL, '2025-04-15 00:00:00'),
    (11, 'purchase', -35.00, 'Kauf Anfrage: Maler Genf', 103, '2025-04-10 16:20:00'),
    (11, 'credit', 15.00, 'Teilgutschrift für nicht gelieferten Service', 103, '2025-04-11 09:00:00');

ALTER TABLE offer_purchases
    ADD COLUMN rating TINYINT NULL AFTER payment_method,
    ADD COLUMN review TEXT NULL AFTER rating,
    ADD CONSTRAINT fk_offer_purchases_user FOREIGN KEY (user_id) REFERENCES users(id);

INSERT INTO offer_purchases (user_id, offer_id, price, payment_method, rating, review, created_at) VALUES
   (11, 1, 199.99, 'paypal', 5, 'Sehr gutes Angebot, schnelle Abwicklung.', '2025-06-01 10:15:00'),
   (11, 2, 149.50, 'balance', NULL, NULL, '2025-06-10 14:30:00'),
   (11, 3, 299.00, 'creditcard', 4, 'Preis war etwas hoch, aber insgesamt zufrieden.', '2025-05-20 09:00:00'),
   (11, 4, 89.99, 'paypal', 3, 'Qualität hätte besser sein können.', '2025-06-15 11:45:00'),
   (11, 5, 120.00, 'balance', NULL, NULL, '2025-06-16 16:20:00');

   /*(11, 6, 210.00, 'creditcard', 5, 'Top Service, klare Empfehlung!', '2025-05-28 13:05:00'),
   (11, 7, 75.50, 'paypal', NULL, NULL, '2025-06-18 08:00:00'),
   (11, 8, 180.00, 'balance', 4, 'Gut, aber Versand dauerte lange.', '2025-06-02 12:30:00'),
   (11, 9, 220.00, 'creditcard', NULL, NULL, '2025-06-14 15:00:00'),
   (11, 10, 160.00, 'paypal', 5, 'Alles bestens, danke!', '2025-06-10 17:10:00');*/

ALTER TABLE `offer_purchases`
    ADD `status` varchar(12) COLLATE 'utf8mb4_general_ci' NULL AFTER `review`;

ALTER TABLE `offer_purchases`
    ADD `price_paid` decimal(10,2) NOT NULL AFTER `price`;
ALTER TABLE `offer_purchases`
    MODIFY `status` ENUM('pending', 'paid', 'cancelled', 'refunded')
        CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
        DEFAULT 'pending' NULL;

CREATE TABLE `blocked_days` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `date` DATE NOT NULL,
    UNIQUE KEY `user_date_unique` (`user_id`, `date`)
);

CREATE TABLE `payment_methods` (
   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   `code` VARCHAR(50) NOT NULL UNIQUE,        -- z.B. 'creditcard', 'paypal', 'twint'
   `name` VARCHAR(100) NOT NULL,               -- z.B. "Kreditkarte", "PayPal"
   `active` TINYINT(1) NOT NULL DEFAULT 1,
   `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
   `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO payment_methods (code, name) VALUES
 ('creditcard', 'Kreditkarte'),
 ('paypal', 'PayPal'),
 ('twint', 'TWINT');

CREATE TABLE `user_payment_methods` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `payment_method_code` VARCHAR(50) NOT NULL, -- z.B. 'paypal', 'creditcard', 'twint'
    `provider_data` TEXT NULL, -- JSON-Daten, z.B. PayPal E-Mail, Token etc.
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_payment_methods` (`user_id`, `payment_method_code`, `provider_data`) VALUES
   (11, 'paypal', '{"email":"user1@example.com"}'),
   (11, 'creditcard', '{"last4":"4242","brand":"Visa","exp_month":12,"exp_year":2026,"token":"tok_1AbCdEfGhIjKlMnOpQr"}'),
   (11, 'twint', '{"phone":"+41791234567"}');

CREATE TABLE `reviews` (
   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   `recipient_id` INT NOT NULL,
   `created_by` INT NOT NULL,
   `rating` TINYINT NOT NULL,
   `comment` TEXT,
   `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO reviews (recipient_id, created_by, rating, comment, created_at)
VALUES
    (11, 1, 5, 'Sehr professionell und zuverlässig!', '2024-01-10'),
    (11, 2, 4, 'Schnell geantwortet, gerne wieder.', '2024-01-15'),
    (11, 3, 5, 'Top Arbeit. Jederzeit wieder.', '2024-02-01'),
    (11, 4, 3, 'War ok, aber hätte besser sein können.', '2024-02-10'),
    (11, 5, 4, 'Solide Leistung und pünktlich.', '2024-03-01'),
    (11, 6, 2, 'Leider verspätet angekommen.', '2024-03-15'),
    (11, 7, 5, 'Perfekte Abwicklung!', '2024-04-01'),
    (11, 8, 5, 'Alles bestens. Klare Weiterempfehlung.', '2024-04-10'),
    (11, 9, 4, 'Gute Kommunikation und Ergebnis.', '2024-04-20'),
    (11, 10, 5, 'Exzellent und freundlich.', '2024-05-01'),
    (11, 11, 4, 'Schneller Service. Danke!', '2024-05-10'),
    (11, 12, 3, 'In Ordnung, aber Luft nach oben.', '2024-05-15'),
    (11, 13, 5, 'Super sauber gearbeitet!', '2024-05-20'),
    (11, 14, 5, 'Zuverlässig wie immer.', '2024-06-01'),
    (11, 15, 4, 'Kleine Verzögerung, aber gute Arbeit.', '2024-06-05'),
    (11, 16, 5, 'Alles wie abgesprochen.', '2024-06-10'),
    (11, 17, 5, 'Sehr freundlich und effizient.', '2024-06-15'),
    (11, 18, 3, 'Neutral, kann besser sein.', '2024-06-16'),
    (11, 19, 4, 'Termingerecht geliefert.', '2024-06-17'),
    (11, 20, 5, 'Ausgezeichnete Dienstleistung!', '2024-06-18');


ALTER TABLE `offer_purchases`
    ADD `updated_at` datetime NULL;


DROP TABLE IF EXISTS `offers`;
CREATE TABLE `offers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'new',
  `price` decimal(10,2) DEFAULT 0.00,
  `buyers` int(10) unsigned DEFAULT 0,
  `bought_by` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bought_by`)),
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `additional_service` varchar(255) DEFAULT NULL,
  `service_url` varchar(512) DEFAULT NULL,
  `uuid` varchar(64) NOT NULL,
  `customer_type` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `form_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`form_fields`)),
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`headers`)),
  `referer` varchar(512) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `verify_type` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_cleaning`;
CREATE TABLE `offers_cleaning` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `offer_id` int(10) unsigned NOT NULL,
   `cleaning_type` varchar(100) DEFAULT NULL,
   `property_size` varchar(50) DEFAULT NULL,
   `extras` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extras`)),
   `created_at` datetime DEFAULT current_timestamp(),
   `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `offer_id` (`offer_id`),
   CONSTRAINT `offers_cleaning_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_gardening`;
CREATE TABLE `offers_gardening` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `offer_id` int(10) unsigned NOT NULL,
    `work_type` varchar(100) DEFAULT NULL,
    `area_m2` int(11) DEFAULT NULL,
    `duration_estimation` varchar(50) DEFAULT NULL,
    `special_requests` text DEFAULT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `offer_id` (`offer_id`),
    CONSTRAINT `offers_gardening_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `offers_move`;
CREATE TABLE `offers_move` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`offer_id` int(10) unsigned NOT NULL,
`apartment_size` varchar(20) DEFAULT NULL,
`move_date` date DEFAULT NULL,
`distance` varchar(50) DEFAULT NULL,
`additional_services` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_services`)),
`created_at` datetime DEFAULT current_timestamp(),
`updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
PRIMARY KEY (`id`),
KEY `offer_id` (`offer_id`),
CONSTRAINT `offers_move_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `offers_painting`;
CREATE TABLE `offers_painting` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `offer_id` int(10) unsigned NOT NULL,
   `work_type` enum('interior','exterior') DEFAULT 'interior',
   `area_m2` int(11) DEFAULT NULL,
   `duration_estimation` varchar(50) DEFAULT NULL,
   `special_requests` text DEFAULT NULL,
   `created_at` datetime DEFAULT current_timestamp(),
   `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `offer_id` (`offer_id`),
   CONSTRAINT `offers_painting_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `offers_plumbing`;
CREATE TABLE `offers_plumbing` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `offer_id` int(10) unsigned NOT NULL,
   `work_scope` varchar(100) DEFAULT NULL,
   `urgency_level` enum('low','medium','high') DEFAULT 'medium',
   `affected_rooms` varchar(100) DEFAULT NULL,
   `special_requests` text DEFAULT NULL,
   `created_at` datetime DEFAULT current_timestamp(),
   `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `offer_id` (`offer_id`),
   CONSTRAINT `offers_plumbing_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `offer_purchases`;
CREATE TABLE `offer_purchases` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `user_id` int(10) unsigned NOT NULL,
   `offer_id` int(10) unsigned NOT NULL,
   `price` decimal(10,2) NOT NULL,
   `price_paid` decimal(10,2) NOT NULL,
   `payment_method` enum('balance','paypal','creditcard') NOT NULL,
   `rating` tinyint(4) DEFAULT NULL,
   `review` text DEFAULT NULL,
   `status` enum('pending','paid','cancelled','refunded') DEFAULT 'pending',
   `created_at` datetime DEFAULT current_timestamp(),
   `updated_at` datetime DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `offer_id` (`offer_id`),
   KEY `fk_offer_purchases_user` (`user_id`),
   CONSTRAINT `fk_offer_purchases_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
   CONSTRAINT `offer_purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `offers`
    ADD `checked_at` datetime NULL AFTER `from_campaign`,
    CHANGE `updated_at` `updated_at` datetime NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

ALTER TABLE offers
    ADD COLUMN reminder_sent_at DATETIME NULL DEFAULT NULL AFTER checked_at,
    ADD COLUMN verification_token VARCHAR(64) NULL DEFAULT NULL AFTER reminder_sent_at;

ALTER TABLE users
    ADD COLUMN stripe_customer_id VARCHAR(255) NULL,
    ADD COLUMN payrexx_customer_id VARCHAR(255) NULL AFTER stripe_customer_id;

ALTER TABLE `offers`
    ADD `title` varchar(200) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `type`,
    CHANGE `updated_at` `updated_at` datetime NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

ALTER TABLE `bookings`
    CHANGE `type` `type` enum('purchase','offer_purchase','topup','credit','subscription') COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `user_id`;

ALTER TABLE `users`
    CHANGE `updated_at` `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
    ADD `filter_regions` longtext COLLATE 'utf8mb4_bin' NOT NULL AFTER `filter_cantons`;

ALTER TABLE `users`
    CHANGE `updated_at` `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
    ADD `filter_custom_zip` longtext COLLATE 'utf8mb4_bin' NOT NULL AFTER `filter_absences`;

ALTER TABLE `users`
    CHANGE `updated_at` `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
    CHANGE `filter_address` `filter_categories` longtext COLLATE 'utf8mb4_general_ci' NULL AFTER `company_phone`;

ALTER TABLE `offers`
    CHANGE `updated_at` `updated_at` datetime NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
    ADD `group_id` int(11) NULL;

ALTER TABLE offers ADD COLUMN group_id VARCHAR(36) DEFAULT NULL;

ALTER TABLE `offers`
    ADD `form_fields_combo` longtext COLLATE 'utf8mb4_bin' NULL AFTER `form_fields`,
    CHANGE `updated_at` `updated_at` datetime NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
