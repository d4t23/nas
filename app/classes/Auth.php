<?php

require_once __DIR__ . '/User.php';

class Auth
{
    private User $userModel;

    public function __construct()
    {
        // Start session kama haijaanza
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->userModel = new User();
    }

    /**
     * Register user
     */
    public function register(array $data): array
    {
        // Validate password policy
        $password = $data['password'] ?? '';
        $validation = $this->validatePassword($password);
        if (!$validation['success']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }

        // Check email kama ipo tayari
        if ($this->userModel->findByEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'Email already registered'
            ];
        }

        // Create user
        if ($this->userModel->create($data)) {
            return [
                'success' => true,
                'message' => 'Registration successful'
            ];
        }

        return [
            'success' => false,
            'message' => 'Unable to register user'
        ];
    }

    /**
     * Login user
     */
    public function login(string $email, string $password): array
    {
        // Find user by email
        $user = $this->userModel->findByEmail($email);

        // Check kama user exists
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email not found'
            ];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Incorrect password'
            ];
        }

        // Save session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ];
    }

    public function validatePassword(string $password): array
    {
        if (strlen($password) < 8) {
            return [
                'success' => false,
                'message' => 'Password must be at least 8 characters long.'
            ];
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return [
                'success' => false,
                'message' => 'Password must include at least one uppercase letter.'
            ];
        }

        if (!preg_match('/\d/', $password)) {
            return [
                'success' => false,
                'message' => 'Password must include at least one number.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Password is valid.'
        ];
    }

    /**
     * Check if user logged in
     */
    public function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user
     */
    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }

        $user = $this->userModel->findById((int) $_SESSION['user_id']);

        return $user ?: null;
    }

    /**
     * Require authentication
     */
    public function requireAuth(): void
    {
        if (!$this->check()) {
            header('Location: login.php');
            exit;
        }

        // Check kama user bado yupo database
        if ($this->user() === null) {

            $this->logout();

            header('Location: login.php');
            exit;
        }
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        // Remove all session data
        $_SESSION = [];

        // Destroy session
        session_destroy();
    }
}
