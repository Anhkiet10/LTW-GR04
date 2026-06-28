// public/assets/js/admin/statistics-dashboard.js

/**
 * Statistics Dashboard - Tab Navigation & Charts
 * Hoàn chỉnh với tất cả các biểu đồ và chức năng
 */

// ═══ KHAI BÁO BIẾN CHARTS GLOBAL ═══
let charts = {};

/**
 * ═══ KHỞI TẠO KHI DOM SẴN SÀNG ═══
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Statistics Dashboard initializing...');
    
    // Khởi tạo tab navigation
    setupTabs();
    
    // Khởi tạo tất cả biểu đồ
    initializeCharts();
    
    // Khởi tạo bảng dữ liệu
    setupTables();
    
    console.log('Statistics Dashboard initialized successfully!');
});


/**
 * ═══ TAB NAVIGATION ═══
 * Xử lý chuyển đổi giữa các tab
 */
function setupTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');

            // Ẩn tất cả tab content
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            // Bỏ active class khỏi tất cả button
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            // Hiển thị tab được click
            const targetContent = document.getElementById(tabName + '-tab');
            if (targetContent) {
                targetContent.classList.add('active');
            }
            this.classList.add('active');

            // Cập nhật biểu đồ khi tab thay đổi (để responsive)
            setTimeout(() => {
                Object.values(charts).forEach(chart => {
                    if (chart && chart.resize) {
                        chart.resize();
                    }
                });
            }, 100);
        });
    });
}

/**
 * ═══ INITIALIZE ALL CHARTS ═══
 * Khởi tạo tất cả biểu đồ dựa trên dữ liệu có sẵn
 */
function initializeCharts() {
    const data = window.chartData || {};
    console.log('Chart data:', data);

    // Tab 1: Biểu đồ doanh thu theo tháng
    if (data.monthly) {
        initMonthlyRevenueChart(data.monthly);
    }

    // Tab 2: Biểu đồ trạng thái đơn hàng
    if (data.orderStatus) {
        initOrderStatusChart(data.orderStatus);
    }

    // Tab 3: Biểu đồ top 5 sản phẩm bán chạy
    if (data.topProducts) {
        initTopProductsChart(data.topProducts);
    }

    // Tab 4: Biểu đồ chi tiết doanh thu theo tháng
    if (data.monthly) {
        initMonthlyDetailChart(data.monthly);
    }
}

/**
 * ═══ TAB 1: BIỂU ĐỒ DOANH THU THEO THÁNG ═══
 * Line Chart - Hiển thị xu hướng doanh thu
 */
function initMonthlyRevenueChart(data) {
    const ctx = document.getElementById('monthlyRevenueChart');
    
    if (!ctx) {
        console.warn('Canvas monthlyRevenueChart not found');
        return;
    }

    charts.monthlyRevenue = new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: { size: 13, weight: '500' },
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(31, 41, 55, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(124, 58, 237, 0.3)',
                    borderWidth: 2,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += formatCurrency(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: function(value) {
                            return formatCurrencyShort(value);
                        },
                        font: { size: 11 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    
    console.log('Monthly Revenue Chart initialized');
}

/**
 * ═══ TAB 2: BIỂU ĐỒ TRẠNG THÁI ĐƠN HÀNG ═══
 * Horizontal Bar Chart - Phân phối trạng thái đơn hàng
 */
function initOrderStatusChart(data) {
    const ctx = document.getElementById('orderStatusChart');
    
    if (!ctx) {
        console.warn('Canvas orderStatusChart not found');
        return;
    }

    charts.orderStatus = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            indexAxis: 'y', // Horizontal bar
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: { size: 13, weight: '500' },
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'rectRounded'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(31, 41, 55, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(124, 58, 237, 0.3)',
                    borderWidth: 2,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.x + ' đơn hàng';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        font: { size: 11 },
                        callback: function(value) {
                            return value + ' đơn';
                        }
                    }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 12, weight: '500' } }
                }
            }
        }
    });
    
    console.log('Order Status Chart initialized');
}

/**
 * ═══ TAB 3: BIỂU ĐỒ TOP 5 SẢN PHẨM BÁN CHẠY ═══
 * Mixed Chart (Bar + Line) - Số lượng và doanh thu
 */
function initTopProductsChart(data) {
    const ctx = document.getElementById('topProductsChart');
    
    if (!ctx) {
        console.warn('Canvas topProductsChart not found');
        return;
    }

    charts.topProducts = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: { size: 13, weight: '500' },
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'rectRounded'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(31, 41, 55, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(124, 58, 237, 0.3)',
                    borderWidth: 2,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            
                            if (context.dataset.yAxisID === 'y1') {
                                label += formatCurrency(context.parsed.y);
                            } else {
                                label += context.parsed.y + ' sản phẩm';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Số lượng bán',
                        font: { size: 12, weight: '500' },
                        color: '#6b7280'
                    },
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        beginAtZero: true,
                        font: { size: 11 }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Doanh thu (₫)',
                        font: { size: 12, weight: '500' },
                        color: '#6b7280'
                    },
                    grid: { drawOnChartArea: false },
                    ticks: {
                        callback: function(value) {
                            return formatCurrencyShort(value);
                        },
                        font: { size: 11 }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    
    console.log('Top Products Chart initialized');
}

/**
 * ═══ TAB 4: BIỂU ĐỒ CHI TIẾT DOANH THU THEO THÁNG ═══
 * Line Chart - Hiển thị chi tiết 12 tháng
 */
function initMonthlyDetailChart(data) {
    const ctx = document.getElementById('monthlyDetailChart');
    
    if (!ctx) {
        console.warn('Canvas monthlyDetailChart not found');
        return;
    }

    // Đảm bảo dữ liệu có cấu trúc đúng
    const chartData = {
        labels: data.labels || [],
        datasets: data.datasets || []
    };

    // Nếu chưa có dataset, tạo mới
    if (chartData.datasets.length === 0) {
        chartData.datasets = [{
            label: 'Doanh thu',
            data: [],
            borderColor: 'rgb(124, 58, 237)',
            backgroundColor: 'rgba(124, 58, 237, 0.05)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointBackgroundColor: 'rgb(124, 58, 237)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }];
    }

    charts.monthlyDetail = new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: { size: 13, weight: '500' },
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(31, 41, 55, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(124, 58, 237, 0.3)',
                    borderWidth: 2,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                    },
                    ticks: {
                        callback: function(value) {
                            return formatCurrencyShort(value);
                        },
                        font: { size: 11 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    
    console.log('Monthly Detail Chart initialized');
}

/**
 * ═══ SETUP TABLES ═══
 * Khởi tạo bảng dữ liệu cho các tab
 */
function setupTables() {
    const data = window.chartData || {};
    
    // Tab 3: Bảng top sản phẩm
    if (data.topProducts) {
        updateTopProductsTable(data.topProducts);
    }
    
    // Tab 4: Bảng doanh thu theo tháng
    if (data.monthly) {
        updateMonthlyTable(data.monthly);
    }
}

/**
 * ═══ UPDATE TABLES ═══
 * Cập nhật bảng dữ liệu
 */

/**
 * Cập nhật bảng Top Products (Tab 3)
 */
function updateTopProductsTable(data) {
    const tbody = document.getElementById('topProductsTableBody');
    if (!tbody) return;

    let products = [];
    
    // Lấy dữ liệu từ chart data
    if (data && data.labels && data.datasets) {
        const quantities = data.datasets[0]?.data || [];
        const revenues = data.datasets[1]?.data || [];
        const totalRevenue = window.statisticsData?.totalRevenue || 0;
        
        for (let i = 0; i < data.labels.length; i++) {
            products.push({
                name: data.labels[i],
                quantity: quantities[i] || 0,
                revenue: revenues[i] || 0,
                percentage: totalRevenue > 0 ? (revenues[i] / totalRevenue * 100) : 0
            });
        }
    }

    if (products.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center; padding:40px; color:#9ca3af;">
                    <i class="fas fa-inbox" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                    Chưa có dữ liệu sản phẩm
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    const icons = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
    
    products.forEach((product, index) => {
        const rank = index < 3 ? icons[index] : `#${index + 1}`;
        html += `
            <tr>
                <td><strong>${rank}</strong></td>
                <td><strong>${product.name}</strong></td>
                <td>${product.quantity.toLocaleString()}</td>
                <td>${formatCurrency(product.revenue)}</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${Math.min(product.percentage, 100)}%; background: #7c3aed;"></div>
                        <span class="progress-label">${product.percentage.toFixed(1)}%</span>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

/**
 * Cập nhật bảng doanh thu theo tháng (Tab 4)
 */
function updateMonthlyTable(data) {
    const tbody = document.getElementById('monthlyTableBody');
    if (!tbody) return;

    let months = [];
    
    if (data && data.labels && data.datasets) {
        const revenues = data.datasets[0]?.data || [];
        const monthNames = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                            'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
        
        for (let i = 0; i < data.labels.length; i++) {
            const revenue = revenues[i] || 0;
            // Ước tính số đơn dựa trên doanh thu
            const orders = Math.round(revenue / 500000) || 0;
            const avg = orders > 0 ? revenue / orders : 0;
            
            months.push({
                label: data.labels[i] || monthNames[i] || 'Tháng ' + (i + 1),
                revenue: revenue,
                orders: orders,
                average: avg
            });
        }
    }

    if (months.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center; padding:40px; color:#9ca3af;">
                    <i class="fas fa-inbox" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                    Chưa có dữ liệu tháng
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    const maxRevenue = Math.max(...months.map(m => m.revenue), 1);
    
    months.forEach(month => {
        const trend = (month.revenue / maxRevenue * 100);
        const trendIcon = trend >= 70 ? '📈' : trend >= 40 ? '➡️' : '📉';
        const trendColor = trend >= 70 ? '#22c55e' : trend >= 40 ? '#f59e0b' : '#ef4444';
        
        html += `
            <tr>
                <td><strong>${month.label}</strong></td>
                <td>${formatCurrency(month.revenue)}</td>
                <td>${month.orders.toLocaleString()}</td>
                <td>${formatCurrency(month.average)}</td>
                <td>
                    <span style="font-size:20px;">${trendIcon}</span>
                    <span style="color:${trendColor}; font-size:12px; margin-left:4px;">
                        ${trend.toFixed(0)}%
                    </span>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

/**
 * ═══ FORMAT FUNCTIONS ═══
 * Hàm định dạng số và tiền tệ
 */

/**
 * Format tiền tệ (đầy đủ)
 * Ví dụ: 1234567 -> "1.234.567 ₫"
 */
function formatCurrency(value) {
    if (value === null || value === undefined || isNaN(value)) return '0 ₫';
    return Number(value).toLocaleString('vi-VN') + ' ₫';
}

/**
 * Format tiền tệ (rút gọn)
 * Ví dụ: 1234567 -> "1.2M"
 */
function formatCurrencyShort(value) {
    if (value === null || value === undefined || isNaN(value)) return '0';
    
    if (value >= 1000000000) {
        return (value / 1000000000).toFixed(1) + ' tỷ';
    } else if (value >= 1000000) {
        return (value / 1000000).toFixed(1) + ' tr';
    } else if (value >= 1000) {
        return (value / 1000).toFixed(1) + ' K';
    }
    return value.toFixed(0);
}

/**
 * ═══ UPDATE CHARTS WITH FILTER ═══
 * Cập nhật tất cả biểu đồ khi filter thay đổi
 */
async function updateAllCharts() {
    const fromInput = document.querySelector('input[name="from"]');
    const toInput = document.querySelector('input[name="to"]');

    if (!fromInput || !toInput) return;

    const from = fromInput.value;
    const to = toInput.value;

    // Cập nhật stat cards
    try {
        const response = await fetch(`/WEB_GR4/admin/api/statistics/stats?from=${from}&to=${to}`);
        const stats = await response.json();
        updateStatCards(stats);
        
        if (window.statisticsData) {
            window.statisticsData.totalRevenue = stats.total_revenue || 0;
            window.statisticsData.totalOrders = stats.total_orders || 0;
        }
    } catch (error) {
        console.error('Lỗi cập nhật stat cards:', error);
    }

    // Cập nhật các biểu đồ
    try {
        // Cập nhật biểu đồ tháng
        const monthlyResponse = await fetch(`/WEB_GR4/admin/api/statistics/monthly-chart`);
        const monthlyData = await monthlyResponse.json();
        if (charts.monthlyRevenue) {
            charts.monthlyRevenue.data = monthlyData;
            charts.monthlyRevenue.update();
        }
        if (charts.monthlyDetail) {
            const barData = {
                labels: monthlyData.labels || [],
                datasets: monthlyData.datasets || []
            };
            charts.monthlyDetail.data = barData;
            charts.monthlyDetail.update();
        }
        updateMonthlyTable(monthlyData);

        // Cập nhật biểu đồ top products
        const topProductsResponse = await fetch(`/WEB_GR4/admin/api/statistics/top-products?from=${from}&to=${to}`);
        const topProductsData = await topProductsResponse.json();
        if (charts.topProducts) {
            charts.topProducts.data = topProductsData;
            charts.topProducts.update();
        }
        updateTopProductsTable(topProductsData);

        // Cập nhật biểu đồ order status
        const orderStatusResponse = await fetch('/WEB_GR4/admin/api/statistics/order-status');
        const orderStatusData = await orderStatusResponse.json();
        if (charts.orderStatus) {
            charts.orderStatus.data = orderStatusData;
            charts.orderStatus.update();
        }
    } catch (error) {
        console.error('Lỗi cập nhật biểu đồ:', error);
    }
}

/**
 * Cập nhật các stat cards
 */
function updateStatCards(stats) {
    // Cập nhật các thẻ trong tab Revenue
    const statNumbers = document.querySelectorAll('.stat-card .stat-number');
    
    if (statNumbers.length >= 4) {
        if (statNumbers[0]) {
            statNumbers[0].textContent = formatCurrency(stats.total_revenue || 0);
        }
        if (statNumbers[1]) {
            statNumbers[1].textContent = formatCurrency(stats.today_revenue || 0);
        }
        if (statNumbers[2]) {
            statNumbers[2].textContent = formatCurrency(stats.week_revenue || 0);
        }
        if (statNumbers[3]) {
            statNumbers[3].textContent = formatCurrency(stats.month_revenue || 0);
        }
    }

    // Cập nhật sản phẩm bán chạy nhất
    const bestsellerName = document.querySelector('.featured-info h2');
    const bestsellerQty = document.querySelector('.featured-stat .value');
    const bestsellerRevenue = document.querySelectorAll('.featured-stat .value')[1];
    
    if (bestsellerName && stats.best_seller) {
        bestsellerName.textContent = stats.best_seller.product_name || 'Chưa có dữ liệu';
    }
    if (bestsellerQty && stats.best_seller) {
        bestsellerQty.textContent = (stats.best_seller.total_sold || 0).toLocaleString();
    }
    if (bestsellerRevenue && stats.best_seller) {
        bestsellerRevenue.textContent = formatCurrency(stats.best_seller.revenue || 0);
    }
}

/**
 * ═══ ADD DYNAMIC STYLES ═══
 * Thêm CSS động cho progress bar
 */
(function addStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .progress-bar {
            position: relative;
            width: 100%;
            max-width: 150px;
            height: 20px;
            background: #f3f4f6;
            border-radius: 10px;
            overflow: hidden;
            display: inline-block;
        }
        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.8s ease;
        }
        .progress-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 11px;
            font-weight: 600;
            color: #1f2937;
        }
    `;
    document.head.appendChild(style);
})();

/**
 * ═══ EXPOSE FUNCTIONS TO GLOBAL ═══
 * Để có thể gọi từ bên ngoài
 */
window.updateAllCharts = updateAllCharts;
window.formatCurrency = formatCurrency;
window.formatCurrencyShort = formatCurrencyShort;