<?php
require_once __DIR__ . '/../../core/Model.php';

class OrderModel extends Model {

    public function getAddressesByUser($userId) {
        $userId = (int)$userId;
        $sql = "SELECT *
                FROM addresses
                WHERE user_id = $userId
                ORDER BY is_default DESC, address_id DESC";
        return $this->fetchAll($sql);
    }

    public function getOrdersByUser($userId) {
        $userId = (int)$userId;
        $sql = "SELECT o.*, a.full_address, a.city,
                       COUNT(DISTINCT oi.order_item_id) as item_count,
                       GROUP_CONCAT(DISTINCT pdt.product_name ORDER BY oi.order_item_id SEPARATOR ', ') as product_names,
                       p.payment_method, p.payment_status
                FROM orders o
                LEFT JOIN addresses a ON o.address_id = a.address_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN products pdt ON oi.product_id = pdt.product_id
                LEFT JOIN payments p ON o.order_id = p.order_id
                WHERE o.user_id = $userId
                GROUP BY o.order_id
                ORDER BY o.order_date DESC";
        return $this->fetchAll($sql);
    }

    /**
     * Lấy đơn hàng theo ID.
     * - Nếu $userId > 0 → chỉ trả về đơn của user đó (member).
     * - Nếu $userId = 0 → không lọc user_id (dùng cho admin hoặc guest lookup bằng phone).
     */
    public function getOrderByIdForUser($orderId, $userId) {
        $orderId = (int)$orderId;
        $userId  = (int)$userId;

        $userFilter = $userId > 0 ? "AND o.user_id = $userId" : "AND o.user_id IS NULL";

        $sql = "SELECT o.*,
                    COALESCE(u.full_name, o.guest_name)    AS full_name,
                    COALESCE(u.email,     o.guest_email)   AS email,
                    COALESCE(u.phone,     o.guest_phone)   AS phone,
                    COALESCE(a.full_address, o.guest_address) AS full_address,
                    COALESCE(a.city,         o.guest_city)    AS city,
                    p.payment_method, p.payment_status, p.transaction_id
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                LEFT JOIN addresses a ON o.address_id = a.address_id
                LEFT JOIN payments p ON o.order_id = p.order_id
                WHERE o.order_id = $orderId $userFilter";

        return $this->fetchOne($sql);
    }

    public function createOrder($userId, $addressId, $total, $note = null) {
        $userId    = (int)$userId;
        $addressId = $addressId !== null ? (int)$addressId : 'NULL';
        $total     = (float)$total;
        $noteSql   = $note !== null && $note !== ''
            ? "'" . $this->escape($note) . "'"
            : 'NULL';

        $sql = "INSERT INTO orders (user_id, address_id, total_amount, status, note)
                VALUES ($userId, $addressId, $total, 'pending', $noteSql)";
        $this->query($sql);
        return $this->lastInsertId();
    }

    /**
     * Tạo đơn hàng cho khách vãng lai (user_id = NULL).
     */
    public function createGuestOrder(
        string $name,
        string $phone,
        string $email,
        string $address,
        string $city,
        float  $total,
        string $note = ''
    ): int {
        $nameE    = $this->escape($name);
        $phoneE   = $this->escape($phone);
        $emailE   = $email !== '' ? "'" . $this->escape($email) . "'" : 'NULL';
        $addressE = $this->escape($address);
        $cityE    = $this->escape($city);
        $totalF   = number_format($total, 2, '.', '');
        $noteE    = $note !== '' ? "'" . $this->escape($note) . "'" : 'NULL';

        $sql = "INSERT INTO orders
                    (user_id, address_id, total_amount, status, note,
                     guest_name, guest_phone, guest_email, guest_address, guest_city)
                VALUES
                    (NULL, NULL, $totalF, 'pending', $noteE,
                     '$nameE', '$phoneE', $emailE, '$addressE', '$cityE')";

        $this->query($sql);
        return $this->lastInsertId();
    }

    public function addOrderItem($orderId, $productId, $variantId, $quantity, $unitPrice) {
        $orderId   = (int)$orderId;
        $productId = (int)$productId;
        $variantId = $variantId !== null ? (int)$variantId : 'NULL';
        $quantity  = (int)$quantity;
        $unitPrice = (float)$unitPrice;

        $sql = "INSERT INTO order_items (order_id, product_id, variant_id, quantity, unit_price)
                VALUES ($orderId, $productId, $variantId, $quantity, $unitPrice)";
        return $this->query($sql);
    }

    public function createPayment($orderId, $method = null, $status = 'pending')
    {
        $orderId   = (int)$orderId;
        $statusE   = $this->escape($status);
        $methodSql = ($method !== null) ? "'" . $this->escape($method) . "'" : "'cod'";
        $paidAt    = ($status === 'paid') ? 'NOW()' : 'NULL';

        $sql = "INSERT INTO payments (order_id, payment_method, payment_status, paid_at)
                VALUES ($orderId, $methodSql, '$statusE', $paidAt)
                ON DUPLICATE KEY UPDATE
                    payment_method = IF(payment_method IS NULL OR payment_method = 'cod', $methodSql, payment_method),
                    payment_status = '$statusE',
                    paid_at        = IF('$statusE' = 'paid', NOW(), paid_at)";
        return $this->query($sql);
    }

    public function updatePaymentStatus($orderId, $status, $method = null)
    {
        $orderId   = (int)$orderId;
        $statusE   = $this->escape($status);
        $paidAt    = ($status === 'paid') ? ', paid_at = NOW()' : '';
        $methodSql = $method ? ", payment_method = '" . $this->escape($method) . "'" : '';

        $sql = "UPDATE payments
                SET payment_status = '$statusE' $paidAt $methodSql
                WHERE order_id = $orderId";
        return $this->query($sql);
    }

    public function getAllOrders($limit = null, $offset = 0, $status = null) {
        $sql = "SELECT o.*,
                       COALESCE(u.full_name, o.guest_name) AS full_name,
                       COALESCE(u.email,     o.guest_email) AS email,
                       COALESCE(u.phone,     o.guest_phone) AS phone,
                       COALESCE(a.full_address, o.guest_address) AS full_address,
                       COALESCE(a.city,         o.guest_city)    AS city,
                       COUNT(DISTINCT oi.order_item_id) as item_count,
                       GROUP_CONCAT(DISTINCT pdt.product_name ORDER BY oi.order_item_id SEPARATOR ', ') as product_names,
                       p.payment_method, p.payment_status
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                LEFT JOIN addresses a ON o.address_id = a.address_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN products pdt ON oi.product_id = pdt.product_id
                LEFT JOIN payments p ON o.order_id = p.order_id
                WHERE 1=1";

        if ($status) {
            $status = $this->escape($status);
            $sql .= " AND o.status = '$status'";
        }

        $sql .= " GROUP BY o.order_id ORDER BY o.order_date DESC";

        if ($limit) {
            $offset = (int)$offset;
            $limit  = (int)$limit;
            $sql .= " LIMIT $offset, $limit";
        }

        return $this->fetchAll($sql);
    }

    public function getOrderById($orderId) {
        $orderId = (int)$orderId;
        $sql = "SELECT o.*,
                       COALESCE(u.full_name, o.guest_name) AS full_name,
                       COALESCE(u.email,     o.guest_email) AS email,
                       COALESCE(u.phone,     o.guest_phone) AS phone,
                       COALESCE(a.full_address, o.guest_address) AS full_address,
                       COALESCE(a.city,         o.guest_city)    AS city,
                       p.payment_method, p.payment_status, p.transaction_id
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                LEFT JOIN addresses a ON o.address_id = a.address_id
                LEFT JOIN payments p ON o.order_id = p.order_id
                WHERE o.order_id = $orderId";
        return $this->fetchOne($sql);
    }

    public function getOrderItems($orderId) {
        $orderId = (int)$orderId;
        $sql = "SELECT oi.*, p.product_name, pi.image_url,
                       pv.sku, pv.price as variant_price, pv.variant_key
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.product_id
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                LEFT JOIN product_variants pv ON oi.variant_id = pv.variant_id
                WHERE oi.order_id = $orderId
                ORDER BY oi.order_item_id";

        $rows = $this->fetchAll($sql);
        foreach ($rows as &$row) {
            $row['attributes'] = $this->resolveVariantKey($row['variant_key'] ?? '');
        }
        return $rows;
    }

    private function resolveVariantKey(string $key): string {
        if ($key === '' || $key === 'default') return '';
        $ids = array_filter(explode('_', $key), 'is_numeric');
        if (empty($ids)) return '';
        $idList = implode(',', array_map('intval', $ids));
        $sql    = "SELECT av.value_name, a.attribute_name
                   FROM attribute_values av
                   LEFT JOIN attributes a ON av.attribute_id = a.attribute_id
                   WHERE av.value_id IN ($idList)
                   ORDER BY a.attribute_name";
        $values = $this->fetchAll($sql);
        $labels = [];
        foreach ($values as $value) {
            $attrName  = $value['attribute_name'] ?? '';
            $valueName = $value['value_name']      ?? '';
            $labels[]  = $attrName !== '' ? $attrName . ': ' . $valueName : $valueName;
        }
        return implode(', ', $labels);
    }

    public function updateOrderStatus($orderId, $status) {
        $orderId = (int)$orderId;
        $status  = $this->escape($status);
        $sql = "UPDATE orders SET status = '$status' WHERE order_id = $orderId";
        return $this->query($sql);
    }

    public function getOrderStats() {
        $sql = "SELECT
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'shipping'  THEN 1 ELSE 0 END) as shipping,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(total_amount) as total_revenue
                FROM orders";
        return $this->fetchOne($sql);
    }

    public function getTotalOrders($status = null) {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE 1=1";
        if ($status) {
            $status = $this->escape($status);
            $sql .= " AND status = '$status'";
        }
        $result = $this->fetchOne($sql);
        return $result['count'] ?? 0;
    }
}
?>