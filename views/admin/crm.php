<?php
/**
 * صفحة خدمة العملاء CRM - Admin CRM Page
 */
$currentPage = 'crm';
require __DIR__ . '/../layout/admin-header.php';
?>

<main class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-headset"></i> خدمة العملاء - CRM</h3>
    </div>

    <!-- تبويبات CRM -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab-tickets">
                <i class="bi bi-ticket-detailed"></i> تذاكر الدعم
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-customers">
                <i class="bi bi-people"></i> العملاء
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-crm-stats">
                <i class="bi bi-bar-chart"></i> الإحصائيات
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- تذاكر الدعم -->
        <div class="tab-pane fade show active" id="tab-tickets">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">التذاكر المفتوحة</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم التذكرة</th>
                                    <th>العميل</th>
                                    <th>الموضوع</th>
                                    <th>التصنيف</th>
                                    <th>الأولوية</th>
                                    <th>الحالة</th>
                                    <th>الردود</th>
                                    <th>التاريخ</th>
                                    <th>إجراء</th>
                                </tr>
                            </thead>
                            <tbody id="tickets-table">
                                <tr><td colspan="9" class="text-center">جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- العملاء -->
        <div class="tab-pane fade" id="tab-customers">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col"><h5 class="mb-0">قائمة العملاء</h5></div>
                        <div class="col-auto">
                            <input type="text" class="form-control form-control-sm" id="customer-search" placeholder="بحث عن عميل..." style="width:250px">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الهاتف</th>
                                    <th>الطلبات</th>
                                    <th>إجمالي الإنفاق</th>
                                    <th>نقاط الولاء</th>
                                    <th>إجراء</th>
                                </tr>
                            </thead>
                            <tbody id="customers-table">
                                <tr><td colspan="8" class="text-center">جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- إحصائيات CRM -->
        <div class="tab-pane fade" id="tab-crm-stats">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card shadow-sm border-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-people fs-1 text-primary"></i>
                            <h3 id="stat-total-customers">0</h3>
                            <p class="text-muted">إجمالي العملاء</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-success">
                        <div class="card-body text-center">
                            <i class="bi bi-currency-dollar fs-1 text-success"></i>
                            <h3 id="stat-total-customer-revenue">0 ر.س</h3>
                            <p class="text-muted">إجمالي إيرادات العملاء</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-info">
                        <div class="card-body text-center">
                            <i class="bi bi-clock-history fs-1 text-info"></i>
                            <h3 id="stat-avg-resolution">0 ساعة</h3>
                            <p class="text-muted">متوسط وقت حل التذكرة</p>
                        </div>
                    </div>
                </div>

                <!-- أفضل العملاء -->
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-star"></i> أفضل 10 عملاء</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>الترتيب</th>
                                            <th>الاسم</th>
                                            <th>البريد</th>
                                            <th>عدد الطلبات</th>
                                            <th>إجمالي الإنفاق</th>
                                            <th>نقاط الولاء</th>
                                        </tr>
                                    </thead>
                                    <tbody id="top-customers-table">
                                        <tr><td colspan="6" class="text-center">جاري التحميل...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- نافذة تفاصيل التذكرة -->
<div class="modal fade" id="ticketDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ticket-modal-title">تفاصيل التذكرة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="ticket-modal-body">
                جاري التحميل...
            </div>
            <div class="modal-footer">
                <div class="input-group">
                    <input type="text" class="form-control" id="ticket-reply-input" placeholder="اكتب ردك هنا...">
                    <button class="btn btn-primary" onclick="sendTicketReply()"><i class="bi bi-send"></i> إرسال</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const API = '/api';
let currentTicketId = null;

document.addEventListener('DOMContentLoaded', () => {
    loadTickets();
    loadCustomers();
    loadCRMStats();
});

/** تحميل التذاكر المفتوحة */
async function loadTickets() {
    try {
        const res = await fetch(`${API}/crm/tickets`);
        const result = await res.json();
        const tbody = document.getElementById('tickets-table');
        
        if (result.success && result.data) {
            const priorityBadge = {
                'low': 'success', 'medium': 'warning', 'high': 'danger', 'urgent': 'dark'
            };
            const priorityLabel = {
                'low': 'منخفض', 'medium': 'متوسط', 'high': 'عالي', 'urgent': 'عاجل'
            };
            const statusLabel = {
                'open': 'مفتوحة', 'in_progress': 'قيد المعالجة', 'waiting_customer': 'بانتظار العميل'
            };
            const catLabel = {
                'order_issue': 'مشكلة طلب', 'product_inquiry': 'استفسار منتج',
                'return_request': 'طلب إرجاع', 'payment_issue': 'مشكلة دفع', 'general': 'عام'
            };

            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-success">✓ لا توجد تذاكر مفتوحة</td></tr>';
                return;
            }

            tbody.innerHTML = result.data.map(t => `
                <tr>
                    <td><code>${t.ticket_number}</code></td>
                    <td>${t.customer_name}</td>
                    <td>${t.subject}</td>
                    <td>${catLabel[t.category] || t.category}</td>
                    <td><span class="badge bg-${priorityBadge[t.priority]}">${priorityLabel[t.priority]}</span></td>
                    <td><span class="badge bg-info">${statusLabel[t.status] || t.status}</span></td>
                    <td>${t.reply_count}</td>
                    <td><small>${t.created_at}</small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewTicket(${t.id})"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-outline-success" onclick="resolveTicket(${t.id})"><i class="bi bi-check-lg"></i></button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (e) { console.error(e); }
}

/** عرض تفاصيل التذكرة */
async function viewTicket(id) {
    currentTicketId = id;
    const modal = new bootstrap.Modal(document.getElementById('ticketDetailModal'));
    modal.show();
    
    try {
        const res = await fetch(`${API}/crm/tickets/${id}`);
        const result = await res.json();
        
        if (result.success && result.data) {
            const t = result.data;
            document.getElementById('ticket-modal-title').textContent = `تذكرة: ${t.ticket_number} - ${t.subject}`;
            
            let repliesHtml = t.replies.map(r => `
                <div class="card mb-2 ${r.sender_type === 'admin' ? 'border-primary' : 'border-secondary'}">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between">
                            <strong>${r.sender_name} <span class="badge bg-${r.sender_type === 'admin' ? 'primary' : 'secondary'}">${r.sender_type === 'admin' ? 'فريق الدعم' : 'عميل'}</span></strong>
                            <small class="text-muted">${r.created_at}</small>
                        </div>
                        <p class="mb-0 mt-1">${r.message}</p>
                    </div>
                </div>
            `).join('');
            
            document.getElementById('ticket-modal-body').innerHTML = `
                <div class="mb-3">
                    <span class="badge bg-info">${t.category}</span>
                    <span class="badge bg-warning">${t.priority}</span>
                    <span class="badge bg-secondary">${t.status}</span>
                </div>
                <h6>المحادثة:</h6>
                ${repliesHtml}
            `;
        }
    } catch (e) { console.error(e); }
}

/** إرسال رد على التذكرة */
async function sendTicketReply() {
    const message = document.getElementById('ticket-reply-input').value;
    if (!message || !currentTicketId) return;
    
    try {
        await fetch(`${API}/crm/tickets/${currentTicketId}/reply`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message, sender_type: 'admin', sender_name: 'فريق الدعم' })
        });
        document.getElementById('ticket-reply-input').value = '';
        viewTicket(currentTicketId);
    } catch (e) { console.error(e); }
}

/** حل التذكرة */
async function resolveTicket(id) {
    if (!confirm('هل تريد تحديد هذه التذكرة كمحلولة؟')) return;
    try {
        await fetch(`${API}/crm/tickets/${id}/resolve`, { method: 'PUT' });
        loadTickets();
    } catch (e) { console.error(e); }
}

/** تحميل العملاء */
async function loadCustomers() {
    try {
        const res = await fetch(`${API}/crm/customers`);
        const result = await res.json();
        const tbody = document.getElementById('customers-table');
        
        if (result.success && result.data?.data) {
            tbody.innerHTML = result.data.data.map(c => `
                <tr>
                    <td>${c.id}</td>
                    <td>${c.first_name} ${c.last_name}</td>
                    <td>${c.email}</td>
                    <td>${c.phone || '-'}</td>
                    <td>${c.total_orders}</td>
                    <td class="fw-bold">${Number(c.total_spent).toLocaleString('ar-SA')} ر.س</td>
                    <td><span class="badge bg-warning text-dark">${c.loyalty_points} نقطة</span></td>
                    <td><button class="btn btn-sm btn-outline-primary" onclick="viewCustomer(${c.id})"><i class="bi bi-eye"></i></button></td>
                </tr>
            `).join('');
        }
    } catch (e) { console.error(e); }
}

/** تحميل إحصائيات CRM */
async function loadCRMStats() {
    try {
        const res = await fetch(`${API}/crm/stats`);
        const result = await res.json();
        if (result.success && result.data) {
            const d = result.data;
            document.getElementById('stat-total-customers').textContent = d.customers?.total_customers || 0;
            document.getElementById('stat-total-customer-revenue').textContent = 
                Number(d.customers?.total_revenue || 0).toLocaleString('ar-SA') + ' ر.س';
            document.getElementById('stat-avg-resolution').textContent = 
                Math.round(d.tickets?.avg_resolution_time?.avg_hours || 0) + ' ساعة';
            
            // أفضل العملاء
            if (d.top_customers) {
                document.getElementById('top-customers-table').innerHTML = d.top_customers.map((c, i) => `
                    <tr>
                        <td><span class="badge bg-${i < 3 ? 'warning' : 'secondary'}">${i + 1}</span></td>
                        <td>${c.first_name} ${c.last_name}</td>
                        <td>${c.email}</td>
                        <td>${c.total_orders}</td>
                        <td class="fw-bold text-success">${Number(c.total_spent).toLocaleString('ar-SA')} ر.س</td>
                        <td>${c.loyalty_points}</td>
                    </tr>
                `).join('');
            }
        }
    } catch (e) { console.error(e); }
}
</script>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
