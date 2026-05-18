# 🛒 LTW-GR04: Hệ Thống Website Bán Hàng PHP

Dự án xây dựng website bán hàng trực tuyến cơ bản sử dụng ngôn ngữ PHP thuần, kết nối cơ sở dữ liệu MySQL và giao diện HTML/CSS/JS.

---

## 📂 Cấu Trúc Thư Mục Dự Án

WEB_GR4/
├── app/
│ ├── controllers/
│ │ ├── ProductController.php
│ │ ├── CartController.php
│ │ └── AuthController.php
│ ├── models/
│ │ ├── ProductModel.php
│ │ ├── OrderModel.php
│ │ └── UserModel.php
│ └── views/
│ ├── layouts/
│ │ ├── header.php
│ │ └── footer.php
│ ├── products/
│ │ ├── index.php ← danh sách
│ │ └── detail.php ← chi tiết
│ └── home/
│ └── index.php
├── core/
│ ├── Router.php ← phân tích URL
│ ├── Controller.php ← base class
│ └── Model.php ← base class + db
├── config/
│ └── database.php ← Các tệp dùng chung (Kết nối DB, Header, Footer, Hàm bổ trợ).
├── public/
│ └── assets/ ← css, js, img
└── index.php ← điểm vào DUY NHẤT

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
