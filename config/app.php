<?php
/**
 * ============================================
 * الإعدادات العامة للتطبيق
 * Application General Configuration
 * ============================================
 * جميع الإعدادات الحساسة تُقرأ من متغيرات البيئة (Environment Variables)
 */

return [
    // اسم التطبيق
    'app_name'    => 'متجر الأزياء - Clothing Store',
    
    // رابط التطبيق الأساسي - يُقرأ من متغير البيئة أو القيمة الافتراضية
    'base_url'    => getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? 'http://localhost:8080'),
    
    // البيئة: development أو production
    'environment' => getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development'),
    
    // المنطقة الزمنية
    'timezone'    => getenv('APP_TIMEZONE') ?: 'Asia/Riyadh',
    
    // اللغة الافتراضية
    'language'    => 'ar',
    
    // العملة الافتراضية
    'currency'    => 'SAR',
    
    // رمز العملة
    'currency_symbol' => 'ر.س',
    
    // نسبة ضريبة القيمة المضافة (15%)
    'vat_rate'    => 15,
    
    // عدد العناصر في كل صفحة (الترقيم)
    'per_page'    => 20,
    
    // مفتاح التشفير - يُقرأ من متغير البيئة (مطلوب تغييره في الإنتاج)
    'secret_key'  => getenv('APP_SECRET') ?: ($_ENV['APP_SECRET'] ?? 'dev_secret_key_change_in_production'),
    
    // مسار رفع الصور
    'upload_path' => __DIR__ . '/../public/assets/images/uploads/',
    
    // الحجم الأقصى لرفع الصور (5 ميجابايت)
    'max_upload_size' => 5 * 1024 * 1024,
    
    // أنواع الصور المسموحة
    'allowed_image_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    
    // إعدادات الجلسة
    'session' => [
        'lifetime' => 7200,  // ساعتان
        'name'     => 'clothing_store_session'
    ]
];
