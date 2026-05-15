-- به‌روزرسانی ساختار — اجرای خودکار از migrate.php

ALTER TABLE `categories`
    ADD COLUMN IF NOT EXISTS `show_in_header` TINYINT(1) NOT NULL DEFAULT 0 AFTER `sort_order`,
    ADD COLUMN IF NOT EXISTS `show_on_home` TINYINT(1) NOT NULL DEFAULT 0 AFTER `show_in_header`,
    ADD COLUMN IF NOT EXISTS `home_posts_limit` TINYINT UNSIGNED NOT NULL DEFAULT 4 AFTER `show_on_home`;

ALTER TABLE `posts`
    ADD COLUMN IF NOT EXISTS `show_in_ticker` TINYINT(1) NOT NULL DEFAULT 0 AFTER `view_count`,
    ADD COLUMN IF NOT EXISTS `allow_comments` TINYINT(1) NOT NULL DEFAULT 1 AFTER `show_in_ticker`;

CREATE TABLE IF NOT EXISTS `post_gallery` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `post_id` INT UNSIGNED NOT NULL,
    `image` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(300) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_post` (`post_id`),
    CONSTRAINT `fk_gallery_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comments` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
