<?php
/**
 * app/controllers/StatisticsController.php
 * Controller xử lý tất cả request liên quan đến thống kê
 */

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/StatisticsModel.php';

class StatisticsController extends Controller {

    private $statisticsModel;

    public function __construct() {
        parent::__construct();
        $this->statisticsModel = new StatisticsModel();
    }

    /**
     * Kiểm tra quyền admin
     */
    private function requireAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /WEB_GR4/login');
            exit;
        }

        if ($_SESSION['role'] !== 'admin') {
            header('Location: /WEB_GR4/');
            exit;
        }
    }

    /**
     * GET /admin/statistics
     * Hiển thị trang thống kê
     */
    public function index() {
        $this->requireAdmin();

        // Lấy khoảng thời gian (mặc định là tháng này)
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-t');

        // Lấy dữ liệu thống kê
        $dashboardStats = [
            'total_revenue' => $this->statisticsModel->getTotalRevenue($from, $to),
            'total_orders' => $this->statisticsModel->getTotalOrders($from, $to),
            'best_seller' => $this->statisticsModel->getBestSellingProduct($from, $to),
            'today_revenue' => $this->statisticsModel->getTodayRevenue(),
            'week_revenue' => $this->statisticsModel->getWeekRevenue(),
            'month_revenue' => $this->statisticsModel->getMonthRevenue(),
            'year_revenue' => $this->statisticsModel->getYearRevenue(),
        ];

        // Lấy dữ liệu biểu đồ
        $monthlyData = $this->statisticsModel->getMonthlyRevenue(date('Y'));
        $topProducts = $this->statisticsModel->getTopSellingProducts($from, $to, 5);
        $categoryRevenue = $this->statisticsModel->getRevenueByCategory($from, $to);
        $orderStatus = $this->statisticsModel->getOrderStatusStats();

        // Chuyển đổi dữ liệu cho Chart.js
        $chartData = [
            'monthly' => $this->prepareMonthlyChartData($monthlyData),
            'topProducts' => $this->prepareTopProductsChartData($topProducts),
            'categoryRevenue' => $this->prepareCategoryChartData($categoryRevenue),
            'orderStatus' => $this->prepareOrderStatusChartData($orderStatus),
        ];

        // Render view
        $this->render('admin/statistics', [
            'dashboardStats' => $dashboardStats,
            'chartData' => $chartData,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * GET /admin/api/statistics/stats
     * API: Lấy dữ liệu thống kê dạng JSON
     */
    public function getStats() {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }

            $from = $_GET['from'] ?? date('Y-m-01');
            $to = $_GET['to'] ?? date('Y-m-t');

            $stats = [
                'total_revenue' => $this->statisticsModel->getTotalRevenue($from, $to),
                'total_orders' => $this->statisticsModel->getTotalOrders($from, $to),
                'best_seller' => $this->statisticsModel->getBestSellingProduct($from, $to),
                'today_revenue' => $this->statisticsModel->getTodayRevenue(),
                'week_revenue' => $this->statisticsModel->getWeekRevenue(),
                'month_revenue' => $this->statisticsModel->getMonthRevenue(),
                'year_revenue' => $this->statisticsModel->getYearRevenue(),
            ];

            echo json_encode($stats);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * GET /admin/api/statistics/monthly-chart
     * API: Lấy dữ liệu doanh thu theo tháng
     */
    public function getMonthlyChart() {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $year = $_GET['year'] ?? date('Y');
            $monthlyData = $this->statisticsModel->getMonthlyRevenue($year);
            $chartData = $this->prepareMonthlyChartData($monthlyData);

            echo json_encode($chartData);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * GET /admin/api/statistics/top-products
     * API: Lấy dữ liệu sản phẩm bán chạy
     */
    public function getTopProductsChart() {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $from = $_GET['from'] ?? date('Y-m-01');
            $to = $_GET['to'] ?? date('Y-m-t');
            $limit = $_GET['limit'] ?? 5;

            $topProducts = $this->statisticsModel->getTopSellingProducts($from, $to, $limit);
            $chartData = $this->prepareTopProductsChartData($topProducts);

            echo json_encode($chartData);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * GET /admin/api/statistics/category
     * API: Lấy dữ liệu doanh thu theo danh mục
     */
    public function getCategoryChart() {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $from = $_GET['from'] ?? date('Y-m-01');
            $to = $_GET['to'] ?? date('Y-m-t');

            $categoryRevenue = $this->statisticsModel->getRevenueByCategory($from, $to);
            $chartData = $this->prepareCategoryChartData($categoryRevenue);

            echo json_encode($chartData);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * GET /admin/api/statistics/order-status
     * API: Lấy dữ liệu trạng thái đơn hàng
     */
    public function getOrderStatusChart() {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $orderStatus = $this->statisticsModel->getOrderStatusStats();
            $chartData = $this->prepareOrderStatusChartData($orderStatus);

            echo json_encode($chartData);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    // ═══ PRIVATE HELPER METHODS ═══

    private function prepareMonthlyChartData($monthlyData) {
        $labels = [];
        $revenues = [];

        foreach ($monthlyData as $data) {
            $labels[] = 'Tháng ' . $data['month'];
            $revenues[] = (float)$data['revenue'];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Doanh thu',
                    'data' => $revenues,
                    'borderColor' => 'rgb(124, 58, 237)',
                    'backgroundColor' => 'rgba(124, 58, 237, 0.1)',
                    'tension' => 0.3,
                    'fill' => true,
                ]
            ]
        ];
    }

    private function prepareTopProductsChartData($topProducts) {
        $labels = [];
        $quantities = [];
        $revenues = [];

        foreach ($topProducts as $product) {
            $labels[] = substr($product['product_name'], 0, 20);
            $quantities[] = (int)$product['total_sold'];
            $revenues[] = (float)$product['revenue'];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Số lượng bán',
                    'data' => $quantities,
                    'backgroundColor' => [
                        'rgba(124, 58, 237, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(124, 58, 237)',
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(249, 115, 22)',
                        'rgb(236, 72, 153)',
                    ],
                    'borderWidth' => 1,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Doanh thu',
                    'data' => $revenues,
                    'borderColor' => 'rgb(255, 193, 7)',
                    'backgroundColor' => 'rgba(255, 193, 7, 0.1)',
                    'yAxisID' => 'y1',
                ],
            ]
        ];
    }

    private function prepareCategoryChartData($categoryRevenue) {
        $labels = [];
        $data = [];
        $colors = [
            'rgba(124, 58, 237, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(34, 197, 94, 0.8)',
            'rgba(249, 115, 22, 0.8)', 'rgba(236, 72, 153, 0.8)', 'rgba(8, 145, 178, 0.8)',
            'rgba(190, 24, 93, 0.8)', 'rgba(156, 39, 176, 0.8)',
        ];

        foreach ($categoryRevenue as $index => $category) {
            $labels[] = $category['category_name'];
            $data[] = (float)$category['revenue'];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Doanh thu',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderColor' => array_slice($colors, 0, count($data)),
                    'borderWidth' => 2,
                ]
            ]
        ];
    }

    private function prepareOrderStatusChartData($orderStatus) {
        $labels = [
            'pending' => 'Chờ xử lý',
            'paid' => 'Đã thanh toán',
            'shipping' => 'Đang vận chuyển',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        $orderedStatuses = ['pending', 'paid', 'shipping', 'completed', 'cancelled'];
        $orderedLabels = [];
        $orderedData = [];
        $statusColors = [];
        $colorMap = [
            'pending' => 'rgba(255, 193, 7, 0.8)',
            'paid' => 'rgba(59, 130, 246, 0.8)',
            'shipping' => 'rgba(34, 197, 94, 0.8)',
            'completed' => 'rgba(34, 197, 94, 0.8)',
            'cancelled' => 'rgba(239, 68, 68, 0.8)',
        ];

        foreach ($orderedStatuses as $status) {
            $found = false;
            foreach ($orderStatus as $os) {
                if ($os['status'] === $status) {
                    $orderedLabels[] = $labels[$status];
                    $orderedData[] = (int)$os['count'];
                    $statusColors[] = $colorMap[$status];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $orderedLabels[] = $labels[$status];
                $orderedData[] = 0;
                $statusColors[] = $colorMap[$status];
            }
        }

        return [
            'labels' => $orderedLabels,
            'datasets' => [
                [
                    'label' => 'Số đơn hàng',
                    'data' => $orderedData,
                    'backgroundColor' => $statusColors,
                    'borderColor' => $statusColors,
                    'borderWidth' => 2,
                ]
            ]
        ];
    }
}
?>