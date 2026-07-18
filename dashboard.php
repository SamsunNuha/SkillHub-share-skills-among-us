<?php
// Student User Dashboard for SkillSwap
$page_title = "Dashboard";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Route Guard - enforce login
requireLogin();

// Fetch currently logged-in user profile details
$user = getLoggedInUser($pdo);

// Handle POST actions (Delete Skill or Update Request Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Action: Delete own skill
    if (isset($_POST['action']) && $_POST['action'] === 'delete_skill') {
        $skill_id = (int)$_POST['skill_id'];
        try {
            // Verify ownership first before executing delete
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM skills WHERE id = ? AND user_id = ?");
            $check_stmt->execute([$skill_id, $user['id']]);
            
            if ($check_stmt->fetchColumn() > 0) {
                $delete_stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
                $delete_stmt->execute([$skill_id]);
                setFlashMessage('success', 'Skill listing deleted successfully.');
            } else {
                setFlashMessage('danger', 'Unauthorized operation. You cannot delete this skill.');
            }
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Error executing delete: ' . htmlspecialchars($e->getMessage()));
        }
        header("Location: dashboard.php");
        exit;
    }

    // Action: Update received swap request status
    if (isset($_POST['action']) && $_POST['action'] === 'update_request') {
        $request_id = (int)$_POST['request_id'];
        $new_status = sanitize($_POST['status']);
        
        if (in_array($new_status, ['Accepted', 'Declined'])) {
            try {
                // Ensure request belongs to one of this user's skills
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_requests cr
                                             JOIN skills s ON cr.skill_id = s.id
                                             WHERE cr.id = ? AND s.user_id = ?");
                $check_stmt->execute([$request_id, $user['id']]);
                
                if ($check_stmt->fetchColumn() > 0) {
                    $update_stmt = $pdo->prepare("UPDATE contact_requests SET status = ? WHERE id = ?");
                    $update_stmt->execute([$new_status, $request_id]);
                    setFlashMessage('success', 'Request status updated to ' . $new_status . '.');
                } else {
                    setFlashMessage('danger', 'Unauthorized operation.');
                }
            } catch (PDOException $e) {
                setFlashMessage('danger', 'Error updating status: ' . htmlspecialchars($e->getMessage()));
            }
        }
        header("Location: dashboard.php");
        exit;
    }

    // Action: Send a reply to a swap request
    if (isset($_POST['action']) && $_POST['action'] === 'send_reply') {
        $request_id = (int)$_POST['request_id'];
        $reply_msg  = trim($_POST['reply_message']);
        
        // Verify there is an accepted contact request before allowing chat
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_requests cr
                JOIN skills s ON cr.skill_id = s.id
                WHERE cr.id = ? AND cr.status = 'Accepted' AND (cr.sender_email = ? OR s.user_id = ?)");
            $stmt->execute([$request_id, $user['email'], $user['id']]);
            
            if ((int)$stmt->fetchColumn() === 0) {
                setFlashMessage('danger', 'You can only chat after a skill swap request has been accepted.');
            } else if (!empty($reply_msg)) {
                $stmt = $pdo->prepare("INSERT INTO request_replies (request_id, sender_user_id, message) VALUES (?, ?, ?)");
                $stmt->execute([$request_id, $user['id'], $reply_msg]);
                setFlashMessage('success', 'Reply sent successfully!');
            }
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Failed to send reply.');
        }
        header("Location: dashboard.php");
        exit;
    }
}

// Fetch user listings & statistics
$skills_added = 0;
$requests_received = 0;
$my_skills = [];
$received_requests = [];
$sent_requests = [];

try {
    // 1. My Skills Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM skills WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $skills_added = $stmt->fetchColumn();

    // 2. Received Requests Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_requests cr
                           JOIN skills s ON cr.skill_id = s.id
                           WHERE s.user_id = ?");
    $stmt->execute([$user['id']]);
    $requests_received = $stmt->fetchColumn();

    // 3. Fetch My Skills list
    $stmt = $pdo->prepare("SELECT s.*, c.name as category_name 
                           FROM skills s
                           JOIN categories c ON s.category_id = c.id
                           WHERE s.user_id = ? 
                           ORDER BY s.created_at DESC");
    $stmt->execute([$user['id']]);
    $my_skills = $stmt->fetchAll();

    // 4. Fetch Received Inquiries list with skill owner id
    $stmt = $pdo->prepare("SELECT cr.*, s.title as skill_title, s.user_id as skill_owner_id
                           FROM contact_requests cr
                           JOIN skills s ON cr.skill_id = s.id
                           WHERE s.user_id = ?
                           ORDER BY cr.created_at DESC");
    $stmt->execute([$user['id']]);
    $received_requests = $stmt->fetchAll();

    // 5. Fetch Sent Requests (by email match)
    $stmt = $pdo->prepare("SELECT cr.*, s.title as skill_title, u.name as skill_owner_name
                           FROM contact_requests cr
                           JOIN skills s ON cr.skill_id = s.id
                           JOIN users u ON s.user_id = u.id
                           WHERE cr.sender_email = ?
                           ORDER BY cr.created_at DESC");
    $stmt->execute([$user['email']]);
    $sent_requests = $stmt->fetchAll();

    // 6. Fetch all replies for all relevant request IDs
    $all_request_ids = array_merge(
        array_column($received_requests, 'id'),
        array_column($sent_requests, 'id')
    );
    $replies_by_request = [];
    if (!empty($all_request_ids)) {
        $placeholders = implode(',', array_fill(0, count($all_request_ids), '?'));
        $stmt = $pdo->prepare("SELECT rr.*, u.name as replier_name, u.id as replier_id
                               FROM request_replies rr
                               JOIN users u ON rr.sender_user_id = u.id
                               WHERE rr.request_id IN ($placeholders)
                               ORDER BY rr.created_at ASC");
        $stmt->execute($all_request_ids);
        foreach ($stmt->fetchAll() as $reply) {
            $replies_by_request[$reply['request_id']][] = $reply;
        }
    }

} catch (PDOException $e) {
    // Silence/Handle error
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container my-5">
    
    <!-- Flash Messages -->
    <?php displayFlashMessage(); ?>

    <!-- Welcome & Profile Summary Card -->
    <div class="card card-premium p-4 p-md-5 bg-white shadow-sm mb-5">
        <div class="row align-items-center g-4">
            <div class="col-md-2 text-center text-md-start">
                <div class="avatar-wrapper">
                    <img src="assets/uploads/<?php echo htmlspecialchars($user['profile_photo'] ? $user['profile_photo'] : 'default-profile.png'); ?>" alt="Student Photo" class="avatar-profile shadow-md">
                </div>
            </div>
            <div class="col-md-7 text-center text-md-start">
                <h1 class="fw-bold mb-1">Hello, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p class="text-primary fw-semibold mb-2" style="font-size: 0.95rem;">
                    <i class="bi bi-building"></i> <?php echo htmlspecialchars($user['university'] ? $user['university'] : 'University Not Set'); ?> &middot; 
                    <span class="text-muted"><?php echo htmlspecialchars($user['department'] ? $user['department'] : 'Department Not Set'); ?></span>
                </p>
                <p class="text-muted small mb-0 leading-relaxed"><?php echo htmlspecialchars($user['bio'] ? $user['bio'] : "You haven't written a bio description yet. Add one to help classmates find you!"); ?></p>
            </div>
            <div class="col-md-3 text-center text-md-end d-flex flex-column gap-2">
                <a href="add-skill.php" class="btn btn-premium py-2 px-4 w-100 text-center"><i class="bi bi-plus-circle me-1"></i> Add New Skill</a>
                <a href="edit-profile.php" class="btn btn-premium-outline py-2 px-3 w-100 text-center"><i class="bi bi-pencil-square me-1"></i> Edit Profile</a>
            </div>
        </div>
    </div>

    <!-- Quick Stats row -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card card-premium p-4 text-center bg-white shadow-sm border-start border-4 border-primary">
                <div class="text-primary fs-1 mb-2"><i class="bi bi-journals"></i></div>
                <h5 class="text-muted small fw-bold text-uppercase mb-1">Skills Listed</h5>
                <h2 class="fw-extrabold text-dark mb-0"><?php echo $skills_added; ?></h2>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-premium p-4 text-center bg-white shadow-sm border-start border-4 border-secondary">
                <div class="text-secondary-color fs-1 mb-2"><i class="bi bi-chat-left-dots"></i></div>
                <h5 class="text-muted small fw-bold text-uppercase mb-1">Requests Received</h5>
                <h2 class="fw-extrabold text-dark mb-0"><?php echo $requests_received; ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-premium p-4 text-center bg-white shadow-sm border-start border-4 border-success">
                <div class="text-success fs-1 mb-2"><i class="bi bi-patch-check"></i></div>
                <h5 class="text-muted small fw-bold text-uppercase mb-1">Exchange Status</h5>
                <h2 class="fw-extrabold text-success mb-0" style="font-size: 1.5rem;">Active Member</h2>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <!-- Manage My Skills List -->
        <div class="col-lg-6">
            <div class="card card-premium p-4 bg-white shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-list-stars text-primary me-2"></i> My Skills Can Teach</h4>
                </div>

                <?php if (empty($my_skills)): ?>
                    <div class="text-center py-5">
                        <div class="fs-1 text-muted"><i class="bi bi-file-earmark-plus"></i></div>
                        <p class="text-muted mt-3">You haven't listed any skills yet. Let people know what you can teach!</p>
                        <a href="add-skill.php" class="btn btn-sm btn-premium mt-2">Create First Listing</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Skill</th>
                                    <th>Category</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_skills as $skill): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($skill['title']); ?></div>
                                            <span class="badge badge-custom badge-level-<?php echo strtolower($skill['level']); ?>"><?php echo htmlspecialchars($skill['level']); ?></span>
                                        </td>
                                        <td>
                                            <span class="small text-muted"><?php echo htmlspecialchars($skill['category_name']); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="add-skill.php?id=<?php echo $skill['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                                
                                                <form action="dashboard.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_skill">
                                                    <input type="hidden" name="skill_id" value="<?php echo $skill['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-confirm-message="Are you sure you want to delete this skill listing? This action is permanent." title="Delete"><i class="bi bi-trash"></i></button>
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

        <!-- Received Requests List -->
        <div class="col-lg-6">
            <div class="card card-premium p-4 bg-white shadow-sm h-100">
                <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-inbox text-secondary me-2"></i> Swap Requests Received</h4>
                
                <?php if (empty($received_requests)): ?>
                    <div class="text-center py-5">
                        <div class="fs-1 text-muted"><i class="bi bi-envelope-open"></i></div>
                        <p class="text-muted mt-3">No swap requests received yet. Listings are public, so keep an eye out!</p>
                    </div>
                <?php else: ?>
                    <div class="accordion" id="requestsAccordion">
                        <?php foreach ($received_requests as $index => $req): ?>
                            <div class="accordion-item mb-3 border rounded shadow-sm overflow-hidden">
                                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                    <button class="accordion-button collapsed bg-light text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false" aria-controls="collapse<?php echo $index; ?>">
                                        <div class="w-100 d-flex justify-content-between align-items-center pe-3">
                                            <div>
                                                <div class="fw-bold" style="font-size: 0.95rem;"><?php echo htmlspecialchars($req['sender_name']); ?></div>
                                                <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">For: <?php echo htmlspecialchars($req['skill_title']); ?></small>
                                            </div>
                                            <div>
                                                <?php if ($req['status'] === 'Pending'): ?>
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                <?php elseif ($req['status'] === 'Accepted'): ?>
                                                    <span class="badge bg-success">Accepted</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Declined</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#requestsAccordion">
                                    <div class="accordion-body bg-white p-4">
                                        <h6 class="fw-bold mb-2">Sender Information:</h6>
                                        <p class="small mb-3"><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($req['sender_email']); ?>"><?php echo htmlspecialchars($req['sender_email']); ?></a></p>

                                        <!-- Conversation Thread -->
                                        <h6 class="fw-bold mb-2"><i class="bi bi-chat-dots me-1 text-primary"></i> Conversation:</h6>
                                        <div class="mb-3" style="max-height:260px; overflow-y:auto;">
                                            <!-- Original message (sender bubble) -->
                                            <div class="d-flex mb-2">
                                                <div class="rounded-3 p-3 small" style="background:#f0f4ff; max-width:85%; border-left:3px solid #4f8ef7;">
                                                    <div class="fw-bold text-primary mb-1" style="font-size:0.8rem;"><?php echo htmlspecialchars($req['sender_name']); ?> <span class="text-muted fw-normal">(original)</span></div>
                                                    <div style="white-space:pre-wrap;"><?php echo htmlspecialchars($req['message']); ?></div>
                                                    <div class="text-muted mt-1" style="font-size:0.72rem;"><?php echo date('M d, Y H:i', strtotime($req['created_at'])); ?></div>
                                                </div>
                                            </div>
                                            <!-- Replies -->
                                            <?php if (!empty($replies_by_request[$req['id']])): ?>
                                                <?php foreach ($replies_by_request[$req['id']] as $reply): ?>
                                                    <?php $is_mine = ($reply['replier_id'] == $user['id']); ?>
                                                    <div class="d-flex mb-2 <?php echo $is_mine ? 'justify-content-end' : ''; ?>">
                                                        <div class="rounded-3 p-3 small" style="max-width:85%; <?php echo $is_mine ? 'background:#e8f5e9; border-right:3px solid #43a047;' : 'background:#fff3e0; border-left:3px solid #fb8c00;'; ?>">
                                                            <div class="fw-bold mb-1" style="font-size:0.8rem; color:<?php echo $is_mine ? '#2e7d32' : '#e65100'; ?>"><?php echo htmlspecialchars($reply['replier_name']); ?> <?php echo $is_mine ? '(you)' : ''; ?></div>
                                                            <div style="white-space:pre-wrap;"><?php echo htmlspecialchars($reply['message']); ?></div>
                                                            <div class="text-muted mt-1" style="font-size:0.72rem;"><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Accept/Decline for Pending -->
                                        <?php if ($req['status'] === 'Pending'): ?>
                                            <div class="d-flex gap-2 mb-3">
                                                <form action="dashboard.php" method="POST" class="w-50">
                                                    <input type="hidden" name="action" value="update_request">
                                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                                    <input type="hidden" name="status" value="Accepted">
                                                    <button type="submit" class="btn btn-success btn-sm w-100 py-2"><i class="bi bi-check-lg me-1"></i> Accept</button>
                                                </form>
                                                <form action="dashboard.php" method="POST" class="w-50">
                                                    <input type="hidden" name="action" value="update_request">
                                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                                    <input type="hidden" name="status" value="Declined">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 py-2"><i class="bi bi-x-lg me-1"></i> Decline</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center text-muted small mb-3">
                                                Inquiry marked as <strong><?php echo $req['status']; ?></strong> on <?php echo date("M d, Y", strtotime($req['created_at'])); ?>.
                                            </div>
                                            <?php if ($req['status'] === 'Accepted'): ?>
                                                <div class="d-grid">
                                                    <a href="chat.php?with=<?php echo $req['skill_owner_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-chat-dots-fill me-1"></i> Chat Now</a>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Reply Form -->
                                        <form action="dashboard.php" method="POST" class="mt-2">
                                            <input type="hidden" name="action" value="send_reply">
                                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                            <div class="input-group">
                                                <textarea name="reply_message" class="form-control form-control-sm" rows="2" placeholder="Type your reply..." required></textarea>
                                                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-send-fill"></i></button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sent Requests Section -->
    <?php if (!empty($sent_requests)): ?>
    <div class="card card-premium p-4 bg-white shadow-sm mt-5">
        <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-send text-primary me-2"></i> My Sent Requests</h4>
        <div class="accordion" id="sentAccordion">
            <?php foreach ($sent_requests as $si => $sreq): ?>
                <div class="accordion-item mb-3 border rounded shadow-sm overflow-hidden">
                    <h2 class="accordion-header" id="sheading<?php echo $si; ?>">
                        <button class="accordion-button collapsed bg-light text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#scollapse<?php echo $si; ?>">
                            <div class="w-100 d-flex justify-content-between align-items-center pe-3">
                                <div>
                                    <div class="fw-bold" style="font-size:0.95rem;">To: <?php echo htmlspecialchars($sreq['skill_owner_name']); ?></div>
                                    <small class="text-muted">For: <?php echo htmlspecialchars($sreq['skill_title']); ?></small>
                                </div>
                                <?php if ($sreq['status'] === 'Pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif ($sreq['status'] === 'Accepted'): ?>
                                    <span class="badge bg-success">Accepted</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Declined</span>
                                <?php endif; ?>
                            </div>
                        </button>
                    </h2>
                    <div id="scollapse<?php echo $si; ?>" class="accordion-collapse collapse" aria-labelledby="sheading<?php echo $si; ?>">
                        <div class="accordion-body bg-white p-4">
                            <!-- Conversation Thread -->
                            <h6 class="fw-bold mb-2"><i class="bi bi-chat-dots me-1 text-primary"></i> Conversation:</h6>
                            <div class="mb-3" style="max-height:260px; overflow-y:auto;">
                                <!-- Original message -->
                                <div class="d-flex justify-content-end mb-2">
                                    <div class="rounded-3 p-3 small" style="background:#e8f5e9; max-width:85%; border-right:3px solid #43a047;">
                                        <div class="fw-bold mb-1" style="font-size:0.8rem; color:#2e7d32;">You (original)</div>
                                        <div style="white-space:pre-wrap;"><?php echo htmlspecialchars($sreq['message']); ?></div>
                                        <div class="text-muted mt-1" style="font-size:0.72rem;"><?php echo date('M d, Y H:i', strtotime($sreq['created_at'])); ?></div>
                                    </div>
                                </div>
                                <!-- Replies -->
                                <?php if (!empty($replies_by_request[$sreq['id']])): ?>
                                    <?php foreach ($replies_by_request[$sreq['id']] as $reply): ?>
                                        <?php $is_mine = ($reply['replier_id'] == $user['id']); ?>
                                        <div class="d-flex mb-2 <?php echo $is_mine ? 'justify-content-end' : ''; ?>">
                                            <div class="rounded-3 p-3 small" style="max-width:85%; <?php echo $is_mine ? 'background:#e8f5e9; border-right:3px solid #43a047;' : 'background:#f0f4ff; border-left:3px solid #4f8ef7;'; ?>">
                                                <div class="fw-bold mb-1" style="font-size:0.8rem; color:<?php echo $is_mine ? '#2e7d32' : '#1565c0'; ?>"><?php echo htmlspecialchars($reply['replier_name']); ?> <?php echo $is_mine ? '(you)' : ''; ?></div>
                                                <div style="white-space:pre-wrap;"><?php echo htmlspecialchars($reply['message']); ?></div>
                                                <div class="text-muted mt-1" style="font-size:0.72rem;"><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <!-- Reply Form for sender -->
                            <form action="dashboard.php" method="POST" class="mt-2">
                                <input type="hidden" name="action" value="send_reply">
                                <input type="hidden" name="request_id" value="<?php echo $sreq['id']; ?>">
                                <div class="input-group">
                                    <textarea name="reply_message" class="form-control form-control-sm" rows="2" placeholder="Continue the conversation..." required></textarea>
                                    <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-send-fill"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
