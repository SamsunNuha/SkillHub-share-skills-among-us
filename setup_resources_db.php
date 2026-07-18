<?php
/**
 * setup_resources_db.php
 *
 * Run this script once (via browser or CLI) to create the additional
 * tables needed for the resource-sharing features.
 */

require_once __DIR__ . '/includes/db.php';

$queries = [
    // 1. Files table - stores uploaded study materials
    "CREATE TABLE IF NOT EXISTS `files` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `title` VARCHAR(150) NOT NULL,
        `description` TEXT,
        `category_id` INT NOT NULL,
        `skill` VARCHAR(100) DEFAULT NULL,
        `university` VARCHAR(150) DEFAULT NULL,
        `department` VARCHAR(150) DEFAULT NULL,
        `subject` VARCHAR(150) DEFAULT NULL,
        `year` YEAR NULL,
        `language` VARCHAR(50) DEFAULT NULL,
        `file_path` VARCHAR(255) NOT NULL,
        `file_type` VARCHAR(20) NOT NULL,
        `size` INT UNSIGNED NOT NULL,
        `thumbnail` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 2. Tags table
    "CREATE TABLE IF NOT EXISTS `tags` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(50) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 3. File Tags relationship
    "CREATE TABLE IF NOT EXISTS `file_tags` (
        `file_id` INT NOT NULL,
        `tag_id` INT NOT NULL,
        PRIMARY KEY (`file_id`, `tag_id`),
        FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 4. Requests table
    "CREATE TABLE IF NOT EXISTS `requests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `title` VARCHAR(150) NOT NULL,
        `description` TEXT,
        `category_id` INT NOT NULL,
        `status` ENUM('Open','Fulfilled','Closed') DEFAULT 'Open',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 5. Downloads table
    "CREATE TABLE IF NOT EXISTS `downloads` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `file_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `downloaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 6. Favorites table
    "CREATE TABLE IF NOT EXISTS `favorites` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `file_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uniq_fav` (`file_id`,`user_id`),
        FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 7. Ratings table
    "CREATE TABLE IF NOT EXISTS `ratings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `file_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `rating` TINYINT UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
        `review` TEXT,
        `rated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uniq_rating` (`file_id`,`user_id`),
        FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 8. Comments table
    "CREATE TABLE IF NOT EXISTS `comments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `file_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `comment` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

echo "<h2>Creating resource tables...</h2>";
foreach ($queries as $sql) {
    try {
        $pdo->exec($sql);
        echo "<p>✅ Query executed successfully.</p>";
    } catch (PDOException $e) {
        echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
echo "<p>Setup completed!</p>";
?>
