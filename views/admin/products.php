<?php
/**
 * صفحة إدارة المنتجات - Admin Products
 */
$currentPage = 'products';
require __DIR__ . '/../layouts/admin-header.php';
?>

<main class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-box-seam"></i> إدارة المنتجات</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus-lg"></i> إضافة منتج
        </button>
    </div>

    <!-- شريط البحث -->
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search-input" placeholder="بحث عن منتج...">
                        <button class="btn btn-outline-primary" onclick="searchProducts()"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filter-category" onchange="loadProducts()">
                        <option value="">كل التصنيفات</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول المنتجات -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>الصورة</th>
                            <th>اسم المنتج</th>
                            <th>التصنيف</th>
                            <th>SKU</th>
                            <th>السعر</th>
                            <th>التكلفة</th>
                            <th>المبيعات</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="products-table">
                        <tr><td colspan="10" class="text-center">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- نافذة إضافة منتج -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة منتج جديد</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="product-form">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">اسم المنتج *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">SKU *</label>
                            <input type="text" class="form-control" name="sku" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">التصنيف *</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">اختر التصنيف</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">العلامة التجارية</label>
                            <input type="text" class="form-control" name="brand">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">نوع القماش</label>
                            <input type="text" class="form-control" name="material">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">سعر البيع *</label>
                            <input type="number" class="form-control" name="base_price" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">سعر الخصم</label>
                            <input type="number" class="form-control" name="sale_price" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">سعر التكلفة *</label>
                            <input type="number" class="form-control" name="cost_price" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">وصف مختصر</label>
                        <input type="text" class="form-control" name="short_description">
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="is_featured" value="1">
                        <label class="form-check-label">منتج مميز</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button class="btn btn-primary" onclick="saveProduct()"><i class="bi bi-check-lg"></i> حفظ المنتج</button>
            </div>
        </div>
    </div>
</div>

<script>
const API = '/api';

document.addEventListener('DOMContentLoaded', loadProducts);

async function loadProducts(page = 1) {
    try {
        const res = await fetch(`${API}/products?page=${page}`);
        const result = await res.json();
        const tbody = document.getElementById('products-table');
        
        if (result.success && result.data?.data) {
            tbody.innerHTML = result.data.data.map(p => `
                <tr>
                    <td>${p.id}</td>
                    <td>
                        <img src="${p.main_image || '/assets/images/placeholder.png'}" 
                             alt="${p.name}" style="width:50px;height:50px;object-fit:cover;border-radius:8px">
                    </td>
                    <td>
                        <strong>${p.name}</strong>
                        ${p.is_featured ? '<span class="badge bg-warning ms-1">مميز</span>' : ''}
                    </td>
                    <td>${p.category_name || '-'}</td>
                    <td><code>${p.sku}</code></td>
                    <td>
                        ${p.sale_price 
                            ? `<span class="text-danger">${Number(p.sale_price).toLocaleString('ar-SA')}</span>
                               <small class="text-muted text-decoration-line-through d-block">${Number(p.base_price).toLocaleString('ar-SA')}</small>`
                            : `${Number(p.base_price).toLocaleString('ar-SA')}`
                        } ر.س
                    </td>
                    <td>${Number(p.cost_price).toLocaleString('ar-SA')} ر.س</td>
                    <td>${p.total_sold}</td>
                    <td><span class="badge bg-${p.is_active ? 'success' : 'secondary'}">${p.is_active ? 'فعال' : 'معطل'}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="editProduct(${p.id})"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-outline-danger" onclick="deleteProduct(${p.id})"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    } catch (e) { console.error(e); }
}

async function saveProduct() {
    const form = document.getElementById('product-form');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    data.is_featured = data.is_featured ? 1 : 0;
    
    try {
        const res = await fetch(`${API}/products`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            alert('تم إضافة المنتج بنجاح');
            bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
            form.reset();
            loadProducts();
        } else {
            alert(result.message);
        }
    } catch (e) { console.error(e); }
}

async function deleteProduct(id) {
    if (!confirm('هل أنت متأكد من حذف هذا المنتج؟')) return;
    try {
        await fetch(`${API}/products/${id}`, { method: 'DELETE' });
        loadProducts();
    } catch (e) { console.error(e); }
}

async function searchProducts() {
    const q = document.getElementById('search-input').value;
    if (!q) { loadProducts(); return; }
    try {
        const res = await fetch(`${API}/products/search?q=${encodeURIComponent(q)}`);
        const result = await res.json();
        // ... تحديث الجدول بنتائج البحث
    } catch (e) { console.error(e); }
}
</script>

<?php require __DIR__ . '/../layouts/admin-footer.php'; ?>
