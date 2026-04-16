<?php
/**
 * ============================================
 * إعدادات الاتصال بقاعدة البيانات - PostgreSQL
 * Database Connection Configuration - PostgreSQL (Render.com)
 * ============================================
 * يدعم وضعين:
 * 1. الإنتاج (Production): يقرأ من متغير البيئة DATABASE_URL
 * 2. التطوير المحلي (Development): يستخدم القيم الافتراضية
 */

/**
 * تحليل رابط قاعدة البيانات DATABASE_URL
 * Parse DATABASE_URL into connection components
 * الصيغة: postgres://username:password@host:port/dbname
 * 
 * @param string $url رابط الاتصال
 * @return array مصفوفة تحتوي على مكونات الاتصال
 */
function parseDatabaseUrl(string $url): array
{
    $parts = parse_url($url);
    
    return [
        'host'     => $parts['host'] ?? 'localhost',
        'port'     => $parts['port'] ?? 5432,
        'dbname'   => ltrim($parts['path'] ?? '/clothing_store', '/'),
        'username' => $parts['user'] ?? 'postgres',
        'password' => $parts['pass'] ?? '',
    ];
}

// ============================================
// تحديد مصدر الإعدادات (بيئة أو افتراضي)
// ============================================

$databaseUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? false);

if ($databaseUrl) {
    // === وضع الإنتاج: قراءة من متغير البيئة ===
    $parsed = parseDatabaseUrl($databaseUrl);
    
    return [
        'driver'   => 'pgsql',
        'host'     => $parsed['host'],
        'port'     => (int) $parsed['port'],
        'dbname'   => $parsed['dbname'],
        'username' => $parsed['username'],
        'password' => $parsed['password'],
        'charset'  => 'utf8',
        'sslmode'  => 'require',  // مطلوب لـ Render.com
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    ];
} else {
    // === وضع التطوير المحلي ===
    return [
        'driver'   => 'pgsql',
        'host'     => getenv('DB_HOST') ?: 'localhost',
        'port'     => (int) (getenv('DB_PORT') ?: 5432),
        'dbname'   => getenv('DB_NAME') ?: 'clothing_store',
        'username' => getenv('DB_USER') ?: 'postgres',
        'password' => getenv('DB_PASS') ?: '',
        'charset'  => 'utf8',
        'sslmode'  => 'prefer',
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    ];
}
