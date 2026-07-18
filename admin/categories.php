<?php
// Admin Manage Categories Page
$page_title = "Manage Categories";
$base_path = '../';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Route Guard - enforce admin login
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

$errors = [];

// Handle Actions (Add or Delete Category)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Action: Add new category
    if (isset($_POST['action']) && $_POST['action'] === 'add_category') {
        $name = sanitize($_POST['name']);
        $icon = sanitize($_POST['icon']);
        
        if (empty($name)) {
            $errors[] = "Category name is required.";
        }
        if (empty($icon)) {
            $icon = 'bi-code-slash'; // Default icon
        }

        if (empty($errors)) {
            try {
                // Check duplicate
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
                $check_stmt->execute([$name]);
                
                if ($check_stmt->fetchColumn() > 0) {
                    $errors[] = "A category with the name '" . htmlspecialchars($name) . "' already exists.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, icon) VALUES (?, ?)");
                    $stmt->execute([$name, $icon]);
                    setFlashMessage('success', 'Category "' . htmlspecialchars($name) . '" added successfully!');
                    header("Location: categories.php");
                    exit;
                }
            } catch (PDOException $e) {
                $errors[] = "Database insert error: " . htmlspecialchars($e->getMessage());
            }
        }
    }

    // Action: Delete category
    if (isset($_POST['action']) && $_POST['action'] === 'delete_category') {
        $cat_id = (int)$_POST['category_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$cat_id]);
            setFlashMessage('success', 'Category deleted successfully.');
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Failed to delete category: ' . htmlspecialchars($e->getMessage()));
        }
        header("Location: categories.php");
        exit;
    }
}

// Fetch all categories with skill counts
$categories = [];
try {
    $categories = $pdo->query("SELECT c.*, 
                                      (SELECT COUNT(*) FROM skills WHERE category_id = c.id) as skill_count 
                               FROM categories c 
                               ORDER BY c.name ASC")->fetchAll();
} catch (PDOException $e) {
    // Silence error
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container-fluid my-5">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-2 mb-4">
            <div class="card card-premium p-3 bg-dark text-white border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 text-center py-3">
                    <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-shield-check"></i> System Management</h5>
                </div>
                <div class="list-group list-group-flush bg-transparent">
                    <a href="dashboard.php" class="list-group-item list-group-item-action bg-transparent text-white-50 border-0 py-2"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <a href="users.php" class="list-group-item list-group-item-action bg-transparent text-white-50 border-0 py-2"><i class="bi bi-people me-2"></i> Manage Users</a>
                    <a href="skills.php" class="list-group-item list-group-item-action bg-transparent text-white-50 border-0 py-2"><i class="bi bi-journals"></i> Manage Skills</a>
                    <a href="categories.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 active"><i class="bi bi-tags me-2"></i> Categories</a>
                    <a href="requests.php" class="list-group-item list-group-item-action bg-transparent text-white-50 border-0 py-2"><i class="bi bi-envelope me-2"></i> Inquiries</a>
                </div>
            </div>
        </div>

        <!-- Main Workspace Area -->
        <div class="col-lg-10">
            <!-- Flash Message -->
            <?php displayFlashMessage(); ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo $err; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="fw-bold text-dark mb-1">Manage Categories</h1>
                    <p class="text-muted">Control the categorization topics students can file their skills under.</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Add New Category Form -->
                <div class="col-md-4">
                    <div class="card card-premium bg-white p-4 shadow-sm">
                        <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-plus-circle text-primary me-2"></i> New Category</h4>
                        
                        <form action="categories.php" method="POST">
                            <input type="hidden" name="action" value="add_category">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label form-label-premium">Category Name</label>
                                <input type="text" name="name" id="name" class="form-control form-control-premium" placeholder="e.g. Cooking & Culinary" required>
                            </div>

                            <div class="mb-3">
                                <label for="icon" class="form-label form-label-premium">Bootstrap Icon Class</label>
                                <input type="text" name="icon" id="icon" class="form-control form-control-premium" placeholder="e.g. bi-egg-fried" value="bi-code-slash">
                                <small class="text-muted small">Provide the class name from <a href="https://icons.getbootstrap.com/" target="_blank" class="text-decoration-none">Bootstrap Icons</a>.</small>
                            </div>

                            <button type="submit" class="btn btn-premium w-100 py-3 mt-2">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Add Category
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Categories List Table -->
                <div class="col-md-8">
                    <div class="card card-premium bg-white shadow-sm p-4 h-100">
                        <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-tags text-secondary me-2"></i> Active Categories</h4>
                        
                        <?php if (empty($categories)): ?>
                            <p class="text-muted small py-4">No categories configured in the database.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Icon</th>
                                            <th>Category Name</th>
                                            <th class="text-center">Skills Count</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td class="fs-4 text-primary">
                                                    <i class="bi <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                                                </td>
                                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($cat['name']); ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark border rounded-pill px-3"><?php echo $cat['skill_count']; ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <form action="categories.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="delete_category">
                                                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-confirm-message="Are you sure you want to delete this category? Deleting a category will cascade delete all listed skills and requests registered under it!" title="Delete Category"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
