<?php
require_once __DIR__ . '/../../core/Model.php';

class OrderModel extends Model {

    public function getAllOrders($limit = null, $offset = 0, $status = null) {
        $sql = "SELECT o.*, u.full_name, u.email, u.phone,
                       a.full_address, a.city,
                       COUNT(oi.order_item_id) as item_count,
                       p.payment_method, p.payment_status
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                LEFT JOIN addresses a ON o.address_id = a.address_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN payments p ON o.order_id = p.order_id
                WHERE 1=1";

        if ($status) {
            $status = $this->escape($status);
            $sql .= " AND o.status = '$status'";
        }

        $sql .= " GROUP BY o.order_id
                  ORDER BY o.order_date DESC";

        if ($limit) {
            $offset = (int)$offset;
            $limit = (int)$limit;
            $sql .= " LIMIT $offset, $limit";
        }

        return $this->fetchAll($sql);
    }

    public function getOrderById($orderId) {
        $orderId = (int)$orderId;
        $sql = "SELECT o.*, u.full_name, u.email, u.phone,
                       a.full_address, a.city,
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
        $sql = "SELECT oi.*, p.product_name, pi.image_url
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.product_id
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                WHERE oi.order_id = $orderId
                ORDER BY oi.order_item_id";

        return $this->fetchAll($sql);
    }

    public function updateOrderStatus($orderId, $status) {
        $orderId = (int)$orderId;
        $status = $this->escape($status);

        $sql = "UPDATE orders SET status = '$status' WHERE order_id = $orderId";
        return $this->query($sql);
    }

    public function getOrderStats() {
        $sql = "SELECT
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
                    SUM(CASE WHEN status = 'shipping' THEN 1 ELSE 0 END) as shipping,
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
