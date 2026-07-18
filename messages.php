<?php
$page_title = "Messages";
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();
$user = getLoggedInUser($pdo);

// Fetch all conversations for current user with last message preview
$stmt = $pdo->prepare("
    SELECT c.id AS conv_id,
           IF(c.user1_id = :uid1, c.user2_id, c.user1_id) AS other_id,
           u.name AS other_name,
           u.profile_photo AS other_photo,
           u.university AS other_uni,
           (SELECT body FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_msg,
           (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_time,
           (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != :uid2 AND is_read = 0) AS unread_count,
           (SELECT cr.status FROM contact_requests cr JOIN skills s ON cr.skill_id = s.id WHERE ((cr.sender_email = :email1 AND s.user_id = IF(c.user1_id = :uid3, c.user2_id, c.user1_id)) OR (cr.sender_email = (SELECT email FROM users WHERE id = IF(c.user1_id = :uid4, c.user2_id, c.user1_id)) AND s.user_id = :uid5)) ORDER BY cr.created_at DESC LIMIT 1) AS req_status
    FROM conversations c
    JOIN users u ON u.id = IF(c.user1_id = :uid6, c.user2_id, c.user1_id)
    WHERE c.user1_id = :uid7 OR c.user2_id = :uid8
    ORDER BY last_time DESC
");
$stmt->execute([
    ':uid1' => $user['id'],
    ':uid2' => $user['id'],
    ':uid3' => $user['id'],
    ':uid4' => $user['id'],
    ':uid5' => $user['id'],
    ':uid6' => $user['id'],
    ':uid7' => $user['id'],
    ':uid8' => $user['id'],
    ':email1' => $user['email']
]);
$conversations = $stmt->fetchAll();

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>
<style>
.conv-list { max-height: 70vh; overflow-y: auto; }
.conv-item { cursor: pointer; transition: background .15s; border-left: 3px solid transparent; }
.conv-item:hover { background: #f0f4ff; border-left-color: #4f8ef7; }
.conv-item.unread { background: #eef3ff; border-left-color: #4f8ef7; }
.avatar-conv { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; }
.unread-badge { background: #4f8ef7; color: #fff; border-radius: 50%; font-size: 0.7rem; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; }
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h4 class="fw-bold mb-0"><i class="bi bi-chat-dots-fill text-primary me-2"></i>Messages</h4>
                    <span class="text-muted small"><?php echo count($conversations); ?> conversation<?php echo count($conversations) != 1 ? 's' : ''; ?></span>
                </div>

                <?php if (empty($conversations)): ?>
                    <div class="text-center py-5 px-4">
                        <div style="font-size:3.5rem; color:#c7d2fe;"><i class="bi bi-chat-square-text"></i></div>
                        <h5 class="fw-bold mt-3 text-dark">No messages yet</h5>
                        <p class="text-muted">Visit a user's profile and click <strong>Message</strong> to start a conversation.</p>
                        <a href="browse-skills.php" class="btn btn-primary mt-2">Browse Skills</a>
                    </div>
                <?php else: ?>
                    <div class="conv-list">
                        <?php foreach ($conversations as $c): ?>
                            <?php if ($c['req_status'] === 'Accepted'): ?>
                                <a href="chat.php?with=<?php echo $c['other_id']; ?>" class="text-decoration-none text-dark">
                                    <div class="conv-item d-flex align-items-center gap-3 px-4 py-3 <?php echo $c['unread_count'] > 0 ? 'unread' : ''; ?>">
                                        <img src="assets/uploads/<?php echo htmlspecialchars($c['other_photo'] ?: 'default-profile.png'); ?>" class="avatar-conv" alt="">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold <?php echo $c['unread_count'] > 0 ? 'text-primary' : ''; ?>"><?php echo htmlspecialchars($c['other_name']); ?></span>
                                                <span class="text-muted" style="font-size:0.75rem; white-space:nowrap;"><?php echo $c['last_time'] ? date('M d', strtotime($c['last_time'])) : ''; ?></span>
                                            </div>
                                            <div class="text-muted small text-truncate"><?php echo $c['last_msg'] ? htmlspecialchars($c['last_msg']) : '<em>Start a conversation</em>'; ?></div>
                                        </div>
                                        <?php if ($c['unread_count'] > 0): ?>
                                            <span class="unread-badge"><?php echo $c['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php else: ?>
                                <div class="conv-item d-flex align-items-center gap-3 px-4 py-3 text-muted">
                                    <span class="fw-bold">Pending request with <?php echo htmlspecialchars($c['other_name']); ?></span>
                                </div>
                            <?php endif; ?>
                            <hr class="my-0 mx-4" style="opacity:0.08;">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
