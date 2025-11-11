<?php
include '../config/config.php';

$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    if(empty($email)) {
        $error = "Please enter your email address";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        // Check if email exists in database
        $stmt = $pdo->prepare("SELECT id, username, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if($user) {
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store reset token in database
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$reset_token, $expires, $user['id']]);

            // Send reset email (for now, just show the reset link - in production, use email service)
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/public/reset_password.php?token=" . $reset_token;

            // For demo purposes, we'll show the reset link
            $message = "Password reset link generated successfully. In a real application, this would be sent to your email.<br><br>";
            $message .= "<strong>Demo Reset Link:</strong><br><a href='$reset_link' target='_blank'>$reset_link</a><br><br>";
            $message .= "<small class='text-muted'>This link will expire in 1 hour.</small>";
        } else {
            $error = "No account found with this email address";
        }
    }
}
?>

<?php include 'header.php'; ?>
<style>
.forgot-password-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #071658ff 0%, #3f0d70ff 100%);
    display: flex;
    align-items: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.forgot-password-page::before {
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

.forgot-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    position: relative;
    z-index: 1;
    max-width: 500px;
    width: 100%;
}

.forgot-header {
    background: linear-gradient(135deg, #4b1603ff 0%, #492904ff 100%);
    color: white;
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
}

.forgot-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px 20px 0 0;
}

.forgot-header i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

.forgot-header h4 {
    font-weight: 700;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
}

.forgot-header p {
    margin: 0;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.forgot-body {
    padding: 2.5rem;
}

.btn-reset {
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

.btn-reset::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-reset:hover::before {
    left: 100%;
}

.btn-reset:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
}

.forgot-links {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
}

.forgot-links a {
    color: #ff6b35;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.forgot-links a:hover {
    color: #e55a2b;
}

.alert {
    border-radius: 10px;
    border: none;
}

.demo-link {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 1rem;
    margin: 1rem 0;
    word-break: break-all;
}

.demo-link a {
    color: #007bff;
    text-decoration: none;
}

.demo-link a:hover {
    text-decoration: underline;
}
</style>

<div class="forgot-password-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="forgot-card">
                    <div class="forgot-header">
                        <i class="fas fa-key"></i>
                        <h4>Forgot Password</h4>
                        <p>Enter your email address and we'll send you a link to reset your password</p>
                    </div>
                    <div class="forgot-body">
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

                        <form method="POST" class="user-form">
                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope text-primary me-2"></i>Email Address
                                </label>
                                <input type="email" name="email" id="email" class="form-control form-control-lg"
                                       placeholder="Enter your email address" required
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                <div class="form-text">
                                    We'll send a password reset link to this email address.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-reset mb-3">
                                <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                            </button>
                        </form>

                        <div class="forgot-links">
                            <p class="mb-2">
                                <a href="login.php"><i class="fas fa-arrow-left me-1"></i>Back to Login</a>
                            </p>
                            <p class="mb-0">Don't have an account?
                                <a href="register.php">Register here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-focus on email field
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('email').focus();
});
</script>

<?php include 'footer.php'; ?>
