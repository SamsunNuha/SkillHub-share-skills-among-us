<?php
// Admin Edit User Page
require_once '../includes/auth.php';
requireAdmin();

$pdo = require_once __DIR__.'/../includes/db.php'; // Assuming db returns $pdo

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}
$user_id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form submission
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $name = trim($_POST['name']);
    $university = trim($_POST['university']);
    $department = trim($_POST['department']);
    $bio = trim($_POST['bio']);
    $skills_teach = trim($_POST['skills_teach']);
    $skills_learn = trim($_POST['skills_learn']);
    $profile_photo = trim($_POST['profile_photo']); // simple text field; file upload omitted for brevity

    try {
        $stmt = $pdo->prepare("UPDATE users SET email = ?, name = ?, university = ?, department = ?, bio = ?, skills_teach = ?, skills_learn = ?, profile_photo = ? WHERE id = ?");
        $stmt->execute([$email, $name, $university, $department, $bio, $skills_teach, $skills_learn, $profile_photo, $user_id]);
        setFlashMessage('success', 'User details updated successfully.');
        header('Location: users.php');
        exit;
    } catch (PDOException $e) {
        $error = 'Failed to update user: ' . htmlspecialchars($e->getMessage());
    }
}

// Fetch current user data for the form
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    setFlashMessage('danger', 'User not found.');
    header('Location: users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User – Admin Panel</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require_once '../includes/navbar.php'; ?>
<div class="container my-5">
    <h2 class="mb-4">Edit Student Details</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="edit_user.php?id=<?php echo $user_id; ?>" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">University</label>
            <input type="text" name="university" class="form-control" value="<?php echo htmlspecialchars($user['university']); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Department</label>
            <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($user['department']); ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($user['bio']); ?></textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Skills Teach (comma separated)</label>
            <input type="text" name="skills_teach" class="form-control" value="<?php echo htmlspecialchars($user['skills_teach']); ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Skills Learn (comma separated)</label>
            <input type="text" name="skills_learn" class="form-control" value="<?php echo htmlspecialchars($user['skills_learn']); ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Profile Photo Filename</label>
            <input type="text" name="profile_photo" class="form-control" value="<?php echo htmlspecialchars($user['profile_photo']); ?>">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="users.php" class="btn btn-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
<?php require_once '../includes/footer.php'; ?>
</body>
</html>
