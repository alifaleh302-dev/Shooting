<?php
/**
 * ============================================
 * نقطة الدخول الرئيسية للتطبيق
 * Application Entry Point (Front Controller)
 * ============================================
 * جميع الطلبات تمر من هنا ثم يتم توجيهها للمتحكم المناسب
 * متوافق مع Render.com (PHP Built-in Server)
 */

// تحميل الإعدادات
$appConfig = require __DIR__ . '/../config/app.php';

// ضبط المنطقة الزمنية
date_default_timezone_set($appConfig['timezone']);

// ضبط معالج الأخطاء حسب البيئة
if ($appConfig['environment'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// إعداد الجلسة
session_name($appConfig['session']['name']);
session_start();

// Global fatal error handler - returns JSON instead of blank 500
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'success' => false,
            'message' => 'Fatal PHP error',
            'error'   => [
                'type'    => $err['type'],
                'message' => $err['message'],
                'file'    => basename($err['file'] ?? ''),
                'line'    => $err['line'] ?? 0,
            ],
        ], JSON_UNESCAPED_UNICODE);
    }
});

set_exception_handler(function (\Throwable $e) {
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Uncaught exception',
        'error'   => [
            'class'   => get_class($e),
            'message' => $e->getMessage(),
            'file'    => basename($e->getFile()),
            'line'    => $e->getLine(),
        ],
    ], JSON_UNESCAPED_UNICODE);
});

// السماح بطلبات CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// معالجة طلبات OPTIONS (Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// تحميل تلقائي للفئات (Autoloader)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// ============================================
// تسجيل المسارات (Routes)
// ============================================
$router = new App\Core\Router();

// --- فحص الصحة (Health Check) لـ Render ---
$router->get('/api/health', 'HealthController', 'check');

// --- الصفحة الرئيسية ---
$router->get('/', 'PageController', 'home');

// --- مسارات المنتجات (Products API) ---
$router->get('/api/products', 'ProductController', 'index');
$router->get('/api/products/featured', 'ProductController', 'featured');
$router->get('/api/products/search', 'ProductController', 'search');
$router->get('/api/products/category/{id}', 'ProductController', 'byCategory');
$router->get('/api/categories', 'ProductController', 'categories');
$router->get('/api/products/{id}', 'ProductController', 'show');
$router->post('/api/products', 'ProductController', 'store');
$router->put('/api/products/{id}', 'ProductController', 'update');
$router->delete('/api/products/{id}', 'ProductController', 'destroy');

// --- مسارات سلة التسوق (Cart API) ---
$router->get('/api/cart', 'CartController', 'index');
$router->post('/api/cart', 'CartController', 'store');
$router->put('/api/cart/{id}', 'CartController', 'update');
$router->delete('/api/cart/{id}', 'CartController', 'destroy');
$router->delete('/api/cart/clear', 'CartController', 'clear');

// --- مسارات الطلبات (Orders API) ---
$router->get('/api/orders', 'OrderController', 'index');
$router->get('/api/orders/stats', 'OrderController', 'stats');
$router->get('/api/orders/customer/{id}', 'OrderController', 'byCustomer');
$router->get('/api/orders/{id}', 'OrderController', 'show');
$router->post('/api/orders', 'OrderController', 'store');
$router->put('/api/orders/{id}/status', 'OrderController', 'updateStatus');

// --- مسارات المحاسبة (Accounting API) ---
$router->get('/api/accounting/accounts', 'AccountingController', 'chartOfAccounts');
$router->get('/api/accounting/entries', 'AccountingController', 'entries');
$router->get('/api/accounting/entries/{id}', 'AccountingController', 'showEntry');
$router->post('/api/accounting/entries', 'AccountingController', 'createEntry');
$router->post('/api/accounting/expenses', 'AccountingController', 'recordExpense');
$router->get('/api/accounting/invoices', 'AccountingController', 'invoices');
$router->get('/api/accounting/invoices/{id}', 'AccountingController', 'showInvoice');
$router->get('/api/accounting/reports/sales', 'AccountingController', 'salesReport');
$router->get('/api/accounting/reports/profit', 'AccountingController', 'profitReport');
$router->get('/api/accounting/reports/trial-balance', 'AccountingController', 'trialBalance');

// --- مسارات خدمة العملاء CRM ---
$router->get('/api/crm/customers', 'CRMController', 'customers');
$router->get('/api/crm/customers/{id}', 'CRMController', 'showCustomer');
$router->post('/api/crm/customers/register', 'CRMController', 'registerCustomer');
$router->post('/api/crm/customers/login', 'CRMController', 'loginCustomer');
$router->get('/api/crm/tickets', 'CRMController', 'tickets');
$router->get('/api/crm/tickets/{id}', 'CRMController', 'showTicket');
$router->post('/api/crm/tickets', 'CRMController', 'createTicket');
$router->post('/api/crm/tickets/{id}/reply', 'CRMController', 'replyToTicket');
$router->put('/api/crm/tickets/{id}/resolve', 'CRMController', 'resolveTicket');
$router->get('/api/crm/stats', 'CRMController', 'stats');

// --- مسارات صفحات العرض (Views) ---
$router->get('/admin', 'PageController', 'adminDashboard');
$router->get('/admin/products', 'PageController', 'adminProducts');
$router->get('/admin/orders', 'PageController', 'adminOrders');
$router->get('/admin/accounting', 'PageController', 'adminAccounting');
$router->get('/admin/crm', 'PageController', 'adminCRM');
$router->get('/shop', 'PageController', 'shop');
$router->get('/shop/product/{id}', 'PageController', 'productDetail');
$router->get('/shop/cart', 'PageController', 'cartPage');
$router->get('/shop/checkout', 'PageController', 'checkout');

// تنفيذ التوجيه
$router->resolve();
