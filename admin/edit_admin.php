<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin(); // ensure only admin can access

// Set base path for assets/includes
$base_path = '../';
$page_title = "Edit Admin Profile";

// Initialize variables
$adminId = 1; // assuming single admin with id 1
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);
    if (!$email) {
        $error = 'Please provide a valid email address.';
    } else {
        try {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('UPDATE admins SET email = ?, password = ? WHERE id = ?');
                $stmt->execute([$email, $hashed, $adminId]);
            } else {
                $stmt = $pdo->prepare('UPDATE admins SET email = ? WHERE id = ?');
                $stmt->execute([$email, $adminId]);
            }
            $success = 'Admin credentials updated successfully.';
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch current admin details
$stmt = $pdo->prepare('SELECT email FROM admins WHERE id = ?');
$stmt->execute([$adminId]);
$admin = $stmt->fetch();
$currentEmail = $admin['email'] ?? '';

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container my-5 pt-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-md border-0 rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-glow text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-shield-lock-fill fs-2"></i>
                    </div>
                    <h2 class="fw-bold mb-1">System Management</h2>
                    <p class="text-muted">Edit Admin Profile Settings</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <div><?php echo htmlspecialchars($success); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="edit_admin.php">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold text-dark">Admin Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control bg-light border-start-0 ps-0" id="email" name="email" value="<?php echo htmlspecialchars($currentEmail); ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold text-dark">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control bg-light border-start-0 ps-0" id="password" name="password" placeholder="Leave blank to keep current">
                        </div>
                        <div class="form-text text-muted mt-1" style="font-size: 0.8rem;">Keep this secure. Use a strong password with letters, numbers, and symbols.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-premium w-100 py-3 rounded-pill mt-2">
                        <i class="bi bi-save me-2"></i>Update Credentials
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="dashboard.php" class="text-primary text-decoration-none fw-semibold small">
                        <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Back to Admin Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
