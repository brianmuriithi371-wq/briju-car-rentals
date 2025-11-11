<?php
include '../config/config.php';

// Add session start and CSRF protection
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add CSRF token validation
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Security token invalid. Please try again.";
    } else {
        // Validate inputs
        $username = trim($_POST['username']);
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $user_type = $_POST['user_type'];

        // Check terms agreement
        if(!isset($_POST['terms_agreement'])) {
            $error = "You must agree to the terms and conditions!";
        } elseif($check_stmt->fetch()) {
            $error = "Username or email already exists!";
        } elseif(!$email) {
            $error = "Invalid email address!";
        } else {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $password, $user_type, $full_name, $phone]);

                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } catch(PDOException $e) {
                $error = "Registration failed. Please try again.";
                // Log the actual error for debugging
                error_log("Registration error: " . $e->getMessage());
            }
        }
    }
}

// Generate CSRF token
if(empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php include 'header.php'; ?>
<style>
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.register-page {
    flex: 1;
    background: linear-gradient(135deg, #0b1e74ff 0%, #2e0755ff 100%);
    display: flex;
    align-items: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.register-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 30% 70%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 70% 30%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
    animation: float 8s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
}

.image-slider {
    position: relative;
    height: 100%;
    overflow: hidden;
}

.slider-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 1s ease-in-out;
}

.slider-image.active {
    opacity: 1;
}

.register-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    position: relative;
    z-index: 1;
    max-width: 600px;
    width: 100%;
}

.register-header {
    background: linear-gradient(135deg, #6e2207ff 0%, #5f3403ff 100%);
    color: white;
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
}

.register-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px 20px 0 0;
}

.register-header i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

.register-header h4 {
    font-weight: 700;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
}

.register-body {
    padding: 2.5rem;
}

.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.form-control:focus {
    border-color: #571a03ff;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
}

.form-select {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.form-select:focus {
    border-color: #471605ff;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
}

.btn-register {
    background: linear-gradient(135deg, #4e1804ff 0%, #5a3307ff 100%);
    border: none;
    border-radius: 10px;
    padding: 1rem;
    font-weight: 600;
    font-size: 1.1rem;
    width: 100%;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-register::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-register:hover::before {
    left: 100%;
}

.btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
}

.register-links {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
}

.register-links a {
    color: #5f1e06ff;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.register-links a:hover {
    color: #611b04ff;
}

.alert {
    border-radius: 10px;
    border: none;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.is-valid {
    border-color: #198754 !important;
}

.is-invalid {
    border-color: #dc3545 !important;
}

@media (max-width: 768px) {
    .register-page {
        padding: 1rem;
    }

    .register-card {
        margin: 1rem;
    }

    .register-header {
        padding: 2rem 1.5rem;
    }

    .register-body {
        padding: 2rem 1.5rem;
    }
}
</style>

<div class="register-page">
    <div class="container-fluid h-100">
        <div class="row h-100 align-items-center">
            <div class="col-lg-6 col-md-6 d-none d-md-block h-100">
                <div class="image-slider">
                    <img src="../assets/imgs/AMG 1.png" alt="Car 1" class="slider-image active">
                    <img src="../assets/imgs/benz s 650.jpg" alt="Car 2" class="slider-image">
                    <img src="../assets/imgs/defender.jpg" alt="Car 3" class="slider-image">
                    <img src="../assets/imgs/mazda cx5.jpg" alt="Car 4" class="slider-image">
                    <img src="../assets/imgs/nissan coupe.jpg" alt="Car 5" class="slider-image">
                    <img src="../assets/imgs/range rover.jpg" alt="Car 6" class="slider-image">
                    <img src="../assets/imgs/RAV4 LIMITED.jpg" alt="Car 7" class="slider-image">
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="register-card mx-auto" style="max-width: 500px;">
                    <div class="register-header">
                        <i class="fas fa-user-plus"></i>
                        <h4>Register for Briju Car Rental</h4>
                    </div>
                    <div class="register-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger fade-in">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">
                                        <i class="fas fa-user text-primary me-1"></i>Full Name
                                    </label>
                                    <input type="text" name="full_name" id="full_name" class="form-control"
                                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                           required pattern="[A-Za-z\s]{2,}">
                                    <div class="invalid-feedback">Please provide your full name (letters and spaces only, min 2 characters).</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-at text-primary me-1"></i>Username
                                    </label>
                                    <input type="text" name="username" id="username" class="form-control"
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                           required pattern="[A-Za-z0-9_]{3,}">
                                    <div class="invalid-feedback">Username must be at least 3 characters (letters, numbers, underscore only).</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope text-primary me-1"></i>Email
                                    </label>
                                    <input type="email" name="email" id="email" class="form-control"
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                           required>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone text-primary me-1"></i>Phone
                                    </label>
                                    <input type="tel" name="phone" id="phone" class="form-control"
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                           required>
                                    <div class="invalid-feedback">Please provide your phone number.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="user_type" class="form-label">
                                    <i class="fas fa-user-tag text-primary me-1"></i>User Type
                                </label>
                                <select name="user_type" id="user_type" class="form-select" required>
                                    <option value="">Select user type</option>
                                    <option value="client" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'client') ? 'selected' : ''; ?>>Client</option>
                                    <option value="owner" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'owner') ? 'selected' : ''; ?>>Car Owner</option>
                                </select>
                                <div class="invalid-feedback">Please select a user type.</div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock text-primary me-1"></i>Password
                                </label>
                                <input type="password" name="password" id="password" class="form-control" required minlength="6">
                                <div class="invalid-feedback">Password must be at least 6 characters long.</div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms_agreement" name="terms_agreement" required>
                                    <label class="form-check-label" for="terms_agreement">
                                        I agree to the <a href="#" id="terms-link" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                    </label>
                                    <div class="invalid-feedback">You must agree to the terms and conditions to register.</div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-register">
                                <i class="fas fa-user-plus me-2"></i>Register
                            </button>
                        </form>

                        <div class="register-links">
                            <p class="mb-0">Already have an account?
                                <a href="login.php">Login here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image slider
    let currentImageIndex = 0;
    const images = document.querySelectorAll('.slider-image');

    function showNextImage() {
        if (images.length > 0) {
            images[currentImageIndex].classList.remove('active');
            currentImageIndex = (currentImageIndex + 1) % images.length;
            images[currentImageIndex].classList.add('active');
        }
    }

    if (images.length > 0) {
        setInterval(showNextImage, 3000); // Reduced to 3 seconds for better UX
    }

    // Enhanced form validation
    const form = document.querySelector('form');
    const password = document.getElementById('password');
    const phone = document.getElementById('phone');

    // Phone number validation
    phone.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9+\-\s]/g, '');
    });

    // Password strength indicator
    password.addEventListener('input', function() {
        const strength = checkPasswordStrength(this.value);
        updatePasswordStrength(strength);
    });

    // Real-time username availability check
    const usernameInput = document.getElementById('username');
    usernameInput.addEventListener('blur', checkUsernameAvailability);

    form.addEventListener('submit', function (event) {
        if (!validateForm()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    function validateForm() {
        let isValid = true;

        // Custom password validation
        if (password.value.length < 6) {
            showError(password, 'Password must be at least 6 characters long');
            isValid = false;
        }

        // Phone validation
        if (!validatePhone(phone.value)) {
            showError(phone, 'Please enter a valid phone number');
            isValid = false;
        }

        return isValid;
    }

    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/\d/)) strength++;
        if (password.match(/[^a-zA-Z\d]/)) strength++;
        return strength;
    }

    function updatePasswordStrength(strength) {
        // Remove existing indicators
        const existingIndicator = document.getElementById('password-strength');
        if (existingIndicator) existingIndicator.remove();

        const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const strengthColors = ['#dc3545', '#ffc107', '#fd7e14', '#20c997', '#198754'];

        const indicator = document.createElement('div');
        indicator.id = 'password-strength';
        indicator.className = 'mt-1 small';
        indicator.innerHTML = `
            <div class="progress" style="height: 5px;">
                <div class="progress-bar" style="width: ${strength * 25}%; background-color: ${strengthColors[strength]}"></div>
            </div>
            <div class="text-muted">Strength: ${strengthText[strength]}</div>
        `;

        password.parentNode.appendChild(indicator);
    }

    function validatePhone(phone) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
        return phoneRegex.test(phone);
    }

    function showError(input, message) {
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = message;
        }
        input.classList.add('is-invalid');
    }

    async function checkUsernameAvailability() {
        const username = this.value.trim();
        if (username.length < 3) return;

        try {
            // You would typically make an AJAX call here
            // For now, we'll just simulate it
            const response = await fetch(`check_username.php?username=${encodeURIComponent(username)}`);
            const data = await response.json();

            if (!data.available) {
                showError(this, 'Username is already taken');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        } catch (error) {
            console.error('Error checking username:', error);
        }
    }
});
</script>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">
                    <i class="fas fa-file-contract me-2"></i>Terms and Conditions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="terms-content">
                    <h6 class="text-primary mb-3">1. Acceptance of Terms</h6>
                    <p>By accessing and using Briju Car Rental services, you accept and agree to be bound by the terms and provision of this agreement.</p>

                    <h6 class="text-primary mb-3">2. User Account</h6>
                    <ul>
                        <li>You must provide accurate and complete information during registration</li>
                        <li>You are responsible for maintaining the confidentiality of your account</li>
                        <li>You must be at least 18 years old to use our services</li>
                    </ul>

                    <h6 class="text-primary mb-3">3. Car Rental Terms</h6>
                    <ul>
                        <li>All rentals require valid driver's license and identification</li>
                        <li>Renter must meet minimum age requirements (typically 21+ for certain vehicles)</li>
                        <li>Insurance coverage is mandatory for all rentals</li>
                        <li>Fuel charges and mileage limits apply as specified</li>
                    </ul>

                    <h6 class="text-primary mb-3">4. Payment Terms</h6>
                    <ul>
                        <li>All payments must be made in advance</li>
                        <li>Late returns may incur additional charges</li>
                        <li>Security deposit may be required for certain vehicles</li>
                        <li>Refunds are processed according to our refund policy</li>
                    </ul>

                    <h6 class="text-primary mb-3">5. Vehicle Usage</h6>
                    <ul>
                        <li>Vehicles may only be used for lawful purposes</li>
                        <li>No smoking, drugs, or illegal substances in vehicles</li>
                        <li>Maximum passenger limits must be observed</li>
                        <li>Cross-border travel may require additional permissions</li>
                    </ul>

                    <h6 class="text-primary mb-3">6. Liability and Insurance</h6>
                    <ul>
                        <li>Renter is responsible for any damage caused by negligent driving</li>
                        <li>Theft or vandalism is covered by our insurance policies</li>
                        <li>Personal belongings are not covered by rental insurance</li>
                        <li>Additional liability coverage is available for purchase</li>
                    </ul>

                    <h6 class="text-primary mb-3">7. Cancellation Policy</h6>
                    <ul>
                        <li>Cancellations must be made 24 hours in advance for full refund</li>
                        <li>Late cancellations may incur cancellation fees</li>
                        <li>No-shows will be charged the full rental amount</li>
                    </ul>

                    <h6 class="text-primary mb-3">8. Privacy Policy</h6>
                    <ul>
                        <li>We collect personal information necessary for rental services</li>
                        <li>Your data is protected and not shared with third parties</li>
                        <li>Location data may be collected for GPS tracking purposes</li>
                        <li>You have the right to access and correct your personal information</li>
                    </ul>

                    <h6 class="text-primary mb-3">9. Termination</h6>
                    <p>We reserve the right to terminate your account and access to our services at any time for violation of these terms.</p>

                    <h6 class="text-primary mb-3">10. Governing Law</h6>
                    <p>These terms are governed by the laws of Kenya. Any disputes will be resolved through the appropriate legal channels.</p>

                    <h6 class="text-primary mb-3">11. Changes to Terms</h6>
                    <p>We reserve the right to modify these terms at any time. Continued use of our services constitutes acceptance of updated terms.</p>

                    <h6 class="text-primary mb-3">12. Contact Information</h6>
                    <p>For questions about these terms, please contact us at support@brijucar.com or call +254 XXX XXX XXX.</p>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong> By agreeing to these terms, you acknowledge that you have read, understood, and agree to be bound by all the terms and conditions outlined above.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>I Agree
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
