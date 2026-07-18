<?php
require_once __DIR__ . '/includes/db.php';
// Reset admins table to ensure only one admin
$pdo->exec("DELETE FROM admins");

echo "Starting Database Seeder...\n";

// Seed default admin (username: admin, password: admin123)
$admin_password = password_hash('admin123', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
$stmt->execute(['admin', $admin_password, 'admin@gmail.com']);

// 1. Create a dummy user for the default skills
$stmt = $pdo->query("SELECT id FROM users WHERE username = 'system_tutor' LIMIT 1");
$dummy_user = $stmt->fetch();

if (!$dummy_user) {
    echo "Creating dummy user...\n";
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, name, university, department, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['system_tutor', 'tutor@skillswap.local', password_hash('password123', PASSWORD_BCRYPT), 'SkillSwap Tutor', 'State University', 'Education', 'default-profile.png']);
    $user_id = $pdo->lastInsertId();
} else {
    $user_id = $dummy_user['id'];
}

echo "Using Dummy User ID: $user_id\n";

// 2. Define standard categories if they don't exist
$default_categories = [
    'Academics & Tutoring' => 'bi-book-half',
    'Business & Marketing' => 'bi-graph-up',
    'Graphic Design' => 'bi-palette',
    'Languages & Culture' => 'bi-translate',
    'Music & Arts' => 'bi-music-note-beamed',
    'Photography & Video' => 'bi-camera',
    'Programming & Tech' => 'bi-code-slash'
];

foreach ($default_categories as $name => $icon) {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, icon) VALUES (?, ?)");
        $stmt->execute([$name, $icon]);
        echo "Created category: $name\n";
    }
}

// 3. Get all categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

$dummy_skills = [
    'Academics & Tutoring' => [
        ['title' => 'College Algebra Help', 'desc' => 'Struggling with equations? I can help you understand the core concepts of algebra, functions, and graphs.', 'level' => 'Beginner'],
        ['title' => 'Essay Writing & Peer Review', 'desc' => 'I will help you structure your academic essays, cite sources properly, and polish your grammar.', 'level' => 'Intermediate'],
        ['title' => 'Physics 101 Mechanics', 'desc' => 'Comprehensive tutoring on kinematics, Newton\'s laws, and energy conservation principles.', 'level' => 'Advanced']
    ],
    'Business & Marketing' => [
        ['title' => 'Intro to Digital Marketing', 'desc' => 'Learn the basics of SEO, social media marketing, and content strategy for your next startup.', 'level' => 'Beginner'],
        ['title' => 'Financial Accounting Basics', 'desc' => 'Understanding balance sheets, income statements, and cash flow analysis for beginners.', 'level' => 'Beginner'],
        ['title' => 'Advanced Excel Modeling', 'desc' => 'Build dynamic financial models, use pivot tables, and master VLOOKUP/INDEX-MATCH.', 'level' => 'Advanced']
    ],
    'Graphic Design' => [
        ['title' => 'UI/UX & Mobile Interface Design', 'desc' => 'Learn how to construct wireframes, design mockups, and build prototypes in Figma. We will focus on typography, grids, and user journey flows.', 'level' => 'Intermediate'],
        ['title' => 'Photoshop for Beginners', 'desc' => 'Master layers, masking, and color correction in Adobe Photoshop. No prior experience needed!', 'level' => 'Beginner'],
        ['title' => 'Logo Design Masterclass', 'desc' => 'Learn the principles of vector graphics and typography to create stunning logos using Adobe Illustrator.', 'level' => 'Advanced']
    ],
    'Languages & Culture' => [
        ['title' => 'Conversational Spanish', 'desc' => 'Practice speaking Spanish in real-life scenarios. We will focus on pronunciation, vocabulary, and everyday grammar.', 'level' => 'Beginner'],
        ['title' => 'Japanese Hiragana & Katakana', 'desc' => 'Start your Japanese learning journey by mastering the two basic writing systems in just a few sessions.', 'level' => 'Beginner'],
        ['title' => 'Advanced French Literature', 'desc' => 'Deep dive into classic French literature and improve your reading comprehension and analytical skills.', 'level' => 'Advanced']
    ],
    'Music & Arts' => [
        ['title' => 'Acoustic Guitar Basics', 'desc' => 'Learn basic chords, strumming patterns, and how to play your first full song in a week.', 'level' => 'Beginner'],
        ['title' => 'Music Production with FL Studio', 'desc' => 'Learn how to arrange beats, mix audio, and master your own tracks using FL Studio software.', 'level' => 'Intermediate'],
        ['title' => 'Watercolor Painting Techniques', 'desc' => 'Discover the beauty of watercolors. We will cover washes, blending, and texture creation.', 'level' => 'Beginner']
    ],
    'Photography & Video' => [
        ['title' => 'DSLR Photography 101', 'desc' => 'Understand the exposure triangle (ISO, Aperture, Shutter Speed) to take manual control of your camera.', 'level' => 'Beginner'],
        ['title' => 'Video Editing with Premiere Pro', 'desc' => 'Learn how to cut, color correct, and add effects to your videos like a professional.', 'level' => 'Intermediate'],
        ['title' => 'Cinematography & Lighting', 'desc' => 'Master the art of lighting a scene to create cinematic moods and professional-looking shots.', 'level' => 'Advanced']
    ],
    'Programming & Tech' => [
        ['title' => 'Introduction to Web Dev with PHP', 'desc' => 'I can teach you the basics of building dynamic websites using PHP, MySQL database connections, and standard CRUD operations. Perfect for beginners!', 'level' => 'Beginner'],
        ['title' => 'Python Data Analysis', 'desc' => 'Learn how to use Pandas and NumPy to clean, analyze, and visualize large datasets effectively.', 'level' => 'Intermediate'],
        ['title' => 'React.js Frontend Development', 'desc' => 'Build modern single-page applications using React components, hooks, and state management.', 'level' => 'Advanced']
    ]
];

foreach ($categories as $cat) {
    $cat_id = $cat['id'];
    $cat_name = $cat['name'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM skills WHERE category_id = ?");
    $stmt->execute([$cat_id]);
    $count = $stmt->fetchColumn();
    
    echo "Category '$cat_name' has $count skills.\n";
    
    if ($count < 3) {
        $needed = 3 - $count;
        echo "Adding $needed skills to '$cat_name'...\n";
        
        $skills_to_add = isset($dummy_skills[$cat_name]) ? $dummy_skills[$cat_name] : [
            ['title' => "Sample Skill for $cat_name 1", 'desc' => 'This is a sample description.', 'level' => 'Beginner'],
            ['title' => "Sample Skill for $cat_name 2", 'desc' => 'This is a sample description.', 'level' => 'Intermediate'],
            ['title' => "Sample Skill for $cat_name 3", 'desc' => 'This is a sample description.', 'level' => 'Advanced']
        ];
        
        // Add up to 3
        for ($i = 0; $i < $needed; $i++) {
            $skill = $skills_to_add[$i % count($skills_to_add)];
            $stmt = $pdo->prepare("INSERT INTO skills (user_id, category_id, title, description, level) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $cat_id, $skill['title'], $skill['desc'], $skill['level']]);
        }
    }
}

echo "Database seeding completed successfully.\n";
?>
