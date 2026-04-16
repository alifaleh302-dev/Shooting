-- ============================================
-- مخطط قاعدة البيانات - متجر الملابس الإلكتروني
-- Database Schema - Clothing Store E-Commerce
-- ============================================
-- PostgreSQL Version (Render.com Compatible)
-- ============================================
-- يتضمن: المنتجات، المخزون، العملاء، الطلبات، الفواتير، النظام المحاسبي، خدمة العملاء CRM
-- ============================================

-- ============================================
-- تعريف أنواع ENUM المخصصة لـ PostgreSQL
-- Custom ENUM Types for PostgreSQL
-- ============================================
DO $$ BEGIN
    -- جنس العميل
    CREATE TYPE gender_type AS ENUM ('male', 'female', 'other');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- حالة الطلب
    CREATE TYPE order_status_type AS ENUM ('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- طريقة الدفع
    CREATE TYPE payment_method_type AS ENUM ('cash_on_delivery', 'credit_card', 'bank_transfer', 'wallet');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- حالة الدفع
    CREATE TYPE payment_status_type AS ENUM ('pending', 'paid', 'failed', 'refunded');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- نوع الخصم
    CREATE TYPE coupon_type AS ENUM ('percentage', 'fixed');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- نوع الحساب المحاسبي
    CREATE TYPE account_type AS ENUM ('asset', 'liability', 'equity', 'revenue', 'expense');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- نوع المرجع للقيد
    CREATE TYPE journal_ref_type AS ENUM ('order', 'refund', 'expense', 'adjustment', 'manual');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- حالة القيد
    CREATE TYPE journal_status_type AS ENUM ('draft', 'posted', 'cancelled');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- حالة الفاتورة
    CREATE TYPE invoice_status_type AS ENUM ('draft', 'sent', 'paid', 'overdue', 'cancelled');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- تصنيف تذكرة الدعم
    CREATE TYPE ticket_category_type AS ENUM ('order_issue', 'product_inquiry', 'return_request', 'payment_issue', 'general');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- أولوية التذكرة
    CREATE TYPE ticket_priority_type AS ENUM ('low', 'medium', 'high', 'urgent');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- حالة التذكرة
    CREATE TYPE ticket_status_type AS ENUM ('open', 'in_progress', 'waiting_customer', 'resolved', 'closed');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- نوع مُرسل الرد
    CREATE TYPE sender_type AS ENUM ('customer', 'admin');
EXCEPTION WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    -- دور المشرف
    CREATE TYPE admin_role_type AS ENUM ('super_admin', 'admin', 'accountant', 'support');
EXCEPTION WHEN duplicate_object THEN null;
END $$;


-- ============================================
-- 1. جدول التصنيفات (Categories)
-- ============================================
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    parent_id INT NULL REFERENCES categories(id) ON DELETE SET NULL,
    image VARCHAR(255) NULL,
    is_active SMALLINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE categories IS 'جدول تصنيفات المنتجات';
COMMENT ON COLUMN categories.name IS 'اسم التصنيف';
COMMENT ON COLUMN categories.slug IS 'الرابط المختصر';
COMMENT ON COLUMN categories.parent_id IS 'التصنيف الأب (للتصنيفات الفرعية)';


-- ============================================
-- 2. جدول المنتجات (Products)
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    category_id INT NOT NULL REFERENCES categories(id) ON DELETE RESTRICT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    description TEXT NULL,
    short_description VARCHAR(500) NULL,
    base_price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    sku VARCHAR(50) NOT NULL UNIQUE,
    brand VARCHAR(100) NULL,
    material VARCHAR(100) NULL,
    main_image VARCHAR(255) NULL,
    is_active SMALLINT DEFAULT 1,
    is_featured SMALLINT DEFAULT 0,
    total_sold INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_price ON products(base_price);
CREATE INDEX idx_products_active ON products(is_active);

COMMENT ON TABLE products IS 'جدول المنتجات';
COMMENT ON COLUMN products.base_price IS 'السعر الأساسي';
COMMENT ON COLUMN products.sale_price IS 'سعر البيع (الخصم)';
COMMENT ON COLUMN products.cost_price IS 'سعر التكلفة';
COMMENT ON COLUMN products.sku IS 'رمز المنتج SKU';


-- ============================================
-- 3. جدول صور المنتجات (Product Images)
-- ============================================
CREATE TABLE IF NOT EXISTS product_images (
    id SERIAL PRIMARY KEY,
    product_id INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(200) NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE product_images IS 'صور إضافية للمنتجات';


-- ============================================
-- 4. جدول الألوان (Colors)
-- ============================================
CREATE TABLE IF NOT EXISTS colors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    hex_code VARCHAR(7) NOT NULL,
    is_active SMALLINT DEFAULT 1
);

COMMENT ON TABLE colors IS 'جدول الألوان المتاحة';


-- ============================================
-- 5. جدول الأحجام (Sizes)
-- ============================================
CREATE TABLE IF NOT EXISTS sizes (
    id SERIAL PRIMARY KEY,
    name VARCHAR(10) NOT NULL,
    description VARCHAR(100) NULL,
    sort_order INT DEFAULT 0
);

COMMENT ON TABLE sizes IS 'جدول أحجام الملابس';


-- ============================================
-- 6. جدول المخزون (Inventory) - علاقة Many-to-Many
-- ============================================
CREATE TABLE IF NOT EXISTS inventory (
    id SERIAL PRIMARY KEY,
    product_id INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    color_id INT NOT NULL REFERENCES colors(id) ON DELETE RESTRICT,
    size_id INT NOT NULL REFERENCES sizes(id) ON DELETE RESTRICT,
    quantity INT NOT NULL DEFAULT 0,
    low_stock_threshold INT DEFAULT 5,
    sku_variant VARCHAR(80) NULL,
    additional_price DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (product_id, color_id, size_id)
);

CREATE INDEX idx_inventory_quantity ON inventory(quantity);

COMMENT ON TABLE inventory IS 'جدول المخزون - يربط المنتج بالألوان والأحجام';
COMMENT ON COLUMN inventory.low_stock_threshold IS 'حد التنبيه للمخزون المنخفض';


-- ============================================
-- 7. جدول العملاء (Customers)
-- ============================================
CREATE TABLE IF NOT EXISTS customers (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    gender gender_type NULL,
    date_of_birth DATE NULL,
    avatar VARCHAR(255) NULL,
    is_active SMALLINT DEFAULT 1,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(12,2) DEFAULT 0,
    loyalty_points INT DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_customers_email ON customers(email);
CREATE INDEX idx_customers_phone ON customers(phone);
CREATE INDEX idx_customers_loyalty ON customers(loyalty_points);

COMMENT ON TABLE customers IS 'جدول العملاء';


-- ============================================
-- 8. جدول عناوين العملاء (Customer Addresses)
-- ============================================
CREATE TABLE IF NOT EXISTS customer_addresses (
    id SERIAL PRIMARY KEY,
    customer_id INT NOT NULL REFERENCES customers(id) ON DELETE CASCADE,
    label VARCHAR(50) DEFAULT 'المنزل',
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    country VARCHAR(100) DEFAULT 'المملكة العربية السعودية',
    is_default SMALLINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_cust_addr_customer ON customer_addresses(customer_id);

COMMENT ON TABLE customer_addresses IS 'عناوين الشحن للعملاء';


-- ============================================
-- 9. جدول الطلبات (Orders)
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    customer_id INT NOT NULL REFERENCES customers(id) ON DELETE RESTRICT,
    order_number VARCHAR(30) NOT NULL UNIQUE,
    status order_status_type DEFAULT 'pending',
    subtotal DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    vat_amount DECIMAL(10,2) DEFAULT 0,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    
    -- معلومات الشحن
    shipping_name VARCHAR(100) NULL,
    shipping_phone VARCHAR(20) NULL,
    shipping_address TEXT NULL,
    shipping_city VARCHAR(100) NULL,
    shipping_method VARCHAR(50) NULL,
    tracking_number VARCHAR(100) NULL,
    
    -- معلومات الدفع
    payment_method payment_method_type DEFAULT 'cash_on_delivery',
    payment_status payment_status_type DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    
    coupon_code VARCHAR(50) NULL,
    customer_notes TEXT NULL,
    admin_notes TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_payment ON orders(payment_status);
CREATE INDEX idx_orders_date ON orders(created_at);

COMMENT ON TABLE orders IS 'جدول الطلبات';


-- ============================================
-- 10. جدول عناصر الطلب (Order Items)
-- ============================================
CREATE TABLE IF NOT EXISTS order_items (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    inventory_id INT NOT NULL REFERENCES inventory(id) ON DELETE RESTRICT,
    product_name VARCHAR(200) NOT NULL,
    color_name VARCHAR(50) NULL,
    size_name VARCHAR(10) NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_oi_order ON order_items(order_id);
CREATE INDEX idx_oi_product ON order_items(product_id);

COMMENT ON TABLE order_items IS 'عناصر/تفاصيل الطلب';


-- ============================================
-- 11. جدول سلة التسوق (Cart)
-- ============================================
CREATE TABLE IF NOT EXISTS cart (
    id SERIAL PRIMARY KEY,
    customer_id INT NULL REFERENCES customers(id) ON DELETE CASCADE,
    session_id VARCHAR(100) NULL,
    inventory_id INT NOT NULL REFERENCES inventory(id) ON DELETE CASCADE,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_cart_customer ON cart(customer_id);
CREATE INDEX idx_cart_session ON cart(session_id);

COMMENT ON TABLE cart IS 'سلة التسوق';


-- ============================================
-- 12. جدول كوبونات الخصم (Coupons)
-- ============================================
CREATE TABLE IF NOT EXISTS coupons (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type coupon_type NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    max_discount DECIMAL(10,2) NULL,
    usage_limit INT NULL,
    used_count INT DEFAULT 0,
    starts_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    is_active SMALLINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE coupons IS 'كوبونات الخصم';


-- ============================================
-- ★★★ النظام المحاسبي (Accounting System) ★★★
-- ============================================

-- 13. شجرة الحسابات (Chart of Accounts)
-- ============================================
CREATE TABLE IF NOT EXISTS accounts (
    id SERIAL PRIMARY KEY,
    account_code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    type account_type NOT NULL,
    parent_id INT NULL REFERENCES accounts(id) ON DELETE SET NULL,
    description TEXT NULL,
    balance DECIMAL(14,2) DEFAULT 0,
    is_active SMALLINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_accounts_type ON accounts(type);
CREATE INDEX idx_accounts_code ON accounts(account_code);

COMMENT ON TABLE accounts IS 'شجرة الحسابات المحاسبية';
COMMENT ON COLUMN accounts.type IS 'نوع الحساب: أصول/خصوم/حقوق ملكية/إيرادات/مصاريف';


-- ============================================
-- 14. القيود المحاسبية (Journal Entries)
-- ============================================
CREATE TABLE IF NOT EXISTS journal_entries (
    id SERIAL PRIMARY KEY,
    entry_number VARCHAR(30) NOT NULL UNIQUE,
    entry_date DATE NOT NULL,
    description TEXT NOT NULL,
    reference_type journal_ref_type DEFAULT 'manual',
    reference_id INT NULL,
    total_debit DECIMAL(14,2) NOT NULL,
    total_credit DECIMAL(14,2) NOT NULL,
    status journal_status_type DEFAULT 'draft',
    created_by VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_je_date ON journal_entries(entry_date);
CREATE INDEX idx_je_reference ON journal_entries(reference_type, reference_id);
CREATE INDEX idx_je_status ON journal_entries(status);

COMMENT ON TABLE journal_entries IS 'القيود المحاسبية';


-- ============================================
-- 15. تفاصيل القيود (Journal Entry Lines)
-- ============================================
CREATE TABLE IF NOT EXISTS journal_entry_lines (
    id SERIAL PRIMARY KEY,
    journal_entry_id INT NOT NULL REFERENCES journal_entries(id) ON DELETE CASCADE,
    account_id INT NOT NULL REFERENCES accounts(id) ON DELETE RESTRICT,
    debit DECIMAL(14,2) DEFAULT 0,
    credit DECIMAL(14,2) DEFAULT 0,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_jel_entry ON journal_entry_lines(journal_entry_id);
CREATE INDEX idx_jel_account ON journal_entry_lines(account_id);

COMMENT ON TABLE journal_entry_lines IS 'بنود/تفاصيل القيود المحاسبية';


-- ============================================
-- 16. الفواتير (Invoices)
-- ============================================
CREATE TABLE IF NOT EXISTS invoices (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL REFERENCES orders(id) ON DELETE RESTRICT,
    invoice_number VARCHAR(30) NOT NULL UNIQUE,
    customer_id INT NOT NULL REFERENCES customers(id) ON DELETE RESTRICT,
    subtotal DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    vat_rate DECIMAL(5,2) DEFAULT 15.00,
    vat_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    status invoice_status_type DEFAULT 'draft',
    due_date DATE NULL,
    paid_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_invoices_order ON invoices(order_id);
CREATE INDEX idx_invoices_customer ON invoices(customer_id);
CREATE INDEX idx_invoices_status ON invoices(status);

COMMENT ON TABLE invoices IS 'الفواتير';


-- ============================================
-- 17. المصاريف (Expenses)
-- ============================================
CREATE TABLE IF NOT EXISTS expenses (
    id SERIAL PRIMARY KEY,
    account_id INT NOT NULL REFERENCES accounts(id) ON DELETE RESTRICT,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    description TEXT NULL,
    expense_date DATE NOT NULL,
    receipt_image VARCHAR(255) NULL,
    payment_method VARCHAR(50) NULL,
    created_by VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_expenses_date ON expenses(expense_date);
CREATE INDEX idx_expenses_category ON expenses(category);

COMMENT ON TABLE expenses IS 'سجل المصاريف';


-- ============================================
-- ★★★ نظام خدمة العملاء CRM ★★★
-- ============================================

-- 18. تتبع حالة الطلب (Order Status History)
-- ============================================
CREATE TABLE IF NOT EXISTS order_status_history (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    status VARCHAR(50) NOT NULL,
    notes TEXT NULL,
    changed_by VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_osh_order ON order_status_history(order_id);

COMMENT ON TABLE order_status_history IS 'سجل تتبع حالات الطلب';


-- ============================================
-- 19. تذاكر الدعم الفني (Support Tickets)
-- ============================================
CREATE TABLE IF NOT EXISTS support_tickets (
    id SERIAL PRIMARY KEY,
    ticket_number VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT NOT NULL REFERENCES customers(id) ON DELETE RESTRICT,
    order_id INT NULL REFERENCES orders(id) ON DELETE SET NULL,
    subject VARCHAR(200) NOT NULL,
    category ticket_category_type DEFAULT 'general',
    priority ticket_priority_type DEFAULT 'medium',
    status ticket_status_type DEFAULT 'open',
    assigned_to VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL
);

CREATE INDEX idx_tickets_customer ON support_tickets(customer_id);
CREATE INDEX idx_tickets_status ON support_tickets(status);
CREATE INDEX idx_tickets_priority ON support_tickets(priority);

COMMENT ON TABLE support_tickets IS 'تذاكر الدعم الفني';


-- ============================================
-- 20. ردود التذاكر (Ticket Replies)
-- ============================================
CREATE TABLE IF NOT EXISTS ticket_replies (
    id SERIAL PRIMARY KEY,
    ticket_id INT NOT NULL REFERENCES support_tickets(id) ON DELETE CASCADE,
    sender_type sender_type NOT NULL,
    sender_name VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    attachment VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tr_ticket ON ticket_replies(ticket_id);

COMMENT ON TABLE ticket_replies IS 'ردود تذاكر الدعم';


-- ============================================
-- 21. جدول المشرفين (Admins)
-- ============================================
CREATE TABLE IF NOT EXISTS admins (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role admin_role_type DEFAULT 'admin',
    is_active SMALLINT DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE admins IS 'جدول المشرفين والموظفين';


-- ============================================
-- ★★★ دالة تحديث updated_at تلقائياً ★★★
-- PostgreSQL Trigger for auto-updating updated_at
-- ============================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- تطبيق الدالة على جميع الجداول التي تحتوي updated_at
DO $$
DECLARE
    tbl TEXT;
BEGIN
    FOR tbl IN SELECT unnest(ARRAY[
        'categories', 'products', 'inventory', 'customers',
        'orders', 'cart', 'journal_entries', 'invoices',
        'support_tickets', 'admins'
    ]) LOOP
        EXECUTE format('
            DROP TRIGGER IF EXISTS trigger_update_%I ON %I;
            CREATE TRIGGER trigger_update_%I
                BEFORE UPDATE ON %I
                FOR EACH ROW
                EXECUTE FUNCTION update_updated_at_column();
        ', tbl, tbl, tbl, tbl);
    END LOOP;
END $$;


-- ============================================
-- ★★★ إدخال البيانات الأولية ★★★
-- ============================================

-- إدخال الألوان الأساسية
INSERT INTO colors (name, hex_code) VALUES
    ('أبيض', '#FFFFFF'),
    ('أسود', '#000000'),
    ('أحمر', '#FF0000'),
    ('أزرق', '#0000FF'),
    ('أخضر', '#008000'),
    ('رمادي', '#808080'),
    ('بيج', '#F5F5DC'),
    ('كحلي', '#000080'),
    ('بني', '#8B4513'),
    ('وردي', '#FFC0CB')
ON CONFLICT DO NOTHING;

-- إدخال الأحجام الأساسية
INSERT INTO sizes (name, description, sort_order) VALUES
    ('XS', 'صغير جداً - Extra Small', 1),
    ('S', 'صغير - Small', 2),
    ('M', 'متوسط - Medium', 3),
    ('L', 'كبير - Large', 4),
    ('XL', 'كبير جداً - Extra Large', 5),
    ('XXL', 'كبير جداً جداً - Double XL', 6),
    ('3XL', 'ثلاثي الكبر - Triple XL', 7)
ON CONFLICT DO NOTHING;

-- إدخال التصنيفات الرئيسية
INSERT INTO categories (name, slug, description) VALUES
    ('ملابس رجالية', 'mens-clothing', 'تشكيلة واسعة من الملابس الرجالية'),
    ('ملابس نسائية', 'womens-clothing', 'أحدث صيحات الموضة النسائية'),
    ('ملابس أطفال', 'kids-clothing', 'ملابس مريحة وعصرية للأطفال'),
    ('إكسسوارات', 'accessories', 'إكسسوارات وحقائب ومستلزمات')
ON CONFLICT (slug) DO NOTHING;

-- التصنيفات الفرعية
INSERT INTO categories (name, slug, description, parent_id) VALUES
    ('قمصان رجالية', 'mens-shirts', 'قمصان رسمية وكاجوال', 1),
    ('بناطيل رجالية', 'mens-pants', 'بناطيل جينز وقماش', 1),
    ('فساتين', 'dresses', 'فساتين سهرة وكاجوال', 2),
    ('بلوزات', 'blouses', 'بلوزات متنوعة', 2),
    ('ملابس أولاد', 'boys-clothing', 'ملابس للأولاد', 3),
    ('ملابس بنات', 'girls-clothing', 'ملابس للبنات', 3)
ON CONFLICT (slug) DO NOTHING;


-- ============================================
-- شجرة الحسابات المحاسبية (Chart of Accounts)
-- ============================================

-- الحسابات الرئيسية - الأصول (Assets)
INSERT INTO accounts (account_code, name, type, parent_id, description) VALUES
    ('1000', 'الأصول', 'asset', NULL, 'الحساب الرئيسي للأصول'),
    ('1100', 'النقدية والبنوك', 'asset', 1, 'الحسابات النقدية والبنكية'),
    ('1110', 'الصندوق (النقدية)', 'asset', 2, 'النقدية في الصندوق'),
    ('1120', 'البنك', 'asset', 2, 'الحساب البنكي'),
    ('1200', 'المدينون (الذمم المدينة)', 'asset', 1, 'المبالغ المستحقة من العملاء'),
    ('1300', 'المخزون', 'asset', 1, 'مخزون البضائع')
ON CONFLICT (account_code) DO NOTHING;

-- الخصوم (Liabilities)
INSERT INTO accounts (account_code, name, type, parent_id, description) VALUES
    ('2000', 'الخصوم', 'liability', NULL, 'الحساب الرئيسي للخصوم'),
    ('2100', 'الدائنون (الذمم الدائنة)', 'liability', 7, 'المبالغ المستحقة للموردين'),
    ('2200', 'ضريبة القيمة المضافة المستحقة', 'liability', 7, 'ضريبة VAT المستحقة')
ON CONFLICT (account_code) DO NOTHING;

-- حقوق الملكية (Equity)
INSERT INTO accounts (account_code, name, type, parent_id, description) VALUES
    ('3000', 'حقوق الملكية', 'equity', NULL, 'رأس المال وحقوق الملكية'),
    ('3100', 'رأس المال', 'equity', 10, 'رأس المال المستثمر'),
    ('3200', 'الأرباح المحتجزة', 'equity', 10, 'الأرباح المتراكمة')
ON CONFLICT (account_code) DO NOTHING;

-- الإيرادات (Revenue)
INSERT INTO accounts (account_code, name, type, parent_id, description) VALUES
    ('4000', 'الإيرادات', 'revenue', NULL, 'الحساب الرئيسي للإيرادات'),
    ('4100', 'إيرادات المبيعات', 'revenue', 13, 'إيرادات بيع الملابس'),
    ('4200', 'إيرادات الشحن', 'revenue', 13, 'إيرادات رسوم الشحن'),
    ('4900', 'خصومات المبيعات', 'revenue', 13, 'الخصومات والتخفيضات الممنوحة')
ON CONFLICT (account_code) DO NOTHING;

-- المصاريف (Expenses)
INSERT INTO accounts (account_code, name, type, parent_id, description) VALUES
    ('5000', 'المصاريف', 'expense', NULL, 'الحساب الرئيسي للمصاريف'),
    ('5100', 'تكلفة البضاعة المباعة', 'expense', 17, 'تكلفة المنتجات المباعة'),
    ('5200', 'مصاريف الشحن والتوصيل', 'expense', 17, 'تكاليف شحن الطلبات'),
    ('5300', 'مصاريف التسويق والإعلان', 'expense', 17, 'تكاليف التسويق'),
    ('5400', 'مصاريف الرواتب', 'expense', 17, 'رواتب الموظفين'),
    ('5500', 'مصاريف إدارية وعمومية', 'expense', 17, 'مصاريف التشغيل العامة'),
    ('5600', 'مصاريف الإيجار', 'expense', 17, 'إيجار المستودعات والمكاتب')
ON CONFLICT (account_code) DO NOTHING;

-- إدخال مشرف افتراضي (كلمة المرور: admin123)
INSERT INTO admins (name, email, password_hash, role) VALUES
    ('مدير النظام', 'admin@store.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin')
ON CONFLICT (email) DO NOTHING;


-- ============================================
-- نهاية مخطط قاعدة البيانات - PostgreSQL
-- ============================================
