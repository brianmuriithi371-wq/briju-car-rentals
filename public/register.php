<?php
include '../config/config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $user_type, $full_name, $phone]);
        
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit();
    } catch(PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>

<?php include 'header.php'; ?>
<style>
.register-page {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 2rem 0;
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
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
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
    border-color: #ff6b35;
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
    border-color: #ff6b35;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
}

.btn-register {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
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
    color: #ff6b35;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.register-links a:hover {
    color: #e55a2b;
}

.alert {
    border-radius: 10px;
    border: none;
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
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="register-card">
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
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">
                                        <i class="fas fa-user text-primary me-1"></i>Full Name
                                    </label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" required>
                                    <div class="invalid-feedback">Please provide your full name.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-at text-primary me-1"></i>Username
                                    </label>
                                    <input type="text" name="username" id="username" class="form-control" required>
                                    <div class="invalid-feedback">Please choose a username.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope text-primary me-1"></i>Email
                                    </label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone text-primary me-1"></i>Phone
                                    </label>
                                    <input type="tel" name="phone" id="phone" class="form-control" required>
                                    <div class="invalid-feedback">Please provide your phone number.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="user_type" class="form-label">
                                    <i class="fas fa-user-tag text-primary me-1"></i>User Type
                                </label>
                                <select name="user_type" id="user_type" class="form-select" required>
                                    <option value="">Select user type</option>
                                    <option value="client">Client</option>
                                    <option value="owner">Car Owner</option>
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

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('form')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php include 'footer.php'; ?>
