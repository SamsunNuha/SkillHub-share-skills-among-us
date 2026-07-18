<?php
// Admin Manage Skills Page
$page_title = "Manage Skills";
$base_path = '../';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Route Guard - enforce admin login
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Handle Skill Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_skill') {
    $skill_id = (int)$_POST['skill_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
        $stmt->execute([$skill_id]);
        setFlashMessage('success', 'Skill listing deleted successfully from the platform.');
    } catch (PDOException $e) {
        setFlashMessage('danger', 'Failed to delete skill listing: ' . htmlspecialchars($e->getMessage()));
    }
    header("Location: skills.php");
    exit;
}

// Fetch all listed skills
$skills = [];
try {
    $skills = $pdo->query("SELECT s.*, u.name as teacher_name, c.name as category_name,
                                 (SELECT COUNT(*) FROM contact_requests WHERE skill_id = s.id) as request_count 
                          FROM skills s 
                          JOIN users u ON s.user_id = u.id 
                          JOIN categories c ON s.category_id = c.id
                          ORDER BY s.created_at DESC")->fetchAll();
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
                    <a href="skills.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 active"><i class="bi bi-journals"></i> Manage Skills</a>
                    <a href="categories.php" class="list-group-item list-group-item-action bg-transparent text-white-50 border-0 py-2"><i class="bi bi-tags me-2"></i> Categories</a>
                    <a href="requests.php" class="list-group-item list-group-item-action bg-transparent text-white-50 border-0 py-2"><i class="bi bi-envelope me-2"></i> Inquiries</a>
                </div>
            </div>
        </div>

        <!-- Main Workspace Area -->
        <div class="col-lg-10">
            <!-- Flash Message -->
            <?php displayFlashMessage(); ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="fw-bold text-dark mb-1">Manage Skills</h1>
                    <p class="text-muted mb-0">Total of <strong><?php echo count($skills); ?></strong> listed skills.</p>
                </div>
            </div>

            <!-- Skills List Table -->
            <div class="card card-premium bg-white shadow-sm p-4">
                <?php if (empty($skills)): ?>
                    <div class="text-center py-5">
                        <div class="fs-1 text-muted"><i class="bi bi-journals"></i></div>
                        <p class="text-muted mt-3">No skills are listed yet in the database.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Skill Info</th>
                                    <th>Teacher Name</th>
                                    <th>Category</th>
                                    <th class="text-center">Swap Requests</th>
                                    <th>Date Listed</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($skills as $s): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($s['title']); ?></div>
                                            <span class="badge badge-custom badge-level-<?php echo strtolower($s['level']); ?>"><?php echo htmlspecialchars($s['level']); ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?php echo htmlspecialchars($s['teacher_name']); ?></div>
                                        </td>
                                        <td>
                                            <span class="text-muted small"><?php echo htmlspecialchars($s['category_name']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary rounded-pill"><?php echo $s['request_count']; ?></span>
                                        </td>
                                        <td><?php echo date("M d, Y", strtotime($s['created_at'])); ?></td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="../skill-details.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="View Listing"><i class="bi bi-eye"></i></a>
                                                
                                                <form action="skills.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_skill">
                                                    <input type="hidden" name="skill_id" value="<?php echo $s['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-confirm-message="Are you sure you want to delete this skill listing? This will delete all incoming student swap requests sent for this skill!" title="Delete Skill"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
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

<?php require_once '../includes/footer.php'; ?>
