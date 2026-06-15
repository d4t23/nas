<?php
require_once __DIR__ . '/../app/classes/PasswordReset.php';

$token = $_GET['token'] ?? '';
$message = '';
$valid = false;

$reset = new PasswordReset();
if ($token && $reset->findByToken($token)) {
    $valid = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($password !== $confirm) {
        $message = 'Passwords do not match.';
    } elseif ($token && $reset->completeReset($token, $password)) {
        header('Location: login.php');
        exit;
    } else {
        $message = 'Unable to reset password. The token may be invalid or expired.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set New Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h4 class="mb-3 text-center">Create a new password</h4>
                    <?php if ($message): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <?php if ($valid): ?>
                        <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
                            <div class="mb-3">
                                <label class="form-label">New password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Save password</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">Reset token is invalid or has expired.</div>
                    <?php endif; ?>
                    <div class="mt-3 text-center">
                        <a href="login.php">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/alerts.js"></script>
</body>
</html>
