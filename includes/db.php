<?php
$host = "localhost:3307";
$user = "root";
$pass = "";
$db   = "shop_db"; // đổi thành tên database của bạn

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Lỗi kết nối database: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>