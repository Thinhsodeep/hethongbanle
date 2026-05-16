<?php

declare(strict_types=1);

require_once APP_ROOT . '/app/Models/User.php';

class AuthController extends Controller
{
    public function login(): void
    {
        if (!empty($_SESSION['user_id'])) {
            if (in_array($_SESSION['role'] ?? '', ['admin', 'manager'], true)) {
                $this->redirect('/admin/dashboard');
            } else {
                $this->redirect('/inventory/index');
            }
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['password'] ?? '';
            $user  = (new User())->findByEmail($email);

            if ($user && password_verify($pass, $user['password_hash'])) {
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['role']      = $user['role_name'];
                $_SESSION['branch_id'] = $user['branch_id'];
                $_SESSION['full_name'] = $user['full_name'];
                if (in_array($user['role_name'], ['admin', 'manager'], true)) {
                    $this->redirect('/admin/dashboard');
                } elseif ($user['role_name'] === 'staff') {
                    $this->redirect('/inventory/index');
                } else {
                    $this->redirect('/inventory/index');
                }
            }
            $error = 'Email hoặc mật khẩu không đúng.';
        }

        require_once APP_ROOT . '/app/Views/auth/login.php';
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->redirect('/auth/login');
    }
}
