<?php
// Administrator Dashboard
$page_title = "Admin Dashboard";
$base_path = '../';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Route Guard - enforce admin login
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Fetch aggregates
$count_users = 0;
$count_skills = 0;
$count_categories = 0;
$count_requests = 0;

try {
    $count_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $count_skills = $pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
    $count_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $count_requests = $pdo->query("SELECT COUNT(*) FROM contact_requests")->fetchColumn();
} catch (PDOException $e) {
    // Silence error
}

// Fetch recent 5 users
$recent_users = [];
try {
    $recent_users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    // Silence error
}

// Fetch recent 5 skills
$recent_skills = [];
try {
    $recent_skills = $pdo->query("SELECT s.*, u.name as teacher_name, c.name as category_name 
                                  FROM skills s
                                  JOIN users u ON s.user_id = u.id
                                  JOIN categories c ON s.category_id = c.id
                                  ORDER BY s.created_at DESC LIMIT 5")->fetchAll();
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
                    <a href="dashboard.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<a href="edit_admin.php" class="list-group-item list-group-item-action bg-transparent text-white-50 border-0 py-2"><i class="bi bi-gear me-2"></i> Edit Admin Profile</a>
<a href="users.php" class="list-group-item list-group-item-action bg-transparent text-white-50 border-0 py-2"><i class="bi bi-people me-2"></i> Manage Users</a>
                </div>
            </div>
        </div>

        <!-- Main Workspace Area -->
        <div class="col-lg-10">
            <!-- Flash Message -->
            <?php displayFlashMessage(); ?>

            <!-- Dashboard Welcome Headers -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-8">
                    <h1 class="fw-bold text-dark mb-1">Administrative Overview</h1>
                    <p class="text-muted">Manage system components, moderation listings, and check analytics counts.</p>
                </div>
            </div>

            <!-- Quantitative Stats Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card card-premium p-4 bg-primary text-white border-0 shadow-sm position-relative overflow-hidden">
                        <h6 class="text-white-50 small fw-bold text-uppercase mb-1">Total Users</h6>
                        <h2 class="fw-extrabold mb-0"><?php echo $count_users; ?></h2>
                        <i class="bi bi-people position-absolute text-white" style="font-size: 3.5rem; opacity: 0.15; right: 15px; bottom: 5px;"></i>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-premium p-4 bg-purple text-white border-0 shadow-sm position-relative overflow-hidden" style="background-color: var(--secondary-color) !important;">
                        <h6 class="text-white-50 small fw-bold text-uppercase mb-1">Skills Listed</h6>
                        <h2 class="fw-extrabold mb-0"><?php echo $count_skills; ?></h2>
                        <i class="bi bi-journals position-absolute text-white" style="font-size: 3.5rem; opacity: 0.15; right: 15px; bottom: 5px;"></i>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-premium p-4 bg-info text-white border-0 shadow-sm position-relative overflow-hidden" style="background-color: var(--accent-color) !important;">
                        <h6 class="text-white-50 small fw-bold text-uppercase mb-1">Categories</h6>
                        <h2 class="fw-extrabold mb-0"><?php echo $count_categories; ?></h2>
                        <i class="bi bi-tags position-absolute text-white" style="font-size: 3.5rem; opacity: 0.15; right: 15px; bottom: 5px;"></i>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-premium p-4 bg-success text-white border-0 shadow-sm position-relative overflow-hidden">
                        <h6 class="text-white-50 small fw-bold text-uppercase mb-1">Contact Inquiries</h6>
                        <h2 class="fw-extrabold mb-0"><?php echo $count_requests; ?></h2>
                        <i class="bi bi-envelope-open position-absolute text-white" style="font-size: 3.5rem; opacity: 0.15; right: 15px; bottom: 5px;"></i>
                    </div>
                </div>
            </div>

            <!-- Recent database logs / tables -->
            <div class="row g-4">
                <!-- Recent registered users -->
                <div class="col-md-6">
                    <div class="card card-premium p-4 bg-white shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-person-plus text-primary me-2"></i> Recent Registrations</h5>
                            <a href="users.php" class="btn btn-sm btn-link text-decoration-none">Manage All</a>
                        </div>

                        <?php if (empty($recent_users)): ?>
                            <p class="text-muted small my-3">No student records exist in the database.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" style="font-size: 0.85rem;">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>University</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_users as $u): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($u['name']); ?></div>
                                                    <span class="text-muted">@<?php echo htmlspecialchars($u['username']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($u['university']); ?></td>
                                                <td><?php echo date("M d, Y", strtotime($u['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent posted skills -->
                <div class="col-md-6">
                    <div class="card card-premium p-4 bg-white shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-journal-check text-success me-2"></i> Recent Skills Added</h5>
                            <a href="skills.php" class="btn btn-sm btn-link text-decoration-none">Manage All</a>
                        </div>

                        <?php if (empty($recent_skills)): ?>
                            <p class="text-muted small my-3">No skills are listed yet in the database.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" style="font-size: 0.85rem;">
                                    <thead>
                                        <tr>
                                            <th>Skill Title</th>
                                            <th>Teacher</th>
                                            <th>Category</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_skills as $s): ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo htmlspecialchars($s['title']); ?></td>
                                                <td><?php echo htmlspecialchars($s['teacher_name']); ?></td>
                                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($s['category_name']); ?></span></td>
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
