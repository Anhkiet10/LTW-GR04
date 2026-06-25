<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/CategoryModelAdmin.php';

class AdminCategoryController extends Controller
{
    private CategoryModelAdmin $categoryModel;

    public function __construct()
    {
        $this->checkAdminAuth();
        $this->categoryModel = new CategoryModelAdmin();
    }

    // ------------------------------------------------------------------ //
    //  AUTH GUARD                                                          //
    // ------------------------------------------------------------------ //

    private function checkAdminAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /WEB_GR4/login');
            exit;
        }
    }

    // ------------------------------------------------------------------ //
    //  GET /admin/categories  – danh sách danh mục (tìm kiếm + phân trang)//
    // ------------------------------------------------------------------ //

    public function index(): void
    {
        $search  = trim($_GET['search'] ?? '');
        $type    = $_GET['type']        ?? '';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;

        $filters = compact('search', 'type');

        $categories = $this->categoryModel->getAll($filters, $perPage, $offset);
        $total      = $this->categoryModel->countAll($filters);
        $totalPages = (int) ceil($total / $perPage);

        $this->render('admin/Category', [
            'categories' => $categories,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
            'filters'    => $filters,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  GET /admin/categories/create  – form thêm danh mục mới             //
    // ------------------------------------------------------------------ //

    public function create(): void
    {
        $parentList = $this->categoryModel->getAllForSelect();

        $this->render('admin/CategoryForm', [
            'category'   => null,
            'errors'     => [],
            'mode'       => 'create',
            'parentList' => $parentList,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  POST /admin/categories/store  – lưu danh mục mới                   //
    // ------------------------------------------------------------------ //

    public function store(): void
    {
        $data   = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('admin/CategoryForm', [
                'category'   => $data,
                'errors'     => $errors,
                'mode'       => 'create',
                'parentList' => $this->categoryModel->getAllForSelect(),
            ]);
            return;
        }

        if ($this->categoryModel->nameExists($data['category_name'])) {
            $errors['category_name'] = 'Tên danh mục đã tồn tại.';
            $this->render('admin/CategoryForm', [
                'category'   => $data,
                'errors'     => $errors,
                'mode'       => 'create',
                'parentList' => $this->categoryModel->getAllForSelect(),
            ]);
            return;
        }

        $this->categoryModel->create($data);

        $this->setFlash('success', 'Thêm danh mục thành công!');
        header('Location: /WEB_GR4/admin/categories');
        exit;
    }

    // ------------------------------------------------------------------ //
    //  GET /admin/categories/edit?id=  – form sửa danh mục                //
    // ------------------------------------------------------------------ //

    public function edit(): void
    {
        $id       = (int)($_GET['id'] ?? 0);
        $category = $this->categoryModel->findById($id);

        if (!$category) {
            $this->setFlash('error', 'Danh mục không tồn tại.');
            header('Location: /WEB_GR4/admin/categories');
            exit;
        }

        // Loại trừ chính nó khỏi dropdown danh mục cha
        $parentList = $this->categoryModel->getAllForSelect($id);

        $this->render('admin/CategoryForm', [
            'category'   => $category,
            'errors'     => [],
            'mode'       => 'edit',
            'parentList' => $parentList,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  POST /admin/categories/update  – cập nhật danh mục                 //
    // ------------------------------------------------------------------ //

    public function update(): void
    {
        $id       = (int)($_POST['id'] ?? 0);
        $category = $this->categoryModel->findById($id);

        if (!$category) {
            $this->setFlash('error', 'Danh mục không tồn tại.');
            header('Location: /WEB_GR4/admin/categories');
            exit;
        }

        $data   = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('admin/CategoryForm', [
                'category'   => array_merge($category, $data),
                'errors'     => $errors,
                'mode'       => 'edit',
                'parentList' => $this->categoryModel->getAllForSelect($id),
            ]);
            return;
        }

        if ($this->categoryModel->nameExistsExcept($data['category_name'], $id)) {
            $errors['category_name'] = 'Tên danh mục đã được sử dụng.';
            $this->render('admin/CategoryForm', [
                'category'   => array_merge($category, $data),
                'errors'     => $errors,
                'mode'       => 'edit',
                'parentList' => $this->categoryModel->getAllForSelect($id),
            ]);
            return;
        }

        // Không cho chọn chính mình làm danh mục cha
        if (!empty($data['parent_id']) && (int)$data['parent_id'] === $id) {
            $errors['parent_id'] = 'Danh mục không thể là cha của chính nó.';
            $this->render('admin/CategoryForm', [
                'category'   => array_merge($category, $data),
                'errors'     => $errors,
                'mode'       => 'edit',
                'parentList' => $this->categoryModel->getAllForSelect($id),
            ]);
            return;
        }

        $this->categoryModel->update($id, $data);

        $this->setFlash('success', 'Cập nhật danh mục thành công!');
        header('Location: /WEB_GR4/admin/categories');
        exit;
    }

    // ------------------------------------------------------------------ //
    //  POST /admin/categories/delete  – xóa danh mục                      //
    // ------------------------------------------------------------------ //

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);

        if (!$this->categoryModel->findById($id)) {
            $this->setFlash('error', 'Danh mục không tồn tại.');
            header('Location: /WEB_GR4/admin/categories');
            exit;
        }

        // Không cho xóa nếu còn danh mục con
        if ($this->categoryModel->hasChildren($id)) {
            $this->setFlash('error', 'Không thể xóa danh mục đang có danh mục con.');
            header('Location: /WEB_GR4/admin/categories');
            exit;
        }

        // Không cho xóa nếu còn sản phẩm
        if ($this->categoryModel->hasProducts($id)) {
            $this->setFlash('error', 'Không thể xóa danh mục đang có sản phẩm.');
            header('Location: /WEB_GR4/admin/categories');
            exit;
        }

        $this->categoryModel->delete($id);

        $this->setFlash('success', 'Đã xóa danh mục.');
        header('Location: /WEB_GR4/admin/categories');
        exit;
    }

    // ------------------------------------------------------------------ //
    //  HELPERS                                                             //
    // ------------------------------------------------------------------ //

    private function sanitizeInput(array $post): array
    {
        return [
            'id'            => (int)($post['id']            ?? 0),
            'category_name' => trim($post['category_name']  ?? ''),
            'description'   => trim($post['description']    ?? ''),
            'parent_id'     => ($post['parent_id'] ?? '') !== '' ? (int)$post['parent_id'] : null,
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['category_name'])) {
            $errors['category_name'] = 'Tên danh mục không được để trống.';
        } elseif (mb_strlen($data['category_name']) < 2) {
            $errors['category_name'] = 'Tên danh mục phải có ít nhất 2 ký tự.';
        } elseif (mb_strlen($data['category_name']) > 100) {
            $errors['category_name'] = 'Tên danh mục không được vượt quá 100 ký tự.';
        }

        return $errors;
    }

    private function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'] = compact('type', 'message');
    }
}