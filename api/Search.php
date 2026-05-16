<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

$safe = mysqli_real_escape_string($conn, $q);

// Tìm kiếm tên sản phẩm có chứa từ khóa (giống typo tolerance cơ bản)
$sql = "SELECT id, name, price, image FROM products
        WHERE name LIKE '%$safe%'
        ORDER BY
            CASE WHEN name LIKE '$safe%' THEN 0 ELSE 1 END,
            name ASC
        LIMIT 6";

$result = mysqli_query($conn, $sql);
$suggestions = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $suggestions[] = [
            'id'    => $row['id'],
            'name'  => $row['name'],
            'price' => number_format($row['price'], 0, ',', '.') . 'đ',
            'image' => $row['image'] ? '/WEB_GR4/assets/upload/' . $row['image'] : ''
        ];
    }
}

echo json_encode($suggestions, JSON_UNESCAPED_UNICODE);
?>