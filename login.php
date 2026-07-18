<?php
// Unified Login Page — Admin & Students
$page_title = "Login";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Already logged in as admin → admin dashboard
if (isAdminLoggedIn()) {
    header("Location: admin/dashboard.php");
    exit;
}
// Already logged in as student → student dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['username_or_email']);
    $password    = $_POST['password'];

    if (empty($login_input) || empty($password)) {
        $error = "Please fill in all credentials.";
    } else {
        try {
            // ── 1. Check admin table (by email OR username) ──────────────────
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? OR username = ? LIMIT 1");
            $stmt->execute([$login_input, $login_input]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Start admin session
                $_SESSION['admin_id']       = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                setFlashMessage('success', 'Welcome, Administrator!');
                header("Location: admin/dashboard.php");
                exit;
            }

            // ── 2. Check students table (by email OR username) ───────────────
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
            $stmt->execute([$login_input, $login_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Start student session
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                setFlashMessage('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');
                header("Location: dashboard.php");
                exit;
            }

            // ── 3. Neither matched ───────────────────────────────────────────
            $error = "Incorrect email/username or password.";

        } catch (PDOException $e) {
            $error = "An unexpected error occurred. Please try again.";
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            
            <?php displayFlashMessage(); ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card card-premium shadow-lg">
                <div class="card-header-gradient text-center text-white py-4">
                    <h3 class="mb-1"><i class="bi bi-person-lock"></i> Welcome Back</h3>
                    <p class="mb-0 small text-white-50">Log in to exchange skills with fellow students</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <form action="login.php" method="POST">
                        <div class="mb-4">
                            <label for="username_or_email" class="form-label form-label-premium">Username or Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="username_or_email" id="username_or_email" class="form-control form-control-premium border-start-0" placeholder="e.g. alex_jones" value="<?php echo isset($_POST['username_or_email']) ? htmlspecialchars($_POST['username_or_email']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <label for="password" class="form-label form-label-premium mb-0">Password</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                                <input type="password" name="password" id="password" class="form-control form-control-premium border-start-0" placeholder="Enter your password" required>
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label text-muted small" for="remember">Remember me on this computer</label>
                        </div>

                        <button type="submit" class="btn btn-premium w-100 py-3 mb-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Log In
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-0 small">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold text-primary">Sign Up Now</a></p>
                    </div>

                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
