// SkillSwap Modern Javascript Handler

document.addEventListener('DOMContentLoaded', function() {
    // 1. Dynamic Navbar Scroll Effect
    const navbar = document.querySelector('.navbar-custom');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 30) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // 2. Client-side Password Confirmation Checker (for registration)
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        registerForm.addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                e.preventDefault();
                alert("Passwords do not match. Please double-check!");
                confirmPasswordInput.focus();
            }
        });
    }

    // 3. Image File Upload Preview & Validation
    const photoInput = document.getElementById('profile_photo');
    const photoPreview = document.getElementById('photo_preview');
    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Check size (2MB limit)
                if (file.size > 2 * 1024 * 1024) {
                    alert("Image file size is too large! Maximum limit is 2MB.");
                    this.value = '';
                    return;
                }
                
                // Check extension
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert("Invalid file format. Please upload JPG, PNG, or GIF files only.");
                    this.value = '';
                    return;
                }

                // Render Preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // 4. Confirm Deletes for CRUD Actions
    const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm-message') || "Are you sure you want to delete this item? This action is permanent.";
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // 5. Fade in elements smoothly on page load
    const animElements = document.querySelectorAll('.fade-in-on-load');
    animElements.forEach((el, index) => {
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
