<?php
// Skill Details and Inquiry Page for SkillSwap
$page_title = "Skill Details";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Validate Skill ID parameter
$skill_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($skill_id <= 0) {
    header("Location: browse-skills.php");
    exit;
}

// Fetch skill along with teacher details
$skill = null;
try {
    $stmt = $pdo->prepare("SELECT s.*, u.name as teacher_name, u.email as teacher_email, u.university as teacher_uni, 
                                  u.department as teacher_dept, u.bio as teacher_bio, u.profile_photo as teacher_photo,
                                  c.name as category_name, c.icon as category_icon
                           FROM skills s
                           JOIN users u ON s.user_id = u.id
                           JOIN categories c ON s.category_id = c.id
                           WHERE s.id = ?");
    $stmt->execute([$skill_id]);
    $skill = $stmt->fetch();
} catch (PDOException $e) {
    // Silence/display database error
}

if (!$skill) {
    setFlashMessage('danger', 'The requested skill listing could not be found.');
    header("Location: browse-skills.php");
    exit;
}

// Check if current user is the owner of the skill
$is_owner = isLoggedIn() && ($_SESSION['user_id'] == $skill['user_id']);

// Handle Form Submission for Contact Request
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_owner) {
    $sender_name = sanitize($_POST['sender_name']);
    $sender_email = sanitize($_POST['sender_email']);
    $message = sanitize($_POST['message']);

    if (empty($sender_name) || empty($sender_email) || empty($message)) {
        $error = "Please complete all fields before sending your request.";
    } elseif (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_requests (skill_id, sender_name, sender_email, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$skill_id, $sender_name, $sender_email, $message]);
            $success = true;
        } catch (PDOException $e) {
            $error = "Unable to process request: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Fetch pre-filled details for logged-in user
$logged_user = isLoggedIn() ? getLoggedInUser($pdo) : null;
$prefill_name = $logged_user ? $logged_user['name'] : '';
$prefill_email = $logged_user ? $logged_user['email'] : '';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container my-5">
    
    <?php displayFlashMessage(); ?>

    <div class="row g-5">
        <!-- Skill Information -->
        <div class="col-lg-7">
            <div class="card card-premium p-4 p-md-5 bg-white shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="badge badge-custom badge-category fs-6">
                        <i class="bi <?php echo htmlspecialchars($skill['category_icon']); ?> me-1"></i> <?php echo htmlspecialchars($skill['category_name']); ?>
                    </span>
                    <span class="badge badge-custom badge-level-<?php echo strtolower($skill['level']); ?> fs-6"><?php echo htmlspecialchars($skill['level']); ?></span>
                </div>

                <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($skill['title']); ?></h1>
                
                <p class="text-muted leading-relaxed" style="font-size: 1.1rem; white-space: pre-line;"><?php echo htmlspecialchars($skill['description']); ?></p>
                
                <hr class="my-4">

                <div class="row g-3">
                    <div class="col-sm-6">
                        <h6 class="text-muted mb-1"><i class="bi bi-clock-history me-1"></i> Availability Info</h6>
                        <p class="fw-semibold mb-0"><?php echo htmlspecialchars($skill['availability']); ?></p>
                    </div>
                    <div class="col-sm-6">
                        <h6 class="text-muted mb-1"><i class="bi bi-calendar-event me-1"></i> Listed Date</h6>
                        <p class="fw-semibold mb-0"><?php echo date("F d, Y", strtotime($skill['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Teacher Bio Profile -->
            <div class="card card-premium p-4 p-md-5 bg-white shadow-sm">
                <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-person-workspace text-primary me-2"></i> Meet the Teacher</h4>
                
                <div class="d-flex flex-column flex-sm-row gap-4 align-items-center text-center text-sm-start">
                    <img src="assets/uploads/<?php echo htmlspecialchars($skill['teacher_photo'] ? $skill['teacher_photo'] : 'default-profile.png'); ?>" alt="Teacher Profile" class="avatar-profile shadow-sm">
                    <div>
                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($skill['teacher_name']); ?></h4>
                        <p class="text-primary mb-2 fw-semibold" style="font-size: 0.95rem;">
                            <i class="bi bi-building"></i> <?php echo htmlspecialchars($skill['teacher_uni']); ?> &bull; <?php echo htmlspecialchars($skill['teacher_dept']); ?>
                        </p>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($skill['teacher_bio'] ? $skill['teacher_bio'] : "This student teacher hasn't written a biography yet."); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Inquire Box -->
        <div class="col-lg-5">
            <?php if ($is_owner): ?>
                <div class="card card-premium p-4 text-center bg-light border-warning">
                    <div class="fs-1 text-warning mb-3"><i class="bi bi-info-circle-fill"></i></div>
                    <h5 class="fw-bold">Your Listing</h5>
                    <p class="text-muted small">This is your own skill listing. If you want to modify this card description, please visit your dashboard or edit the skill directly.</p>
                    <a href="add-skill.php?id=<?php echo $skill['id']; ?>" class="btn btn-premium btn-sm w-100 mt-2">
                        <i class="bi bi-pencil-square me-1"></i> Edit Listing
                    </a>
                </div>
            <?php else: ?>
                <div class="card card-premium p-4 p-md-5 bg-white shadow">
                    <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-envelope-paper-fill text-primary"></i> Swap Request</h4>
                    <p class="text-muted small mb-4">Send an exchange request to <strong><?php echo htmlspecialchars($skill['teacher_name']); ?></strong> detailing what skill you want to learn or swap.</p>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 shadow-sm" role="alert">
                            <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-check-circle-fill"></i> Request Sent!</h6>
                            <p class="mb-0 small">The student teacher has been notified. They will get back to you via your email address.</p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="skill-details.php?id=<?php echo $skill['id']; ?>" method="POST">
                        <div class="mb-3">
                            <label for="sender_name" class="form-label form-label-premium">Your Name</label>
                            <input type="text" name="sender_name" id="sender_name" class="form-control form-control-premium" placeholder="e.g. David Lee" value="<?php echo htmlspecialchars($prefill_name); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="sender_email" class="form-label form-label-premium">Your Email</label>
                            <input type="email" name="sender_email" id="sender_email" class="form-control form-control-premium" placeholder="e.g. david@university.edu" value="<?php echo htmlspecialchars($prefill_email); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label form-label-premium">Swap Message</label>
                            <textarea name="message" id="message" rows="4" class="form-control form-control-premium" placeholder="e.g. Hi! I would love to learn PHP from you. In return, I can help you with Figma UI/UX grids and wireframing! Let me know if you are free." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-premium w-100 py-3">
                            <i class="bi bi-send-check me-2"></i> Send Swap Request
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
