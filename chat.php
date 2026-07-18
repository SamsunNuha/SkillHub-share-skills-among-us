<?php
$page_title = "Chat";
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();
$user = getLoggedInUser($pdo);

$with_id = isset($_GET['with']) ? (int)$_GET['with'] : 0;
if (!$with_id || $with_id === $user['id']) { header('Location: messages.php'); exit; }

// Fetch other user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$with_id]);
$other = $stmt->fetch();
if (!$other) { header('Location: messages.php'); exit; }

// Verify that a skill swap request between the two users has been accepted
$stmt = $pdo->prepare("
    SELECT cr.status 
    FROM contact_requests cr 
    JOIN skills s ON cr.skill_id = s.id 
    WHERE ((cr.sender_email = :my_email AND s.user_id = :other_id) 
       OR (cr.sender_email = :other_email AND s.user_id = :my_id)) 
    ORDER BY cr.created_at DESC 
    LIMIT 1
");
$stmt->execute([
    ':my_email' => $user['email'],
    ':other_id' => $with_id,
    ':other_email' => $other['email'],
    ':my_id' => $user['id']
]);
$statusRow = $stmt->fetch();
if (!$statusRow || $statusRow['status'] !== 'Accepted') {
    setFlashMessage('danger', 'You can only chat after a skill swap request has been accepted.');
    header('Location: messages.php');
    exit;
}

// Find or create conversation (always store lower id as user1)
$u1 = min($user['id'], $with_id);
$u2 = max($user['id'], $with_id);

$stmt = $pdo->prepare("SELECT id FROM conversations WHERE user1_id = ? AND user2_id = ?");
$stmt->execute([$u1, $u2]);
$conv = $stmt->fetch();

if (!$conv) {
    $stmt = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
    $stmt->execute([$u1, $u2]);
    $conv_id = $pdo->lastInsertId();
} else {
    $conv_id = $conv['id'];
}

// AJAX endpoints for seamless real-time chat
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'fetch') {
        header('Content-Type: application/json');
        $stmt = $pdo->prepare("SELECT m.*, u.name AS sender_name, u.profile_photo AS sender_photo
                                FROM messages m
                                JOIN users u ON u.id = m.sender_id
                                WHERE m.conversation_id = ?
                                ORDER BY m.created_at ASC");
        $stmt->execute([$conv_id]);
        $all_msgs = $stmt->fetchAll();
        
        // Mark as read
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id = ? AND is_read = 0")
            ->execute([$conv_id, $with_id]);
            
        echo json_encode(['status' => 'success', 'messages' => $all_msgs]);
        exit;
    }
    if ($_GET['action'] === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        $body = isset($_POST['body']) ? trim($_POST['body']) : '';
        if (!empty($body)) {
            $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, body) VALUES (?, ?, ?)");
            $stmt->execute([$conv_id, $user['id'], $body]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Empty message']);
        }
        exit;
    }
}

// Handle new message POST (Standard fallback fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['body'])) {
    $body = trim($_POST['body']);
    if (!empty($body)) {
        $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, body) VALUES (?, ?, ?)");
        $stmt->execute([$conv_id, $user['id'], $body]);
    }
    header("Location: chat.php?with=$with_id");
    exit;
}

// Mark messages from other user as read
$pdo->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id = ? AND is_read = 0")
    ->execute([$conv_id, $with_id]);

// Fetch all messages
$stmt = $pdo->prepare("SELECT m.*, u.name AS sender_name, u.profile_photo AS sender_photo
                        FROM messages m
                        JOIN users u ON u.id = m.sender_id
                        WHERE m.conversation_id = ?
                        ORDER BY m.created_at ASC");
$stmt->execute([$conv_id]);
$messages = $stmt->fetchAll();

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>
<style>
.chat-wrapper { height: calc(100vh - 220px); min-height: 400px; display: flex; flex-direction: column; }
.chat-header { background: #fff; border-bottom: 1px solid #e9ecef; border-radius: 16px 16px 0 0; }
.chat-messages { flex: 1; overflow-y: auto; padding: 20px; background: #f8f9ff; display: flex; flex-direction: column; gap: 10px; }
.bubble-wrap { display: flex; align-items: flex-end; gap: 8px; }
.bubble-wrap.mine { flex-direction: row-reverse; }
.bubble { max-width: 70%; padding: 10px 14px; border-radius: 18px; font-size: 0.9rem; line-height: 1.45; word-break: break-word; }
.bubble.mine { background: linear-gradient(135deg, #4f8ef7, #6c63ff); color: #fff; border-bottom-right-radius: 4px; }
.bubble.other { background: #fff; color: #222; border: 1px solid #e0e7ff; border-bottom-left-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.bubble-avatar { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.bubble-time { font-size: 0.7rem; color: #aaa; margin-top: 2px; }
.bubble-wrap.mine .bubble-time { text-align: right; }
.chat-footer { background: #fff; border-top: 1px solid #e9ecef; padding: 12px 16px; border-radius: 0 0 16px 16px; }
.chat-input { border: 1.5px solid #d0d9ff; border-radius: 24px; padding: 10px 20px; resize: none; font-size: 0.92rem; transition: border .2s; outline: none; }
.chat-input:focus { border-color: #4f8ef7; box-shadow: 0 0 0 3px rgba(79,142,247,.12); }
.send-btn { border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #4f8ef7, #6c63ff); border: none; color: #fff; font-size: 1.1rem; flex-shrink: 0; cursor: pointer; transition: transform .15s, box-shadow .15s; }
.send-btn:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(79,142,247,.4); }
.date-divider { text-align: center; font-size: 0.75rem; color: #aaa; margin: 8px 0; }
.date-divider span { background: #f0f2ff; padding: 2px 12px; border-radius: 20px; }
</style>

<div class="container my-4" style="max-width: 760px;">
    <!-- Back link -->
    <a href="messages.php" class="text-decoration-none text-muted small mb-3 d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Messages
    </a>

    <div class="card shadow border-0 rounded-4 overflow-hidden chat-wrapper">
        <!-- Header -->
        <div class="chat-header d-flex align-items-center gap-3 px-4 py-3">
            <img src="assets/uploads/<?php echo htmlspecialchars($other['profile_photo'] ?: 'default-profile.png'); ?>"
                 style="width:44px;height:44px;border-radius:50%;object-fit:cover;" alt="">
            <div class="flex-grow-1">
                <div class="fw-bold text-dark"><?php echo htmlspecialchars($other['name']); ?></div>
                <div class="text-muted" style="font-size:0.8rem;"><?php echo htmlspecialchars($other['university'] ?: ''); ?> <?php echo $other['department'] ? '· ' . htmlspecialchars($other['department']) : ''; ?></div>
            </div>
            <a href="profile.php?id=<?php echo $other['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">View Profile</a>
        </div>

        <!-- Messages -->
        <div class="chat-messages" id="chatMessages">
            <?php if (empty($messages)): ?>
                <div class="text-center text-muted my-auto">
                    <i class="bi bi-chat-heart" style="font-size:2.5rem; color:#c7d2fe;"></i>
                    <p class="mt-2 small">Say hi to <?php echo htmlspecialchars($other['name']); ?>! 👋</p>
                </div>
            <?php else: ?>
                <?php
                $prev_date = '';
                foreach ($messages as $msg):
                    $mine = ($msg['sender_id'] == $user['id']);
                    $date_label = date('M d, Y', strtotime($msg['created_at']));
                    if ($date_label !== $prev_date):
                        $prev_date = $date_label;
                ?>
                    <div class="date-divider"><span><?php echo $date_label === date('M d, Y') ? 'Today' : $date_label; ?></span></div>
                <?php endif; ?>

                <div class="bubble-wrap <?php echo $mine ? 'mine' : ''; ?>">
                    <?php if (!$mine): ?>
                        <img src="assets/uploads/<?php echo htmlspecialchars($msg['sender_photo'] ?: 'default-profile.png'); ?>" class="bubble-avatar" alt="">
                    <?php endif; ?>
                    <div>
                        <div class="bubble <?php echo $mine ? 'mine' : 'other'; ?>"><?php echo nl2br(htmlspecialchars($msg['body'])); ?></div>
                        <div class="bubble-time"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Input -->
        <div class="chat-footer">
            <form method="POST" action="chat.php?with=<?php echo $with_id; ?>" class="d-flex align-items-end gap-2" id="chatForm">
                <textarea name="body" id="msgInput" class="form-control chat-input flex-grow-1" rows="1"
                          placeholder="Write a message…" required
                          oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"></textarea>
                <button type="submit" class="send-btn" title="Send"><i class="bi bi-send-fill"></i></button>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom
const chatBox = document.getElementById('chatMessages');
chatBox.scrollTop = chatBox.scrollHeight;

// Send on Enter (Shift+Enter = new line)
document.getElementById('msgInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (this.value.trim()) document.getElementById('chatForm').submit();
    }
});

// Auto-refresh every 8 seconds to get new messages
setTimeout(function poll() {
    fetch('chat.php?with=<?php echo $with_id; ?>&poll=1')
        .then(r => r.text())
        .then(() => { location.reload(); });
}, 8000);
</script>

<?php require_once 'includes/footer.php'; ?>
