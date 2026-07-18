<?php
// index.php - Main Landing Page for SkillSwap
$page_title = "Home";
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch Statistics
$user_count = 0;
$skill_count = 0;
$category_count = 0;

try {
    $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $skill_count = $pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
    $category_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
} catch (PDOException $e) {
    // Fail silently or handle error
}

// Fetch Popular/Recent Categories
$categories = [];
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC LIMIT 6")->fetchAll();
} catch (PDOException $e) {
    // Fail silently
}

// Fetch Recent Skills
$recent_skills = [];
try {
    $recent_skills = $pdo->query("
        SELECT s.*, u.name as teacher_name, u.university as teacher_uni, c.name as category_name, c.icon as category_icon
        FROM skills s
        JOIN users u ON s.user_id = u.id
        JOIN categories c ON s.category_id = c.id
        ORDER BY s.created_at DESC
        LIMIT 4
    ")->fetchAll();
} catch (PDOException $e) {
    // Fail silently
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>


<!-- ===== HERO SECTION ===== -->
<section class="hero-section pb-0 pt-5">
    <div class="container position-relative" style="z-index:2;">
        <div class="row align-items-center g-5">
            <!-- Left Text -->
            <div class="col-lg-6 text-center text-lg-start">
                <span class="pill-badge mb-3 d-inline-flex align-items-center gap-2">
                    <i class="bi bi-mortarboard-fill text-primary"></i> Peer-to-Peer Learning
                </span>
                <h1 class="hero-heading mb-4 text-dark fw-bold" style="font-size: 3.5rem; line-height: 1.1;">
                    Exchange Skills<br>with <span class="text-primary">Classmates</span>
                </h1>
                <p class="hero-sub mb-5 text-muted" style="font-size: 1.1rem; max-width: 90%;">
                    skillHub is a collaborative university ecosystem where you can tutor others in your strengths and learn new skills from peers in return. <strong class="text-primary">No money needed.</strong>
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start align-items-center mb-5">
                    <a href="register.php" class="btn btn-primary px-4 py-3 fw-semibold rounded-pill d-flex align-items-center gap-2 shadow" style="background: #5b21b6; border: none;">
                        Get Started Free <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#how-it-works" class="btn btn-outline-secondary px-4 py-3 fw-semibold rounded-pill d-flex align-items-center gap-2" style="border: 1px solid #d1d5db; color: #4b5563;">
                        <i class="bi bi-play-circle-fill text-primary fs-5"></i> How It Works
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="row row-cols-2 row-cols-md-4 g-3 mt-4 text-start justify-content-center justify-content-lg-start">
                    <div class="col">
                        <div class="trust-item d-flex align-items-center gap-2 p-2 bg-white border rounded-pill w-100">
                            <div class="icon-box rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px; height:40px; background:#f3e8ff;">
                                <i class="bi bi-people-fill" style="color:#7e22ce;"></i>
                            </div>
                            <div class="lh-sm">
                                <span class="fw-bold text-dark d-block"><?php echo number_format(max(1200, $user_count)); ?>+</span>
                                <small class="text-muted" style="font-size: 0.75rem;">Students</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="trust-item d-flex align-items-center gap-2 p-2 bg-white border rounded-pill w-100">
                            <div class="icon-box rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px; height:40px; background:#dcfce7;">
                                <i class="bi bi-shield-fill-check" style="color:#15803d;"></i>
                            </div>
                            <div class="lh-sm">
                                <span class="fw-bold text-dark d-block">100%</span>
                                <small class="text-muted" style="font-size: 0.75rem;">Free to Join</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="trust-item d-flex align-items-center gap-2 p-2 bg-white border rounded-pill w-100">
                            <div class="icon-box rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px; height:40px; background:#fef3c7;">
                                <i class="bi bi-star-fill" style="color:#b45309;"></i>
                            </div>
                            <div class="lh-sm">
                                <span class="fw-bold text-dark d-block">4.9</span>
                                <small class="text-muted" style="font-size: 0.75rem;">User Rating</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="trust-item d-flex align-items-center gap-2 p-2 bg-white border rounded-pill w-100">
                            <div class="icon-box rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px; height:40px; background:#e0e7ff;">
                                <i class="bi bi-globe" style="color:#4338ca;"></i>
                            </div>
                            <div class="lh-sm">
                                <span class="fw-bold text-dark d-block" style="font-size: 0.85rem; white-space: nowrap;">All Universities</span>
                                <small class="text-muted" style="font-size: 0.75rem;">Students</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Image Area -->
            <div class="col-lg-6 text-center position-relative">
                <div class="hero-img-wrapper position-relative d-inline-block">
                    <img
                        src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=680"
                        alt="Students collaborating on SkillSwap"
                        class="img-fluid rounded-4 shadow-lg"
                        style="border: 8px solid white; object-fit: cover;"
                        referrerpolicy="no-referrer"
                    />
                    
                    <!-- Floating Card Top Right -->
                    <div class="position-absolute bg-white rounded-3 shadow p-2 d-flex align-items-center gap-3" style="top: -20px; right: -30px; animation: float 3s ease-in-out infinite alternate;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:45px; height:45px; background:#e0e7ff;">
                            <i class="bi bi-people-fill fs-5" style="color:#4338ca;"></i>
                        </div>
                        <div class="text-start pe-2">
                            <h6 class="mb-0 fw-bold text-dark">1,200+</h6>
                            <small class="text-muted" style="font-size: 0.75rem;">Active Students <i class="bi bi-graph-up-arrow text-success ms-1"></i></small>
                        </div>
                    </div>

                    <!-- Floating Card Bottom Left -->
                    <div class="position-absolute bg-white rounded-3 shadow p-2 d-flex align-items-center gap-3" style="bottom: 20px; left: -40px; animation: float 4s ease-in-out infinite alternate-reverse;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:45px; height:45px; background:#fef3c7;">
                            <i class="bi bi-star-fill fs-5" style="color:#b45309;"></i>
                        </div>
                        <div class="text-start pe-2">
                            <h6 class="mb-0 fw-bold text-dark">4.9/5</h6>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">User Rating</small>
                            <div class="text-warning" style="font-size: 0.7rem;"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                        </div>
                    </div>

                    <!-- Floating Card Bottom Right -->
                    <div class="position-absolute bg-white rounded-3 shadow p-2 d-flex align-items-center gap-3" style="bottom: -20px; right: -20px; animation: float 3.5s ease-in-out infinite alternate;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:45px; height:45px; background:#f3e8ff;">
                            <i class="bi bi-chat-dots-fill fs-5" style="color:#7e22ce;"></i>
                        </div>
                        <div class="text-start pe-2">
                            <h6 class="mb-0 fw-bold text-dark">New Learning Request</h6>
                            <small class="text-primary" style="font-size: 0.75rem;">2 new requests today</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="section-pad bg-transparent" id="how-it-works">
    <div class="container mt-5">
        <h3 class="text-center mb-5 fw-bold text-dark">How <span class="text-primary">skillHub</span> Works</h3>
        
        <div class="d-flex flex-column flex-lg-row justify-content-center align-items-center gap-4">
            <!-- Step 1 -->
            <div class="d-flex align-items-center gap-3 bg-white p-3 rounded-4 shadow-sm" style="flex: 1; max-width: 320px;">
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px; background:#f3e8ff; flex-shrink: 0;">
                    <i class="bi bi-search fs-4" style="color:#7e22ce;"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1 text-dark">1. Find Skills</h6>
                    <p class="text-muted mb-0" style="font-size: 0.8rem; line-height: 1.3;">Discover skills you want to learn or teach from your classmates.</p>
                </div>
            </div>
            
            <i class="bi bi-arrow-right text-muted fs-4 d-none d-lg-block"></i>
            <i class="bi bi-arrow-down text-muted fs-4 d-block d-lg-none"></i>

            <!-- Step 2 -->
            <div class="d-flex align-items-center gap-3 bg-white p-3 rounded-4 shadow-sm" style="flex: 1; max-width: 320px;">
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px; background:#e0e7ff; flex-shrink: 0;">
                    <i class="bi bi-calendar-event fs-4" style="color:#4338ca;"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1 text-dark">2. Connect & Learn</h6>
                    <p class="text-muted mb-0" style="font-size: 0.8rem; line-height: 1.3;">Connect with peers, schedule sessions and start learning.</p>
                </div>
            </div>

            <i class="bi bi-arrow-right text-muted fs-4 d-none d-lg-block"></i>
            <i class="bi bi-arrow-down text-muted fs-4 d-block d-lg-none"></i>

            <!-- Step 3 -->
            <div class="d-flex align-items-center gap-3 bg-white p-3 rounded-4 shadow-sm" style="flex: 1; max-width: 320px;">
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px; background:#dcfce7; flex-shrink: 0;">
                    <i class="bi bi-mortarboard fs-4" style="color:#15803d;"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1 text-dark">3. Share & Grow</h6>
                    <p class="text-muted mb-0" style="font-size: 0.8rem; line-height: 1.3;">Share your knowledge, help others and grow together.</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ===== POPULAR CATEGORIES ===== -->
<section class="section-pad" style="background: var(--bg-light);">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="pill-badge mb-3 d-inline-block">Browse Areas</span>
            <h2 class="section-title">Popular Swap Categories</h2>
            <p class="section-sub mx-auto">Explore different disciplines and find the subject that interests you.</p>
        </div>

        <div class="row g-4">
            <?php if (empty($categories)): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="category-card" onclick="window.location.href='browse-skills.php?category=1'">
                        <div class="category-icon"><i class="bi bi-code-slash"></i></div>
                        <h5 class="category-title">Programming</h5>
                        <p class="category-count">Python, Java, Web Dev &amp; more</p>
                        <div class="category-arrow"><i class="bi bi-arrow-right-short"></i></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="category-card" onclick="window.location.href='browse-skills.php?category=2'">
                        <div class="category-icon"><i class="bi bi-palette"></i></div>
                        <h5 class="category-title">Design</h5>
                        <p class="category-count">UI/UX, Figma, Illustration</p>
                        <div class="category-arrow"><i class="bi bi-arrow-right-short"></i></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="category-card" onclick="window.location.href='browse-skills.php?category=3'">
                        <div class="category-icon"><i class="bi bi-graph-up-arrow"></i></div>
                        <h5 class="category-title">Marketing</h5>
                        <p class="category-count">SEO, Social Media, Content</p>
                        <div class="category-arrow"><i class="bi bi-arrow-right-short"></i></div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card" onclick="window.location.href='browse-skills.php?category=<?php echo $cat['id']; ?>'">
                            <div class="category-icon"><i class="bi <?php echo htmlspecialchars($cat['icon'] ?: 'bi-bookmarks'); ?>"></i></div>
                            <h5 class="category-title"><?php echo htmlspecialchars($cat['name']); ?></h5>
                            <p class="category-count">Click to view listings</p>
                            <div class="category-arrow"><i class="bi bi-arrow-right-short"></i></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===== RECENTLY ADDED SKILLS ===== -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between mb-5 gap-3">
            <div>
                <span class="pill-badge mb-3 d-inline-block">New Additions</span>
                <h2 class="section-title mb-0">Recently Added Skills</h2>
            </div>
            <a href="browse-skills.php" class="btn btn-premium-outline">
                View All Listings <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if (empty($recent_skills)): ?>
            <div class="card card-premium p-5 text-center bg-white">
                <p class="text-muted mb-0">No skill listings available yet. Be the first to add one!</p>
                <div class="mt-3">
                    <a href="add-skill.php" class="btn btn-premium">Add a Skill</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($recent_skills as $skill): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="skill-card h-100">
                            <div class="skill-card-header">
                                <span class="badge badge-category">
                                    <i class="bi <?php echo htmlspecialchars($skill['category_icon'] ?: 'bi-tag-fill'); ?> me-1"></i>
                                    <?php echo htmlspecialchars($skill['category_name']); ?>
                                </span>
                                <span class="badge badge-level-<?php echo strtolower($skill['level']); ?>">
                                    <?php echo htmlspecialchars($skill['level']); ?>
                                </span>
                            </div>
                            <h5 class="skill-title"><?php echo htmlspecialchars($skill['title']); ?></h5>
                            <p class="skill-desc">
                                <?php echo htmlspecialchars(substr($skill['description'], 0, 120)) . (strlen($skill['description']) > 120 ? '...' : ''); ?>
                            </p>
                            <div class="skill-card-footer">
                                <div class="skill-author">
                                    <i class="bi bi-person-circle fs-3 text-primary"></i>
                                    <div>
                                        <div class="author-name"><?php echo htmlspecialchars($skill['teacher_name']); ?></div>
                                        <div class="author-uni"><?php echo htmlspecialchars($skill['teacher_uni']); ?></div>
                                    </div>
                                </div>
                                <a href="skill-details.php?id=<?php echo $skill['id']; ?>" class="btn btn-sm btn-premium px-3">
                                    Details <i class="bi bi-arrow-right-short"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===== CALL TO ACTION ===== -->
<section class="cta-section">
    <div class="cta-blob cta-blob-1"></div>
    <div class="cta-blob cta-blob-2"></div>
    <div class="container position-relative" style="z-index:2;">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <span class="pill-badge pill-badge--light mb-4 d-inline-block">Join the Community</span>
                <h2 class="cta-title mb-4">Ready to Share and Learn?</h2>
                <p class="cta-sub mb-5 mx-auto">
                    Join over 1,200 students already swapping skills. Share what you know, learn what you love – completely free.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="register.php" class="btn btn-light btn-lg px-5 fw-bold cta-btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Sign Up Free
                    </a>
                    <button id="copyPathBtn" class="btn btn-outline-light btn-lg px-5 cta-btn-outline">
                        <i class="bi bi-copy me-2"></i>Copy Path
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Scripts & Scroll Animation -->
<script>
    // Copy path button
    const copyBtn = document.getElementById('copyPathBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const path = window.location.pathname;
            navigator.clipboard.writeText(path).then(() => {
                alert('Path copied to clipboard: ' + path);
            }).catch(err => console.error('Failed to copy path:', err));
        });
    }

    // Scroll animation observer
    document.addEventListener("DOMContentLoaded", function() {
        const animEls = document.querySelectorAll('.step-card, .category-card, .skill-card, .stat-item');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('animate-in');
                    observer.unobserve(e.target);
                }
            });
        }, { threshold: 0.12 });
        animEls.forEach(el => observer.observe(el));
    });
</script>

<?php require_once 'includes/footer.php'; ?>
