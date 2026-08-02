<?php

declare(strict_types=1);

function db_version(): int
{
    try {
        return (int) (db()->query("SELECT value FROM settings WHERE `key` = 'db_version'")->fetchColumn() ?: 0);
    } catch (Throwable) {
        return 0;
    }
}

function set_db_version(int $version): void
{
    db()->prepare("INSERT INTO settings (`key`, value) VALUES ('db_version', ?)
        ON DUPLICATE KEY UPDATE value = ?")->execute([(string) $version, (string) $version]);
}

function run_migrations(): void
{
    static $done = false;
    if ($done || !is_installed()) {
        return;
    }
    $done = true;

    if (!kh_db_config_complete() || !kh_test_db_connection()) {
        return;
    }

    $pdo = db();

    $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
        `key` VARCHAR(50) NOT NULL PRIMARY KEY,
        `value` VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $version = db_version();

    if ($version < 2) {
        $alters = [
            "ALTER TABLE categories ADD COLUMN show_in_header TINYINT(1) NOT NULL DEFAULT 0",
            "ALTER TABLE categories ADD COLUMN show_on_home TINYINT(1) NOT NULL DEFAULT 0",
            "ALTER TABLE categories ADD COLUMN home_posts_limit TINYINT UNSIGNED NOT NULL DEFAULT 4",
            "ALTER TABLE posts ADD COLUMN show_in_ticker TINYINT(1) NOT NULL DEFAULT 0",
            "ALTER TABLE posts ADD COLUMN allow_comments TINYINT(1) NOT NULL DEFAULT 1",
        ];
        foreach ($alters as $sql) {
            try {
                $pdo->exec($sql);
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column') === false) {
                    throw $e;
                }
            }
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS `post_gallery` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `post_id` INT UNSIGNED NOT NULL,
            `image` VARCHAR(255) NOT NULL,
            `caption` VARCHAR(300) NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `idx_post` (`post_id`),
            CONSTRAINT `fk_gallery_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `comments` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `post_id` INT UNSIGNED NOT NULL,
            `parent_id` INT UNSIGNED NULL DEFAULT NULL,
            `author_name` VARCHAR(100) NOT NULL,
            `author_email` VARCHAR(120) NULL,
            `body` TEXT NOT NULL,
            `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            `ip_address` VARCHAR(45) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_post_status` (`post_id`, `status`),
            CONSTRAINT `fk_comment_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_comment_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        set_db_version(2);
        $version = 2;
    }

    if ($version < 3) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `charity_members` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(120) NOT NULL,
            `position` VARCHAR(120) NOT NULL DEFAULT '',
            `photo` VARCHAR(255) NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_active_sort` (`is_active`, `sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        set_db_version(3);
        $version = 3;
    }

    if ($version < 4) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `jehadi_calls` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(150) NOT NULL,
            `description` LONGTEXT NOT NULL,
            `banner` VARCHAR(255) NOT NULL,
            `date_from` DATE NOT NULL,
            `date_to` DATE NOT NULL,
            `max_registrations` INT UNSIGNED NOT NULL DEFAULT 50,
            `status` ENUM('active','cancelled') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_status` (`status`),
            KEY `idx_dates` (`date_from`, `date_to`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `jehadi_registrations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `call_id` INT UNSIGNED NOT NULL,
            `full_name` VARCHAR(120) NOT NULL,
            `personal_details` TEXT NULL,
            `birth_date` DATE NOT NULL,
            `national_id` CHAR(10) NOT NULL,
            `mobile` VARCHAR(11) NOT NULL,
            `ip_address` VARCHAR(45) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_call_national` (`call_id`, `national_id`),
            KEY `idx_call` (`call_id`),
            CONSTRAINT `fk_jehadi_reg_call` FOREIGN KEY (`call_id`) REFERENCES `jehadi_calls` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        set_db_version(4);
        $version = 4;
    }

    if ($version < 5) {
        try {
            $pdo->exec('ALTER TABLE settings MODIFY `value` TEXT NOT NULL');
        } catch (PDOException $e) {
            // ignore if already TEXT
        }
        set_db_version(5);
    }
}
