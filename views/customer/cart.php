<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'سلة التسوق' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/store.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/"><i class="bi bi-shop"></i> متجر الأزياء</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/">الرئيسية</a></li>
                <li class="nav-item"><a class="nav-link" href="/shop">المتجر</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <h2 class="fw-bold mb-4"><i class="bi bi-cart3"></i> سلة التسوق</h2>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div id="cart-items">
                        <div class="text-center py-5">
                            <i class="bi bi-cart-x display-1 text-muted"></i>
                            <p class="text-muted mt-3">جارٍ تحميل السلة...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top:80px">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-receipt"></i> ملخص الطلب</h5>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>المجموع الفرعي:</span>
                        <strong id="subtotal">0.00 ر.س</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>ضريبة القيمة المضافة (15%):</span>
                        <strong id="vat">0.00 ر.س</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="h5">الإجمالي:</span>
                        <strong class="h5 text-primary" id="total">0.00 ر.س</strong>
                    </div>
                    <a href="/shop/checkout" class="btn btn-primary w-100">
                        <i class="bi bi-credit-card"></i> إتمام الشراء
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const SESSION_ID = localStorage.getItem('cart_session_id') || (() => {
    const s = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('cart_session_id', s);
    return s;
})();

async function loadCart() {
    try {
        const res = await fetch('/api/cart?session_id=' + SESSION_ID);
        const json = await res.json();
        renderCart(json.data || { items: [], summary: {} });
    } catch (e) {
        document.getElementById('cart-items').innerHTML =
            '<div class="alert alert-danger">فشل تحميل السلة</div>';
    }
}

function renderCart(data) {
    const items = data.items || [];
    const container = document.getElementById('cart-items');
    if (!items.length) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-cart-x display-1 text-muted"></i>
                <p class="text-muted mt-3">سلة التسوق فارغة</p>
                <a href="/shop" class="btn btn-primary">تصفح المنتجات</a>
            </div>`;
        return;
    }
    container.innerHTML = items.map(item => `
        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
            <div>
                <h6 class="mb-1">${item.product_name || 'منتج'}</h6>
                <small class="text-muted">${item.color_name || ''} - ${item.size_name || ''}</small>
            </div>
            <div class="text-end">
                <div>الكمية: ${item.quantity}</div>
                <strong class="text-primary">${parseFloat(item.unit_price || 0).toFixed(2)} ر.س</strong>
            </div>
        </div>
    `).join('');
    const summary = data.summary || {};
    document.getElementById('subtotal').textContent = (summary.subtotal || 0).toFixed(2) + ' ر.س';
    document.getElementById('vat').textContent = (summary.vat || 0).toFixed(2) + ' ر.س';
    document.getElementById('total').textContent = (summary.total || 0).toFixed(2) + ' ر.س';
}

loadCart();
</script>
</body>
</html>
