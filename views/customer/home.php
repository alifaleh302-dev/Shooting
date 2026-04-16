<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'متجر الأزياء' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/store.css" rel="stylesheet">
</head>
<body>
    <!-- شريط التنقل العلوي -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <i class="bi bi-shop"></i> متجر الأزياء
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/">الرئيسية</a></li>
                    <li class="nav-item"><a class="nav-link" href="/shop">المتجر</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="/shop/cart">
                            <i class="bi bi-cart3 fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">0</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> حسابي
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">تسجيل الدخول</a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">حساب جديد</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- البانر الرئيسي -->
    <section class="hero-section text-white text-center py-5" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);">
        <div class="container py-5">
            <h1 class="display-4 fw-bold mb-3">أناقتك تبدأ من هنا</h1>
            <p class="lead mb-4">اكتشف أحدث تشكيلات الملابس العصرية بأسعار تنافسية</p>
            <a href="/shop" class="btn btn-light btn-lg px-5">
                <i class="bi bi-bag"></i> تسوق الآن
            </a>
        </div>
    </section>

    <!-- التصنيفات -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4"><i class="bi bi-grid"></i> تسوق حسب التصنيف</h2>
            <div class="row g-3" id="categories-grid">
                <!-- يتم تحميلها عبر JavaScript -->
            </div>
        </div>
    </section>

    <!-- المنتجات المميزة -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4"><i class="bi bi-stars"></i> منتجات مميزة</h2>
            <div class="row g-3" id="featured-products">
                <div class="text-center text-muted">جاري تحميل المنتجات...</div>
            </div>
        </div>
    </section>

    <!-- المميزات -->
    <section class="py-5 bg-dark text-white">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-3">
                    <i class="bi bi-truck fs-1 text-warning mb-2"></i>
                    <h5>شحن مجاني</h5>
                    <p class="text-muted">للطلبات فوق 200 ر.س</p>
                </div>
                <div class="col-md-3">
                    <i class="bi bi-shield-check fs-1 text-success mb-2"></i>
                    <h5>دفع آمن</h5>
                    <p class="text-muted">معاملات مشفرة وآمنة</p>
                </div>
                <div class="col-md-3">
                    <i class="bi bi-arrow-repeat fs-1 text-info mb-2"></i>
                    <h5>استبدال وإرجاع</h5>
                    <p class="text-muted">خلال 14 يوم</p>
                </div>
                <div class="col-md-3">
                    <i class="bi bi-headset fs-1 text-danger mb-2"></i>
                    <h5>دعم فني</h5>
                    <p class="text-muted">متوفر على مدار الساعة</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="bi bi-shop"></i> متجر الأزياء</h5>
                    <p class="text-muted">وجهتك الأولى للأزياء العصرية</p>
                </div>
                <div class="col-md-4">
                    <h6>روابط سريعة</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">سياسة الخصوصية</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">شروط الاستخدام</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">سياسة الإرجاع</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>تواصل معنا</h6>
                    <p class="text-muted mb-1"><i class="bi bi-envelope"></i> info@clothing-store.com</p>
                    <p class="text-muted"><i class="bi bi-phone"></i> +966 50 000 0000</p>
                </div>
            </div>
            <hr class="border-secondary">
            <p class="text-center text-muted mb-0">&copy; 2024 متجر الأزياء. جميع الحقوق محفوظة</p>
        </div>
    </footer>

    <!-- نافذة تسجيل الدخول -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5>تسجيل الدخول</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form id="login-form">
                        <div class="mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">دخول</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة التسجيل -->
    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5>حساب جديد</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form id="register-form">
                        <div class="row mb-3">
                            <div class="col"><input type="text" class="form-control" name="first_name" placeholder="الاسم الأول" required></div>
                            <div class="col"><input type="text" class="form-control" name="last_name" placeholder="اسم العائلة" required></div>
                        </div>
                        <div class="mb-3"><input type="email" class="form-control" name="email" placeholder="البريد الإلكتروني" required></div>
                        <div class="mb-3"><input type="tel" class="form-control" name="phone" placeholder="رقم الهاتف"></div>
                        <div class="mb-3"><input type="password" class="form-control" name="password" placeholder="كلمة المرور" required></div>
                        <button type="submit" class="btn btn-success w-100">إنشاء حساب</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/store.js"></script>

    <script>
    const API = '/api';
    
    document.addEventListener('DOMContentLoaded', () => {
        loadFeaturedProducts();
        loadCategories();
    });

    async function loadFeaturedProducts() {
        try {
            const res = await fetch(`${API}/products/featured`);
            const result = await res.json();
            const container = document.getElementById('featured-products');
            
            if (result.success && result.data && result.data.length > 0) {
                container.innerHTML = result.data.map(p => `
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="card h-100 shadow-sm product-card">
                            <div class="position-relative">
                                <img src="${p.main_image || '/assets/images/placeholder.png'}" class="card-img-top" alt="${p.name}" style="height:250px; object-fit:cover;">
                                ${p.sale_price ? '<span class="badge bg-danger position-absolute top-0 start-0 m-2">خصم</span>' : ''}
                            </div>
                            <div class="card-body">
                                <small class="text-muted">${p.category_name || ''}</small>
                                <h6 class="card-title mt-1">${p.name}</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        ${p.sale_price 
                                            ? `<span class="text-danger fw-bold">${Number(p.sale_price).toLocaleString('ar-SA')} ر.س</span>
                                               <small class="text-muted text-decoration-line-through">${Number(p.base_price).toLocaleString('ar-SA')}</small>`
                                            : `<span class="fw-bold">${Number(p.base_price).toLocaleString('ar-SA')} ر.س</span>`
                                        }
                                    </div>
                                    <a href="/shop/product/${p.id}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="col-12 text-center text-muted"><p>لا توجد منتجات مميزة حالياً</p></div>';
            }
        } catch (e) { console.error(e); }
    }

    async function loadCategories() {
        const categories = [
            { name: 'ملابس رجالية', icon: 'bi-person', color: '#0d6efd' },
            { name: 'ملابس نسائية', icon: 'bi-person-hearts', color: '#e91e8c' },
            { name: 'ملابس أطفال', icon: 'bi-emoji-smile', color: '#ffc107' },
            { name: 'إكسسوارات', icon: 'bi-gem', color: '#198754' }
        ];
        
        document.getElementById('categories-grid').innerHTML = categories.map(c => `
            <div class="col-md-3 col-6">
                <div class="card text-center border-0 shadow-sm h-100 category-card" style="cursor:pointer">
                    <div class="card-body py-4">
                        <i class="bi ${c.icon} fs-1" style="color:${c.color}"></i>
                        <h5 class="mt-2">${c.name}</h5>
                    </div>
                </div>
            </div>
        `).join('');
    }

    // تسجيل الدخول
    document.getElementById('login-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const res = await fetch(`${API}/crm/customers/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.fromEntries(formData))
            });
            const result = await res.json();
            if (result.success) {
                localStorage.setItem('customer', JSON.stringify(result.data));
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (e) { console.error(e); }
    });

    // التسجيل
    document.getElementById('register-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const res = await fetch(`${API}/crm/customers/register`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.fromEntries(formData))
            });
            const result = await res.json();
            if (result.success) {
                alert('تم التسجيل بنجاح! يمكنك الآن تسجيل الدخول');
                bootstrap.Modal.getInstance(document.getElementById('registerModal')).hide();
            } else {
                alert(result.message);
            }
        } catch (e) { console.error(e); }
    });
    </script>
</body>
</html>
