<?php
/**
 * app/models/StatisticsModel.php
 * Model xử lý tất cả logic lấy dữ liệu thống kê từ database
 * ✅ FIXED: Tính TẤT CẢ đơn hàng (không filter status)
 */

require_once __DIR__ . '/../../config/database.php';

class StatisticsModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
        if (!$this->db) {
            throw new Exception('Database connection failed');
        }
    }

    /**
     * Lấy tổng doanh thu
     * @param string $from Ngày bắt đầu (Y-m-d)
     * @param string $to Ngày kết thúc (Y-m-d)
     * @return float
     */
    public function getTotalRevenue($from = null, $to = null) {
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total 
                  FROM orders 
                  WHERE 1=1";

        if ($from && $to) {
            $query .= " AND DATE(order_date) BETWEEN ? AND ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ss', $from, $to);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }

        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (float)($result['total'] ?? 0);
    }

    /**
     * Lấy tổng số đơn hàng
     * @param string $from Ngày bắt đầu (Y-m-d)
     * @param string $to Ngày kết thúc (Y-m-d)
     * @return int
     */
    public function getTotalOrders($from = null, $to = null) {
        $query = "SELECT COUNT(*) as total 
                  FROM orders 
                  WHERE 1=1";

        if ($from && $to) {
            $query .= " AND DATE(order_date) BETWEEN ? AND ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ss', $from, $to);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }

        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy sản phẩm bán chạy nhất
     * @param string $from Ngày bắt đầu (Y-m-d)
     * @param string $to Ngày kết thúc (Y-m-d)
     * @return array
     */
    public function getBestSellingProduct($from = null, $to = null) {
        $query = "SELECT 
                    p.product_id,
                    p.product_name,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.quantity * oi.unit_price) as revenue
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.product_id
                  JOIN orders o ON oi.order_id = o.order_id
                  WHERE 1=1";

        if ($from && $to) {
            $query .= " AND DATE(o.order_date) BETWEEN ? AND ?";
        }

        $query .= " GROUP BY p.product_id, p.product_name
                   ORDER BY total_sold DESC
                   LIMIT 1";

        if ($from && $to) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ss', $from, $to);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }

        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result ?: [
            'product_id' => null,
            'product_name' => 'Chưa có dữ liệu',
            'total_sold' => 0,
            'revenue' => 0
        ];
    }

    /**
     * Lấy doanh thu theo tháng (năm hiện tại)
     * @param int $year Năm (mặc định năm hiện tại)
     * @return array
     */
    public function getMonthlyRevenue($year = null) {
        if (!$year) {
            $year = date('Y');
        }

        $query = "SELECT 
                    MONTH(order_date) as month,
                    COALESCE(SUM(total_amount), 0) as revenue,
                    COUNT(*) as order_count
                  FROM orders
                  WHERE YEAR(order_date) = ?
                  GROUP BY MONTH(order_date)
                  ORDER BY MONTH(order_date) ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Điền các tháng không có dữ liệu
        $allMonths = [];
        for ($m = 1; $m <= 12; $m++) {
            $allMonths[$m] = [
                'month' => $m,
                'revenue' => 0,
                'order_count' => 0
            ];
        }

        foreach ($results as $result) {
            $allMonths[$result['month']] = $result;
        }

        return array_values($allMonths);
    }

    /**
     * Lấy top N sản phẩm bán chạy nhất
     * @param string $from Ngày bắt đầu (Y-m-d)
     * @param string $to Ngày kết thúc (Y-m-d)
     * @param int $limit Số lượng (mặc định 5)
     * @return array
     */
    public function getTopSellingProducts($from = null, $to = null, $limit = 5) {
        $query = "SELECT 
                    p.product_id,
                    p.product_name,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.quantity * oi.unit_price) as revenue
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.product_id
                  JOIN orders o ON oi.order_id = o.order_id
                  WHERE 1=1";

        if ($from && $to) {
            $query .= " AND DATE(o.order_date) BETWEEN ? AND ?";
        }

        $query .= " GROUP BY p.product_id, p.product_name
                   ORDER BY total_sold DESC
                   LIMIT ?";

        if ($from && $to) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ssi', $from, $to, $limit);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
        }

        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $results ?: [];
    }

    /**
     * Lấy doanh thu theo danh mục
     * @param string $from Ngày bắt đầu (Y-m-d)
     * @param string $to Ngày kết thúc (Y-m-d)
     * @return array
     */
    public function getRevenueByCategory($from = null, $to = null) {
        $query = "SELECT 
                    c.category_id,
                    c.category_name,
                    COALESCE(SUM(oi.quantity * oi.unit_price), 0) as revenue,
                    COALESCE(SUM(oi.quantity), 0) as total_sold
                  FROM categories c
                  LEFT JOIN products p ON c.category_id = p.category_id
                  LEFT JOIN order_items oi ON p.product_id = oi.product_id
                  LEFT JOIN orders o ON oi.order_id = o.order_id
                  WHERE 1=1";

        if ($from && $to) {
            $query .= " AND DATE(o.order_date) BETWEEN ? AND ?";
        }

        $query .= " GROUP BY c.category_id, c.category_name
                   ORDER BY revenue DESC";

        if ($from && $to) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ss', $from, $to);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }

        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $results ?: [];
    }

    /**
     * Lấy thống kê trạng thái đơn hàng
     * @return array
     */
    public function getOrderStatusStats() {
        $query = "SELECT 
                    status,
                    COUNT(*) as count,
                    COALESCE(SUM(total_amount), 0) as total_amount
                  FROM orders
                  GROUP BY status
                  ORDER BY status";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $results ?: [];
    }

    /**
     * Lấy doanh thu hôm nay
     * @return float
     */
    public function getTodayRevenue() {
        $today = date('Y-m-d');
        return $this->getTotalRevenue($today, $today);
    }

    /**
     * Lấy doanh thu tuần này
     * @return float
     */
    public function getWeekRevenue() {
        $start = date('Y-m-d', strtotime('monday this week'));
        $end = date('Y-m-d', strtotime('sunday this week'));
        return $this->getTotalRevenue($start, $end);
    }

    /**
     * Lấy doanh thu tháng này
     * @return float
     */
    public function getMonthRevenue() {
        $start = date('Y-m-01');
        $end = date('Y-m-t');
        return $this->getTotalRevenue($start, $end);
    }

    /**
     * Lấy doanh thu năm này
     * @return float
     */
    public function getYearRevenue() {
        $start = date('Y-01-01');
        $end = date('Y-12-31');
        return $this->getTotalRevenue($start, $end);
    }
}
?>