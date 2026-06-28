<?php
require_once __DIR__ . '/../../core/Model.php';

class OrderModel extends Model {

    public function getAllOrders($limit = null, $offset = 0, $status = null, $guestOnly = false, $search = '') {
        $sql = "SELECT o.*,
                       COALESCE(u.full_name,  o.guest_name)    AS full_name,
                       COALESCE(u.email,      o.guest_email)   AS email,
                       COALESCE(u.phone,      o.guest_phone)   AS phone,
                       COALESCE(a.full_address, o.guest_address) AS full_address,
                       COALESCE(a.city,         o.guest_city)    AS city,
                       IF(o.user_id IS NULL, 1, 0)             AS is_guest,
                       COUNT(oi.order_item_id) as item_count,
                       p.payment_method, p.payment_status, p.paid_at
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                LEFT JOIN addresses a ON o.address_id = a.address_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN payments p ON o.order_id = p.order_id
                WHERE 1=1";

        if ($status) {
            $s = $this->escape($status);
            $sql .= " AND o.status = '$s'";
        }

        if ($guestOnly) {
            $sql .= " AND o.user_id IS NULL";
        }

        if ($search !== '') {
            $q = $this->escape($search);
            $sql .= " AND (
                COALESCE(u.email,   o.guest_email) LIKE '%$q%'
                OR COALESCE(u.phone, o.guest_phone) LIKE '%$q%'
                OR COALESCE(u.full_name, o.guest_name) LIKE '%$q%'
                OR o.order_id = " . ((int)$search ?: 0) . "
            )";
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
                       COALESCE(u.full_name,  o.guest_name)    AS full_name,
                       COALESCE(u.email,      o.guest_email)   AS email,
                       COALESCE(u.phone,      o.guest_phone)   AS phone,
                       COALESCE(a.full_address, o.guest_address) AS full_address,
                       COALESCE(a.city,         o.guest_city)    AS city,
                       IF(o.user_id IS NULL, 1, 0)             AS is_guest,
                       p.payment_method, p.payment_status, p.transaction_id, p.paid_at
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                LEFT JOIN addresses a ON o.address_id = a.address_id
                LEFT JOIN payments p ON o.order_id = p.order_id
                WHERE o.order_id = $orderId";

        return $this->fetchOne($sql);
    }

    public function getOrderItems($orderId) {
        $orderId = (int)$orderId;
        $sql = "SELECT oi.*, p.product_name,
                       COALESCE(piv.image_url, pip.image_url) as image_url,
                       pv.sku, pv.price as variant_price, pv.variant_key, pv.stock_quantity
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.product_id
                LEFT JOIN product_images piv ON oi.variant_id = piv.variant_id AND piv.is_primary = 1
                LEFT JOIN product_images pip ON oi.product_id = pip.product_id AND pip.variant_id IS NULL AND pip.is_primary = 1
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
        $sql = "SELECT av.value_name, a.attribute_name
                FROM attribute_values av
                LEFT JOIN attributes a ON av.attribute_id = a.attribute_id
                WHERE av.value_id IN ($idList)
                ORDER BY a.attribute_name";
        $values = $this->fetchAll($sql);
        return implode(', ', array_column($values, 'value_name'));
    }

    public function updateOrderStatus($orderId, $status) {
        $orderId = (int)$orderId;
        $status  = $this->escape($status);
        $sql = "UPDATE orders SET status = '$status' WHERE order_id = $orderId";
        return $this->query($sql);
    }

    public function updatePaymentStatus($orderId, $status) {
        $orderId = (int)$orderId;
        $status  = $this->escape($status);
        $paidAt  = ($status === 'paid') ? ', paid_at = NOW()' : '';
        $sql = "UPDATE payments
                SET payment_status = '$status' $paidAt
                WHERE order_id = $orderId";
        return $this->query($sql);
    }

    public function getOrderStats() {
        $sql = "SELECT
                    COUNT(DISTINCT o.order_id) as total_orders,
                    SUM(CASE WHEN o.status = 'pending'   THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN o.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN o.status = 'shipping'  THEN 1 ELSE 0 END) as shipping,
                    SUM(CASE WHEN o.status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN p.payment_status = 'processing' THEN 1 ELSE 0 END) as awaiting_approval,
                    SUM(CASE WHEN o.user_id IS NULL THEN 1 ELSE 0 END) as guest_orders,
                    SUM(CASE WHEN o.status != 'cancelled' THEN o.total_amount ELSE 0 END) as total_revenue
                FROM orders o
                LEFT JOIN payments p ON o.order_id = p.order_id";
        return $this->fetchOne($sql);
    }

    public function getTotalOrders($status = null, $guestOnly = false, $search = '') {
        $sql = "SELECT COUNT(DISTINCT o.order_id) as count
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                WHERE 1=1";

        if ($status) {
            $s = $this->escape($status);
            $sql .= " AND o.status = '$s'";
        }

        if ($guestOnly) {
            $sql .= " AND o.user_id IS NULL";
        }

        if ($search !== '') {
            $q = $this->escape($search);
            $sql .= " AND (
                COALESCE(u.email,    o.guest_email) LIKE '%$q%'
                OR COALESCE(u.phone, o.guest_phone) LIKE '%$q%'
                OR COALESCE(u.full_name, o.guest_name) LIKE '%$q%'
                OR o.order_id = " . ((int)$search ?: 0) . "
            )";
        }

        $result = $this->fetchOne($sql);
        return $result['count'] ?? 0;
    }
}
?>