<?php
/**
 * ============================================
 * النواة: فئة الاتصال بقاعدة البيانات
 * Core: Database Connection Class (Singleton Pattern)
 * ============================================
 * تدعم PostgreSQL وتقرأ إعدادات الاتصال من متغيرات البيئة
 * Supports PostgreSQL with DATABASE_URL environment variable
 */

namespace App\Core;

class Database
{
    /** @var Database|null النسخة الوحيدة من الكائن */
    private static ?Database $instance = null;
    
    /** @var \PDO كائن PDO للاتصال */
    private \PDO $pdo;

    /**
     * المُنشئ الخاص - يمنع إنشاء نسخ مباشرة
     * Private constructor - prevents direct instantiation
     */
    private function __construct()
    {
        // تحميل إعدادات قاعدة البيانات
        $config = require __DIR__ . '/../../config/database.php';

        // بناء سلسلة الاتصال DSN لـ PostgreSQL
        $dsn = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;options='--client_encoding=%s'",
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset'] ?? 'utf8'
        );

        // إضافة sslmode إذا كان محدداً (مطلوب لـ Render)
        if (!empty($config['sslmode'])) {
            $dsn .= ";sslmode=" . $config['sslmode'];
        }

        try {
            // إنشاء اتصال PDO جديد
            $this->pdo = new \PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );

            // ضبط ترميز الأحرف
            $this->pdo->exec("SET client_encoding TO 'UTF8'");
            
        } catch (\PDOException $e) {
            // في حالة فشل الاتصال - إرسال رد JSON بالخطأ
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'فشل الاتصال بقاعدة البيانات',
                'error'   => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /**
     * الحصول على النسخة الوحيدة من الاتصال
     * Get the singleton instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * الحصول على كائن PDO
     * Get PDO connection object
     */
    public function getConnection(): \PDO
    {
        return $this->pdo;
    }

    /**
     * تنفيذ استعلام مع معاملات محمية
     * Execute a prepared query with bound parameters
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * جلب صف واحد
     * Fetch single row
     */
    public function fetchOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * جلب جميع الصفوف
     * Fetch all rows
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * الحصول على آخر معرف مُدرج
     * Get last inserted ID
     * ملاحظة: PostgreSQL يتطلب تمرير اسم التسلسل (sequence)
     * Note: PostgreSQL requires the sequence name
     */
    public function lastInsertId(?string $sequenceName = null): string
    {
        return $this->pdo->lastInsertId($sequenceName);
    }

    /**
     * بدء معاملة (Transaction)
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * تأكيد المعاملة
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * التراجع عن المعاملة
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /** منع الاستنساخ */
    private function __clone() {}

    /** منع إلغاء التسلسل */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
