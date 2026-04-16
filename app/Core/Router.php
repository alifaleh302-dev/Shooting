<?php
/**
 * ============================================
 * النواة: الموجّه (Router)
 * Core: URL Router
 * ============================================
 * يقوم بتوجيه الطلبات إلى المتحكم المناسب بناءً على الرابط
 * متوافق مع Render.com (بدون مسار أساسي ثابت)
 */

namespace App\Core;

class Router
{
    /** @var array مصفوفة المسارات المسجلة */
    private array $routes = [];

    /**
     * تسجيل مسار GET
     */
    public function get(string $path, string $controller, string $method): self
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
        return $this;
    }

    /**
     * تسجيل مسار POST
     */
    public function post(string $path, string $controller, string $method): self
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
        return $this;
    }

    /**
     * تسجيل مسار PUT
     */
    public function put(string $path, string $controller, string $method): self
    {
        $this->routes['PUT'][$path] = ['controller' => $controller, 'method' => $method];
        return $this;
    }

    /**
     * تسجيل مسار DELETE
     */
    public function delete(string $path, string $controller, string $method): self
    {
        $this->routes['DELETE'][$path] = ['controller' => $controller, 'method' => $method];
        return $this;
    }

    /**
     * معالجة الطلب الحالي وتوجيهه للمتحكم المناسب
     */
    public function resolve(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri    = $this->parseUri();

        // دعم طرق PUT و DELETE عبر حقل _method
        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        }

        // البحث عن المسار المطابق
        foreach ($this->routes[$requestMethod] ?? [] as $route => $handler) {
            $params = $this->matchRoute($route, $requestUri);
            if ($params !== false) {
                $this->dispatch($handler, $params);
                return;
            }
        }

        // خدمة الملفات الثابتة (CSS, JS, Images) في وضع PHP Built-in Server
        $staticFile = __DIR__ . '/../../public' . $requestUri;
        if (php_sapi_name() === 'cli-server' && is_file($staticFile)) {
            return; // يتم التعامل مع الملف الثابت بواسطة الخادم المدمج
        }

        // لم يتم العثور على المسار - 404
        http_response_code(404);
        
        // إذا كان الطلب يبدأ بـ /api، نرجع JSON
        if (str_starts_with($requestUri, '/api')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'المسار غير موجود - Route not found',
                'path'    => $requestUri
            ], JSON_UNESCAPED_UNICODE);
        } else {
            // لطلبات المتصفح، نعرض صفحة 404 الجميلة
            $viewFile = __DIR__ . '/../../views/404.php';
            if (file_exists($viewFile)) {
                require $viewFile;
            } else {
                echo "<h1>404 - الصفحة غير موجودة</h1>";
            }
        }
    }

    /**
     * تحليل الرابط واستخراج المسار
     * يدعم كلا البيئتين: Apache (htaccess) و Render (PHP Built-in Server)
     */
    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);

        // إزالة المسار الأساسي إذا كان موجوداً (للتطوير المحلي على Apache)
        $basePath = getenv('APP_BASE_PATH') ?: ($_ENV['APP_BASE_PATH'] ?? '');
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        // إزالة الشرطة المائلة المزدوجة وتنظيف المسار
        $uri = '/' . trim($uri, '/');

        return $uri ?: '/';
    }

    /**
     * مطابقة المسار واستخراج المعاملات الديناميكية
     */
    private function matchRoute(string $route, string $uri): array|false
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    /**
     * توجيه الطلب إلى المتحكم والوظيفة المناسبة
     */
    private function dispatch(array $handler, array $params): void
    {
        $controllerClass = "App\\Controllers\\" . $handler['controller'];
        $method = $handler['method'];

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => "المتحكم غير موجود: {$handler['controller']}"
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => "الوظيفة غير موجودة: {$method}"
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        call_user_func_array([$controller, $method], $params);
    }
}
