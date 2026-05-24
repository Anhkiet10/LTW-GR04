# 🛒 LTW-GR04: Hệ Thống Website Bán Hàng PHP

Dự án xây dựng website bán hàng trực tuyến cơ bản sử dụng ngôn ngữ PHP thuần, kết nối cơ sở dữ liệu MySQL và giao diện HTML/CSS/JS.

---

## 📂 Cấu Trúc Thư Mục Dự Án

```text
WEB_GR4/
├── api/
│   └── search.php
│
>>>>>>> cf981dd (ud)
├── app/                        # Thư mục chứa logic chính của ứng dụng
│   ├── controllers/            # Điều hướng và xử lý yêu cầu từ người dùng
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   └── HomeController.php
>>>>>>> cf981dd (ud)
│   │   └── AuthController.php
│   ├── models/                 # Xử lý dữ liệu và tương tác với Database
│   │   ├── ProductModel.php
│   │   ├── OrderModel.php
│   │   └── UserModel.php
│   └── views/                  # Giao diện hiển thị (HTML/PHP)
│       ├── layouts/            # Thành phần giao diện chung
│       │   ├── header.php
│       │   └── footer.php
│       │   └── product-card.php
>>>>>>> cf981dd (ud)
│       ├── products/           # Giao diện liên quan đến sản phẩm
│       │   ├── index.php       # Trang danh sách sản phẩm
│       │   └── detail.php      # Trang chi tiết sản phẩm
│       └── home/               # Giao diện trang chủ
│           └── index.php
├── core/                       # Lõi của hệ thống (Custom Framework)
│   ├── Router.php              # Phân tích URL và định tuyến đến Controller phù hợp
│   ├── Controller.php          # Base Controller (Cung cấp hàm render view)
│   └── Model.php               # Base Model (Quản lý kết nối Database bằng PDO)
├── config/                     # Cấu hình hệ thống
│   └── database.php            # Cấu hình kết nối cơ sở dữ liệu
├── public/                     # Thư mục chứa tài nguyên tĩnh tiếp cận công khai
│   └── assets/                 # Chứa các file CSS, JS, Images, Fonts
│       └── css/
│       └──js/
│       └──upload/
├── index.php                   # Single Entry Point (Điểm vào DUY NHẤT của ứng dụng)
│
└── .htaccess
>>>>>>> cf981dd (ud)
```

## 🛠 Công Nghệ Sử Dụng

- **Frontend:** HTML5, CSS3, JavaScript.
- **Backend:** PHP (xử lý logic phía máy chủ).
- **Database:** MySQL (hệ quản trị cơ sở dữ liệu).
- **Server:** XAMPP (Apache).

---

## 🚀 Hướng Dẫn Cài Đặt (Localhost)

1.  **Tải mã nguồn:**
    ```bash
    git clone [https://github.com/Anhkiet10/LTW-GR04.git](https://github.com/Anhkiet10/LTW-GR04.git)
    ```
2.  **Thiết lập Cơ sở dữ liệu:**
    - Mở **XAMPP Control Panel**, khởi động Apache và MySQL.
    - Truy cập `localhost/phpmyadmin`.
    - Tạo một cơ sở dữ liệu mới (ví dụ: `shop_db`).
    - Chọn tab **Import** và tải tệp `database.sql` từ thư mục gốc của dự án lên.

3.  **Cấu hình kết nối:**
    - Mở tệp `database.php/db.php`.
    - Kiểm tra và chỉnh sửa thông tin kết nối (host, username, password, db_name) cho khớp với cấu hình máy cá nhân.

4.  **Chạy dự án:**
    - Copy thư mục dự án vào `C:/xampp/htdocs/`.
    - Truy cập `localhost/LTW-GR04` trên trình duyệt.

---

## 👥 Thành Viên Nhóm 4 (GR04)

- **Hoàng Anh Kiệt** (Nhóm trưởng)
- **Trương Thành Đạt**
- **Châu Hoàng Phúc**
- **Huỳnh Nhật Phương**
- **Lê Lý Hoàng Đức**
- **Lê Văn Khải**

---

## 📝 Lưu Ý

- Đảm bảo bật MySQL trước khi truy cập các trang có dữ liệu sản phẩm.
- Các ảnh sản phẩm mới tải lên sẽ nằm trong thư mục `assets
