<?php
// Navbar Template for SkillSwap
// Expects $base_path variable
$base_path = isset($base_path) ? $base_path : '';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$current_user = null;
$unread_count = 0;
if (isLoggedIn()) {
    $current_user = getLoggedInUser($pdo);
    // Count unread messages for nav badge
    try {
        $uid = $_SESSION['user_id'];
        $uc_stmt = $pdo->prepare("SELECT COUNT(*) FROM messages m
            JOIN conversations c ON c.id = m.conversation_id
            WHERE (c.user1_id = ? OR c.user2_id = ?)
              AND m.sender_id != ? AND m.is_read = 0");
        $uc_stmt->execute([$uid, $uid, $uid]);
        $unread_count = (int)$uc_stmt->fetchColumn();
    } catch (Exception $e) { $unread_count = 0; }
}
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand navbar-brand-custom d-flex align-items-center" href="<?php echo $base_path; ?>index.php">
            <img src="<?php echo $base_path; ?>assets/img/logo2.jpeg" alt="SkillSwap Logo" style="height: 75px; width: auto; object-fit: contain;">
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation Links -->
            <ul class="navbar-nav ms-lg-5 me-auto mb-2 mb-lg-0 align-items-lg-center gap-3">
                <?php if (isAdminLoggedIn()): ?>
                    <!-- Admin specific links -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>admin/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>admin/users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>admin/skills.php">Skills</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>admin/categories.php">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>admin/requests.php">Requests</a>
                    </li>
                <?php else: ?>
                    <!-- Student / Guest links -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>browse-skills.php">Browse Skills</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>contact.php">Contact</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="<?php echo $base_path; ?>add-skill.php">Add Skill</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom position-relative" href="<?php echo $base_path; ?>messages.php">
                                <i class="bi bi-chat-dots-fill"></i> Messages
                                <?php if ($unread_count > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!-- Profile & Call to Action Controls -->
            <div class="d-flex align-items-center gap-3 ms-auto">
                <?php if (isAdminLoggedIn()): ?>
                    <!-- Admin Logout -->
                    <span class="badge bg-danger">Admin Mode</span>
                    <a href="<?php echo $base_path; ?>logout.php" class="btn btn-premium-outline py-2 px-4">Logout</a>
                <?php elseif (isLoggedIn() && $current_user): ?>
                    <!-- Logged in Student Dropdown -->
                    <div class="dropdown">
                        <a class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle text-dark" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php 
                            $photo_src = $base_path . 'assets/uploads/' . ($current_user['profile_photo'] ? $current_user['profile_photo'] : 'default-profile.png');
                            ?>
                            <img src="<?php echo $photo_src; ?>" alt="Profile" class="avatar-nav">
                            <span class="fw-semibold d-none d-sm-inline"><?php echo htmlspecialchars($current_user['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3" aria-labelledby="profileDropdown" style="border-radius: 12px; min-width: 200px;">
                            <li><h6 class="dropdown-header text-muted">My Session</h6></li>
                            <li><a class="dropdown-item py-2" href="<?php echo $base_path; ?>dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item py-2" href="<?php echo $base_path; ?>profile.php?id=<?php echo $current_user['id']; ?>"><i class="bi bi-person me-2"></i> My Profile</a></li>
                            <li><a class="dropdown-item py-2 d-flex align-items-center justify-content-between" href="<?php echo $base_path; ?>messages.php"><span><i class="bi bi-chat-dots me-2"></i> Messages</span><?php if ($unread_count > 0): ?><span class="badge bg-danger rounded-pill"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                            <li><a class="dropdown-item py-2" href="<?php echo $base_path; ?>edit-profile.php"><i class="bi bi-gear me-2"></i> Edit Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="<?php echo $base_path; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Log Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest Actions -->
                    <a href="<?php echo $base_path; ?>login.php" class="btn btn-premium-outline py-2 px-4">Log In</a>
                    <a href="<?php echo $base_path; ?>register.php" class="btn btn-premium py-2 px-4">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
