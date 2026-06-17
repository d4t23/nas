<?php
session_start();
require_once __DIR__ . '/../app/classes/Auth.php';

$auth = new Auth();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $result = $auth->login($email, $password);


    if ($result['success']) {
        header("Location: landling.php");
        exit();
    }
    $message = $result['message'];
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GoCloud Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Black+Ops+One&family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
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
    <div class="container py-5 min-vh-50">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class=" card-body p-4">
                        <h4 class="mb-3 text-center"><b>Welcome back</b></h4>
                        <p class="text-center text-muted">
                            login to access your dashboard
                        </p>
                        <?php if ($message): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="login.php">
                            <div class="form-floating mb-3">
                                <input type="email" name="email" id="email" class="form-control ps-5" placeholder="Email" required>
                                <label for="email"><i class="bi bi-envelope me-2"></i> Email address</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" name="password" id="password" class="form-control ps-5" placeholder="Password" required>
                                <label for="password"><i class="bi bi-shield-lock me-2"></i> Password</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="password_reset.php">Forgot password?</a>
                        </div>
                        <div class="mt-2 text-center">
                            <a href="register.php">Create an account</a>
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