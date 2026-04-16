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
            <h3 class="mb-1"><i class="bi bi-speedometer2"></i> لوحة التحكم</h3>
            <p class="text-muted mb-0">مرحباً بك في نظام إدارة متجر الأزياء</p>
        </div>
        <div>
            <span class="badge bg-success fs-6" id="current-date"></span>
        </div>
    </div>

    <!-- بطاقات الإحصائيات -->
    <div class="row g-3 mb-4">
        <!-- طلبات اليوم -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title opacity-75">طلبات اليوم</h6>
                            <h2 class="mb-0" id="stat-today-orders">0</h2>
                        </div>
                        <div class="fs-1 opacity-50"><i class="bi bi-cart"></i></div>
                    </div>
                    <small class="opacity-75">إيرادات: <span id="stat-today-revenue">0</span> ر.س</small>
                </div>
            </div>
        </div>

        <!-- إيرادات الشهر -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title opacity-75">إيرادات الشهر</h6>
                            <h2 class="mb-0" id="stat-month-revenue">0</h2>
                        </div>
                        <div class="fs-1 opacity-50"><i class="bi bi-currency-dollar"></i></div>
                    </div>
                    <small class="opacity-75">عدد الطلبات: <span id="stat-month-orders">0</span></small>
                </div>
            </div>
        </div>

        <!-- التذاكر المفتوحة -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title opacity-75">تذاكر مفتوحة</h6>
                            <h2 class="mb-0" id="stat-open-tickets">0</h2>
                        </div>
                        <div class="fs-1 opacity-50"><i class="bi bi-headset"></i></div>
                    </div>
                    <small class="opacity-75">تحتاج متابعة</small>
                </div>
            </div>
        </div>

        <!-- المنتجات منخفضة المخزون -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title opacity-75">مخزون منخفض</h6>
                            <h2 class="mb-0" id="stat-low-stock">0</h2>
                        </div>
                        <div class="fs-1 opacity-50"><i class="bi bi-exclamation-triangle"></i></div>
                    </div>
                    <small class="opacity-75">منتج يحتاج إعادة تزويد</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- رسم بياني للمبيعات -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> مخطط المبيعات (آخر 7 أيام)</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- الطلبات حسب الحالة -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> حالة الطلبات</h5>
                </div>
                <div class="card-body">
                    <canvas id="ordersStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- آخر الطلبات -->
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> آخر الطلبات</h5>
                    <a href="/admin/orders" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>العميل</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody id="recent-orders">
                                <tr><td colspan="5" class="text-center text-muted">جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- أعلى المنتجات مبيعاً -->
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-trophy"></i> أكثر المنتجات مبيعاً</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="top-products">
                        <div class="list-group-item text-center text-muted">جاري التحميل...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
/**
 * تحميل بيانات لوحة التحكم عبر API
 * Load dashboard data via API calls
 */
document.addEventListener('DOMContentLoaded', function() {
    // عرض التاريخ الحالي
    document.getElementById('current-date').textContent = new Date().toLocaleDateString('ar-SA', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    // جلب إحصائيات الطلبات
    loadDashboardStats();
});

/**
 * جلب الإحصائيات من API
 */
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

            // رسم المخططات البيانية
            renderSalesChart();
            renderOrdersStatusChart(data.by_status || []);
            renderTopProducts(data.top_products || []);
        }
    } catch (error) {
        console.error('خطأ في تحميل الإحصائيات:', error);
    }
}

/**
 * رسم مخطط المبيعات
 */
function renderSalesChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const labels = [];
    const data = [];
    
    // بيانات تجريبية لآخر 7 أيام
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
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
}

/**
 * رسم مخطط حالة الطلبات
 */
function renderOrdersStatusChart(statusData) {
    const ctx = document.getElementById('ordersStatusChart').getContext('2d');
    
    const statusLabels = {
        'pending': 'قيد الانتظار',
        'confirmed': 'مؤكد',
        'processing': 'قيد المعالجة',
        'shipped': 'تم الشحن',
        'delivered': 'مكتمل',
        'cancelled': 'ملغي'
    };
    
    const colors = ['#ffc107', '#17a2b8', '#0d6efd', '#6f42c1', '#198754', '#dc3545'];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(s => statusLabels[s.status] || s.status),
            datasets: [{
                data: statusData.map(s => s.count),
                backgroundColor: colors.slice(0, statusData.length)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

/**
 * عرض أكثر المنتجات مبيعاً
 */
function renderTopProducts(products) {
    const container = document.getElementById('top-products');
    if (products.length === 0) {
        container.innerHTML = '<div class="list-group-item text-center text-muted">لا توجد بيانات</div>';
        return;
    }

    container.innerHTML = products.map((p, i) => `
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-primary rounded-pill me-2">${i + 1}</span>
                ${p.name}
            </div>
            <div class="text-end">
                <small class="text-muted">${p.total_qty} قطعة</small><br>
                <strong class="text-success">${Number(p.total_revenue).toLocaleString('ar-SA')} ر.س</strong>
            </div>
        </div>
    `).join('');
}
</script>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
