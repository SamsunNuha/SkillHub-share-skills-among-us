<?php
// Contact Page for SkillSwap
$page_title = "Contact Support";
require_once 'includes/db.php';
require_once 'includes/auth.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_name = sanitize($_POST['name']);
    $sender_email = sanitize($_POST['email']);
    $message = sanitize($_POST['message']);

    if (empty($sender_name) || empty($sender_email) || empty($message)) {
        $error = "Please fill in all form fields before submitting.";
    } elseif (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            // Insert general contact request into DB (skill_id = NULL)
            $stmt = $pdo->prepare("INSERT INTO contact_requests (skill_id, sender_name, sender_email, message) VALUES (NULL, ?, ?, ?)");
            $stmt->execute([$sender_name, $sender_email, $message]);
            $success = true;
            
            // Clear inputs on success
            $_POST = [];
        } catch (PDOException $e) {
            $error = "Failed to submit request. Please try again. " . htmlspecialchars($e->getMessage());
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<!-- Header Banner -->
<section class="py-5 bg-light text-center" style="background: var(--gradient-soft) !important;">
    <div class="container my-4">
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-2 fw-semibold">Get In Touch</span>
        <h1 class="display-5 fw-bold">Contact Us</h1>
        <p class="text-muted lead mx-auto" style="max-width: 600px;">Have questions, feedback, or need help? Send us a message and we'll reply shortly.</p>
    </div>
</section>

<div class="container my-5 py-3">
    <div class="row g-5">
        <!-- Contact details -->
        <div class="col-lg-5">
            <h2 class="fw-bold mb-4 gradient-text">Contact Information</h2>
            <p class="text-muted mb-5">If you have suggestions for our system, or require administrator help regarding your account, please reach out via the coordinates below or submit the message form.</p>

            <div class="d-flex gap-4 mb-4">
                <div class="fs-3 text-primary"><i class="bi bi-geo-alt-fill"></i></div>
                <div>
                    <h5 class="fw-bold mb-1">Campus Office</h5>
                    <p class="text-muted small">Student Activity Center, Room 402<br>State University, Campus Drive, NY 10012</p>
                </div>
            </div>

            <div class="d-flex gap-4 mb-4">
                <div class="fs-3 text-secondary-color"><i class="bi bi-envelope-at-fill"></i></div>
                <div>
                    <h5 class="fw-bold mb-1">Email Support</h5>
                    <p class="text-muted small"><a href="mailto:support@skillHub.edu" class="text-decoration-none text-secondary-color">support@skillHub.edu</a></p>
                </div>
            </div>

            <div class="d-flex gap-4">
                <div class="fs-3 text-accent-color"><i class="bi bi-telephone-fill"></i></div>
                <div>
                    <h5 class="fw-bold mb-1">Telephone Contact</h5>
                    <p class="text-muted small">+94 753360265<br>Monday to Friday, 9:00 AM - 5:00 PM</p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="card card-premium p-4 p-md-5 bg-white shadow">
                <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-chat-dots-fill text-primary"></i> Send a Message</h3>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> Your message has been sent successfully! Our team will contact you soon.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST">
                    <div class="mb-4">
                        <label for="name" class="form-label form-label-premium">Student Name</label>
                        <input type="text" name="name" id="name" class="form-control form-control-premium" placeholder="e.g. Alex Jones" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label form-label-premium">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control form-control-premium" placeholder="e.g. alex@university.edu" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="message" class="form-label form-label-premium">Message / Inquiry</label>
                        <textarea name="message" id="message" rows="5" class="form-control form-control-premium" placeholder="Type your message here..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-premium w-100 py-3">
                        <i class="bi bi-send-fill me-2"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
