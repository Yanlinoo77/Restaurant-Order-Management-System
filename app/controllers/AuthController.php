<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/App.php';

class AuthController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function showLogin($role) {
        session_start_if_not_started();
        if (isset($_SESSION[$role . '_id'])) {
            App::redirect('/' . $role . '/dashboard');
        }
        $error = $_SESSION['login_error'] ?? '';
        unset($_SESSION['login_error']);
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function login() {
        session_start_if_not_started();
        $role = $_POST['role'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Please fill in all fields.';
            App::redirect('/login/' . $role);
        }

        $user = null;
        switch ($role) {
            case 'admin':
                $user = $this->db->fetchOne("SELECT * FROM admin WHERE email = ?", [$email], 's');
                break;
            case 'waiter':
                $user = $this->db->fetchOne("SELECT * FROM waiter WHERE email = ? AND status = 'active'", [$email], 's');
                break;
            case 'kitchen':
                $user = $this->db->fetchOne("SELECT * FROM kitchen WHERE email = ? AND status = 'active'", [$email], 's');
                break;
        }

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION[$role . '_id'] = $user[$role === 'admin' ? 'admin_id' : ($role === 'waiter' ? 'waiter_id' : 'kitchen_id')];
            $_SESSION[$role] = $user;
            $_SESSION['role'] = $role;
            App::redirect('/' . $role . '/dashboard');
        } else {
            $_SESSION['login_error'] = 'Invalid email or password.';
            App::redirect('/login/' . $role);
        }
    }

    public function logout() {
        session_start_if_not_started();
        $role = $_SESSION['role'] ?? 'admin';
        session_destroy();
        App::redirect('/login/' . $role);
    }
}
