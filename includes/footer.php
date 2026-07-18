<?php
// Footer Template for SkillSwap
// Expects $base_path variable
$base_path = isset($base_path) ? $base_path : '';
?>
<footer class="footer-custom">
    <div class="container">
        <div class="row g-4 mb-5">
            <!-- Brand & Info -->
            <div class="col-lg-4 col-md-6">
                <h5 class="footer-title d-flex align-items-center gap-2">
                    <img src="<?php echo $base_path; ?>assets/img/logo2.jpeg" alt="SkillHub Logo" style="height: 32px; width: auto; object-fit: contain;">
                    <span style="font-weight: 700; letter-spacing: -0.5px; text-transform: none;">SkillHub</span>
                </h5>
                <p class="mb-4 text-secondary-white">A peer-to-peer knowledge sharing and skill exchange platform built specifically for university students. Discover, collaborate, and grow together.</p>
                <div class="d-flex">
                    <a href="#" class="social-icon-btn"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon-btn"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-icon-btn"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon-btn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 col-6">
                <h5 class="footer-title">Platform</h5>
                <ul class="footer-links">
                    <li><a href="<?php echo $base_path; ?>index.php">Home</a></li>
                    <li><a href="<?php echo $base_path; ?>browse-skills.php">Browse Skills</a></li>
                    <li><a href="<?php echo $base_path; ?>about.php">About Us</a></li>
                    <li><a href="<?php echo $base_path; ?>contact.php">Contact</a></li>
                </ul>
            </div>
            <!-- Categories -->
            <div class="col-lg-3 col-md-6 col-6">
                <h5 class="footer-title">Popular Skills</h5>
                <ul class="footer-links">
                    <li><a href="<?php echo $base_path; ?>browse-skills.php">Programming & Tech</a></li>
                    <li><a href="<?php echo $base_path; ?>browse-skills.php">Graphic Design</a></li>
                    <li><a href="<?php echo $base_path; ?>browse-skills.php">Languages & Culture</a></li>
                    <li><a href="<?php echo $base_path; ?>browse-skills.php">Academics & Tutoring</a></li>
                </ul>
            </div>
            <!-- Support / Admin -->
            <div class="col-lg-3 col-md-6">
                <h5 class="footer-title">Join Our Community</h5>
                <p>Register with your university email address to search for teachers and list skills you can share.</p>
                <a href="<?php echo $base_path; ?>register.php" class="btn btn-premium btn-sm mt-2">Get Started</a>
                <div class="mt-4">
                    <a href="<?php echo $base_path; ?>admin/login.php" class="text-muted text-decoration-none small"><i class="bi bi-lock me-1"></i> Admin Access</a>
                </div>
            </div>
        </div>
        <!-- Copyright -->
        <hr style="border-color: #1e293b;">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">&copy; <?php echo date("Y"); ?> skillHub. All rights reserved. Made for University Mini Project.</p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item"><a href="#" class="text-muted text-decoration-none small">Terms of Service</a></li>
                    <li class="list-inline-item ms-3"><a href="#" class="text-muted text-decoration-none small">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo $base_path; ?>assets/js/main.js"></script>
</body>
</html>
