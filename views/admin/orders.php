<?php
/**
 * صفحة إدارة الطلبات - Admin Orders
 */
$currentPage = 'orders';
require __DIR__ . '/../layouts/admin-header.php';
?>

<main class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-cart-check"></i> إدارة الطلبات</h3>
        <div>
            <select class="form-select form-select-sm d-inline-block" style="width:auto" id="filter-status" onchange="loadOrders()">
                <option value="">الكل</option>
                <option value="pending">قيد الانتظار</option>
                <option value="confirmed">مؤكد</option>
                <option value="processing">قيد المعالجة</option>
                <option value="shipped">تم الشحن</option>
                <option value="delivered">مكتمل</option>
                <option value="cancelled">ملغي</option>
            </select>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th>حالة الدفع</th>
                            <th>حالة الطلب</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="orders-table">
                        <tr><td colspan="8" class="text-center">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ترقيم الصفحات -->
    <nav class="mt-3">
        <ul class="pagination justify-content-center" id="orders-pagination"></ul>
    </nav>
</main>

<!-- نافذة تفاصيل الطلب -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل الطلب</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="order-detail-body">جاري التحميل...</div>
            <div class="modal-footer">
                <select class="form-select" style="width:auto" id="update-status-select">
                    <option value="pending">قيد الانتظار</option>
                    <option value="confirmed">مؤكد</option>
                    <option value="processing">قيد المعالجة</option>
                    <option value="shipped">تم الشحن</option>
                    <option value="delivered">مكتمل</option>
                    <option value="cancelled">ملغي</option>
                </select>
                <button class="btn btn-primary" onclick="updateOrderStatus()">تحديث الحالة</button>
            </div>
        </div>
    </div>
</div>

<script>
const API = '/api';
let currentOrderId = null;

document.addEventListener('DOMContentLoaded', loadOrders);

async function loadOrders(page = 1) {
    try {
        const res = await fetch(`${API}/orders?page=${page}`);
        const result = await res.json();
        const tbody = document.getElementById('orders-table');
        
        if (result.success && result.data?.data) {
            const statusBadge = {
                'pending': 'warning', 'confirmed': 'info', 'processing': 'primary',
                'shipped': 'purple', 'delivered': 'success', 'cancelled': 'danger', 'refunded': 'dark'
            };
            const statusLabel = {
                'pending': 'قيد الانتظار', 'confirmed': 'مؤكد', 'processing': 'قيد المعالجة',
                'shipped': 'تم الشحن', 'delivered': 'مكتمل', 'cancelled': 'ملغي', 'refunded': 'مسترجع'
            };
            const paymentLabel = {
                'cash_on_delivery': 'عند الاستلام', 'credit_card': 'بطاقة ائتمان',
                'bank_transfer': 'تحويل بنكي', 'wallet': 'محفظة'
            };
            const payStatusBadge = { 'pending': 'warning', 'paid': 'success', 'failed': 'danger', 'refunded': 'dark' };

            tbody.innerHTML = result.data.data.map(o => `
                <tr>
                    <td><code>${o.order_number}</code></td>
                    <td>${o.shipping_name || o.customer_id}</td>
                    <td class="fw-bold">${Number(o.total_amount).toLocaleString('ar-SA')} ر.س</td>
                    <td>${paymentLabel[o.payment_method] || o.payment_method}</td>
                    <td><span class="badge bg-${payStatusBadge[o.payment_status]}">${o.payment_status}</span></td>
                    <td><span class="badge bg-${statusBadge[o.status]}">${statusLabel[o.status]}</span></td>
                    <td><small>${o.created_at}</small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${o.id})">
                            <i class="bi bi-eye"></i> تفاصيل
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (e) { console.error(e); }
}

async function viewOrder(id) {
    currentOrderId = id;
    const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
    modal.show();

    try {
        const res = await fetch(`${API}/orders/${id}`);
        const result = await res.json();
        
        if (result.success && result.data) {
            const o = result.data;
            document.getElementById('update-status-select').value = o.status;
            
            let itemsHtml = o.items?.map(i => `
                <tr>
                    <td>${i.product_name}</td>
                    <td>${i.color_name || '-'} / ${i.size_name || '-'}</td>
                    <td>${i.quantity}</td>
                    <td>${Number(i.unit_price).toLocaleString('ar-SA')}</td>
                    <td class="fw-bold">${Number(i.total_price).toLocaleString('ar-SA')}</td>
                </tr>
            `).join('') || '';

            let historyHtml = o.status_history?.map(h => `
                <li class="list-group-item d-flex justify-content-between">
                    <span><strong>${h.status}</strong> - ${h.notes || ''}</span>
                    <small class="text-muted">${h.created_at}</small>
                </li>
            `).join('') || '';

            document.getElementById('order-detail-body').innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>بيانات الطلب</h6>
                        <p>رقم الطلب: <code>${o.order_number}</code></p>
                        <p>العميل: ${o.customer_name || '-'}</p>
                        <p>البريد: ${o.customer_email || '-'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>بيانات الشحن</h6>
                        <p>الاسم: ${o.shipping_name || '-'}</p>
                        <p>الهاتف: ${o.shipping_phone || '-'}</p>
                        <p>العنوان: ${o.shipping_address || '-'}</p>
                        <p>المدينة: ${o.shipping_city || '-'}</p>
                    </div>
                </div>
                <h6>عناصر الطلب</h6>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>المنتج</th><th>اللون/الحجم</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr></thead>
                    <tbody>${itemsHtml}</tbody>
                    <tfoot>
                        <tr><td colspan="4" class="text-end">المجموع الفرعي:</td><td>${Number(o.subtotal).toLocaleString('ar-SA')} ر.س</td></tr>
                        <tr><td colspan="4" class="text-end">الضريبة:</td><td>${Number(o.vat_amount).toLocaleString('ar-SA')} ر.س</td></tr>
                        <tr class="fw-bold"><td colspan="4" class="text-end">الإجمالي:</td><td>${Number(o.total_amount).toLocaleString('ar-SA')} ر.س</td></tr>
                    </tfoot>
                </table>
                <h6>سجل الحالات</h6>
                <ul class="list-group">${historyHtml || '<li class="list-group-item">لا يوجد</li>'}</ul>
            `;
        }
    } catch (e) { console.error(e); }
}

async function updateOrderStatus() {
    if (!currentOrderId) return;
    const status = document.getElementById('update-status-select').value;
    
    try {
        const res = await fetch(`${API}/orders/${currentOrderId}/status`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status, notes: 'تحديث من لوحة التحكم', changed_by: 'مشرف' })
        });
        const result = await res.json();
        if (result.success) {
            alert('تم تحديث الحالة بنجاح');
            loadOrders();
        } else {
            alert(result.message);
        }
    } catch (e) { console.error(e); }
}
</script>

<?php require __DIR__ . '/../layouts/admin-footer.php'; ?>
