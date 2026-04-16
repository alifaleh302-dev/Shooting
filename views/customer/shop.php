<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'المتجر - تسوق الآن' ?></title>
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
                <li class="nav-item"><a class="nav-link active" href="/shop">المتجر</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link position-relative" href="/shop/cart">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">0</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold"><i class="bi bi-bag-check"></i> تسوق المنتجات</h2>
            <p class="text-muted">اكتشف أحدث تشكيلاتنا من الأزياء</p>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" class="form-control" id="search-input" placeholder="ابحث عن منتج...">
                <button class="btn btn-primary" id="search-btn"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </div>

    <div class="row">
        <aside class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-funnel"></i> التصنيفات</h5>
                    <div id="categories-list" class="list-group list-group-flush">
                        <button class="list-group-item list-group-item-action active" data-category="all">جميع المنتجات</button>
                    </div>
                </div>
            </div>
        </aside>

        <div class="col-md-9">
            <div id="products-grid" class="row g-3">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="text-muted mt-2">جارٍ تحميل المنتجات...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5">
    <p class="mb-0">&copy; 2026 متجر الأزياء - جميع الحقوق محفوظة</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadProducts();

    document.getElementById('search-btn').addEventListener('click', () => {
        const q = document.getElementById('search-input').value.trim();
        if (q) searchProducts(q);
        else loadProducts();
    });
});

async function loadCategories() {
    try {
        const res = await fetch('/api/products/category/1');
        // Note: No dedicated categories API; use static list or fetch from another endpoint
    } catch (e) { console.error(e); }
}

async function loadProducts(categoryId = null) {
    const grid = document.getElementById('products-grid');
    grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
    try {
        const url = categoryId ? `/api/products/category/${categoryId}` : '/api/products';
        const res = await fetch(url);
        const json = await res.json();
        renderProducts(json.data || []);
    } catch (e) {
        grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">فشل تحميل المنتجات</div></div>';
    }
}

async function searchProducts(query) {
    const grid = document.getElementById('products-grid');
    grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
    try {
        const res = await fetch('/api/products/search?q=' + encodeURIComponent(query));
        const json = await res.json();
        renderProducts(json.data || []);
    } catch (e) {
        grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">فشل البحث</div></div>';
    }
}

function renderProducts(products) {
    const grid = document.getElementById('products-grid');
    if (!products.length) {
        grid.innerHTML = '<div class="col-12"><div class="alert alert-info text-center">لا توجد منتجات متاحة حالياً</div></div>';
        return;
    }
    grid.innerHTML = products.map(p => `
        <div class="col-md-4 col-sm-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">${escapeHtml(p.name)}</h5>
                    <p class="card-text text-muted small">${escapeHtml((p.description || '').substring(0, 80))}...</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 text-primary mb-0">${parseFloat(p.base_price).toFixed(2)} ر.س</span>
                        <a href="/shop/product/${p.id}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i> تفاصيل
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
</script>
</body>
</html>
