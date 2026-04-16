<?php
/**
 * صفحة النظام المحاسبي - Accounting Dashboard
 */
$currentPage = 'accounting';
require __DIR__ . '/../layouts/admin-header.php';
?>

<main class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-calculator"></i> النظام المحاسبي</h3>
    </div>

    <!-- تبويبات النظام المحاسبي -->
    <ul class="nav nav-tabs mb-4" id="accountingTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab-accounts">
                <i class="bi bi-diagram-3"></i> شجرة الحسابات
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-entries">
                <i class="bi bi-journal-text"></i> القيود المحاسبية
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-invoices">
                <i class="bi bi-receipt"></i> الفواتير
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-expenses">
                <i class="bi bi-cash-stack"></i> المصاريف
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-reports">
                <i class="bi bi-bar-chart-line"></i> التقارير
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- شجرة الحسابات -->
        <div class="tab-pane fade show active" id="tab-accounts">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>رمز الحساب</th>
                                    <th>اسم الحساب</th>
                                    <th>النوع</th>
                                    <th>الرصيد</th>
                                </tr>
                            </thead>
                            <tbody id="accounts-table">
                                <tr><td colspan="4" class="text-center">جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- القيود المحاسبية -->
        <div class="tab-pane fade" id="tab-entries">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">القيود المحاسبية</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newEntryModal">
                        <i class="bi bi-plus-lg"></i> قيد جديد
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم القيد</th>
                                    <th>التاريخ</th>
                                    <th>الوصف</th>
                                    <th>المدين</th>
                                    <th>الدائن</th>
                                    <th>الحالة</th>
                                    <th>إجراء</th>
                                </tr>
                            </thead>
                            <tbody id="entries-table">
                                <tr><td colspan="7" class="text-center">جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- الفواتير -->
        <div class="tab-pane fade" id="tab-invoices">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>رقم الطلب</th>
                                    <th>العميل</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الاستحقاق</th>
                                    <th>إجراء</th>
                                </tr>
                            </thead>
                            <tbody id="invoices-table">
                                <tr><td colspan="7" class="text-center">جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- المصاريف -->
        <div class="tab-pane fade" id="tab-expenses">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between">
                    <h5 class="mb-0">سجل المصاريف</h5>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
                        <i class="bi bi-plus-lg"></i> تسجيل مصروف
                    </button>
                </div>
                <div class="card-body">
                    <div id="expenses-list">جاري التحميل...</div>
                </div>
            </div>
        </div>

        <!-- التقارير -->
        <div class="tab-pane fade" id="tab-reports">
            <div class="row g-3">
                <!-- تقرير المبيعات -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-graph-up-arrow"></i> تقرير المبيعات</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col">
                                    <input type="date" class="form-control" id="sales-start" value="">
                                </div>
                                <div class="col">
                                    <input type="date" class="form-control" id="sales-end" value="">
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-success" onclick="loadSalesReport()">عرض</button>
                                </div>
                            </div>
                            <div id="sales-report-data"></div>
                        </div>
                    </div>
                </div>

                <!-- تقرير الأرباح -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-wallet2"></i> تقرير الأرباح</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col">
                                    <input type="date" class="form-control" id="profit-start" value="">
                                </div>
                                <div class="col">
                                    <input type="date" class="form-control" id="profit-end" value="">
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary" onclick="loadProfitReport()">عرض</button>
                                </div>
                            </div>
                            <div id="profit-report-data"></div>
                        </div>
                    </div>
                </div>

                <!-- ميزان المراجعة -->
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white d-flex justify-content-between">
                            <h5 class="mb-0"><i class="bi bi-table"></i> ميزان المراجعة</h5>
                            <button class="btn btn-light btn-sm" onclick="loadTrialBalance()">تحديث</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>رمز الحساب</th>
                                            <th>اسم الحساب</th>
                                            <th>النوع</th>
                                            <th>مدين</th>
                                            <th>دائن</th>
                                        </tr>
                                    </thead>
                                    <tbody id="trial-balance-table">
                                        <tr><td colspan="5" class="text-center">اضغط تحديث لعرض البيانات</td></tr>
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

<!-- نافذة تسجيل مصروف -->
<div class="modal fade" id="newExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تسجيل مصروف جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="expense-form">
                    <div class="mb-3">
                        <label class="form-label">تصنيف المصروف</label>
                        <select class="form-select" name="category" required>
                            <option value="shipping">شحن وتوصيل</option>
                            <option value="marketing">تسويق وإعلان</option>
                            <option value="salary">رواتب</option>
                            <option value="rent">إيجار</option>
                            <option value="general">مصاريف عامة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المبلغ (ر.س)</label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea class="form-control" name="description" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">التاريخ</label>
                        <input type="date" class="form-control" name="expense_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" onclick="submitExpense()">تسجيل المصروف</button>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * وظائف النظام المحاسبي
 * Accounting JavaScript Functions
 */

// Base URL للـ API
const API_BASE = '/api';

// تحميل البيانات عند فتح الصفحة
document.addEventListener('DOMContentLoaded', () => {
    loadChartOfAccounts();
    loadJournalEntries();
    loadInvoices();
    
    // ضبط التواريخ الافتراضية
    const today = new Date().toISOString().split('T')[0];
    const monthStart = today.substring(0, 8) + '01';
    document.getElementById('sales-start').value = monthStart;
    document.getElementById('sales-end').value = today;
    document.getElementById('profit-start').value = monthStart;
    document.getElementById('profit-end').value = today;
});

/** تحميل شجرة الحسابات */
async function loadChartOfAccounts() {
    try {
        const res = await fetch(`${API_BASE}/accounting/accounts`);
        const result = await res.json();
        const tbody = document.getElementById('accounts-table');
        
        if (result.success && result.data) {
            const typeLabels = {
                'asset': '<span class="badge bg-primary">أصول</span>',
                'liability': '<span class="badge bg-danger">خصوم</span>',
                'equity': '<span class="badge bg-info">حقوق ملكية</span>',
                'revenue': '<span class="badge bg-success">إيرادات</span>',
                'expense': '<span class="badge bg-warning text-dark">مصاريف</span>'
            };
            
            tbody.innerHTML = result.data.map(a => `
                <tr>
                    <td><code>${a.account_code}</code></td>
                    <td>${a.name}</td>
                    <td>${typeLabels[a.type] || a.type}</td>
                    <td class="${a.balance >= 0 ? 'text-success' : 'text-danger'} fw-bold">
                        ${Number(a.balance).toLocaleString('ar-SA')} ر.س
                    </td>
                </tr>
            `).join('');
        }
    } catch (e) { console.error('خطأ:', e); }
}

/** تحميل القيود المحاسبية */
async function loadJournalEntries() {
    try {
        const res = await fetch(`${API_BASE}/accounting/entries`);
        const result = await res.json();
        const tbody = document.getElementById('entries-table');
        
        if (result.success && result.data?.data) {
            tbody.innerHTML = result.data.data.map(e => `
                <tr>
                    <td><code>${e.entry_number}</code></td>
                    <td>${e.entry_date}</td>
                    <td>${e.description}</td>
                    <td class="text-success">${Number(e.total_debit).toLocaleString('ar-SA')}</td>
                    <td class="text-danger">${Number(e.total_credit).toLocaleString('ar-SA')}</td>
                    <td><span class="badge bg-${e.status === 'posted' ? 'success' : 'secondary'}">${e.status === 'posted' ? 'مُعتمد' : 'مسودة'}</span></td>
                    <td><button class="btn btn-sm btn-outline-primary" onclick="viewEntry(${e.id})"><i class="bi bi-eye"></i></button></td>
                </tr>
            `).join('');
        }
    } catch (e) { console.error('خطأ:', e); }
}

/** تحميل الفواتير */
async function loadInvoices() {
    try {
        const res = await fetch(`${API_BASE}/accounting/invoices`);
        const result = await res.json();
        const tbody = document.getElementById('invoices-table');
        
        if (result.success && result.data?.data) {
            const statusBadge = {
                'draft': 'secondary', 'sent': 'info', 'paid': 'success', 
                'overdue': 'danger', 'cancelled': 'dark'
            };
            tbody.innerHTML = result.data.data.map(inv => `
                <tr>
                    <td><code>${inv.invoice_number}</code></td>
                    <td>${inv.order_id}</td>
                    <td>${inv.customer_id}</td>
                    <td class="fw-bold">${Number(inv.total_amount).toLocaleString('ar-SA')} ر.س</td>
                    <td><span class="badge bg-${statusBadge[inv.status]}">${inv.status}</span></td>
                    <td>${inv.due_date || '-'}</td>
                    <td><button class="btn btn-sm btn-outline-success" onclick="viewInvoice(${inv.id})"><i class="bi bi-file-earmark-text"></i></button></td>
                </tr>
            `).join('');
        }
    } catch (e) { console.error('خطأ:', e); }
}

/** تقرير المبيعات */
async function loadSalesReport() {
    const startDate = document.getElementById('sales-start').value;
    const endDate = document.getElementById('sales-end').value;
    try {
        const res = await fetch(`${API_BASE}/accounting/reports/sales?start_date=${startDate}&end_date=${endDate}`);
        const result = await res.json();
        const container = document.getElementById('sales-report-data');
        
        if (result.success && result.data) {
            let totalRevenue = result.data.reduce((sum, d) => sum + Number(d.total_revenue), 0);
            container.innerHTML = `
                <div class="alert alert-success">
                    <h4>إجمالي المبيعات: ${totalRevenue.toLocaleString('ar-SA')} ر.س</h4>
                    <p>عدد الفواتير: ${result.data.reduce((sum, d) => sum + Number(d.invoice_count), 0)}</p>
                </div>
            `;
        }
    } catch (e) { console.error('خطأ:', e); }
}

/** تقرير الأرباح */
async function loadProfitReport() {
    const startDate = document.getElementById('profit-start').value;
    const endDate = document.getElementById('profit-end').value;
    try {
        const res = await fetch(`${API_BASE}/accounting/reports/profit?start_date=${startDate}&end_date=${endDate}`);
        const result = await res.json();
        const container = document.getElementById('profit-report-data');
        
        if (result.success && result.data) {
            const d = result.data;
            container.innerHTML = `
                <table class="table table-bordered">
                    <tr><td>إجمالي الإيرادات</td><td class="text-success fw-bold">${Number(d.total_revenue).toLocaleString('ar-SA')} ر.س</td></tr>
                    <tr><td>تكلفة البضاعة المباعة</td><td class="text-danger">${Number(d.cost_of_goods).toLocaleString('ar-SA')} ر.س</td></tr>
                    <tr><td>إجمالي الربح</td><td class="fw-bold">${Number(d.gross_profit).toLocaleString('ar-SA')} ر.س</td></tr>
                    <tr><td>المصاريف التشغيلية</td><td class="text-danger">${Number(d.total_expenses).toLocaleString('ar-SA')} ر.س</td></tr>
                    <tr class="table-${d.net_profit >= 0 ? 'success' : 'danger'}">
                        <td><strong>صافي الربح</strong></td>
                        <td><strong>${Number(d.net_profit).toLocaleString('ar-SA')} ر.س</strong></td>
                    </tr>
                </table>
            `;
        }
    } catch (e) { console.error('خطأ:', e); }
}

/** ميزان المراجعة */
async function loadTrialBalance() {
    try {
        const res = await fetch(`${API_BASE}/accounting/reports/trial-balance`);
        const result = await res.json();
        const tbody = document.getElementById('trial-balance-table');
        
        if (result.success && result.data) {
            tbody.innerHTML = result.data.map(a => `
                <tr>
                    <td><code>${a.account_code}</code></td>
                    <td>${a.name}</td>
                    <td>${a.type}</td>
                    <td class="text-success">${Number(a.debit_balance).toLocaleString('ar-SA')}</td>
                    <td class="text-danger">${Number(a.credit_balance).toLocaleString('ar-SA')}</td>
                </tr>
            `).join('');
        }
    } catch (e) { console.error('خطأ:', e); }
}

/** تسجيل مصروف جديد */
async function submitExpense() {
    const form = document.getElementById('expense-form');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    data.account_id = 22; // مصاريف إدارية (افتراضي)
    
    try {
        const res = await fetch(`${API_BASE}/accounting/expenses`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            alert('تم تسجيل المصروف بنجاح');
            bootstrap.Modal.getInstance(document.getElementById('newExpenseModal')).hide();
            form.reset();
        } else {
            alert(result.message);
        }
    } catch (e) { console.error('خطأ:', e); }
}
</script>

<?php require __DIR__ . '/../layouts/admin-footer.php'; ?>
