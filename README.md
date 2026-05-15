# 🛒 LTW-GR04: Hệ Thống Website Bán Hàng PHP

Dự án xây dựng website bán hàng trực tuyến cơ bản sử dụng ngôn ngữ PHP thuần, kết nối cơ sở dữ liệu MySQL và giao diện HTML/CSS/JS.

---

## 📂 Cấu Trúc Thư Mục Dự Án

Việc tổ chức thư mục giúp dự án dễ dàng bảo trì và mở rộng:

- **`assets/`**: Chứa tài nguyên tĩnh (CSS, JS, Hình ảnh sản phẩm).
- **`includes/`**: Các tệp dùng chung (Kết nối DB, Header, Footer, Hàm bổ trợ).
- **`pages/`**: Giao diện các trang dành cho người dùng (Home, Products, Cart, Login...).
- **`admin/`**: Khu vực quản trị viên (Quản lý sản phẩm, đơn hàng, người dùng).
- **`api/`**: Xử lý logic phía Server (Nhận dữ liệu từ Form, xử lý giỏ hàng, đăng nhập).
- **`index.php`**: Điểm điều hướng chính của toàn bộ website.
- **`database.sql`**: Tệp truy vấn để tạo cấu trúc bảng và dữ liệu mẫu.

---

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
    - Mở tệp `includes/db.php`.
    - Kiểm tra và chỉnh sửa thông tin kết nối (host, username, password, db_name) cho khớp với cấu hình máy cá nhân.

4.  **Chạy dự án:**
    - Copy thư mục dự án vào `C:/xampp/htdocs/`.
    - Truy cập `localhost/LTW-GR04` trên trình duyệt.

---

## 👥 Thành Viên Nhóm 4 (GR04)

- **Hoàng Anh Kiệt** (Nhóm trưởng)
- [Tên thành viên 2]
- [Tên thành viên 3]

---

## 📝 Lưu Ý

- Đảm bảo bật MySQL trước khi truy cập các trang có dữ liệu sản phẩm.
- Các ảnh sản phẩm mới tải lên sẽ nằm trong thư mục `assets/uploads/`.
