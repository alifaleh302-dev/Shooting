<?php
/**
 * ============================================
 * سكربت ترحيل قاعدة البيانات
 * Database Migration Script
 * ============================================
 * يقوم بتنفيذ schema.sql على قاعدة البيانات PostgreSQL
 * يعمل في بيئة التطوير والإنتاج (Render.com)
 */

echo "============================================\n";
echo "🗃️  بدء ترحيل قاعدة البيانات...\n";
echo "   Database Migration Started...\n";
echo "============================================\n\n";

// تحميل إعدادات قاعدة البيانات
$config = require __DIR__ . '/../config/database.php';

// بناء سلسلة الاتصال DSN
$dsn = sprintf(
    "pgsql:host=%s;port=%d;dbname=%s",
    $config['host'],
    $config['port'],
    $config['dbname']
);

// إضافة sslmode إذا كان محدداً
if (!empty($config['sslmode'])) {
    $dsn .= ";sslmode=" . $config['sslmode'];
}

try {
    echo "📡 جاري الاتصال بقاعدة البيانات...\n";
    echo "   Host: {$config['host']}:{$config['port']}\n";
    echo "   Database: {$config['dbname']}\n\n";

    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "✅ تم الاتصال بنجاح!\n\n";

    // قراءة ملف الـ Schema
    $schemaFile = __DIR__ . '/schema.sql';
    
    if (!file_exists($schemaFile)) {
        echo "❌ ملف schema.sql غير موجود!\n";
        exit(1);
    }

    $sql = file_get_contents($schemaFile);
    
    echo "📄 جاري تنفيذ مخطط قاعدة البيانات...\n";
    echo "   حجم الملف: " . round(strlen($sql) / 1024, 2) . " KB\n\n";

    // تنفيذ الـ Schema
    $pdo->exec($sql);

    echo "✅ تم تنفيذ المخطط بنجاح!\n\n";

    // التحقق من الجداول المُنشأة
    $tables = $pdo->query(
        "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename"
    )->fetchAll();

    echo "📊 الجداول المُنشأة (" . count($tables) . " جدول):\n";
    foreach ($tables as $i => $table) {
        echo "   " . ($i + 1) . ". {$table['tablename']}\n";
    }

    echo "\n============================================\n";
    echo "🎉 تم ترحيل قاعدة البيانات بنجاح!\n";
    echo "   Migration completed successfully!\n";
    echo "============================================\n";

} catch (PDOException $e) {
    echo "\n❌ خطأ في الاتصال أو التنفيذ:\n";
    echo "   " . $e->getMessage() . "\n\n";
    
    // محاولة إنشاء قاعدة البيانات إذا لم تكن موجودة
    if (strpos($e->getMessage(), 'does not exist') !== false) {
        echo "💡 تلميح: قاعدة البيانات غير موجودة. في Render.com، تأكد من:\n";
        echo "   1. إنشاء PostgreSQL Database في لوحة تحكم Render\n";
        echo "   2. ربط DATABASE_URL في إعدادات البيئة\n";
    }
    
    exit(1);
}
