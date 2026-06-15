<?php

require_once __DIR__ . '/Database.php';

class PasswordReset
{
    private PDO $db;
    private int $expirySeconds = 900; // 15 minutes

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function requestReset(string $email, string $baseUrl): array
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => true, 'message' => 'If that email exists in our system, a reset link has been sent.'];
        }

        $token = bin2hex(random_bytes(16));
        $stmt = $this->db->prepare('INSERT INTO password_resets (user_id, token, created_at) VALUES (:user_id, :token, NOW())');
        $saved = $stmt->execute([':user_id' => $user['id'], ':token' => $token]);

        if (!$saved) {
            return ['success' => false, 'message' => 'Unable to process your password reset request at this time.'];
        }

        $link = rtrim($baseUrl, '/') . '/reset_password.php?token=' . urlencode($token);
        $subject = 'GoCloud Password Reset Request';
        $body = "Hello,\n\nWe received a request to reset the password for your GoCloud account.\n\nClick the link below to set a new password:\n\n{$link}\n\nThis link will expire in 15 minutes.\n\nIf you did not request a password reset, please ignore this email.\n\nRegards,\nGoCloud Team";

        if (!$this->sendEmail($email, $subject, $body)) {
            $cleanup = $this->db->prepare('DELETE FROM password_resets WHERE token = :token');
            $cleanup->execute([':token' => $token]);
            return ['success' => false, 'message' => 'Unable to send the reset email. Please try again later.'];
        }

        return ['success' => true, 'message' => 'If that email exists in our system, a reset link has been sent.'];
    }

    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT pr.*, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = :token LIMIT 1');
        $stmt->execute([':token' => $token]);
        $reset = $stmt->fetch();
        if (!$reset) {
            return null;
        }

        $created = strtotime($reset['created_at']);
        if ($created < time() - $this->expirySeconds) {
            return null;
        }

        return $reset;
    }

    public function completeReset(string $token, string $password): bool
    {
        $reset = $this->findByToken($token);
        if (!$reset) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        $updated = $stmt->execute([':password' => password_hash($password, PASSWORD_DEFAULT), ':id' => $reset['user_id']]);
        if ($updated) {
            $cleanup = $this->db->prepare('DELETE FROM password_resets WHERE token = :token');
            $cleanup->execute([':token' => $token]);
        }

        return $updated;
    }

    private function sendEmail(string $to, string $subject, string $message): bool
    {
        $headers = "From: GoCloud <noreply@localhost>\r\n";
        $headers .= "Reply-To: noreply@localhost\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        return mail($to, $subject, $message, $headers);
    }
}
