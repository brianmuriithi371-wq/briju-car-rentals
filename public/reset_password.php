<?php
include '../config/config.php';

$message = '';
$error = '';
$token_valid = false;
$user_id = null;

if(isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify token
    $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if($user) {
        $current_time = date('Y-m-d H:i:s');
        if($current_time < $user['reset_expires']) {
            $token_valid = true;
            $user_id = $user['id'];
        } else {
            $error = "This reset link has expired. Please request a new one.";
        }
    } else {
        $error = "Invalid reset token.";
    }
} else {
    $error = "No reset token provided.";
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valid) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if(empty($password)) {
        $error = "Please enter a new password";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update password and clear reset token
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);

        $message = "Password reset successfully! You can now <a href='login.php'>login</a> with your new password.";
        $token_valid = false; // Prevent form from showing again
    }
}
?>

<?php include 'header.php'; ?>
<style>
.reset-password-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #071658ff 0%, #3f0d70ff 100%);
    display: flex;
    align-items: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.reset-password-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.reset-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    position: relative;
    z-index: 1;
    max-width: 500px;
    width: 100%;
}

.reset-header {
    background: linear-gradient(135deg, #4b1603ff 0%, #492904ff 100%);
    color: white;
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
}

.reset-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px 20px 0 0;
}

.reset-header i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

.reset-header h4 {
    font-weight: 700;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
}

.reset-header p {
    margin: 0;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.reset-body {
    padding: 2.5rem;
}

.btn-update {
    background: linear-gradient(135deg, #661e04ff 0%, #522d04ff 100%);
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

.btn-update::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-update:hover::before {
    left: 100%;
}

.btn-update:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
}

.reset-links {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
}

.reset-links a {
    color: #ff6b35;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.reset-links a:hover {
    color: #e55a2b;
}

.alert {
    border-radius: 10px;
    border: none;
}

.password-strength {
    margin-top: 0.5rem;
    font-size: 0.875rem;
}

.password-strength.weak {
    color: #dc3545;
}

.password-strength.medium {
    color: #ffc107;
}

.password-strength.strong {
    color: #28a745;
}
</style>

<div class="reset-password-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="reset-card">
                    <div class="reset-header">
                        <i class="fas fa-lock"></i>
                        <h4>Reset Password</h4>
                        <p>Enter your new password below</p>
                    </div>
                    <div class="reset-body">
                        <?php if(isset($error) && !empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($message) && !empty($message)): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($token_valid): ?>
                            <form method="POST" class="user-form">
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock text-primary me-2"></i>New Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control form-control-lg"
                                               placeholder="Enter new password" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye" id="passwordIcon"></i>
                                        </button>
                                    </div>
                                    <div id="passwordStrength" class="password-strength"></div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock text-primary me-2"></i>Confirm Password
                                    </label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control form-control-lg"
                                           placeholder="Confirm new password" required minlength="6">
                                    <div id="passwordMatch" class="form-text"></div>
                                </div>

                                <button type="submit" class="btn btn-update mb-3">
                                    <i class="fas fa-save me-2"></i>Update Password
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="reset-links">
                            <p class="mb-0">
                                <a href="login.php"><i class="fas fa-arrow-left me-1"></i>Back to Login</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.add('fa-eye');
    }
});

// Password strength checker
document.getElementById('password')?.addEventListener('input', function() {
    const password = this.value;
    const strengthIndicator = document.getElementById('passwordStrength');

    if(password.length === 0) {
        strengthIndicator.textContent = '';
        strengthIndicator.className = 'password-strength';
        return;
    }

    let strength = 0;
    let feedback = [];

    if(password.length >= 6) strength++;
    if(password.match(/[a-z]/)) strength++;
    if(password.match(/[A-Z]/)) strength++;
    if(password.match(/[0-9]/)) strength++;
    if(password.match(/[^A-Za-z0-9]/)) strength++;

    switch(strength) {
        case 0:
        case 1:
            strengthIndicator.textContent = 'Weak password';
            strengthIndicator.className = 'password-strength weak';
            break;
        case 2:
        case 3:
            strengthIndicator.textContent = 'Medium strength password';
            strengthIndicator.className = 'password-strength medium';
            break;
        case 4:
        case 5:
            strengthIndicator.textContent = 'Strong password';
            strengthIndicator.className = 'password-strength strong';
            break;
    }
});

// Password confirmation checker
document.getElementById('confirm_password')?.addEventListener('input', function() {
    const confirmPassword = this.value;
    const password = document.getElementById('password').value;
    const matchIndicator = document.getElementById('passwordMatch');

    if(confirmPassword.length === 0) {
        matchIndicator.textContent = '';
        return;
    }

    if(password === confirmPassword) {
        matchIndicator.innerHTML = '<span style="color: #28a745;">✓ Passwords match</span>';
    } else {
        matchIndicator.innerHTML = '<span style="color: #dc3545;">✗ Passwords do not match</span>';
    }
});

// Auto-focus on password field
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    if(passwordField) {
        passwordField.focus();
    }
});
</script>

<?php include 'footer.php'; ?>
