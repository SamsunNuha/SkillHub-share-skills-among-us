<?php
// About Page for SkillSwap
$page_title = "About Us";
require_once 'includes/auth.php';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<!-- Header Banner -->
<section class="py-5 bg-light text-center" style="background: var(--gradient-soft) !important;">
    <div class="container my-4">
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-2 fw-semibold">Learn More</span>
        <h1 class="display-5 fw-bold">About SkillSwap</h1>
        <p class="text-muted lead mx-auto" style="max-width: 600px;">Understanding the ideology, goal, and system behind the peer learning platform.</p>
    </div>
</section>

<!-- Content Details -->
<div class="container my-5 py-3">
    <div class="row g-5">
        <!-- Mission & Vision -->
        <div class="col-lg-6">
            <h2 class="fw-bold mb-4 gradient-text">Our Mission</h2>
            <p class="text-muted">Our mission is to foster a collaborative university learning ecosystem. We want to remove the financial barrier of supplementary education by letting students trade their unique skills. By tutoring others in their fields of mastery, students not only help peers but also solidify their own understanding through instruction.</p>
            
            <h2 class="fw-bold mt-5 mb-4 gradient-text">Our Vision</h2>
            <p class="text-muted">We envision a campus environment where no skill goes unshared. Whether it is mastering a complex programming language, learning digital sketching, practicing a foreign tongue, or prepping for calculus exams—SkillSwap acts as the bridge connecting eager learners with student teachers.</p>
        </div>

        <!-- Visual Side Card -->
        <div class="col-lg-6">
            <div class="card card-premium p-4 p-md-5 bg-white h-100">
                <h3 class="fw-bold mb-4 text-primary"><i class="bi bi-gift-fill me-2"></i> Key Benefits</h3>
                
                <div class="d-flex gap-3 mb-4">
                    <div class="fs-3 text-secondary-color"><i class="bi bi-wallet2"></i></div>
                    <div>
                        <h5 class="fw-bold mb-1">100% Free Peer Learning</h5>
                        <p class="text-muted small mb-0">No credits, tokens, or money required. Just trade skills directly with classmates.</p>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-4">
                    <div class="fs-3 text-primary"><i class="bi bi-journals"></i></div>
                    <div>
                        <h5 class="fw-bold mb-1">Reinforce Your Strengths</h5>
                        <p class="text-muted small mb-0">Teaching is the best way to learn. Solidify your academic knowledge by explaining concepts to peers.</p>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-4">
                    <div class="fs-3 text-accent-color"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <h5 class="fw-bold mb-1">Interdisciplinary Connections</h5>
                        <p class="text-muted small mb-0">Break out of your departmental bubble and form study relations with students from other majors.</p>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <div class="fs-3 text-success"><i class="bi bi-patch-check"></i></div>
                    <div>
                        <h5 class="fw-bold mb-1">Enhance Your Portfolio</h5>
                        <p class="text-muted small mb-0">Add tutoring experience and peer leadership accomplishments straight to your professional resume.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
