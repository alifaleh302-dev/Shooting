<?php
/**
 * لوحة التحكم الرئيسية - Admin Dashboard
 */
$currentPage = 'dashboard';
require __DIR__ . '/../layout/admin-header.php';
?>

<!-- المحتوى الرئيسي -->
<main class="main-content flex-grow-1 p-4">
    <!-- شريط العنوان -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1"><i class="bi bi-speedometer2 text-primary"></i> لوحة التحكم</h3>
            <p class="text-muted mb-0">مرحباً بك في نظام إدارة متجر الأزياء</p>
        </div>
        <div>
            <span class="badge bg-light text-dark border fs-6 p-2" id="current-date"></span>
        </div>
    </div>

    <!-- بطاقات الإحصائيات -->
    <div class="row g-3 mb-4">
        <!-- طلبات اليوم -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title opacity-75">طلبات اليوم</h6>
                            <h2 class="mb-0 fw-bold" id="stat-today-orders">0</h2>
                        </div>
                        <div class="fs-1 opacity-50"><i class="bi bi-cart-plus"></i></div>
                    </div>
                    <div class="mt-2 small opacity-75">إيرادات: <span id="stat-today-revenue" class="fw-bold">0</span> ر.س</div>
                </div>
            </div>
        </div>

        <!-- إيرادات الشهر -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title opacity-75">إيرادات الشهر</h6>
                            <h2 class="mb-0 fw-bold" id="stat-month-revenue">0</h2>
                        </div>
                        <div class="fs-1 opacity-50"><i class="bi bi-cash-stack"></i></div>
                    </div>
                    <div class="mt-2 small opacity-75">عدد الطلبات: <span id="stat-month-orders" class="fw-bold">0</span></div>
                </div>
            </div>
        </div>

        <!-- التذاكر المفتوحة -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title opacity-75">تذاكر مفتوحة</h6>
                            <h2 class="mb-0 fw-bold" id="stat-open-tickets">0</h2>
                        </div>
                        <div class="fs-1 opacity-50"><i class="bi bi-headset"></i></div>
                    </div>
                    <div class="mt-2 small opacity-75">تحتاج متابعة فورية</div>
                </div>
            </div>
        </div>

        <!-- المنتجات منخفضة المخزون -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title opacity-75">مخزون منخفض</h6>
                            <h2 class="mb-0 fw-bold" id="stat-low-stock">0</h2>
                        </div>
                        <div class="fs-1 opacity-50"><i class="bi bi-box-seam"></i></div>
                    </div>
                    <div class="mt-2 small opacity-75">منتجات تحتاج إعادة تزويد</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- رسم بياني للمبيعات -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up text-primary me-1"></i> مخطط المبيعات (آخر 7 أيام)</h6>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- الطلبات حسب الحالة -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart text-primary me-1"></i> حالة الطلبات</h6>
                </div>
                <div class="card-body d-flex align-items-center">
                    <canvas id="ordersStatusChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- آخر الطلبات -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-1"></i> آخر الطلبات</h6>
                    <a href="/admin/orders" class="btn btn-sm btn-link text-decoration-none">عرض الكل <i class="bi bi-arrow-left small"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">رقم الطلب</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody id="recent-orders">
                                <tr><td colspan="4" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- أعلى المنتجات مبيعاً -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-trophy text-primary me-1"></i> أكثر المنتجات مبيعاً</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="top-products">
                        <div class="list-group-item text-center py-4 text-muted">جاري التحميل...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // عرض التاريخ الحالي
    document.getElementById('current-date').textContent = new Date().toLocaleDateString('ar-SA', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    loadDashboardStats();
});

async function loadDashboardStats() {
    try {
        const response = await fetch('/api/orders/stats');
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            
            // تحديث البطاقات
            document.getElementById('stat-today-orders').textContent = data.today_orders?.count || 0;
            document.getElementById('stat-today-revenue').textContent = 
                Number(data.today_orders?.revenue || 0).toLocaleString('ar-SA');
            document.getElementById('stat-month-orders').textContent = data.month_orders?.count || 0;
            document.getElementById('stat-month-revenue').textContent = 
                Number(data.month_orders?.revenue || 0).toLocaleString('ar-SA');
            
            // تحديث البطاقات الجديدة
            document.getElementById('stat-open-tickets').textContent = data.open_tickets || 0;
            document.getElementById('stat-low-stock').textContent = data.low_stock_count || 0;

            // رسم المخططات البيانية
            renderSalesChart();
            renderOrdersStatusChart(data.by_status || []);
            renderTopProducts(data.top_products || []);
            renderRecentOrders(data.recent_orders || []);
        }
    } catch (error) {
        console.error('خطأ في تحميل الإحصائيات:', error);
    }
}

function renderSalesChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const labels = [];
    const data = [];
    
    // ملاحظة: حالياً نستخدم بيانات تجريبية للمخطط الزمني لأن الـ API لا يوفرها بعد
    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        labels.push(date.toLocaleDateString('ar-SA', { month: 'short', day: 'numeric' }));
        data.push(Math.floor(Math.random() * 5000) + 1000);
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'المبيعات (ر.س)',
                data: data,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.05)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });
}

function renderOrdersStatusChart(statusData) {
    const ctx = document.getElementById('ordersStatusChart').getContext('2d');
    
    const statusLabels = {
        'pending': 'قيد الانتظار',
        'confirmed': 'مؤكد',
        'processing': 'قيد المعالجة',
        'shipped': 'تم الشحن',
        'delivered': 'مكتمل',
        'cancelled': 'ملغي',
        'refunded': 'مسترجع'
    };
    
    const colors = ['#ffc107', '#17a2b8', '#0d6efd', '#6f42c1', '#198754', '#dc3545', '#6c757d'];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(s => statusLabels[s.status] || s.status),
            datasets: [{
                data: statusData.map(s => s.count),
                backgroundColor: colors.slice(0, statusData.length),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, font: { size: 11 } } }
            }
        }
    });
}

function renderTopProducts(products) {
    const container = document.getElementById('top-products');
    if (!products || products.length === 0) {
        container.innerHTML = '<div class="list-group-item text-center py-4 text-muted">لا توجد بيانات مبيعات حالياً</div>';
        return;
    }

    container.innerHTML = products.map((p, i) => `
        <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 border-bottom">
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-primary rounded-circle me-3" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">${i + 1}</span>
                <span class="fw-medium">${escapeHtml(p.name)}</span>
            </div>
            <div class="text-end">
                <div class="fw-bold text-success">${Number(p.total_revenue).toLocaleString('ar-SA')} ر.س</div>
                <small class="text-muted">${p.total_qty} قطعة</small>
            </div>
        </div>
    `).join('');
}

function renderRecentOrders(orders) {
    const container = document.getElementById('recent-orders');
    if (!orders || orders.length === 0) {
        container.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">لا توجد طلبات حديثة</td></tr>';
        return;
    }

    const statusBadges = {
        'pending': 'bg-warning text-dark',
        'confirmed': 'bg-info text-white',
        'processing': 'bg-primary text-white',
        'shipped': 'bg-secondary text-white',
        'delivered': 'bg-success text-white',
        'cancelled': 'bg-danger text-white',
        'refunded': 'bg-dark text-white'
    };

    const statusLabels = {
        'pending': 'انتظار',
        'confirmed': 'مؤكد',
        'processing': 'معالجة',
        'shipped': 'شحن',
        'delivered': 'مكتمل',
        'cancelled': 'ملغي',
        'refunded': 'مسترجع'
    };

    container.innerHTML = orders.map(o => `
        <tr>
            <td class="ps-3 fw-medium">${o.order_number}</td>
            <td class="fw-bold">${Number(o.total_amount).toLocaleString('ar-SA')} ر.س</td>
            <td><span class="badge ${statusBadges[o.status] || 'bg-light text-dark'} rounded-pill">${statusLabels[o.status] || o.status}</span></td>
            <td class="text-muted small">${new Date(o.created_at).toLocaleDateString('ar-SA')}</td>
        </tr>
    `).join('');
}

function escapeHtml(s) {
    if (!s) return '';
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
</script>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
