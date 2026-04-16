<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'إتمام الشراء' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/store.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/"><i class="bi bi-shop"></i> متجر الأزياء</a>
    </div>
</nav>

<div class="container py-5">
    <h2 class="fw-bold mb-4"><i class="bi bi-credit-card"></i> إتمام الشراء</h2>
    <form id="checkout-form">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">بيانات الشحن</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">الاسم الكامل</label>
                                <input type="text" class="form-control" name="shipping_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">رقم الجوال</label>
                                <input type="tel" class="form-control" name="shipping_phone" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">العنوان</label>
                                <input type="text" class="form-control" name="shipping_address" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">المدينة</label>
                                <input type="text" class="form-control" name="shipping_city" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الرمز البريدي</label>
                                <input type="text" class="form-control" name="shipping_postal_code">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">طريقة الدفع</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" value="cash_on_delivery" checked>
                            <label class="form-check-label"><i class="bi bi-cash"></i> الدفع عند الاستلام</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" value="credit_card">
                            <label class="form-check-label"><i class="bi bi-credit-card-2-front"></i> بطاقة ائتمانية</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="bank_transfer">
                            <label class="form-check-label"><i class="bi bi-bank"></i> تحويل بنكي</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">ملخص الطلب</h5>
                        <div id="order-summary" class="small"></div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h5">الإجمالي:</span>
                            <strong class="h5 text-primary" id="final-total">0.00 ر.س</strong>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> تأكيد الطلب
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div id="result" class="mt-4"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('checkout-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    data.customer_id = parseInt(localStorage.getItem('customer_id') || '1');

    try {
        const res = await fetch('/api/orders', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const json = await res.json();
        const div = document.getElementById('result');
        if (json.success) {
            div.innerHTML = `<div class="alert alert-success"><h5>✓ تم إنشاء طلبك بنجاح!</h5><p>رقم الطلب: ${json.data.order_number || json.data.id}</p></div>`;
        } else {
            div.innerHTML = `<div class="alert alert-danger">${json.message || 'فشل إنشاء الطلب'}</div>`;
        }
    } catch (e) {
        document.getElementById('result').innerHTML = '<div class="alert alert-danger">فشل الاتصال</div>';
    }
});
</script>
</body>
</html>
