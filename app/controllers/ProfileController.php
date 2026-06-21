<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/ProfileModel.php';

class ProfileController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────────────────

    private function requireLogin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/login');
            exit();
        }
    }

    private function currentUserId()
    {
        return (int) $_SESSION['user_id'];
    }

    // ── Profile ──────────────────────────────────────────────────────────────

    public function index()
    {
        $this->requireLogin();

        $model     = new ProfileModel();
        $user      = $model->getUserById($this->currentUserId());
        $addresses = $model->getUserAddresses($this->currentUserId());

        if (!$user) {
            die("Không tìm thấy thông tin người dùng.");
        }

        $this->render('profile/index', [
            'title'     => 'Hồ sơ cá nhân',
            'user'      => $user,
            'addresses' => $addresses,
        ]);
    }

    public function edit()
    {
        $this->requireLogin();

        $model = new ProfileModel();
        $user  = $model->getUserById($this->currentUserId());

        $this->render('profile/edit', [
            'title' => 'Chỉnh sửa hồ sơ',
            'user'  => $user,
        ]);
    }

    public function update()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /WEB_GR4/profile');
            exit();
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email']     ?? '');
        $phone    = trim($_POST['phone']     ?? '');
        $password = $_POST['password']         ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($fullName) || empty($email)) {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ họ tên và email.';
            header('Location: /WEB_GR4/profile/edit');
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email không hợp lệ.';
            header('Location: /WEB_GR4/profile/edit');
            exit();
        }

        if (!empty($password)) {
            if (strlen($password) < 6) {
                $_SESSION['error'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
                header('Location: /WEB_GR4/profile/edit');
                exit();
            }
            if ($password !== $confirm) {
                $_SESSION['error'] = 'Mật khẩu xác nhận không khớp.';
                header('Location: /WEB_GR4/profile/edit');
                exit();
            }
        }

        $model  = new ProfileModel();
        $result = $model->updateUser(
            $this->currentUserId(),
            $fullName,
            $email,
            $phone,
            $password
        );

        if ($result === 'EMAIL_TAKEN') {
            $_SESSION['error'] = 'Email đã được sử dụng bởi tài khoản khác.';
            header('Location: /WEB_GR4/profile/edit');
            exit();
        }

        if ($result) {
            // Cập nhật lại tên trong session nếu có lưu
            $_SESSION['full_name']  = $fullName;
            $_SESSION['success']    = 'Cập nhật thông tin thành công.';
            header('Location: /WEB_GR4/profile');
            exit();
        }

        $_SESSION['error'] = 'Có lỗi xảy ra, vui lòng thử lại.';
        header('Location: /WEB_GR4/profile/edit');
        exit();
    }

    // ── Addresses ────────────────────────────────────────────────────────────

    public function addAddress()
    {
        $this->requireLogin();

        $this->render('profile/addAddress', [
            'title' => 'Thêm địa chỉ',
        ]);
    }

    public function storeAddress()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /WEB_GR4/profile');
            exit();
        }

        $label       = trim($_POST['label']        ?? '');
        $fullAddress = trim($_POST['full_address'] ?? '');
        $city        = trim($_POST['city']         ?? '');
        $isDefault   = isset($_POST['is_default']) ? 1 : 0;

        if (empty($fullAddress) || empty($city)) {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ địa chỉ và thành phố.';
            header('Location: /WEB_GR4/profile/addAddress');
            exit();
        }

        $model  = new ProfileModel();
        $result = $model->addAddress(
            $this->currentUserId(),
            $label ?: 'Nhà',
            $fullAddress,
            $city,
            $isDefault
        );

        if ($result) {
            $_SESSION['success'] = 'Thêm địa chỉ thành công.';
        } else {
            $_SESSION['error'] = 'Có lỗi khi thêm địa chỉ.';
        }

        header('Location: /WEB_GR4/profile');
        exit();
    }

    public function editAddress($addressId = null)
    {
        $this->requireLogin();

        // Hỗ trợ cả /profile/edit-address/5 và ?id=5
        $addressId = (int)($addressId ?? $_GET['id'] ?? 0);

        if (!$addressId) {
            header('Location: /WEB_GR4/profile');
            exit();
        }

        $model   = new ProfileModel();
        $address = $model->getAddressById($addressId, $this->currentUserId());

        if (!$address) {
            $_SESSION['error'] = 'Không tìm thấy địa chỉ.';
            header('Location: /WEB_GR4/profile');
            exit();
        }

        $this->render('profile/editAddress', [
            'title'   => 'Chỉnh sửa địa chỉ',
            'address' => $address,
        ]);
    }

    public function updateAddress()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /WEB_GR4/profile');
            exit();
        }

        $addressId   = (int)($_POST['address_id']  ?? 0);
        $label       = trim($_POST['label']         ?? '');
        $fullAddress = trim($_POST['full_address']  ?? '');
        $city        = trim($_POST['city']          ?? '');
        $isDefault   = isset($_POST['is_default']) ? 1 : 0;

        if (!$addressId || empty($fullAddress) || empty($city)) {
            $_SESSION['error'] = 'Dữ liệu không hợp lệ.';
            header('Location: /WEB_GR4/profile');
            exit();
        }

        $model  = new ProfileModel();
        $result = $model->updateAddress(
            $addressId,
            $this->currentUserId(),
            $label ?: 'Nhà',
            $fullAddress,
            $city,
            $isDefault
        );

        if ($result) {
            $_SESSION['success'] = 'Cập nhật địa chỉ thành công.';
        } else {
            $_SESSION['error'] = 'Có lỗi khi cập nhật địa chỉ.';
        }

        header('Location: /WEB_GR4/profile');
        exit();
    }

    public function deleteAddress()
    {
        $this->requireLogin();

        $addressId = (int)($_POST['address_id'] ?? $_GET['id'] ?? 0);

        if (!$addressId) {
            header('Location: /WEB_GR4/profile');
            exit();
        }

        $model  = new ProfileModel();
        $result = $model->deleteAddress($addressId, $this->currentUserId());

        if ($result) {
            $_SESSION['success'] = 'Đã xóa địa chỉ.';
        } else {
            $_SESSION['error'] = 'Không thể xóa địa chỉ này.';
        }

        header('Location: /WEB_GR4/profile');
        exit();
    }

    public function setDefaultAddress()
    {
        $this->requireLogin();

        $addressId = (int)($_POST['address_id'] ?? $_GET['id'] ?? 0);

        if (!$addressId) {
            header('Location: /WEB_GR4/profile');
            exit();
        }

        $model  = new ProfileModel();
        $result = $model->setDefaultAddress($addressId, $this->currentUserId());

        if ($result) {
            $_SESSION['success'] = 'Đã đặt làm địa chỉ mặc định.';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra.';
        }

        header('Location: /WEB_GR4/profile');
        exit();
    }
}