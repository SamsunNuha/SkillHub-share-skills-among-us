<?php
// Skill Listing CRUD (Add & Edit) Page for SkillSwap
$page_title = "Manage Skill Listing";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Route Guard - enforce login
requireLogin();

$user = getLoggedInUser($pdo);

// Determine Mode (Add vs Edit)
$edit_mode = false;
$skill_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$skill = null;

if ($skill_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM skills WHERE id = ?");
        $stmt->execute([$skill_id]);
        $skill = $stmt->fetch();
        
        if ($skill) {
            // Verify ownership guard
            if ($skill['user_id'] != $user['id']) {
                setFlashMessage('danger', 'Unauthorized operation. You do not own this listing.');
                header("Location: dashboard.php");
                exit;
            }
            $edit_mode = true;
            $page_title = "Edit Skill Listing";
        }
    } catch (PDOException $e) {
        // Silence/Handle error
    }
}

// Fetch categories for selector
$categories = [];
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    // Silence error
}

$errors = [];

// Handle Form Submit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    $level = sanitize($_POST['level']);
    $availability = sanitize($_POST['availability']);

    // Validations
    if (empty($title)) $errors[] = "Skill Title is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if ($category_id <= 0) $errors[] = "Please select a valid Category.";
    if (!in_array($level, ['Beginner', 'Intermediate', 'Advanced'])) $errors[] = "Invalid skill level chosen.";
    if (empty($availability)) $errors[] = "Availability is required.";

    if (empty($errors)) {
        try {
            if ($edit_mode) {
                // Update statement
                $stmt = $pdo->prepare("UPDATE skills SET title = ?, description = ?, category_id = ?, level = ?, availability = ? WHERE id = ?");
                $stmt->execute([$title, $description, $category_id, $level, $availability, $skill_id]);
                setFlashMessage('success', 'Skill listing "' . htmlspecialchars($title) . '" updated successfully!');
            } else {
                // Insert statement
                $stmt = $pdo->prepare("INSERT INTO skills (user_id, title, description, category_id, level, availability) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user['id'], $title, $description, $category_id, $level, $availability]);
                setFlashMessage('success', 'Skill listing "' . htmlspecialchars($title) . '" posted successfully!');
            }
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Error saving skill record: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Setup form variables
$form_title = $edit_mode ? $skill['title'] : '';
$form_desc = $edit_mode ? $skill['description'] : '';
$form_category_id = $edit_mode ? $skill['category_id'] : 0;
$form_level = $edit_mode ? $skill['level'] : 'Beginner';
$form_availability = $edit_mode ? $skill['availability'] : '';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading fw-bold"><i class="bi bi-x-circle-fill"></i> Submission Errors:</h5>
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
                    <h3 class="mb-1"><i class="bi bi-journal-plus"></i> <?php echo $edit_mode ? "Edit Skill Listing" : "Share a New Skill"; ?></h3>
                    <p class="mb-0 small text-white-50"><?php echo $edit_mode ? "Update details of your existing listing" : "Offer your skills to help fellow students"; ?></p>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <form action="add-skill.php<?php echo $edit_mode ? '?id=' . $skill_id : ''; ?>" method="POST">
                        <div class="row g-4">
                            <!-- Skill Title -->
                            <div class="col-md-12">
                                <label for="title" class="form-label form-label-premium">Skill Title</label>
                                <input type="text" name="title" id="title" class="form-control form-control-premium" placeholder="e.g. Introduction to Web Dev with PHP" value="<?php echo htmlspecialchars($form_title); ?>" required>
                                <small class="text-muted small">Choose a descriptive, direct title summarizing what you will teach.</small>
                            </div>

                            <!-- Category -->
                            <div class="col-md-6">
                                <label for="category_id" class="form-label form-label-premium">Skill Category</label>
                                <select name="category_id" id="category_id" class="form-select form-control-premium" required>
                                    <option value="" disabled <?php echo !$edit_mode ? 'selected' : ''; ?>>-- Choose Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $form_category_id == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Level -->
                            <div class="col-md-6">
                                <label for="level" class="form-label form-label-premium">Difficulty Level</label>
                                <select name="level" id="level" class="form-select form-control-premium" required>
                                    <option value="Beginner" <?php echo $form_level === 'Beginner' ? 'selected' : ''; ?>>Beginner (No coding/graphics experience needed)</option>
                                    <option value="Intermediate" <?php echo $form_level === 'Intermediate' ? 'selected' : ''; ?>>Intermediate (Basic concepts understood)</option>
                                    <option value="Advanced" <?php echo $form_level === 'Advanced' ? 'selected' : ''; ?>>Advanced (Advanced topics, code refactoring)</option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="col-md-12">
                                <label for="description" class="form-label form-label-premium">Detailed Description</label>
                                <textarea name="description" id="description" rows="6" class="form-control form-control-premium" placeholder="Explain what topics you will cover, how you prefer to teach (on Zoom, in person, library sessions), and who this course is perfect for..." required><?php echo htmlspecialchars($form_desc); ?></textarea>
                            </div>

                            <!-- Availability -->
                            <div class="col-md-12">
                                <label for="availability" class="form-label form-label-premium">Availability (Days & Times)</label>
                                <input type="text" name="availability" id="availability" class="form-control form-control-premium" placeholder="e.g. Weekends (Saturday & Sunday afternoon) or Tuesdays 5-7pm" value="<?php echo htmlspecialchars($form_availability); ?>" required>
                                <small class="text-muted small">Be specific about the hours and days you are generally free to swap.</small>
                            </div>
                        </div>

                        <div class="mt-5 border-top pt-4 text-end">
                            <a href="dashboard.php" class="btn btn-premium-outline py-2 px-4 me-2">Cancel</a>
                            <button type="submit" class="btn btn-premium py-2 px-5">
                                <i class="bi bi-cloud-arrow-up me-1"></i> <?php echo $edit_mode ? "Update Listing" : "Post Skill Listing"; ?>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
