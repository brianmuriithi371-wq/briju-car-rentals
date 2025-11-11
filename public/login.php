<?php
include '../config/config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<?php include 'header.php'; ?>
<style>
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.login-page {
    flex: 1;
    background: linear-gradient(135deg, #071658ff 0%, #3f0d70ff 100%);
    display: flex;
    align-items: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.login-page::before {
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

.car-section {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    height: 100%;
}

.car-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
}

.login-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    position: relative;
    z-index: 1;
    max-width: 450px;
    width: 100%;
}

.login-header {
    background: linear-gradient(135deg, #4b1603ff 0%, #492904ff 100%);
    color: white;
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
}

.login-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px 20px 0 0;
}

.login-header i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

.login-header h4 {
    font-weight: 700;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
}

.login-header p {
    margin: 0;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.login-body {
    padding: 2.5rem;
}

.form-floating {
    margin-bottom: 1.5rem;
}

.form-floating > label {
    padding: 1rem 0.75rem;
}

.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem 0.75rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #491603ff;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
}

.input-group .form-control {
    border-radius: 0 10px 10px 0;
}

.input-group .btn {
    border-radius: 10px 0 0 10px;
    border: 2px solid #e2e8f0;
    border-right: none;
}

.btn-login {
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

.btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-login:hover::before {
    left: 100%;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
}

.login-links {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
}

.login-links a {
    color: #ff6b35;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.login-links a:hover {
    color: #e55a2b;
}

.alert {
    border-radius: 10px;
    border: none;
}

@media (max-width: 576px) {
    .login-page {
        padding: 1rem;
    }

    .login-card {
        margin: 1rem;
    }

    .login-header {
        padding: 2rem 1.5rem;
    }

    .login-body {
        padding: 2rem 1.5rem;
    }
}
</style>

<div class="login-page">
    <div class="container-fluid h-100">
        <div class="row h-100 align-items-center">
            <div class="col-lg-6 col-md-6">
                <div class="login-card mx-auto" style="max-width: 500px;">
                    <div class="login-header">
                        <i class="fas fa-sign-in-alt"></i>
                        <h4>Welcome Back</h4>
                        <p>Sign in to your Briju Car Rental account</p>
                    </div>
                    <div class="login-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="user-form">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user text-primary me-2"></i>Username
                                </label>
                                <input type="text" name="username" id="username" class="form-control form-control-lg"
                                       placeholder="Enter your username" required
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock text-primary me-2"></i>Password
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control form-control-lg"
                                           placeholder="Enter your password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                                <label class="form-check-label" for="rememberMe">
                                    Remember me
                                </label>
                            </div>

                            <button type="submit" class="btn btn-login mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </form>

                        <div class="login-links">
                            <p class="mb-2">Don't have an account?
                                <a href="register.php">Register here</a>
                            </p>
                            <a href="#" class="text-muted small">Forgot your password?</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 d-none d-md-block">
                <div class="car-section">
                    <img src="../assets/imgs/AMG 1.png" alt="Car Rental" class="car-image">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
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

// Auto-focus on username field
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('username').focus();
});
</script>

<?php include 'footer.php'; ?>
