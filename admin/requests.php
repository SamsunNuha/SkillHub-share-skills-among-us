<?php
// Admin Manage Contact Requests Page
$page_title = "Manage Requests";
$base_path = '../';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Route Guard - enforce admin login
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Handle Request Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_request') {
    $request_id = (int)$_POST['request_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contact_requests WHERE id = ?");
        $stmt->execute([$request_id]);
        setFlashMessage('success', 'Inquiry record deleted successfully from system logs.');
    } catch (PDOException $e) {
        setFlashMessage('danger', 'Failed to delete inquiry record: ' . htmlspecialchars($e->getMessage()));
    }
    header("Location: requests.php");
    exit;
}

// Fetch all inquiries from database
$requests = [];
try {
    $requests = $pdo->query("SELECT cr.*, s.title as skill_title 
                             FROM contact_requests cr
                             LEFT JOIN skills s ON cr.skill_id = s.id 
                             ORDER BY cr.created_at DESC")->fetchAll();
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
                    <a href="categories.php" class="list-group-item list-group-item-action bg-transparent text-white-50 border-0 py-2"><i class="bi bi-tags me-2"></i> Categories</a>
                    <a href="requests.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 active"><i class="bi bi-envelope me-2"></i> Inquiries</a>
                </div>
            </div>
        </div>

        <!-- Main Workspace Area -->
        <div class="col-lg-10">
            <!-- Flash Message -->
            <?php displayFlashMessage(); ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="fw-bold text-dark mb-1">Manage Inquiries & Requests</h1>
                    <p class="text-muted mb-0">Total of <strong><?php echo count($requests); ?></strong> requests stored in database.</p>
                </div>
            </div>

            <!-- Requests Accordion List -->
            <div class="card card-premium bg-white shadow-sm p-4">
                <?php if (empty($requests)): ?>
                    <div class="text-center py-5">
                        <div class="fs-1 text-muted"><i class="bi bi-envelope-open"></i></div>
                        <p class="text-muted mt-3">No contact inquiries or exchange requests exist in the database logs.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Sender</th>
                                    <th>Type</th>
                                    <th>Target Skill / Context</th>
                                    <th>Message Snippet</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $r): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($r['sender_name']); ?></div>
                                            <small class="text-muted"><a href="mailto:<?php echo htmlspecialchars($r['sender_email']); ?>"><?php echo htmlspecialchars($r['sender_email']); ?></a></small>
                                        </td>
                                        <td>
                                            <?php if ($r['skill_id'] === null): ?>
                                                <span class="badge bg-dark">General Support</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">Skill Swap Request</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($r['skill_id'] === null): ?>
                                                <span class="text-muted small">Contact Page Message</span>
                                            <?php else: ?>
                                                <a href="../skill-details.php?id=<?php echo $r['skill_id']; ?>" target="_blank" class="fw-semibold text-decoration-none small text-primary"><?php echo htmlspecialchars($r['skill_title']); ?></a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="text-muted small text-truncate" style="max-width: 250px;" title="<?php echo htmlspecialchars($r['message']); ?>">
                                                <?php echo htmlspecialchars($r['message']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($r['status'] === 'Pending'): ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php elseif ($r['status'] === 'Accepted'): ?>
                                                <span class="badge bg-success">Accepted</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Declined</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <!-- Accordion collapse button to view full content -->
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMsg<?php echo $r['id']; ?>" aria-expanded="false" aria-controls="collapseMsg<?php echo $r['id']; ?>" title="Read Message">
                                                    <i class="bi bi-chat-text"></i>
                                                </button>
                                                
                                                <form action="requests.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_request">
                                                    <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-confirm-message="Are you sure you want to delete this message log? This is permanent." title="Delete Record"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Full Message Collapse Row -->
                                    <tr class="collapse" id="collapseMsg<?php echo $r['id']; ?>">
                                        <td colspan="6" class="bg-light p-4">
                                            <div class="card p-3 shadow-sm border border-light">
                                                <h6 class="fw-bold mb-2">Full Inquiry Message:</h6>
                                                <p class="mb-0 text-muted small" style="white-space: pre-line;"><?php echo htmlspecialchars($r['message']); ?></p>
                                                <div class="mt-3 text-muted small">
                                                    Received on: <?php echo date("F d, Y - h:i A", strtotime($r['created_at'])); ?>
                                                </div>
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
