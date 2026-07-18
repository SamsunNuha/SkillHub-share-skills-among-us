<?php
// Browse Skills Page for SkillSwap
$page_title = "Browse Skills";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch all categories for filter sidebar
$categories = [];
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    // Silence error
}

// Get filter inputs
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build query
$query = "SELECT s.*, u.name as teacher_name, u.university as teacher_uni, c.name as category_name, c.icon as category_icon
          FROM skills s
          JOIN users u ON s.user_id = u.id
          JOIN categories c ON s.category_id = c.id
          WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (s.title LIKE ? OR s.description LIKE ? OR u.name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($category_filter > 0) {
    $query .= " AND s.category_id = ?";
    $params[] = $category_filter;
}

$query .= " ORDER BY s.created_at DESC";

// Execute search query
$skills = [];
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $skills = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching skills database: " . $e->getMessage();
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<!-- Header Banner -->
<section class="py-5 bg-light text-center" style="background: var(--gradient-soft) !important;">
    <div class="container my-3">
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-2 fw-semibold">Directory</span>
        <h1 class="display-5 fw-bold">Browse Swap Skills</h1>
        <p class="text-muted lead mx-auto" style="max-width: 600px;">Search through listings to find students to learn from and share with.</p>
    </div>
</section>

<div class="container my-5">
    <div class="row g-4">
        <!-- Search & Filter Sidebar -->
        <div class="col-lg-3">
            <div class="card card-premium p-4 bg-white sticky-lg-top" style="top: 100px; z-index: 10;">
                <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-funnel text-primary me-2"></i> Filter Skills</h4>
                
                <form action="browse-skills.php" method="GET">
                    <!-- Text Search -->
                    <div class="mb-4">
                        <label for="search" class="form-label form-label-premium">Keywords</label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="form-control form-control-premium" placeholder="e.g. PHP, Figma" value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-premium px-3"><i class="bi bi-search"></i></button>
                        </div>
                    </div>

                    <!-- Category Selector -->
                    <div class="mb-4">
                        <label class="form-label form-label-premium">Categories</label>
                        <div class="list-group list-group-flush">
                            <a href="browse-skills.php?search=<?php echo urlencode($search); ?>" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center py-2 <?php echo $category_filter === 0 ? 'text-primary fw-bold' : 'text-muted'; ?>">
                                <span><i class="bi bi-grid-fill me-2"></i> All Categories</span>
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="browse-skills.php?category=<?php echo $cat['id']; ?>&search=<?php echo urlencode($search); ?>" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center py-2 <?php echo $category_filter === (int)$cat['id'] ? 'text-primary fw-bold' : 'text-muted'; ?>">
                                    <span><i class="bi <?php echo htmlspecialchars($cat['icon']); ?> me-2"></i> <?php echo htmlspecialchars($cat['name']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Reset Button -->
                    <?php if (!empty($search) || $category_filter > 0): ?>
                        <a href="browse-skills.php" class="btn btn-premium-outline w-100 mt-2 py-2">
                            <i class="bi bi-x-circle me-1"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Main Skills Listings Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted mb-0">Found <strong><?php echo count($skills); ?></strong> listings matching your criteria</p>
            </div>

            <?php if (empty($skills)): ?>
                <!-- Empty State -->
                <div class="card card-premium p-5 text-center bg-white">
                    <div class="display-1 text-muted"><i class="bi bi-search-heart"></i></div>
                    <h3 class="fw-bold mt-3">No Skills Found</h3>
                    <p class="text-muted mx-auto" style="max-width: 450px;">We couldn't find any skills matching your search parameters. Try clearing the filters or search for something else!</p>
                    <div class="mt-3">
                        <a href="browse-skills.php" class="btn btn-premium"><i class="bi bi-arrow-counterclockwise me-1"></i> View All Listings</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Grid of Skills Cards -->
                <div class="row g-4">
                    <?php foreach ($skills as $skill): ?>
                        <div class="col-md-6">
                            <div class="card card-premium h-100 d-flex flex-column justify-content-between p-4 bg-white">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge badge-custom badge-category">
                                            <i class="bi <?php echo htmlspecialchars($skill['category_icon']); ?> me-1"></i> <?php echo htmlspecialchars($skill['category_name']); ?>
                                        </span>
                                        <span class="badge badge-custom badge-level-<?php echo strtolower($skill['level']); ?>"><?php echo htmlspecialchars($skill['level']); ?></span>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($skill['title']); ?></h4>
                                    <p class="text-muted small mb-4"><?php echo htmlspecialchars(substr($skill['description'], 0, 150)) . (strlen($skill['description']) > 150 ? '...' : ''); ?></p>
                                </div>
                                
                                <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                                    <!-- Teacher Avatar Info -->
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-circle text-primary fs-3"></i>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.85rem;"><?php echo htmlspecialchars($skill['teacher_name']); ?></h6>
                                            <small class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($skill['teacher_uni']); ?></small>
                                        </div>
                                    </div>
                                    <!-- Action Button -->
                                    <a href="skill-details.php?id=<?php echo $skill['id']; ?>" class="btn btn-sm btn-premium py-2 px-3">
                                        View Details <i class="bi bi-arrow-right-short"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
