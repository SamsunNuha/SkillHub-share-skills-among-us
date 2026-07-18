<?php
// Student Profile Edit Page for SkillSwap
$page_title = "Edit Profile";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Route Guard - enforce login
requireLogin();

// Fetch current user
$user = getLoggedInUser($pdo);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize text inputs
    $name = $user['name']; // Name field removed from UI, keep existing
    $new_username = sanitize($_POST['username'] ?? $user['username']);
    $university = sanitize($_POST['university']);
    $department = sanitize($_POST['department']);
    $bio = sanitize($_POST['bio']);
    $skills_teach = sanitize($_POST['skills_teach']);
    $skills_learn = sanitize($_POST['skills_learn']);

    // Validate username
    if (empty($new_username)) {
        $errors[] = "Username cannot be empty.";
    } elseif (preg_match('/[^a-zA-Z0-9_]/', $new_username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    } elseif ($new_username !== $user['username']) {
        // Check if username is already taken by another user
        $stmt_chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt_chk->execute([$new_username, $user['id']]);
        if ($stmt_chk->fetchColumn() > 0) {
            $errors[] = "That username is already taken. Please choose another.";
        }
    }
    
    // Validation for text fields (name removed)

    $photo_filename = $user['profile_photo']; // Default to current photo

    // Handle Profile Photo Upload if a file is uploaded
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['profile_photo'];
        
        // 1. Check for system upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed with error code: " . $file['error'];
        } else {
            // 2. Validate file size (2MB limit)
            $max_size = 2 * 1024 * 1024; // 2MB
            if ($file['size'] > $max_size) {
                $errors[] = "File size exceeds the 2MB limit.";
            }

            // 3. Validate file extension/type
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_info = pathinfo($file['name']);
            $extension = isset($file_info['extension']) ? strtolower($file_info['extension']) : '';
            
            if (!in_array($extension, $allowed_extensions)) {
                $errors[] = "Invalid file extension. Please upload JPG, JPEG, PNG, or GIF files only.";
            } else {
                // 4. Validate MIME Type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/pjpeg', 'image/x-png'];
                if (!in_array($mime_type, $allowed_mimes)) {
                    $errors[] = "Invalid image file type. Please upload a real image file.";
                }
            }

            // 5. Save file to server
            if (empty($errors)) {
                $upload_dir = __DIR__ . '/assets/uploads/';
                
                // Create directory if not exists
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Create a unique filename to avoid duplicates
                $new_filename = 'user_' . $user['id'] . '_' . time() . '.' . $extension;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Delete old profile picture if not the default profile
                    if (!empty($user['profile_photo']) && $user['profile_photo'] !== 'default-profile.png') {
                        $old_photo_path = $upload_dir . $user['profile_photo'];
                        if (file_exists($old_photo_path)) {
                            unlink($old_photo_path);
                        }
                    }
                    $photo_filename = $new_filename;
                } else {
                    $errors[] = "Failed to save uploaded file to destination directory.";
                }
            }
        }
    }

    // Save profile to database if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, university = ?, department = ?, bio = ?, skills_teach = ?, skills_learn = ?, profile_photo = ? WHERE id = ?");
            $stmt->execute([$name, $new_username, $university, $department, $bio, $skills_teach, $skills_learn, $photo_filename, $user['id']]);
            
            // Refresh local user variables
            $_SESSION['user_name'] = $new_username;
            $user = getLoggedInUser($pdo);
            $success = true;
            setFlashMessage('success', 'Profile details updated successfully!');
        } catch (PDOException $e) {
            $errors[] = "Error saving profile details to database: " . htmlspecialchars($e->getMessage());
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <?php displayFlashMessage(); ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading fw-bold"><i class="bi bi-x-circle-fill"></i> Update Failed:</h5>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo $err; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card card-premium shadow-lg bg-white">
                <div class="card-header-gradient text-center text-white py-4">
                    <h3 class="mb-1"><i class="bi bi-person-gear"></i> Edit Profile Settings</h3>
                    <p class="mb-0 small text-white-50">Keep your academic bio and skill list up to date</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <form action="edit-profile.php" method="POST" enctype="multipart/form-data">
                        
                        <!-- Profile Photo Section -->
                        <div class="row mb-5 align-items-center bg-light p-4 rounded g-4">
                            <div class="col-sm-3 text-center">
                                <img src="assets/uploads/<?php echo htmlspecialchars($user['profile_photo'] ? $user['profile_photo'] : 'default-profile.png'); ?>" alt="Profile Preview" id="photo_preview" class="avatar-profile shadow">
                            </div>
                            <div class="col-sm-9">
                                <h5 class="fw-bold mb-2">Change Profile Photo</h5>
                                <p class="text-muted small mb-3">Upload a clean square image of yourself. Allowed formats: JPG, PNG, GIF. Max file size: 2MB.</p>
                                <input type="file" name="profile_photo" id="profile_photo" class="form-control form-control-premium" accept="image/*">
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Username -->
                            <div class="col-md-6">
                                <label for="username" class="form-label form-label-premium"><i class="bi bi-at me-1 text-primary"></i>Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-at text-muted"></i></span>
                                    <input type="text" name="username" id="username" class="form-control form-control-premium border-start-0" value="<?php echo htmlspecialchars($user['username']); ?>" placeholder="e.g. alex_jones" required>
                                </div>
                                <small class="text-muted">Only letters, numbers, and underscores allowed.</small>
                            </div>

                            <!-- University -->
                            <div class="col-md-6">
                                <label for="university" class="form-label form-label-premium">University / College</label>
                                <select name="university" id="university" class="form-select form-control-premium">
                                    <option value="" <?php echo empty($user['university']) ? 'selected' : ''; ?>>Select your university...</option>
                                    <option value="University of Colombo" <?php echo ($user['university'] === 'University of Colombo') ? 'selected' : ''; ?>>University of Colombo</option>
                                    <option value="University of Peradeniya" <?php echo ($user['university'] === 'University of Peradeniya') ? 'selected' : ''; ?>>University of Peradeniya</option>
                                    <option value="University of Sri Jayewardenepura" <?php echo ($user['university'] === 'University of Sri Jayewardenepura') ? 'selected' : ''; ?>>University of Sri Jayewardenepura</option>
                                    <option value="University of Kelaniya" <?php echo ($user['university'] === 'University of Kelaniya') ? 'selected' : ''; ?>>University of Kelaniya</option>
                                    <option value="University of Moratuwa" <?php echo ($user['university'] === 'University of Moratuwa') ? 'selected' : ''; ?>>University of Moratuwa</option>
                                    <option value="University of Jaffna" <?php echo ($user['university'] === 'University of Jaffna') ? 'selected' : ''; ?>>University of Jaffna</option>
                                    <option value="University of Ruhuna" <?php echo ($user['university'] === 'University of Ruhuna') ? 'selected' : ''; ?>>University of Ruhuna</option>
                                    <option value="Eastern University, Sri Lanka" <?php echo ($user['university'] === 'Eastern University, Sri Lanka') ? 'selected' : ''; ?>>Eastern University, Sri Lanka</option>
                                    <option value="South Eastern University of Sri Lanka" <?php echo ($user['university'] === 'South Eastern University of Sri Lanka') ? 'selected' : ''; ?>>South Eastern University of Sri Lanka</option>
                                    <option value="Rajarata University of Sri Lanka" <?php echo ($user['university'] === 'Rajarata University of Sri Lanka') ? 'selected' : ''; ?>>Rajarata University of Sri Lanka</option>
                                    <option value="Wayamba University of Sri Lanka" <?php echo ($user['university'] === 'Wayamba University of Sri Lanka') ? 'selected' : ''; ?>>Wayamba University of Sri Lanka</option>
                                    <option value="Sabaragamuwa University of Sri Lanka" <?php echo ($user['university'] === 'Sabaragamuwa University of Sri Lanka') ? 'selected' : ''; ?>>Sabaragamuwa University of Sri Lanka</option>
                                    <option value="Uva Wellassa University" <?php echo ($user['university'] === 'Uva Wellassa University') ? 'selected' : ''; ?>>Uva Wellassa University</option>
                                    <option value="University of the Visual and Performing Arts" <?php echo ($user['university'] === 'University of the Visual and Performing Arts') ? 'selected' : ''; ?>>University of the Visual and Performing Arts</option>
                                    <option value="Open University of Sri Lanka" <?php echo ($user['university'] === 'Open University of Sri Lanka') ? 'selected' : ''; ?>>Open University of Sri Lanka</option>
                                    <option value="Gampaha Wickramarachchi University of Indigenous Medicine" <?php echo ($user['university'] === 'Gampaha Wickramarachchi University of Indigenous Medicine') ? 'selected' : ''; ?>>Gampaha Wickramarachchi University of Indigenous Medicine</option>
                                    <option value="University of Vavuniya" <?php echo ($user['university'] === 'University of Vavuniya') ? 'selected' : ''; ?>>University of Vavuniya</option>
                                    <option value="SLIIT" <?php echo ($user['university'] === 'SLIIT') ? 'selected' : ''; ?>>SLIIT</option>
                                    <option value="NSBM Green University" <?php echo ($user['university'] === 'NSBM Green University') ? 'selected' : ''; ?>>NSBM Green University</option>
                                </select>
                            </div>

                            <!-- Department -->
                            <div class="col-md-6">
                                <label for="department" class="form-label form-label-premium">Department / Major</label>
                                <input type="text" name="department" id="department" class="form-control form-control-premium" placeholder="e.g. Computer Science" value="<?php echo htmlspecialchars($user['department']); ?>">
                            </div>

                            <!-- Biography -->
                            <div class="col-md-12">
                                <label for="bio" class="form-label form-label-premium">About Me (Bio)</label>
                                <textarea name="bio" id="bio" rows="4" class="form-control form-control-premium" placeholder="Tell other students about your background, interests, and academic year..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                            </div>

                            <!-- Skills Can Teach -->
                            <div class="col-md-6">
                                <label for="skills_teach" class="form-label form-label-premium">Skills Can Teach (Comma-separated)</label>
                                <input type="text" name="skills_teach" id="skills_teach" class="form-control form-control-premium" placeholder="e.g. PHP, JavaScript, CSS, HTML" value="<?php echo htmlspecialchars($user['skills_teach']); ?>">
                                <small class="text-muted small">Separate individual skills using commas.</small>
                            </div>

                            <!-- Skills Want to Learn -->
                            <div class="col-md-6">
                                <label for="skills_learn" class="form-label form-label-premium">Skills Want to Learn (Comma-separated)</label>
                                <input type="text" name="skills_learn" id="skills_learn" class="form-control form-control-premium" placeholder="e.g. UI/UX Design, Figma, Spanish" value="<?php echo htmlspecialchars($user['skills_learn']); ?>">
                                <small class="text-muted small">Separate individual skills using commas.</small>
                            </div>
                        </div>

                        <div class="mt-5 border-top pt-4 text-end">
                            <a href="dashboard.php" class="btn btn-premium-outline py-2 px-4 me-2">Cancel</a>
                            <button type="submit" class="btn btn-premium py-2 px-5">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>

                    </form>

                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
