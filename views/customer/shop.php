<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'المتجر - تسوق الآن' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/store.css" rel="stylesheet">
    <style>
        .category-item { cursor: pointer; transition: all 0.2s; }
        .category-item:hover { background-color: #f8f9fa; padding-right: 25px; }
        .category-item.active { background-color: #0d6efd; color: white; font-weight: bold; }
        .product-card { transition: transform 0.3s, box-shadow 0.3s; border: none; border-radius: 12px; overflow: hidden; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .product-img-container { height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; position: relative; }
        .product-img-container i { font-size: 4rem; color: #ccc; }
        .badge-category { position: absolute; top: 10px; right: 10px; font-size: 0.7rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/"><i class="bi bi-shop"></i> متجر الأزياء</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
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
    <div class="row mb-4 align-items-center">
        <div class="col-md-7">
            <h2 class="fw-bold mb-1"><i class="bi bi-bag-check text-primary"></i> تسوق المنتجات</h2>
            <p class="text-muted">اكتشف أحدث تشكيلاتنا من الأزياء الراقية</p>
        </div>
        <div class="col-md-5">
            <div class="input-group shadow-sm">
                <input type="text" class="form-control border-0" id="search-input" placeholder="ابحث عن منتج بالاسم أو الوصف...">
                <button class="btn btn-primary px-4" id="search-btn"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </div>

    <div class="row">
        <aside class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="p-3 border-bottom">
                        <h6 class="fw-bold mb-0"><i class="bi bi-funnel me-1"></i> التصنيفات</h6>
                    </div>
                    <div id="categories-list" class="list-group list-group-flush">
                        <button class="list-group-item list-group-item-action category-item active" data-id="all">
                            <i class="bi bi-grid-fill me-2"></i> جميع المنتجات
                        </button>
                        <!-- سيتم تحميل التصنيفات هنا -->
                        <div class="text-center py-3 category-loader">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="col-md-9">
            <div id="products-grid" class="row g-4">
                <!-- سيتم تحميل المنتجات هنا -->
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="text-muted mt-2">جارٍ تحميل المنتجات...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p class="mb-0">&copy; 2026 متجر الأزياء - جميع الحقوق محفوظة</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/store.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadProducts();

    // معالجة البحث
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');

    searchBtn.addEventListener('click', () => {
        const q = searchInput.value.trim();
        if (q) searchProducts(q);
        else loadProducts();
    });

    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const q = searchInput.value.trim();
            if (q) searchProducts(q);
            else loadProducts();
        }
    });

    // معالجة النقر على التصنيفات
    document.getElementById('categories-list').addEventListener('click', (e) => {
        const btn = e.target.closest('.category-item');
        if (!btn) return;

        // تحديث الحالة النشطة
        document.querySelectorAll('.category-item').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');

        const catId = btn.dataset.id;
        if (catId === 'all') {
            loadProducts();
        } else {
            loadProducts(catId);
        }
    });
});

async function loadCategories() {
    const list = document.getElementById('categories-list');
    try {
        const res = await fetch('/api/categories');
        const json = await res.json();
        
        if (json.success && json.data) {
            const loader = list.querySelector('.category-loader');
            if (loader) loader.remove();
            
            json.data.forEach(cat => {
                const btn = document.createElement('button');
                btn.className = 'list-group-item list-group-item-action category-item';
                btn.dataset.id = cat.id;
                btn.innerHTML = `<i class="bi bi-chevron-left small me-2"></i> ${escapeHtml(cat.name)}`;
                list.appendChild(btn);
            });
        }
    } catch (e) { 
        console.error('Error loading categories:', e); 
    }
}

async function loadProducts(categoryId = null) {
    const grid = document.getElementById('products-grid');
    grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
    try {
        const url = categoryId ? `/api/products/category/${categoryId}` : '/api/products';
        const res = await fetch(url);
        const json = await res.json();
        
        // التعامل مع هيكل البيانات المرجع (قد يكون paginated)
        const products = json.data.data || json.data || [];
        renderProducts(products);
    } catch (e) {
        grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">فشل تحميل المنتجات. يرجى المحاولة لاحقاً.</div></div>';
    }
}

async function searchProducts(query) {
    const grid = document.getElementById('products-grid');
    grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
    try {
        const res = await fetch('/api/products/search?q=' + encodeURIComponent(query));
        const json = await res.json();
        const products = json.data.data || json.data || [];
        renderProducts(products);
    } catch (e) {
        grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">فشل البحث. يرجى المحاولة لاحقاً.</div></div>';
    }
}

function renderProducts(products) {
    const grid = document.getElementById('products-grid');
    if (!products || !products.length) {
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                <h5>لا توجد منتجات تطابق بحثك</h5>
                <p class="text-muted">جرب كلمات بحث أخرى أو تصفح تصنيفات مختلفة</p>
                <button class="btn btn-outline-primary mt-2" onclick="loadProducts()">عرض الكل</button>
            </div>`;
        return;
    }
    
    grid.innerHTML = products.map(p => `
        <div class="col-md-4 col-sm-6">
            <div class="card h-100 product-card shadow-sm">
                <div class="product-img-container">
                    ${p.main_image ? `<img src="${p.main_image}" class="img-fluid" alt="${escapeHtml(p.name)}">` : '<i class="bi bi-image"></i>'}
                    ${p.category_name ? `<span class="badge bg-primary badge-category">${escapeHtml(p.category_name)}</span>` : ''}
                </div>
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title fw-bold mb-2">${escapeHtml(p.name)}</h6>
                    <p class="card-text text-muted small mb-3 flex-grow-1">
                        ${escapeHtml((p.short_description || p.description || '').substring(0, 60))}...
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div>
                            ${p.sale_price ? `
                                <span class="h6 text-danger fw-bold mb-0">${parseFloat(p.sale_price).toFixed(2)} ر.س</span>
                                <small class="text-decoration-line-through text-muted ms-1">${parseFloat(p.base_price).toFixed(2)}</small>
                            ` : `
                                <span class="h6 text-primary fw-bold mb-0">${parseFloat(p.base_price).toFixed(2)} ر.س</span>
                            `}
                        </div>
                        <a href="/shop/product/${p.id}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            تفاصيل
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function escapeHtml(s) {
    if (!s) return '';
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
</script>
</body>
</html>
