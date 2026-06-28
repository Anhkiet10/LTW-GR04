<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/UserModelAdmin.php';

class AdminUserController extends Controller
{
    private UserModelAdmin $userModel;

    public function __construct()
    {
        $this->checkAdminAuth();
        $this->userModel = new UserModelAdmin();
    }

    // ------------------------------------------------------------------ //
    //  AUTH GUARD                                                          //
    // ------------------------------------------------------------------ //

private function checkAdminAuth(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Dùng đúng session key được set bởi AuthController
    if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: /WEB_GR4/login');
        exit;
    }
}

    // ------------------------------------------------------------------ //
    //  GET /admin/users  – danh sách user (có tìm kiếm + phân trang)      //
    // ------------------------------------------------------------------ //
    public function index(): void
    {
        $search   = trim($_GET['search']  ?? '');
        $role     = $_GET['role']         ?? '';
        $status   = $_GET['status']       ?? '';
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 10;
        $offset   = ($page - 1) * $perPage;

        $filters = compact('search', 'role', 'status');

        $users = $this->userModel->getAll($filters, $perPage, $offset);
        $total = $this->userModel->countAll($filters);
        $totalPages = (int) ceil($total / $perPage);

        $this->render('admin/User', [
            'users'      => $users,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
            'filters'    => $filters,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  GET /admin/users/create  – form thêm user mới                      //
    // ------------------------------------------------------------------ //
    public function create(): void
    {
        $this->render('admin/UserForm', [
            'user'   => null,
            'errors' => [],
            'mode'   => 'create',
        ]);
    }

    // ------------------------------------------------------------------ //
    //  POST /admin/users/store  – lưu user mới                            //
    // ------------------------------------------------------------------ //
    public function store(): void
    {
        $data   = $this->sanitizeInput($_POST);
        $errors = $this->validate($data, isNew: true);

        if (!empty($errors)) {
            $this->render('admin/UserForm', [
                'user'   => $data,
                'errors' => $errors,
                'mode'   => 'create',
            ]);
            return;
        }

        if ($this->userModel->emailExists($data['email'])) {
            $errors['email'] = 'Email đã tồn tại.';
            $this->render('admin/UserForm', [
                'user'   => $data,
                'errors' => $errors,
                'mode'   => 'create',
            ]);
            return;
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $this->userModel->create($data);

        $this->setFlash('success', 'Thêm người dùng thành công!');
        header('Location: /WEB_GR4/admin/users'); 
        exit;
    }

    // ------------------------------------------------------------------ //
    //  GET /admin/users/edit?id=  – form sửa user                         //
    // ------------------------------------------------------------------ //
    public function edit(): void
    {
        $id   = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->findById($id);

        if (!$user) {
            $this->setFlash('error', 'Người dùng không tồn tại.');
            header('Location: /WEB_GR4/admin/users');
            exit;
        }

        $this->render('admin/UserForm', [
            'user'   => $user,
            'errors' => [],
            'mode'   => 'edit',
        ]);
    }

    // ------------------------------------------------------------------ //
    //  POST /admin/users/update  – cập nhật user                          //
    // ------------------------------------------------------------------ //
    public function update(): void
    {
        $id   = (int)($_POST['id'] ?? 0);
        $user = $this->userModel->findById($id);

        if (!$user) {
            $this->setFlash('error', 'Người dùng không tồn tại.');
            header('Location: /WEB_GR4/admin/users');
            exit;
        }

        $data   = $this->sanitizeInput($_POST);
        $errors = $this->validate($data, isNew: false);

        if (!empty($errors)) {
            $this->render('admin/UserForm', [
                'user'   => array_merge($user, $data),
                'errors' => $errors,
                'mode'   => 'edit',
            ]);
            return;
        }

        // Email dùng bởi user khác?
        if ($this->userModel->emailExistsExcept($data['email'], $id)) {
            $errors['email'] = 'Email đã được sử dụng bởi tài khoản khác.';
            $this->render('admin/UserForm', [
                'user'   => array_merge($user, $data),
                'errors' => $errors,
                'mode'   => 'edit',
            ]);
            return;
        }

        // Chỉ hash lại password nếu admin nhập password mới
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }

        $this->userModel->update($id, $data);

        $this->setFlash('success', 'Cập nhật người dùng thành công!');
        header('Location: /WEB_GR4/admin/users'); 
        exit;
    }

    // ------------------------------------------------------------------ //
    //  POST /admin/users/delete  – xóa user                               //
    // ------------------------------------------------------------------ //
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);

        // Không cho xóa chính mình
        if ($id === (int)$_SESSION['user_id']) {
            $this->setFlash('error', 'Không thể xóa tài khoản đang đăng nhập.');
            header('Location: /WEB_GR4/admin/users');
            exit;
        }

        $this->userModel->delete($id);
        $this->setFlash('success', 'Đã xóa người dùng.');
        header('Location: /WEB_GR4/admin/users');
        exit;
    }

    // ------------------------------------------------------------------ //
    //  POST /admin/users/toggle-status  – khoá / mở khoá tài khoản       //
    // ------------------------------------------------------------------ //
    public function toggleStatus(): void
    {
        $id   = (int)($_POST['id'] ?? 0);
        $user = $this->userModel->findById($id);

        if (!$user) {
            $this->jsonResponse(['success' => false, 'message' => 'User không tồn tại']);
            return;
        }

        if ($id === (int)$_SESSION['user_id']) {
            $this->jsonResponse(['success' => false, 'message' => 'Không thể khoá chính mình']);
            return;
        }

        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        $this->userModel->updateStatus($id, $newStatus);

        $this->jsonResponse(['success' => true, 'status' => $newStatus]);
    }

    // ------------------------------------------------------------------ //
    //  GET /admin/users/detail?id=  – xem chi tiết user                   //
    // ------------------------------------------------------------------ //
    public function detail(): void
    {
        $id   = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->findById($id);

        if (!$user) {
            $this->setFlash('error', 'Người dùng không tồn tại.');
            header('Location: /WEB_GR4/admin/users');
            exit;
        }

        $orderCount = $this->userModel->countOrders($id);

        $this->render('admin/UserDetail', [
            'user'       => $user,
            'orderCount' => $orderCount,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  HELPERS                                                             //
    // ------------------------------------------------------------------ //
    private function sanitizeInput(array $post): array
    {
        return [
            'id'        => (int)($post['id']        ?? 0),
            'full_name' => trim($post['full_name']   ?? ''),  //  form gửi full_name
            'email'     => trim($post['email']       ?? ''),
            'phone'     => trim($post['phone']       ?? ''),
            'role'      => in_array($post['role'] ?? '', ['customer', 'admin']) ? $post['role'] : 'customer', // ✅ Fix Bug 2: DB dùng 'customer' không phải 'user'
            'status'    => in_array($post['status'] ?? '', ['active', 'inactive']) ? $post['status'] : 'active',
            'password'  => $post['password']         ?? '',
        ];
    }

    private function validate(array $data, bool $isNew): array
    {
        $errors = [];

        // key phải là 'full_name' cho khớp với sanitizeInput
        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Họ tên không được để trống.';
        } elseif (mb_strlen($data['full_name']) < 2) {
            $errors['full_name'] = 'Họ tên phải có ít nhất 2 ký tự.';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }

        if (!empty($data['phone']) && !preg_match('/^[0-9]{9,11}$/', $data['phone'])) {
            $errors['phone'] = 'Số điện thoại không hợp lệ (9–11 chữ số).';
        }

        // Password bắt buộc khi tạo mới; tùy chọn khi cập nhật
        if ($isNew && empty($data['password'])) {
            $errors['password'] = 'Mật khẩu không được để trống.';
        } elseif (!empty($data['password']) && strlen($data['password']) < 6) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }

        return $errors;
    }

    private function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'] = compact('type', 'message');
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}