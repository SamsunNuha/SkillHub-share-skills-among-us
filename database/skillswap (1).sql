-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2026 at 07:16 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skillswap`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(7, 'admin', '$2y$10$GCjEGEEl6qVHxxW5bVxm5.KYZYbwA4/kJcHzVJeMD7GyGpoPMCmMO', 'admin@gmail.com', '2026-07-18 10:30:22');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT 'bi-code-slash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`) VALUES
(1, 'Programming & Tech', 'bi-code-slash'),
(2, 'Graphic Design', 'bi-palette'),
(3, 'Languages & Culture', 'bi-translate'),
(4, 'Music & Arts', 'bi-music-note-beamed'),
(5, 'Academics & Tutoring', 'bi-book'),
(6, 'Business & Marketing', 'bi-graph-up-arrow'),
(7, 'Photography & Video', 'bi-camera');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_requests`
--

CREATE TABLE `contact_requests` (
  `id` int(11) NOT NULL,
  `skill_id` int(11) DEFAULT NULL,
  `sender_name` varchar(100) NOT NULL,
  `sender_email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Pending','Accepted','Declined') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_requests`
--

INSERT INTO `contact_requests` (`id`, `skill_id`, `sender_name`, `sender_email`, `message`, `status`, `created_at`) VALUES
(1, 1, 'Sarah Smith', 'sarah@university.edu', 'Hey Alex! I would love to learn the basics of PHP. I have some ideas for a design portfolio website and want to connect it to a database.', 'Pending', '2026-07-18 09:57:54'),
(2, 2, 'Alex Jones', 'alex@university.edu', 'Hi Sarah, I saw your listing for Figma design. I want to improve the UI of my PHP web app. Let me know when you are free to meet!', 'Accepted', '2026-07-18 09:57:54'),
(3, 3, 'Sophia Patel', 'sophia@university.edu', 'Hola Emily! I really want to learn conversational Spanish to travel next summer. Hope we can arrange a session soon!', 'Pending', '2026-07-18 09:57:54'),
(4, 4, 'Alex Jones', 'alex@university.edu', 'Hi Michael, I am launching a small side project and could really use some SEO tips. Let me know if you are open to swap coding for marketing help!', 'Pending', '2026-07-18 09:57:54'),
(5, 5, 'Emily Davis', 'emily@university.edu', 'Hey Sophia, I always wanted to play acoustic guitar! I can teach you French or Spanish in return. When is a good time to start?', 'Accepted', '2026-07-18 09:57:54'),
(6, 6, 'Michael Chen', 'michael@university.edu', 'Hello David, I have an upcoming math mid-term exam and I am struggling with linear equations. Would appreciate a quick tutoring session!', 'Declined', '2026-07-18 09:57:54'),
(7, 9, 'Nuha', 'nuha@gmail.com', 'explain how to easy learn', 'Pending', '2026-07-18 11:03:37'),
(8, 22, 'raja', 'raja@gmail.com', 'hii can u explain what python', 'Accepted', '2026-07-18 11:05:36'),
(9, 22, 'zumla', 'zumla@gmail.com', 'could i connect?', 'Accepted', '2026-07-18 15:50:37'),
(10, 22, 'hdfjsgfd', 'fgdfgdf@gmail.com', 'hlw dear', 'Accepted', '2026-07-19 14:44:20'),
(11, 22, 'raja1', 'raja1@gmail.com', 'hi love', 'Pending', '2026-07-19 15:21:33');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `downloads`
--

CREATE TABLE `downloads` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `downloaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `skill` varchar(100) DEFAULT NULL,
  `university` varchar(150) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `year` year(4) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(20) NOT NULL,
  `size` int(10) UNSIGNED NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_tags`
--

CREATE TABLE `file_tags` (
  `file_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL CHECK (`rating` between 1 and 5),
  `review` text DEFAULT NULL,
  `rated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `status` enum('Open','Fulfilled','Closed') DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_replies`
--

CREATE TABLE `request_replies` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `sender_user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_replies`
--

INSERT INTO `request_replies` (`id`, `request_id`, `sender_user_id`, `message`, `created_at`) VALUES
(1, 9, 8, 'hi dear how are you', '2026-07-18 16:00:22'),
(2, 9, 8, 'hii', '2026-07-18 16:00:37'),
(3, 9, 11, 'hi', '2026-07-19 14:07:06'),
(4, 10, 8, 'hi', '2026-07-19 14:45:00'),
(5, 10, 8, 'hi', '2026-07-19 15:00:54');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `level` enum('Beginner','Intermediate','Advanced') NOT NULL,
  `availability` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `user_id`, `title`, `description`, `category_id`, `level`, `availability`, `created_at`) VALUES
(1, 1, 'Introduction to Web Dev with PHP', 'I can teach you the basics of building dynamic websites using PHP, MySQL database connections, and standard CRUD operations. Perfect for beginners!', 1, 'Beginner', 'Weekends (Saturday & Sunday afternoon)', '2026-07-18 09:57:54'),
(2, 2, 'UI/UX & Mobile Interface Design', 'Learn how to construct wireframes, design mockups, and build prototypes in Figma. We will focus on typography, grids, and user journey flows.', 2, 'Intermediate', 'Tuesdays and Thursdays, 5:00 PM to 7:00 PM', '2026-07-18 09:57:54'),
(3, 3, 'Conversational Spanish Practice', 'Practice your Spanish speaking skills in a relaxed, friendly setting. We will cover basic conversational phrases, vocabulary, and common expressions.', 3, 'Beginner', 'Monday and Wednesday evenings', '2026-07-18 09:57:54'),
(4, 4, 'Intro to Digital Marketing & SEO', 'Understand search engine optimization, social media content strategies, and running online campaigns to grow a startup or personal brand.', 6, 'Beginner', 'Fridays from 2:00 PM to 5:00 PM', '2026-07-18 09:57:54'),
(5, 5, 'Acoustic Guitar Basics', 'Get comfortable holding, tuning, and strumming an acoustic guitar. Learn fundamental chords and how to play your first few songs in a week.', 4, 'Beginner', 'Saturdays, 10:00 AM to 12:00 PM', '2026-07-18 09:57:54'),
(6, 6, 'College Algebra & Calculus Help', 'Struggling with linear equations, graphing, or basic derivatives? I will break down complex concepts into simple, easy-to-understand steps.', 5, 'Intermediate', 'Monday to Thursday, 6:00 PM onwards', '2026-07-18 09:57:54'),
(7, 7, 'Introduction to Web Dev with PHP', 'I can teach you the basics of building dynamic websites using PHP, MySQL database connections, and standard CRUD operations. Perfect for beginners!', 1, 'Beginner', '', '2026-07-18 10:30:22'),
(8, 7, 'Python Data Analysis', 'Learn how to use Pandas and NumPy to clean, analyze, and visualize large datasets effectively.', 1, 'Intermediate', '', '2026-07-18 10:30:22'),
(9, 7, 'UI/UX & Mobile Interface Design', 'Learn how to construct wireframes, design mockups, and build prototypes in Figma. We will focus on typography, grids, and user journey flows.', 2, 'Intermediate', '', '2026-07-18 10:30:22'),
(10, 7, 'Photoshop for Beginners', 'Master layers, masking, and color correction in Adobe Photoshop. No prior experience needed!', 2, 'Beginner', '', '2026-07-18 10:30:22'),
(11, 7, 'Conversational Spanish', 'Practice speaking Spanish in real-life scenarios. We will focus on pronunciation, vocabulary, and everyday grammar.', 3, 'Beginner', '', '2026-07-18 10:30:22'),
(12, 7, 'Japanese Hiragana & Katakana', 'Start your Japanese learning journey by mastering the two basic writing systems in just a few sessions.', 3, 'Beginner', '', '2026-07-18 10:30:22'),
(13, 7, 'Acoustic Guitar Basics', 'Learn basic chords, strumming patterns, and how to play your first full song in a week.', 4, 'Beginner', '', '2026-07-18 10:30:22'),
(14, 7, 'Music Production with FL Studio', 'Learn how to arrange beats, mix audio, and master your own tracks using FL Studio software.', 4, 'Intermediate', '', '2026-07-18 10:30:22'),
(15, 7, 'College Algebra Help', 'Struggling with equations? I can help you understand the core concepts of algebra, functions, and graphs.', 5, 'Beginner', '', '2026-07-18 10:30:22'),
(16, 7, 'Essay Writing & Peer Review', 'I will help you structure your academic essays, cite sources properly, and polish your grammar.', 5, 'Intermediate', '', '2026-07-18 10:30:22'),
(17, 7, 'Intro to Digital Marketing', 'Learn the basics of SEO, social media marketing, and content strategy for your next startup.', 6, 'Beginner', '', '2026-07-18 10:30:22'),
(18, 7, 'Financial Accounting Basics', 'Understanding balance sheets, income statements, and cash flow analysis for beginners.', 6, 'Beginner', '', '2026-07-18 10:30:22'),
(19, 7, 'DSLR Photography 101', 'Understand the exposure triangle (ISO, Aperture, Shutter Speed) to take manual control of your camera.', 7, 'Beginner', '', '2026-07-18 10:30:22'),
(20, 7, 'Video Editing with Premiere Pro', 'Learn how to cut, color correct, and add effects to your videos like a professional.', 7, 'Intermediate', '', '2026-07-18 10:30:22'),
(21, 7, 'Cinematography & Lighting', 'Master the art of lighting a scene to create cinematic moods and professional-looking shots.', 7, 'Advanced', '', '2026-07-18 10:30:22'),
(22, 8, 'python', 'built on python projects', 1, 'Advanced', 'sunday full time', '2026-07-18 11:02:58');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `university` varchar(150) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `skills_teach` text DEFAULT NULL,
  `skills_learn` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT 'default-profile.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `name`, `university`, `department`, `bio`, `skills_teach`, `skills_learn`, `profile_photo`, `created_at`) VALUES
(1, 'thahani', 'alex@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'Alex Jones', 'State University', 'Computer Science', 'Hi! I am a final-year CS student who loves building web applications in PHP and React. Looking to improve my design skills.', 'PHP, JavaScript, MySQL, HTML/CSS', 'UI/UX Design, Figma, Spanish', 'default-profile.png', '2026-07-18 09:57:54'),
(2, 'zimanjaar', 'sarah@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'Sarah Smith', 'State University', 'Fine Arts', 'Graphic designer and digital artist. I love sketching and working with Illustrator/Figma. I want to learn Python coding.', 'Illustrator, UI/UX Design, Figma, Sketching', 'Python, Programming Basics', 'default-profile.png', '2026-07-18 09:57:54'),
(3, 'emily_d', 'emily@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'Emily Davis', 'State University', 'Languages & Literature', 'Bonjour! I am a student tutor majoring in French and Spanish. Eager to pick up acoustic guitar and Python coding in my free time.', 'French, Spanish, Conversational English', 'Acoustic Guitar, Python', 'default-profile.png', '2026-07-18 09:57:54'),
(4, 'mike_c', 'michael@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'Michael Chen', 'State University', 'Business Administration', 'Marketing enthusiast interested in SEO and advanced Excel. I am currently learning React.js and web design to build my portfolio.', 'Digital Marketing, SEO, Excel Modeling', 'React.js, UI/UX Design, Figma', 'default-profile.png', '2026-07-18 09:57:54'),
(5, 'sophia_p', 'sophia@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'Sophia Patel', 'State University', 'Music & Arts', 'Violinist and guitarist. Happy to teach music theory or acoustic guitar basics to beginners. I am looking for help with photography.', 'Acoustic Guitar, Violin, Music Theory', 'DSLR Photography, Photoshop', 'default-profile.png', '2026-07-18 09:57:54'),
(6, 'david_k', 'david@university.edu', '$2y$10$pj5TDJ59yV/MsQb4QaL5DORAoTaMb0BovzeaZcfXGs/n4PNld1nEa', 'David Kim', 'State University', 'Physics', 'Physics and math tutor. If you need help with physics mechanics or college algebra, let me know! I would love to learn video editing.', 'Physics 101, College Algebra, Calculus', 'Video Editing, Premiere Pro', 'default-profile.png', '2026-07-18 09:57:54'),
(7, 'system_tutor', 'tutor@skillswap.local', '$2y$10$kkg8Ns.ju5kLCLgiDRuq/OCAPiH8qj143zdsdxeJvp2fkMdjO8adW', 'SkillSwap Tutor', 'State University', 'Education', NULL, NULL, NULL, 'default-profile.png', '2026-07-18 10:30:22'),
(8, 'Nuha', 'nuha@gmail.com', '$2y$10$6yUnbLTFQMOVDqxWrs9NSurBUt8hscNuJ79pRhnUxIFnhRRjIPu.G', 'Nuha', 'SLIIT', 'it', NULL, NULL, NULL, 'default-profile.png', '2026-07-18 11:02:01'),
(9, 'raja', 'raja@gmail.com', '$2y$10$prVXCfOhi79m.6S3P5H7mOiv/HFCae2mL4Eo38iOElvafnwjFn6WS', 'raja', 'Gampaha Wickramarachchi University of Indigenous Medicine', 'it', NULL, NULL, NULL, 'default-profile.png', '2026-07-18 11:05:14'),
(10, 'miss', 'miss@gmail.com', '$2y$10$zyho/McuuRr4uHMby./3s.5HknbTa8YVRAdmqYwoX6/9JWrjgFgHK', 'miss', 'Open University of Sri Lanka', 'cst', NULL, NULL, NULL, 'default-profile.png', '2026-07-18 12:16:04'),
(11, 'zumla', 'zumla@gmail.com', '$2y$10$gE8Oub/DnElENNfr7blFgO2d.RoFz7oTz2jlsiXxevEzFPjswNcrm', 'zumla', 'Rajarata University of Sri Lanka', 'it', NULL, NULL, NULL, 'default-profile.png', '2026-07-18 15:50:00'),
(12, 'hdfjsgfd', 'fgdfgdf@gmail.com', '$2y$10$D1c5V2w/0msELQdifjzsruMZkAf9Iy98ofk8EO7T7ZzdFzpASyC..', 'hdfjsgfd', 'SLIIT', 'fwfw', NULL, NULL, NULL, 'default-profile.png', '2026-07-19 14:08:24'),
(13, 'raja1', 'raja1@gmail.com', '$2y$10$wR8p5gUkj0vPvib7jezVXukL324oWrdJN/yKw46.PYBtZjEwjDSgC', 'raja1', 'Sabaragamuwa University of Sri Lanka', 'jbk', NULL, NULL, NULL, 'default-profile.png', '2026-07-19 15:21:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conv` (`user1_id`,`user2_id`),
  ADD KEY `user2_id` (`user2_id`);

--
-- Indexes for table `downloads`
--
ALTER TABLE `downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_fav` (`file_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `file_tags`
--
ALTER TABLE `file_tags`
  ADD PRIMARY KEY (`file_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_rating` (`file_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `request_replies`
--
ALTER TABLE `request_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `sender_user_id` (`sender_user_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_requests`
--
ALTER TABLE `contact_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request_replies`
--
ALTER TABLE `request_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD CONSTRAINT `contact_requests_ibfk_1` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `downloads`
--
ALTER TABLE `downloads`
  ADD CONSTRAINT `downloads_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downloads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `files_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `file_tags`
--
ALTER TABLE `file_tags`
  ADD CONSTRAINT `file_tags_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `request_replies`
--
ALTER TABLE `request_replies`
  ADD CONSTRAINT `request_replies_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `contact_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_replies_ibfk_2` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `skills_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
