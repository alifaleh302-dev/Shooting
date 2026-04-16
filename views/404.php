<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - الصفحة غير موجودة</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .error-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { text-align: center; padding: 40px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 500px; width: 90%; }
        .error-code { font-size: 100px; font-weight: bold; color: #0d6efd; line-height: 1; margin-bottom: 20px; }
        .error-msg { font-size: 24px; color: #333; margin-bottom: 15px; }
        .error-desc { color: #666; margin-bottom: 30px; }
        .btn-home { padding: 12px 30px; border-radius: 30px; font-weight: 600; transition: all 0.3s; }
        .btn-home:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3); }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-code">404</div>
            <h1 class="error-msg">عذراً، الصفحة غير موجودة!</h1>
            <p class="error-desc">يبدو أن الرابط الذي تحاول الوصول إليه غير صحيح أو تم نقله لمكان آخر.</p>
            <a href="/" class="btn btn-primary btn-home">
                <i class="bi bi-house-door me-2"></i> العودة للرئيسية
            </a>
        </div>
    </div>
</body>
</html>
