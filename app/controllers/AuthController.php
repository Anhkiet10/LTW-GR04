<?php
require_once __DIR__ . '/../../core/Controller.php';
class AuthController extends Controller
{
    public function loginPage()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/');
            exit;
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email)) {
                $errors['email'] = 'Vui lòng nhập email.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email không hợp lệ.';
            }

            if (empty($password)) {
                $errors['password'] = 'Vui lòng nhập mật khẩu.';
            }

            if (empty($errors)) {

                require_once __DIR__ . '/../../config/database.php';
                require_once __DIR__ . '/../models/UserModel.php';

                $db = Database::getConnection();
                $userModel = new UserModel($db);

                $user = $userModel->findByEmail($email);

                if (!$user || !password_verify($password, $user['password_hash'])) {

                    $errors['email'] = 'Email hoặc mật khẩu không đúng.';

                } else {

                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        header('Location: /WEB_GR4/admin');
                        exit;
                    }

                    // customer
                    if($user['role']==='customer'){
                        header('Location: /WEB_GR4/');
                        exit;
                    }
                }
            }
        }

        $this->render(
            'auth/login',
            [
                'errors' => $errors
            ]
        );
    }
    public function registerPage()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/');
            exit;
        }

        $errors = [];
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = trim($_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';

            if (empty($fullname)) {
                $errors['fullname'] = 'Vui lòng nhập họ tên.';
            }

            if (empty($email)) {
                $errors['email'] = 'Vui lòng nhập email.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email không hợp lệ.';
            }

            if (strlen($password) < 6) {
                $errors['password'] = 'Mật khẩu phải từ 6 ký tự.';
            }

            if ($password !== $password2) {
                $errors['password2'] = 'Mật khẩu xác nhận không khớp.';
            }

            if (empty($errors)) {

                require_once __DIR__ . '/../../config/database.php';
                require_once __DIR__ . '/../models/UserModel.php';

                $db = Database::getConnection();
                $userModel = new UserModel($db);

                if ($userModel->emailExists($email)) {

                    $errors['email'] = 'Email đã tồn tại.';

                } else {

                    if ($userModel->register($fullname, $email, $password)) {

                        $success = true;

                    } else {

                        $errors['fullname'] = 'Không thể tạo tài khoản.';
                    }
                }
            }
        }

        $this->render(
            'auth/register',
            [
                'errors' => $errors,
                'success' => $success
            ]
        );
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();

        header('Location: /WEB_GR4/login');
        exit;
    }
}