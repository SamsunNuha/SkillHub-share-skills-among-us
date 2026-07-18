<?php
// Student Registration Page
$page_title = "Register";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize values
    $name = sanitize($_POST['username']); // Use username for name field internally
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $university = sanitize($_POST['university']);
    $department = sanitize($_POST['department']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validations
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (preg_match('/[^a-zA-Z0-9_]/', $username)) {
        $errors[] = "Username can only contain alphanumeric characters and underscores.";
    }
    
    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Database duplication checks
    if (empty($errors)) {
        try {
            // Check username
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Username is already taken. Try another one.";
            }

            // Check email
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "An account with this email address already exists.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database validation error: " . $e->getMessage();
        }
    }

    // Insert user and login
    if (empty($errors)) {
        try {
            $photo_filename = 'default-profile.png';

            // Handle Profile Photo Upload if a file is uploaded
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['profile_photo'];
                
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = "Profile photo upload failed with error code: " . $file['error'];
                } else {
                    $max_size = 2 * 1024 * 1024; // 2MB
                    if ($file['size'] > $max_size) {
                        $errors[] = "Profile photo size exceeds the 2MB limit.";
                    }

                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    $file_info = pathinfo($file['name']);
                    $extension = isset($file_info['extension']) ? strtolower($file_info['extension']) : '';
                    
                    if (!in_array($extension, $allowed_extensions)) {
                        $errors[] = "Invalid file extension. Please upload JPG, JPEG, PNG, or GIF files only.";
                    } else {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);
                        
                        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/pjpeg', 'image/x-png'];
                        if (!in_array($mime_type, $allowed_mimes)) {
                            $errors[] = "Invalid image file type. Please upload a real image file.";
                        }
                    }

                    if (empty($errors)) {
                        $upload_dir = __DIR__ . '/assets/uploads/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $new_filename = 'user_reg_' . uniqid() . '_' . time() . '.' . $extension;
                        $destination = $upload_dir . $new_filename;

                        if (move_uploaded_file($file['tmp_name'], $destination)) {
                            $photo_filename = $new_filename;
                        } else {
                            $errors[] = "Failed to save uploaded profile photo.";
                        }
                    }
                }
            }

            if (empty($errors)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, name, university, department, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $name, $university, $department, $photo_filename]);
                
                // Get new user id
                $user_id = $pdo->lastInsertId();

                // Set session (Auto Login)
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;

                setFlashMessage('success', 'Registration successful! Welcome to the skillHub community!');
                header("Location: dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Failed to register user account. Please try again. " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-10">
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <h5 class="alert-heading fw-bold"><i class="bi bi-x-circle-fill me-2"></i> Registration Errors:</h5>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card card-premium shadow-lg">
                <div class="card-header-gradient text-center text-white py-4">
                    <h3 class="mb-1"><i class="bi bi-person-plus"></i> Join SkillSwap</h3>
                    <p class="mb-0 small text-white-50">Create your account and start sharing knowledge</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <form action="register.php" method="POST" id="registerForm">
                        <div class="row g-4">
                            <!-- Full name removed per user request -->
                            
                            <!-- Username -->
                            <div class="col-md-6">
                                <label for="username" class="form-label form-label-premium">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-at text-muted"></i></span>
                                    <input type="text" name="username" id="username" class="form-control form-control-premium border-start-0" placeholder="e.g. alex_jones" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-12">
                                <label for="email" class="form-label form-label-premium">Student Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" name="email" id="email" class="form-control form-control-premium border-start-0" placeholder="e.g. thahani@gmail.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                            </div>

                            <!-- University -->
                            <div class="col-md-6">
                                <label for="university" class="form-label form-label-premium">University / College</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-building text-muted"></i></span>
                                    <?php $selected_uni = isset($_POST['university']) ? $_POST['university'] : ''; ?>
                                    <select name="university" id="university" class="form-select form-control-premium border-start-0" required>
                                        <option value="" disabled <?php echo empty($selected_uni) ? 'selected' : ''; ?>>Select your university...</option>
                                        <option value="University of Colombo" <?php echo ($selected_uni === 'University of Colombo') ? 'selected' : ''; ?>>University of Colombo</option>
                                        <option value="University of Peradeniya" <?php echo ($selected_uni === 'University of Peradeniya') ? 'selected' : ''; ?>>University of Peradeniya</option>
                                        <option value="University of Sri Jayewardenepura" <?php echo ($selected_uni === 'University of Sri Jayewardenepura') ? 'selected' : ''; ?>>University of Sri Jayewardenepura</option>
                                        <option value="University of Kelaniya" <?php echo ($selected_uni === 'University of Kelaniya') ? 'selected' : ''; ?>>University of Kelaniya</option>
                                        <option value="University of Moratuwa" <?php echo ($selected_uni === 'University of Moratuwa') ? 'selected' : ''; ?>>University of Moratuwa</option>
                                        <option value="University of Jaffna" <?php echo ($selected_uni === 'University of Jaffna') ? 'selected' : ''; ?>>University of Jaffna</option>
                                        <option value="University of Ruhuna" <?php echo ($selected_uni === 'University of Ruhuna') ? 'selected' : ''; ?>>University of Ruhuna</option>
                                        <option value="Eastern University, Sri Lanka" <?php echo ($selected_uni === 'Eastern University, Sri Lanka') ? 'selected' : ''; ?>>Eastern University, Sri Lanka</option>
                                        <option value="South Eastern University of Sri Lanka" <?php echo ($selected_uni === 'South Eastern University of Sri Lanka') ? 'selected' : ''; ?>>South Eastern University of Sri Lanka</option>
                                        <option value="Rajarata University of Sri Lanka" <?php echo ($selected_uni === 'Rajarata University of Sri Lanka') ? 'selected' : ''; ?>>Rajarata University of Sri Lanka</option>
                                        <option value="Wayamba University of Sri Lanka" <?php echo ($selected_uni === 'Wayamba University of Sri Lanka') ? 'selected' : ''; ?>>Wayamba University of Sri Lanka</option>
                                        <option value="Sabaragamuwa University of Sri Lanka" <?php echo ($selected_uni === 'Sabaragamuwa University of Sri Lanka') ? 'selected' : ''; ?>>Sabaragamuwa University of Sri Lanka</option>
                                        <option value="Uva Wellassa University" <?php echo ($selected_uni === 'Uva Wellassa University') ? 'selected' : ''; ?>>Uva Wellassa University</option>
                                        <option value="University of the Visual and Performing Arts" <?php echo ($selected_uni === 'University of the Visual and Performing Arts') ? 'selected' : ''; ?>>University of the Visual and Performing Arts</option>
                                        <option value="Open University of Sri Lanka" <?php echo ($selected_uni === 'Open University of Sri Lanka') ? 'selected' : ''; ?>>Open University of Sri Lanka</option>
                                        <option value="Gampaha Wickramarachchi University of Indigenous Medicine" <?php echo ($selected_uni === 'Gampaha Wickramarachchi University of Indigenous Medicine') ? 'selected' : ''; ?>>Gampaha Wickramarachchi University of Indigenous Medicine</option>
                                        <option value="University of Vavuniya" <?php echo ($selected_uni === 'University of Vavuniya') ? 'selected' : ''; ?>>University of Vavuniya</option>
                                        <option value="SLIIT" <?php echo ($selected_uni === 'SLIIT') ? 'selected' : ''; ?>>SLIIT</option>
                                        <option value="NSBM Green University" <?php echo ($selected_uni === 'NSBM Green University') ? 'selected' : ''; ?>>NSBM Green University</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Department -->
                            <div class="col-md-6">
                                <label for="department" class="form-label form-label-premium">Department / Major</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-book text-muted"></i></span>
                                    <input type="text" name="department" id="department" class="form-control form-control-premium border-start-0" placeholder="e.g. Computer Science" value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>" required>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label for="password" class="form-label form-label-premium">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                                    <input type="password" name="password" id="password" class="form-control form-control-premium border-start-0" placeholder="Min. 6 characters" required>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label form-label-premium">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control form-control-premium border-start-0" placeholder="Confirm your password" required>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label text-muted small" for="terms">I agree to exchange my skills respectfully and abide by the community guidelines.</label>
                        </div>

                        <button type="submit" class="btn btn-premium w-100 py-3 mt-2">
                            <i class="bi bi-person-check me-2"></i> Register Account
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-0 small">Already have an account? <a href="login.php" class="text-decoration-none fw-bold text-primary">Log In Here</a></p>
                    </div>

                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
