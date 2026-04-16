# 🛍️ نظام متجر الملابس الإلكتروني
## Clothing Store E-Commerce System (Accounting + CRM)

> **نظام متكامل** مبني بـ PHP (Vanilla MVC) + **PostgreSQL** + Bootstrap 5 RTL  
> **جاهز للنشر** على [Render.com](https://render.com)

---

## 📋 وصف المشروع
نظام متكامل لمتجر ملابس إلكتروني يتضمن:
- ✅ **نظام محاسبي** كامل (شجرة حسابات، قيود تلقائية، فواتير، تقارير أرباح)
- ✅ **نظام خدمة عملاء CRM** (تذاكر دعم، سجل مشتريات، نقاط ولاء)
- ✅ **إدارة طلبات** (سلة تسوق، معالجة دفع، تحديث مخزون تلقائي)
- ✅ **JSON API** كامل لجميع العمليات
- ✅ **واجهة متجاوبة** (Responsive) تدعم العربية RTL

---

## 🛠️ التقنيات المستخدمة

| التقنية | الوصف |
|---------|-------|
| **PHP 8.1+** | لغة Backend (بدون Frameworks) |
| **PostgreSQL** | قاعدة البيانات (Render.com Compatible) |
| **HTML5/CSS3** | واجهات المستخدم |
| **JavaScript (Vanilla)** | التفاعل مع الـ API عبر Fetch |
| **Bootstrap 5 RTL** | التصميم المتجاوب |
| **Chart.js** | الرسوم البيانية والتقارير |
| **PDO (pgsql)** | الاتصال الآمن بقاعدة البيانات |

---

## 📁 هيكل المشروع (MVC)
```
clothing-store/
├── app/                          # طبقة التطبيق
│   ├── Core/                     # النواة الأساسية
│   │   ├── Database.php          # اتصال PostgreSQL (Singleton + DATABASE_URL)
│   │   ├── Router.php            # نظام التوجيه
│   │   ├── Controller.php        # المتحكم الأساسي
│   │   └── Model.php             # النموذج الأساسي (CRUD + RETURNING)
│   ├── Controllers/              # المتحكمات
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   ├── CartController.php
│   │   ├── AccountingController.php
│   │   ├── CRMController.php
│   │   ├── PageController.php
│   │   └── HealthController.php  # فحص الصحة لـ Render
│   └── Models/                   # النماذج
│       ├── Product.php
│       ├── Customer.php
│       ├── Order.php
│       ├── Cart.php
│       ├── Invoice.php
│       ├── JournalEntry.php
│       └── SupportTicket.php
├── views/                        # واجهات المستخدم
│   ├── admin/                    # لوحة تحكم المشرف
│   ├── customer/                 # واجهة العميل
│   └── layouts/                  # القوالب المشتركة
├── public/                       # الملفات العامة (Document Root)
│   ├── index.php                 # نقطة الدخول الرئيسية
│   ├── .htaccess                 # قواعد Apache
│   └── assets/                   # CSS, JS, Images
├── config/                       # ملفات الإعدادات
│   ├── database.php              # إعدادات PostgreSQL + DATABASE_URL
│   └── app.php                   # إعدادات التطبيق (من env vars)
├── database/
│   ├── schema.sql                # مخطط PostgreSQL الكامل
│   └── migrate.php               # سكربت الترحيل التلقائي
├── composer.json                 # تبعيات PHP + ext-pdo_pgsql
├── render.yaml                   # تكوين Render.com
├── .htaccess                     # توجيه Apache للجذر
└── README.md
```

---

## 🗃️ قاعدة البيانات (PostgreSQL - 21 جدول)

| المجموعة | الجداول |
|----------|---------|
| **المنتجات والمخزون** | `categories`, `products`, `product_images`, `colors`, `sizes`, `inventory` |
| **العملاء** | `customers`, `customer_addresses` |
| **الطلبات والسلة** | `orders`, `order_items`, `cart`, `coupons` |
| **النظام المحاسبي** | `accounts`, `journal_entries`, `journal_entry_lines`, `invoices`, `expenses` |
| **خدمة العملاء CRM** | `order_status_history`, `support_tickets`, `ticket_replies`, `admins` |

### التوافق مع PostgreSQL:
- ✅ `SERIAL` بدلاً من `AUTO_INCREMENT`
- ✅ Custom `ENUM` types لجميع الحالات
- ✅ `CURRENT_TIMESTAMP` بدلاً من `NOW()`
- ✅ `ILIKE` بدلاً من `LIKE` (بحث غير حساس)
- ✅ `EXTRACT()` بدلاً من `MONTH()`/`YEAR()`
- ✅ `::date` casting بدلاً من `DATE()`
- ✅ `CASE WHEN` بدلاً من `FIELD()`
- ✅ `RETURNING id` بدلاً من `lastInsertId()`
- ✅ Triggers تلقائية لـ `updated_at`

---

## 🚀 النشر على Render.com (Deployment)

### الطريقة 1: النشر التلقائي عبر render.yaml

1. **سجّل دخول** على [Render Dashboard](https://dashboard.render.com)
2. اضغط **"New" → "Blueprint"**
3. اربط مستودع GitHub الخاص بك
4. سيقرأ Render ملف `render.yaml` تلقائياً ويُنشئ:
   - ✅ Web Service (PHP)
   - ✅ PostgreSQL Database
5. سيتم ضبط `DATABASE_URL` تلقائياً

### الطريقة 2: النشر اليدوي

#### الخطوة 1: إنشاء قاعدة بيانات PostgreSQL
1. اذهب إلى **"New" → "PostgreSQL"**
2. أدخل:
   - **Name**: `clothing-store-db`
   - **Database**: `clothing_store`
   - **User**: `clothing_store_user`
3. اختر **المنطقة** الأقرب لجمهورك
4. اضغط **"Create Database"**
5. **انسخ** الـ **Internal Database URL** أو **External Database URL**

#### الخطوة 2: إنشاء خدمة الويب
1. اذهب إلى **"New" → "Web Service"**
2. اربط مستودع GitHub
3. أدخل الإعدادات:

   | الإعداد | القيمة |
   |---------|--------|
   | **Name** | `clothing-store` |
   | **Runtime** | `PHP` |
   | **Build Command** | `composer install --no-dev --optimize-autoloader && php database/migrate.php` |
   | **Start Command** | `php -S 0.0.0.0:$PORT -t public public/index.php` |

4. **أضف متغيرات البيئة** (Environment Variables):

   | المتغير | القيمة |
   |---------|--------|
   | `DATABASE_URL` | `postgres://user:pass@host:5432/clothing_store` ← انسخه من الخطوة 1 |
   | `APP_ENV` | `production` |
   | `APP_SECRET` | (قيمة عشوائية طويلة) |
   | `APP_TIMEZONE` | `Asia/Riyadh` |

5. اضغط **"Create Web Service"**

#### الخطوة 3: التحقق
- بعد النشر، افتح: `https://your-app.onrender.com/api/health`
- يجب أن ترى:
```json
{
  "success": true,
  "data": {
    "status": "ok",
    "database": "connected"
  }
}
```

---

## 💻 التشغيل محلياً (Local Development)

### المتطلبات
- PHP 8.1+ مع إضافات: `pdo_pgsql`, `json`, `mbstring`
- PostgreSQL 14+
- Composer

### الخطوات

```bash
# 1. استنساخ المشروع
git clone https://github.com/ll361231dsh-debug/-.git clothing-store
cd clothing-store

# 2. تثبيت التبعيات
composer install

# 3. إنشاء قاعدة البيانات
createdb clothing_store
psql -d clothing_store -f database/schema.sql

# 4. ضبط متغيرات البيئة (اختر أحد الطريقتين)

# الطريقة أ: متغيرات بيئة
export DATABASE_URL="postgres://postgres:password@localhost:5432/clothing_store"
export APP_ENV="development"

# الطريقة ب: أو عدّل config/database.php مباشرة

# 5. تشغيل الخادم
php -S localhost:8080 -t public public/index.php

# 6. افتح المتصفح
# المتجر:      http://localhost:8080/
# لوحة التحكم: http://localhost:8080/admin
# فحص الصحة:   http://localhost:8080/api/health
```

---

## ⚡ API Endpoints

### فحص الصحة
```
GET  /api/health                    → فحص صحة التطبيق وقاعدة البيانات
```

### المنتجات
```
GET    /api/products                → جلب المنتجات مع ترقيم
GET    /api/products/{id}           → تفاصيل منتج
GET    /api/products/featured       → المنتجات المميزة
GET    /api/products/search?q=      → البحث (ILIKE)
POST   /api/products                → إنشاء منتج
PUT    /api/products/{id}           → تحديث منتج
DELETE /api/products/{id}           → حذف منتج (ناعم)
```

### سلة التسوق
```
GET    /api/cart                    → عرض السلة
POST   /api/cart                    → إضافة للسلة
PUT    /api/cart/{id}               → تحديث الكمية
DELETE /api/cart/{id}               → حذف عنصر
```

### الطلبات
```
GET    /api/orders                  → جلب الطلبات
GET    /api/orders/{id}             → تفاصيل طلب
POST   /api/orders                  → إنشاء طلب جديد
PUT    /api/orders/{id}/status      → تحديث الحالة
GET    /api/orders/stats            → إحصائيات
```

### المحاسبة
```
GET    /api/accounting/accounts               → شجرة الحسابات
GET    /api/accounting/entries                → القيود المحاسبية
POST   /api/accounting/entries                → قيد يدوي جديد
POST   /api/accounting/expenses               → تسجيل مصروف
GET    /api/accounting/invoices               → الفواتير
GET    /api/accounting/reports/sales          → تقرير المبيعات
GET    /api/accounting/reports/profit         → تقرير الأرباح
GET    /api/accounting/reports/trial-balance  → ميزان المراجعة
```

### خدمة العملاء CRM
```
GET    /api/crm/customers                     → قائمة العملاء
POST   /api/crm/customers/register            → تسجيل عميل
POST   /api/crm/customers/login               → تسجيل دخول
GET    /api/crm/tickets                       → التذاكر المفتوحة
POST   /api/crm/tickets                       → تذكرة جديدة
POST   /api/crm/tickets/{id}/reply            → رد على تذكرة
PUT    /api/crm/tickets/{id}/resolve          → حل التذكرة
GET    /api/crm/stats                         → إحصائيات CRM
```

---

## 🔐 بيانات الدخول الافتراضية
| النوع | البريد | كلمة المرور |
|-------|--------|-------------|
| مشرف | admin@store.com | admin123 |

---

## 🔒 الأمان
- ✅ جميع الإعدادات الحساسة تُقرأ من **متغيرات البيئة** (`getenv()`)
- ✅ لا توجد بيانات مشفرة (Hardcoded) في الكود
- ✅ اتصال قاعدة البيانات عبر **SSL** (`sslmode=require`) في الإنتاج
- ✅ كلمات المرور مشفرة بـ `password_hash()` / `PASSWORD_DEFAULT`
- ✅ استعلامات محمية عبر **Prepared Statements** (PDO)
- ✅ `.htaccess` يمنع الوصول لملفات `.env`, `.sql`, `.yml`

---

## 📝 ملاحظات تحويل MySQL → PostgreSQL
| MySQL | PostgreSQL | ملاحظات |
|-------|-----------|---------|
| `AUTO_INCREMENT` | `SERIAL` | تسلسل تلقائي |
| `TINYINT(1)` | `SMALLINT` | للحقول المنطقية |
| `ENUM('a','b')` | `CREATE TYPE ... AS ENUM` | أنواع مخصصة |
| `NOW()` | `CURRENT_TIMESTAMP` | الوقت الحالي |
| `CURDATE()` | `CURRENT_DATE` | التاريخ الحالي |
| `DATE(col)` | `col::date` | تحويل لتاريخ |
| `MONTH(col)` | `EXTRACT(MONTH FROM col)` | استخراج الشهر |
| `LIKE` | `ILIKE` | بحث غير حساس لحالة الأحرف |
| `FIELD(col,...)` | `CASE WHEN ... END` | ترتيب مخصص |
| `TIMESTAMPDIFF(HOUR,a,b)` | `EXTRACT(EPOCH FROM (b-a))/3600` | فرق الوقت |
| `ON UPDATE CURRENT_TIMESTAMP` | Trigger function | تحديث تلقائي |
| `FULLTEXT INDEX` | `tsvector` / `GIN` | بحث نصي (اختياري) |

---

## 📄 الترخيص
هذا المشروع للاستخدام التعليمي والتجاري.
