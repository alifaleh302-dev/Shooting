<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'تفاصيل المنتج' ?></title>
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
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">الرئيسية</a></li>
            <li class="breadcrumb-item"><a href="/shop">المتجر</a></li>
            <li class="breadcrumb-item active">تفاصيل المنتج</li>
        </ol>
    </nav>

    <div id="product-container">
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-2">جارٍ تحميل تفاصيل المنتج...</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const productId = <?= (int)($product_id ?? 0) ?>;

async function loadProduct() {
    try {
        const res = await fetch('/api/products/' + productId);
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        renderProduct(json.data);
    } catch (e) {
        document.getElementById('product-container').innerHTML =
            '<div class="alert alert-danger">المنتج غير موجود أو حدث خطأ</div>';
    }
}

function renderProduct(p) {
    document.getElementById('product-container').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5 bg-light">
                        <i class="bi bi-image display-1 text-muted"></i>
                        <p class="text-muted mt-3">صورة المنتج</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h1 class="fw-bold">${escapeHtml(p.name)}</h1>
                <p class="text-muted">SKU: ${escapeHtml(p.sku)}</p>
                <h2 class="text-primary my-3">${parseFloat(p.base_price).toFixed(2)} ر.س</h2>
                <p>${escapeHtml(p.description || '')}</p>
                <div class="mt-4">
                    <button class="btn btn-primary btn-lg" onclick="addToCart(${p.id})">
                        <i class="bi bi-cart-plus"></i> أضف إلى السلة
                    </button>
                    <a href="/shop" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-arrow-right"></i> العودة للمتجر
                    </a>
                </div>
            </div>
        </div>
    `;
}

async function addToCart(productId) {
    alert('جارٍ إضافة المنتج إلى السلة...\n(يتطلب اختيار اللون والمقاس والمخزون)');
}

function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

loadProduct();
</script>
</body>
</html>
