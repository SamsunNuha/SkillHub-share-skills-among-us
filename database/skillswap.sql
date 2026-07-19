-- SkillSwap Database Schema
-- Prepared for XAMPP MySQL / MariaDB

CREATE DATABASE IF NOT EXISTS `skillswap` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `skillswap`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `university` VARCHAR(150) DEFAULT NULL,
  `department` VARCHAR(150) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `skills_teach` TEXT DEFAULT NULL,
  `skills_learn` TEXT DEFAULT NULL,
  `profile_photo` VARCHAR(255) DEFAULT 'default-profile.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'bi-code-slash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `skills`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `category_id` INT NOT NULL,
  `level` ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL,
  `availability` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `contact_requests`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `skill_id` INT DEFAULT NULL,
  `sender_name` VARCHAR(100) NOT NULL,
  `sender_email` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('Pending', 'Accepted', 'Declined') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `admins`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `conversations`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conversations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user1_id` INT NOT NULL,
  `user2_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_conversation` (`user1_id`, `user2_id`),
  FOREIGN KEY (`user1_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user2_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `messages`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT NOT NULL,
  `sender_id` INT NOT NULL,
  `body` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Seeding Default Categories
-- --------------------------------------------------------
INSERT INTO `categories` (`name`, `icon`) VALUES
('Programming & Tech', 'bi-code-slash'),
('Graphic Design', 'bi-palette'),
('Languages & Culture', 'bi-translate'),
('Music & Arts', 'bi-music-note-beamed'),
('Academics & Tutoring', 'bi-book'),
('Business & Marketing', 'bi-graph-up-arrow'),
('Photography & Video', 'bi-camera')
ON DUPLICATE KEY UPDATE `icon`=VALUES(`icon`);

-- --------------------------------------------------------
-- Seeding Default Admin (username: admin, password: admin123)
-- --------------------------------------------------------
INSERT INTO `admins` (`id`, `username`, `password`, `email`) VALUES (1, 'admin', '$2y$10$ft7ArVBBlaSId.nGQaB//OIhaHqlrpxAj67eRcXdt2zV6sG.M2znS', 'admin@gmail.com') ON DUPLICATE KEY UPDATE `password`=VALUES(`password`), `email`=VALUES(`email`);;

-- --------------------------------------------------------
-- Seeding Default Students (password: student123)
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password`, `name`, `university`, `department`, `bio`, `skills_teach`, `skills_learn`, `profile_photo`) VALUES
(1, 'thahani', 'thahani123@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'Alex Jones', 'State University', 'Computer Science', 'Hi! I am a final-year CS student who loves building web applications in PHP and React. Looking to improve my design skills.', 'PHP, JavaScript, MySQL, HTML/CSS', 'UI/UX Design, Figma, Spanish', 'default-profile.png'),
(2, 'zimanjaar', 'zimanjaar45@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'Sarah Smith', 'State University', 'Fine Arts', 'Graphic designer and digital artist. I love sketching and working with Illustrator/Figma. I want to learn Python coding.', 'Illustrator, UI/UX Design, Figma, Sketching', 'Python, Programming Basics', 'default-profile.png'),
(3, 'susana', 'susana23@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'Emily Davis', 'State University', 'Languages & Literature', 'Bonjour! I am a student tutor majoring in French and Spanish. Eager to pick up acoustic guitar and Python coding in my free time.', 'French, Spanish, Conversational English', 'Acoustic Guitar, Python', 'default-profile.png'),
(4, 'nuha', 'nuha124@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'Michael Chen', 'State University', 'Business Administration', 'Marketing enthusiast interested in SEO and advanced Excel. I am currently learning React.js and web design to build my portfolio.', 'Digital Marketing, SEO, Excel Modeling', 'React.js, UI/UX Design, Figma', 'default-profile.png'),
(5, 'sophia_p', 'sophia@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'Sophia Patel', 'State University', 'Music & Arts', 'Violinist and guitarist. Happy to teach music theory or acoustic guitar basics to beginners. I am looking for help with photography.', 'Acoustic Guitar, Violin, Music Theory', 'DSLR Photography, Photoshop', 'default-profile.png'),
(6, 'david_k', 'david@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'David Kim', 'State University', 'Physics', 'Physics and math tutor. If you need help with physics mechanics or college algebra, let me know! I would love to learn video editing.', 'Physics 101, College Algebra, Calculus', 'Video Editing, Premiere Pro', 'default-profile.png')
ON DUPLICATE KEY UPDATE `password`=VALUES(`password`), `name`=VALUES(`name`), `university`=VALUES(`university`), `department`=VALUES(`department`), `bio`=VALUES(`bio`), `skills_teach`=VALUES(`skills_teach`), `skills_learn`=VALUES(`skills_learn`);

-- --------------------------------------------------------
-- Seeding Sample Skills
-- --------------------------------------------------------
INSERT INTO `skills` (`id`, `user_id`, `title`, `description`, `category_id`, `level`, `availability`, `created_at`) VALUES
(1, 1, 'Introduction to Web Dev with PHP', 'I can teach you the basics of building dynamic websites using PHP, MySQL database connections, and standard CRUD operations. Perfect for beginners!', 1, 'Beginner', 'Weekends (Saturday & Sunday afternoon)', CURRENT_TIMESTAMP),
(2, 2, 'UI/UX & Mobile Interface Design', 'Learn how to construct wireframes, design mockups, and build prototypes in Figma. We will focus on typography, grids, and user journey flows.', 2, 'Intermediate', 'Tuesdays and Thursdays, 5:00 PM to 7:00 PM', CURRENT_TIMESTAMP),
(3, 3, 'Conversational Spanish Practice', 'Practice your Spanish speaking skills in a relaxed, friendly setting. We will cover basic conversational phrases, vocabulary, and common expressions.', 3, 'Beginner', 'Monday and Wednesday evenings', CURRENT_TIMESTAMP),
(4, 4, 'Intro to Digital Marketing & SEO', 'Understand search engine optimization, social media content strategies, and running online campaigns to grow a startup or personal brand.', 6, 'Beginner', 'Fridays from 2:00 PM to 5:00 PM', CURRENT_TIMESTAMP),
(5, 5, 'Acoustic Guitar Basics', 'Get comfortable holding, tuning, and strumming an acoustic guitar. Learn fundamental chords and how to play your first few songs in a week.', 4, 'Beginner', 'Saturdays, 10:00 AM to 12:00 PM', CURRENT_TIMESTAMP),
(6, 6, 'College Algebra & Calculus Help', 'Struggling with linear equations, graphing, or basic derivatives? I will break down complex concepts into simple, easy-to-understand steps.', 5, 'Intermediate', 'Monday to Thursday, 6:00 PM onwards', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `description`=VALUES(`description`), `category_id`=VALUES(`category_id`), `level`=VALUES(`level`), `availability`=VALUES(`availability`);

-- --------------------------------------------------------
-- Seeding Sample Contact Requests
-- --------------------------------------------------------
INSERT INTO `contact_requests` (`id`, `skill_id`, `sender_name`, `sender_email`, `message`, `status`, `created_at`) VALUES
(1, 1, 'thahani', 'sarah@university.edu', 'Hey thahani! I would love to learn the basics of PHP. I have some ideas for a design portfolio website and want to connect it to a database.', 'Pending', CURRENT_TIMESTAMP),
(2, 2, 'susana', 'alex@university.edu', 'Hi Susana, I saw your listing for Figma design. I want to improve the UI of my PHP web app. Let me know when you are free to meet!', 'Accepted', CURRENT_TIMESTAMP),
(3, 3, 'ishrath', 'sophia@university.edu', 'Hola ishrath! I really want to learn conversational Spanish to travel next summer. Hope we can arrange a session soon!', 'Pending', CURRENT_TIMESTAMP),
(4, 4, 'zimanjaar', 'alex@university.edu', 'Hi ziman, I am launching a small side project and could really use some SEO tips. Let me know if you are open to swap coding for marketing help!', 'Pending', CURRENT_TIMESTAMP),
(5, 5, 'Emily Davis', 'emily@university.edu', 'Hey emily, I always wanted to play acoustic guitar! I can teach you French or Spanish in return. When is a good time to start?', 'Accepted', CURRENT_TIMESTAMP),
(6, 6, 'Michael Chen', 'michael@university.edu', 'Hello michael, I have an upcoming math mid-term exam and I am struggling with linear equations. Would appreciate a quick tutoring session!', 'Declined', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `sender_name`=VALUES(`sender_name`), `sender_email`=VALUES(`sender_email`), `message`=VALUES(`message`), `status`=VALUES(`status`);
