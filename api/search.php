<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

$conn = Database::getConnection();
$safe = $conn->real_escape_string($q);

$sql = "SELECT p.product_id, p.product_name, p.price, pi.image_url
        FROM products p
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE p.is_active = 1 AND
              (p.product_name LIKE '%$safe%' OR p.description LIKE '%$safe%')
        ORDER BY
            CASE WHEN p.product_name LIKE '$safe%' THEN 0 ELSE 1 END,
            p.product_name ASC
        LIMIT 6";

$result = $conn->query($sql);
$suggestions = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = [
            'id'    => $row['product_id'],
            'name'  => $row['product_name'],
            'price' => number_format($row['price'], 0, ',', '.') . 'đ',
            'image' => $row['image_url'] ? $row['image_url'] : ''
        ];
    }
}

echo json_encode($suggestions, JSON_UNESCAPED_UNICODE);
?>