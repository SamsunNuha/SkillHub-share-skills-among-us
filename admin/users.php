<?php
// Admin Manage Users Page
$page_title = "Manage Users";
$base_path = '../';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Route Guard - enforce admin login
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Handle User Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $user_id = (int)$_POST['user_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        setFlashMessage('success', 'Student account deleted successfully from the platform.');
    } catch (PDOException $e) {
        setFlashMessage('danger', 'Failed to delete user: ' . htmlspecialchars($e->getMessage()));
    }
    header("Location: users.php");
    exit;
}

// Fetch all registered users
$users = [];
try {
    // Count user skills and incoming requests dynamically inside query
    $users = $pdo->query("SELECT u.*, 
                                 (SELECT COUNT(*) FROM skills WHERE user_id = u.id) as skill_count 
                          FROM users u 
                          ORDER BY u.created_at DESC")->fetchAll();
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
                    <a href="users.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 active"><i class="bi bi-people me-2"></i> Manage Users</a>
                    <a href="skills.php" class="list-group-item list-group-item-action bg-transparent text-white-50 border-0 py-2"><i class="bi bi-journals"></i> Manage Skills</a>
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
                    <h1 class="fw-bold text-dark mb-1">Manage Users</h1>
                    <p class="text-muted mb-0">Total of <strong><?php echo count($users); ?></strong> registered students.</p>
                </div>
                <a href="../register.php" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-2"></i> Register New Student
                </a>
            </div>

            <!-- Users List Table -->
            <div class="card card-premium bg-white shadow-sm p-4">
                <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <div class="fs-1 text-muted"><i class="bi bi-people"></i></div>
                        <p class="text-muted mt-3">No student records exist in the database yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>University & Dept</th>
                                    <th class="text-center">Skills Listed</th>
                                    <th>Date Joined</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="../assets/uploads/<?php echo htmlspecialchars($u['profile_photo'] ? $u['profile_photo'] : 'default-profile.png'); ?>" alt="Avatar" class="avatar-card">
                                                <div>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($u['name']); ?></div>
                                                    <small class="text-muted">@<?php echo htmlspecialchars($u['username']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($u['email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($u['email']); ?></a>
                                        </td>
                                        <td>
                                            <div class="text-dark small fw-semibold"><?php echo htmlspecialchars($u['university']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($u['department']); ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary rounded-pill"><?php echo $u['skill_count']; ?></span>
                                        </td>
                                        <td><?php echo date("M d, Y", strtotime($u['created_at'])); ?></td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="../profile.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="View Profile"><i class="bi bi-eye"></i></a>
                                                <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit User"><i class="bi bi-pencil"></i></a>
                                                <form action="users.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-confirm-message="Are you sure you want to delete this student account? This will cascade delete all their skills and swap requests!" title="Delete User"><i class="bi bi-trash"></i></button>
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
