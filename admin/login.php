<?php
// Redirect legacy admin login to unified login page
header('Location: ../login.php');
exit;
?>

// Redirect to admin dashboard if already logged in as admin
if (isAdminLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all administrator credentials.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Set Admin Session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                setFlashMessage('success', 'Admin Session initialized. Welcome, ' . htmlspecialchars($admin['username']) . '!');
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid administrator username or password.";
            }
        } catch (PDOException $e) {
            $error = "An unexpected error occurred. Please try again.";
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            
            <?php displayFlashMessage(); ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-shield-slash-fill me-2"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card card-premium shadow-lg border-danger">
                <!-- Administrative themed header -->
                <div class="card-header bg-dark text-center text-white py-4 border-bottom-0">
                    <h3 class="mb-1 text-danger"><i class="bi bi-shield-lock-fill"></i> Admin Portal</h3>
                    <p class="mb-0 small text-white-50">Authorized administrative access only</p>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    
                    <form action="login.php" method="POST">
                        <div class="mb-4">
                            <label for="username" class="form-label form-label-premium text-dark">Admin Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-fill text-muted"></i></span>
                                <input type="text" name="username" id="username" class="form-control form-control-premium border-start-0" placeholder="e.g. admin" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label form-label-premium text-dark">Admin Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-muted"></i></span>
                                <input type="password" name="password" id="password" class="form-control form-control-premium border-start-0" placeholder="Enter secure key" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-3 mb-3 border-danger">
                            <i class="bi bi-box-arrow-in-right me-2 text-danger"></i> Initialize Dashboard
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="../index.php" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Return to Main Site</a>
                    </div>

                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
