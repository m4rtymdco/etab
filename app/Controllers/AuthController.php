<?php

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirectByRole();
        }
        View::render('auth/login', ['title' => 'Sign in'], 'layouts/guest');
    }

    public function login(): void
    {
        verify_csrf();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!Auth::attempt($email, $password)) {
            flash('error', 'Invalid email or password.');
            $_SESSION['_old'] = ['email' => $email];
            redirect('login');
        }
        $this->redirectByRole();
    }

    public function logout(): void
    {
        Auth::logout();
        session_start();
        flash('success', 'You have been signed out.');
        redirect('login');
    }

    public function showForgot(): void
    {
        View::render('auth/forgot', ['title' => 'Reset password'], 'layouts/guest');
    }

    public function forgot(): void
    {
        verify_csrf();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $user = User::findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(16));
            Database::query(
                'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR))',
                [(int) $user['id'], hash('sha256', $token)]
            );
            Audit::log((int) $user['id'], 'password_reset_requested', 'user', (int) $user['id']);
            $link = url('reset-password?token=' . urlencode($token));
            flash('success', 'Reset link generated. For local setups without email, use: ' . $link);
            flash('reset_link', $link);
        } else {
            flash('success', 'If that account exists, a reset link was generated.');
        }
        redirect('forgot-password');
    }

    public function showReset(): void
    {
        $token = $_GET['token'] ?? '';
        View::render('auth/reset', ['title' => 'Set new password', 'token' => $token], 'layouts/guest');
    }

    public function reset(): void
    {
        verify_csrf();
        $token = (string) ($_POST['token'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');
        if (strlen($password) < 8 || $password !== $confirm) {
            flash('error', 'Passwords must match and be at least 8 characters.');
            redirect('reset-password?token=' . urlencode($token));
        }
        $hash = hash('sha256', $token);
        $row = Database::query(
            'SELECT * FROM password_resets WHERE token_hash=? AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1',
            [$hash]
        )->fetch();
        if (!$row) {
            flash('error', 'This reset link is invalid or expired.');
            redirect('forgot-password');
        }
        User::update((int) $row['user_id'], ['password' => $password]);
        Database::query('UPDATE password_resets SET used_at=NOW() WHERE id=?', [(int) $row['id']]);
        Audit::log((int) $row['user_id'], 'password_reset_completed', 'user', (int) $row['user_id']);
        flash('success', 'Password updated. You can sign in now.');
        redirect('login');
    }

    private function redirectByRole(): void
    {
        $user = Auth::user();
        if ($user && $user['role'] === 'admin') {
            redirect('admin');
        }
        redirect('judge');
    }
}
