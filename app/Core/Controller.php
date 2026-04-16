<?php
/**
 * ============================================
 * النواة: المتحكم الأساسي
 * Core: Base Controller
 * ============================================
 * الفئة الأساسية لجميع المتحكمات - توفر وظائف مشتركة
 */

namespace App\Core;

class Controller
{
    /** @var Database كائن قاعدة البيانات */
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * إرسال استجابة JSON ناجحة
     * Send a success JSON response
     * 
     * @param mixed $data البيانات
     * @param string $message رسالة النجاح
     * @param int $code كود HTTP
     */
    protected function jsonSuccess(mixed $data = null, string $message = 'تمت العملية بنجاح', int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * إرسال استجابة JSON بخطأ
     * Send an error JSON response
     * 
     * @param string $message رسالة الخطأ
     * @param int $code كود HTTP
     * @param mixed $errors تفاصيل الأخطاء
     */
    protected function jsonError(string $message = 'حدث خطأ', int $code = 400, mixed $errors = null): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors'  => $errors
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * الحصول على بيانات الطلب (JSON Body)
     * Get request body data (JSON)
     * 
     * @return array
     */
    protected function getRequestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // بيانات JSON
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
            return $data ?? [];
        }

        // بيانات نموذج عادية
        return array_merge($_GET, $_POST);
    }

    /**
     * التحقق من وجود الحقول المطلوبة
     * Validate required fields
     * 
     * @param array $data البيانات
     * @param array $required الحقول المطلوبة
     * @return array الأخطاء (فارغة إذا لا يوجد أخطاء)
     */
    protected function validateRequired(array $data, array $required): array
    {
        $errors = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[$field] = "الحقل {$field} مطلوب";
            }
        }
        return $errors;
    }

    /**
     * عرض صفحة HTML
     * Render an HTML view
     * 
     * @param string $view مسار ملف العرض
     * @param array $data البيانات المُمررة للعرض
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            http_response_code(404);
            echo "View not found: {$view}";
        }
    }

    /**
     * الحصول على معامل الترقيم (Pagination)
     * Get pagination parameters
     * 
     * @return array [page, perPage, offset]
     */
    protected function getPagination(): array
    {
        $config  = require __DIR__ . '/../../config/app.php';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? $config['per_page']);
        $offset  = ($page - 1) * $perPage;

        return [
            'page'     => $page,
            'per_page' => $perPage,
            'offset'   => $offset
        ];
    }
}
