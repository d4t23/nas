<?php
require_once __DIR__ . '/../app/classes/Auth.php';
$auth = new Auth();

if ($auth->check()) {
    header('Location: dashboard_new.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $message = 'Passwords do not match.';
    } else {
        $result = $auth->register(['name' => $name, 'email' => $email, 'password' => $password]);
        if ($result['success']) {
            header('Location: login.php');
            exit;
        }
        $message = $result['message'];
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GoCloud Register</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Black+Ops+One&family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="auth-page bg-light">
    <div class="auth-header">
        <a href="#" class="auth-logo-link">
            <div class="auth-logo-box">
                <div class="auth-logo-cloud"></div>
            </div>
            <div class="auth-logo-text">
                <span>GoCloud</span>
            </div>
        </a>
    </div>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h4 class="mb-2 text-center fw-bold">Create your account</h4>
                        <p class="text-center text-muted mb-4">Start managing your cloud storage</p>
                        <?php if ($message): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="register.php">
                            <div class="form-floating mb-3">
                                <input type="text" name="name" id="name" class="form-control ps-5" placeholder="Full name" required>
                                <label for="name"><i class="bi bi-person me-2"></i> Full name</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" name="email" id="email" class="form-control ps-5" placeholder="Email" required>
                                <label for="email"><i class="bi bi-envelope me-2"></i> Email address</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" name="password" id="password" class="form-control ps-5" placeholder="Password" required>
                                <label for="password"><i class="bi bi-lock me-2"></i> Password</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" name="confirm_password" id="confirm" class="form-control ps-5" placeholder="Confirm Password" required>
                                <label for="confirm"><i class="bi bi-shield-lock me-2"></i> Confirm password</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="login.php">Already have an account?</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="auth-footer text-center py-3">
        <small class="text-muted">
            © <?php echo date('Y'); ?> goClud. All rights reserved.
        </small>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/alerts.js"></script>
</body>

</html>