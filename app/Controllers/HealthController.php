<?php
/**
 * ============================================
 * متحكم فحص الصحة - Health Check Controller
 * ============================================
 * يُستخدم من قبل Render.com للتحقق من أن التطبيق يعمل
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HealthController extends Controller
{
    /**
     * فحص صحة التطبيق وقاعدة البيانات
     * GET /api/health
     */
    public function check(): void
    {
        $status = [
            'status'    => 'ok',
            'app'       => 'Clothing Store API',
            'timestamp' => date('c'),
            'php'       => PHP_VERSION,
            'database'  => 'disconnected'
        ];

        try {
            // اختبار الاتصال بقاعدة البيانات
            $db = Database::getInstance();
            $result = $db->fetchOne("SELECT 1 as check_val");
            
            if ($result && $result['check_val'] == 1) {
                $status['database'] = 'connected';
            }
        } catch (\Exception $e) {
            $status['database'] = 'error: ' . $e->getMessage();
            $status['status'] = 'degraded';
        }

        $this->jsonSuccess($status, 'Health check passed');
    }
}
