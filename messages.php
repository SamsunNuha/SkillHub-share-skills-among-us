<?php
$page_title = "Messages";
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();
$user = getLoggedInUser($pdo);

// ── 1. Fetch all accepted connections (from contact_requests) ──────────────
// Both directions: I sent the request, or I received it
$stmt = $pdo->prepare("
    SELECT DISTINCT
        u.id        AS other_id,
        u.name      AS other_name,
        u.username  AS other_username,
        u.profile_photo AS other_photo,
        u.university AS other_uni
    FROM contact_requests cr
    JOIN skills s ON cr.skill_id = s.id
    JOIN users u ON (
        CASE
            WHEN s.user_id = :me1 THEN u.email = cr.sender_email
            ELSE u.id = s.user_id
        END
    )
    WHERE cr.status = 'Accepted'
      AND (s.user_id = :me2 OR cr.sender_email = :email1)
      AND u.id != :me3
");
$stmt->execute([
    ':me1'    => $user['id'],
    ':me2'    => $user['id'],
    ':me3'    => $user['id'],
    ':email1' => $user['email'],
]);
$connections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── 2. For each connection, get conversation data (last msg, unread, time) ──
$chat_data = [];
foreach ($connections as $conn) {
    $other_id = $conn['other_id'];
    $u1 = min($user['id'], $other_id);
    $u2 = max($user['id'], $other_id);

    // Get or find conversation
    $cs = $pdo->prepare("SELECT id FROM conversations WHERE user1_id = ? AND user2_id = ?");
    $cs->execute([$u1, $u2]);
    $conv = $cs->fetch(PDO::FETCH_ASSOC);
    $conv_id = $conv ? $conv['id'] : null;

    $last_msg   = null;
    $last_time  = null;
    $unread     = 0;

    if ($conv_id) {
        $ms = $pdo->prepare("SELECT body, created_at FROM messages WHERE conversation_id = ? ORDER BY created_at DESC LIMIT 1");
        $ms->execute([$conv_id]);
        $last = $ms->fetch(PDO::FETCH_ASSOC);
        if ($last) {
            $last_msg  = $last['body'];
            $last_time = $last['created_at'];
        }
        $us = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE conversation_id = ? AND sender_id != ? AND is_read = 0");
        $us->execute([$conv_id, $user['id']]);
        $unread = (int)$us->fetchColumn();
    }

    $chat_data[] = array_merge($conn, [
        'last_msg'  => $last_msg,
        'last_time' => $last_time,
        'unread'    => $unread,
    ]);
}

// ── 3. Sort: conversations with messages first (by time), then no messages ──
usort($chat_data, function($a, $b) {
    if ($a['last_time'] && $b['last_time']) return strtotime($b['last_time']) - strtotime($a['last_time']);
    if ($a['last_time']) return -1;
    if ($b['last_time']) return 1;
    return 0;
});

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<style>
/* ── Messages Page ── */
.msg-page-wrap   { max-width: 700px; margin: 2.5rem auto; padding: 0 1rem; }
.msg-card        { background: #fff; border-radius: 20px; box-shadow: 0 4px 32px rgba(79,70,229,.10); overflow: hidden; }
.msg-card-header { padding: 1.4rem 1.8rem; border-bottom: 1.5px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
.msg-card-header h4 { font-weight: 800; font-size: 1.2rem; color: #0f172a; margin: 0; }
.msg-card-header span { font-size: 0.8rem; color: #94a3b8; }

.conn-list { padding: 0.5rem 0; }

.conn-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.95rem 1.8rem;
    text-decoration: none;
    color: inherit;
    border-left: 3px solid transparent;
    transition: background .15s, border-color .15s;
    position: relative;
}
.conn-item:hover { background: #f0f4ff; border-left-color: #4f46e5; }
.conn-item.has-unread { background: #eef3ff; border-left-color: #4f46e5; }

.conn-avatar {
    width: 52px; height: 52px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 2px solid #e2e8f0;
}
.conn-avatar-placeholder {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg,#4f46e5,#06b6d4);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; font-weight: 700; color: #fff;
    flex-shrink: 0;
}

.conn-info { flex: 1; min-width: 0; }
.conn-name { font-weight: 700; font-size: 0.95rem; color: #0f172a; }
.conn-name.unread-name { color: #4f46e5; }
.conn-uni  { font-size: 0.75rem; color: #94a3b8; margin-top: 1px; }
.conn-last { font-size: 0.82rem; color: #64748b; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 340px; }
.conn-last.no-msg { color: #a5b4fc; font-style: italic; }

.conn-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }
.conn-time { font-size: 0.72rem; color: #94a3b8; white-space: nowrap; }
.unread-dot {
    background: #4f46e5; color: #fff;
    border-radius: 999px; font-size: 0.68rem;
    font-weight: 700; min-width: 20px; height: 20px;
    display: inline-flex; align-items: center; justify-content: center;
    padding: 0 5px;
}

.conn-divider { margin: 0 1.8rem; border: none; border-top: 1px solid #f1f5f9; }

/* Empty state */
.empty-state { text-align: center; padding: 3.5rem 2rem; }
.empty-state .empty-icon { font-size: 3.5rem; color: #c7d2fe; }
.empty-state h5 { font-weight: 800; margin-top: 1rem; color: #1e293b; }
.empty-state p  { color: #64748b; font-size: 0.9rem; }
</style>

<div class="msg-page-wrap">
    <div class="msg-card">

        <!-- Header -->
        <div class="msg-card-header">
            <h4><i class="bi bi-chat-dots-fill text-primary me-2"></i>Messages</h4>
            <span><?php echo count($chat_data); ?> connection<?php echo count($chat_data) != 1 ? 's' : ''; ?></span>
        </div>

        <?php if (empty($chat_data)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-people"></i></div>
                <h5>No accepted connections yet</h5>
                <p>Browse skills and send a swap request.<br>Once accepted, your connections will appear here.</p>
                <a href="browse-skills.php" class="btn btn-primary mt-2 rounded-pill px-4">
                    <i class="bi bi-search me-1"></i> Browse Skills
                </a>
            </div>

        <?php else: ?>
            <div class="conn-list">
                <?php foreach ($chat_data as $i => $c): ?>
                    <?php
                        $photo = $c['other_photo'] ? 'assets/uploads/' . htmlspecialchars($c['other_photo']) : null;
                        $initial = strtoupper(substr($c['other_name'], 0, 1));
                        $has_unread = $c['unread'] > 0;
                    ?>

                    <a href="chat.php?with=<?php echo $c['other_id']; ?>"
                       class="conn-item <?php echo $has_unread ? 'has-unread' : ''; ?>">

                        <!-- Avatar -->
                        <?php if ($photo): ?>
                            <img src="<?php echo $photo; ?>" class="conn-avatar" alt="<?php echo htmlspecialchars($c['other_name']); ?>">
                        <?php else: ?>
                            <div class="conn-avatar-placeholder"><?php echo $initial; ?></div>
                        <?php endif; ?>

                        <!-- Info -->
                        <div class="conn-info">
                            <div class="conn-name <?php echo $has_unread ? 'unread-name' : ''; ?>">
                                <?php echo htmlspecialchars($c['other_name']); ?>
                            </div>
                            <?php if ($c['other_uni']): ?>
                                <div class="conn-uni">
                                    <i class="bi bi-building me-1"></i><?php echo htmlspecialchars($c['other_uni']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="conn-last <?php echo !$c['last_msg'] ? 'no-msg' : ''; ?>">
                                <?php if ($c['last_msg']): ?>
                                    <?php echo htmlspecialchars(substr($c['last_msg'], 0, 60)) . (strlen($c['last_msg']) > 60 ? '…' : ''); ?>
                                <?php else: ?>
                                    <i class="bi bi-chat-right me-1"></i>Start a conversation
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Meta (time + unread badge) -->
                        <div class="conn-meta">
                            <?php if ($c['last_time']): ?>
                                <span class="conn-time"><?php echo date('M d', strtotime($c['last_time'])); ?></span>
                            <?php endif; ?>
                            <?php if ($has_unread): ?>
                                <span class="unread-dot"><?php echo $c['unread']; ?></span>
                            <?php endif; ?>
                        </div>

                    </a>

                    <?php if ($i < count($chat_data) - 1): ?>
                        <hr class="conn-divider">
                    <?php endif; ?>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
