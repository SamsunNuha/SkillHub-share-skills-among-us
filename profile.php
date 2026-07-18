<?php
// Public Student Profile View for SkillSwap
$page_title = "Student Profile";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Validate User ID parameter
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    // If no ID is passed, redirect to index or log in page
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
    } else {
        header("Location: index.php");
        exit;
    }
}

// Fetch student profile details
$student = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $student = $stmt->fetch();
} catch (PDOException $e) {
    // Silence/display database error
}

if (!$student) {
    setFlashMessage('danger', 'The requested student profile could not be found.');
    header("Location: index.php");
    exit;
}

// Fetch active skills posted by this student
$student_skills = [];
try {
    $stmt = $pdo->prepare("SELECT s.*, c.name as category_name, c.icon as category_icon 
                           FROM skills s
                           JOIN categories c ON s.category_id = c.id
                           WHERE s.user_id = ? 
                           ORDER BY s.created_at DESC");
    $stmt->execute([$user_id]);
    $student_skills = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silence error
}

// Check if visiting student is viewing their own profile
$is_own_profile = isLoggedIn() && ($_SESSION['user_id'] == $student['id']);

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container my-5">
    
    <?php displayFlashMessage(); ?>

    <div class="row g-5">
        <!-- Student Sidebar Bio Card -->
        <div class="col-lg-4">
            <div class="card card-premium p-4 text-center bg-white shadow-sm">
                <div class="avatar-wrapper mb-3">
                    <img src="assets/uploads/<?php echo htmlspecialchars($student['profile_photo'] ? $student['profile_photo'] : 'default-profile.png'); ?>" alt="Student Photo" class="avatar-profile shadow-md">
                </div>
                
                <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($student['username']); ?></h3>
                <span class="badge bg-primary-subtle text-primary px-3 py-1 mb-2" style="font-size:0.82rem; letter-spacing:0.04em;"><i class="bi bi-person-check-fill me-1"></i>Member</span>
                <?php
                    // Mask the email domain
                    $email_parts = explode('@', $student['email']);
                    $masked_email = $email_parts[0] . '@*****.edu';
                ?>
                <p class="text-muted small mb-2"><i class="bi bi-envelope-at-fill text-primary"></i> <?php echo htmlspecialchars($masked_email); ?></p>
                <p class="text-primary fw-semibold mb-3" style="font-size: 0.95rem;">
                    <i class="bi bi-building"></i> <?php echo htmlspecialchars($student['university'] ? $student['university'] : 'University Not Set'); ?>
                </p>
                <span class="badge bg-light text-dark px-3 py-2 border mb-4"><?php echo htmlspecialchars($student['department'] ? $student['department'] : 'Department Not Set'); ?></span>

                <?php if ($is_own_profile): ?>
                    <a href="edit-profile.php" class="btn btn-premium btn-sm w-100 py-2">
                        <i class="bi bi-pencil-square me-1"></i> Edit My Profile
                    </a>
                <?php elseif (isLoggedIn()): ?>
                    <a href="chat.php?with=<?php echo $student['id']; ?>" class="btn btn-primary w-100 py-2 mb-2 rounded-pill">
                        <i class="bi bi-chat-dots-fill me-1"></i> Message
                    </a>
                    <div class="text-muted small mt-1">
                        <i class="bi bi-calendar3 me-1"></i> Student since <?php echo date("M Y", strtotime($student['created_at'])); ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted small">
                        <i class="bi bi-calendar3 me-1"></i> Student since <?php echo date("M Y", strtotime($student['created_at'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Student Skills & Core Biography -->
        <div class="col-lg-8">
            <!-- About Biography Section -->
            <div class="card card-premium p-4 p-md-5 bg-white shadow-sm mb-4">
                <h4 class="fw-bold mb-3 text-dark"><i class="bi bi-journal-text text-primary me-2"></i> About Me</h4>
                <p class="text-muted mb-4 leading-relaxed" style="white-space: pre-line;"><?php echo htmlspecialchars($student['bio'] ? $student['bio'] : "This student has not written a bio description yet."); ?></p>
                
                <div class="row g-4 pt-3 border-top">
                    <!-- Skills Can Teach -->
                    <div class="col-sm-6">
                        <h5 class="fw-bold text-success mb-2"><i class="bi bi-patch-check-fill"></i> Skills Can Teach</h5>
                        <?php if (!empty($student['skills_teach'])): ?>
                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($student['skills_teach']); ?></p>
                        <?php else: ?>
                            <p class="text-muted small italic mb-0">No list specified yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Skills Want to Learn -->
                    <div class="col-sm-6">
                        <h5 class="fw-bold text-secondary-color mb-2"><i class="bi bi-search"></i> Skills Want to Learn</h5>
                        <?php if (!empty($student['skills_learn'])): ?>
                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($student['skills_learn']); ?></p>
                        <?php else: ?>
                            <p class="text-muted small italic mb-0">No list specified yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Active Listings Posted By User -->
            <div class="card card-premium p-4 p-md-5 bg-white shadow-sm">
                <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i> Listed Skills for Exchange</h4>
                
                <?php if (empty($student_skills)): ?>
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">This student has not posted any active listings yet.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($student_skills as $skill): ?>
                            <div class="col-md-6">
                                <div class="card card-premium h-100 p-4 bg-light border-0 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="badge badge-custom badge-category">
                                                <i class="bi <?php echo htmlspecialchars($skill['category_icon']); ?> me-1"></i> <?php echo htmlspecialchars($skill['category_name']); ?>
                                            </span>
                                            <span class="badge badge-custom badge-level-<?php echo strtolower($skill['level']); ?>"><?php echo htmlspecialchars($skill['level']); ?></span>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($skill['title']); ?></h5>
                                        <p class="text-muted small mb-3"><?php echo htmlspecialchars(substr($skill['description'], 0, 100)) . (strlen($skill['description']) > 100 ? '...' : ''); ?></p>
                                    </div>
                                    <div class="mt-2">
                                        <a href="skill-details.php?id=<?php echo $skill['id']; ?>" class="btn btn-sm btn-premium w-100 text-center py-2">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
