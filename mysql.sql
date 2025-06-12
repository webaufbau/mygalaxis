CREATE TABLE `requests` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `form_name` VARCHAR(255) DEFAULT NULL,
    `form_fields` JSON NOT NULL,
    `headers` JSON DEFAULT NULL,
    `referer` VARCHAR(1024) DEFAULT NULL,
    `verified` TINYINT(1) DEFAULT 0,
    `verify_type` VARCHAR(20) DEFAULT NULL,
    `uuid` VARCHAR(64) DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
