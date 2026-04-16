<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'متجر الأزياء' ?></title>
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <!-- القائمة الجانبية + المحتوى -->
    <div class="d-flex">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar bg-dark text-white">
            <div class="sidebar-header p-3 border-bottom border-secondary">
                <h5 class="mb-0"><i class="bi bi-shop"></i> متجر الأزياء</h5>
                <small class="text-muted">لوحة التحكم</small>
            </div>
            <ul class="nav flex-column p-2">
                <li class="nav-item">
                    <a href="/admin" class="nav-link text-white <?= ($currentPage ?? '') === 'dashboard' ? 'active bg-primary rounded' : '' ?>">
                        <i class="bi bi-speedometer2 me-2"></i> الرئيسية
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/products" class="nav-link text-white <?= ($currentPage ?? '') === 'products' ? 'active bg-primary rounded' : '' ?>">
                        <i class="bi bi-box-seam me-2"></i> المنتجات
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/orders" class="nav-link text-white <?= ($currentPage ?? '') === 'orders' ? 'active bg-primary rounded' : '' ?>">
                        <i class="bi bi-cart-check me-2"></i> الطلبات
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/accounting" class="nav-link text-white <?= ($currentPage ?? '') === 'accounting' ? 'active bg-primary rounded' : '' ?>">
                        <i class="bi bi-calculator me-2"></i> المحاسبة
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/crm" class="nav-link text-white <?= ($currentPage ?? '') === 'crm' ? 'active bg-primary rounded' : '' ?>">
                        <i class="bi bi-headset me-2"></i> خدمة العملاء
                    </a>
                </li>
                <hr class="text-secondary">
                <li class="nav-item">
                    <a href="/shop" class="nav-link text-warning" target="_blank">
                        <i class="bi bi-globe me-2"></i> زيارة المتجر
                    </a>
                </li>
            </ul>
        </nav>
