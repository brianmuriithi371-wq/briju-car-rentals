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
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg-custom">
                <div class="card-header text-center">
                    <h4 class="mb-0"><i class="fas fa-user-plus text-primary me-2"></i>Register for Briju Car Rental</h4>
                </div>
                <div class="card-body p-4">
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
                            <select name="user_type" id="user_type" class="form-control" required>
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

                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account?
                            <a href="login.php" class="text-primary fw-bold">Login here</a>
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
