<?php
// Session and Security Helper Functions for SkillSwap

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Clean user input to prevent XSS attacks
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if a standard student user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if an administrator is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Route guard: redirect non-logged in users to login page
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_msg'] = ['type' => 'danger', 'text' => 'You must login to access this page.'];
        header("Location: login.php");
        exit;
    }
}

/**
 * Route guard: redirect unauthorized users from admin pages
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        $_SESSION['flash_msg'] = ['type' => 'danger', 'text' => 'Access denied. Administrator privileges required.'];
        header("Location: login.php"); // or admin/login.php depending on route
        exit;
    }
}

/**
 * Fetch database profile details of the logged in user
 */
function getLoggedInUser($pdo) {
    if (!isLoggedIn()) return null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Set flash alert message for the next page load
 */
function setFlashMessage($type, $text) {
    $_SESSION['flash_msg'] = ['type' => $type, 'text' => $text];
}

/**
 * Render and clear flash alert message from screen
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_msg'])) {
        $msg = $_SESSION['flash_msg'];
        echo '
        <div class="alert alert-' . $msg['type'] . ' alert-dismissible fade show" role="alert">
            ' . $msg['text'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        unset($_SESSION['flash_msg']);
    }
}
?>
