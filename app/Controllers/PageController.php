<?php
/**
 * ============================================
 * متحكم الصفحات (عرض واجهات HTML)
 * Page Controller - Renders HTML Views
 * ============================================
 * متوافق مع Render.com - مسارات نسبية
 */

namespace App\Controllers;

use App\Core\Controller;

class PageController extends Controller
{
    /**
     * الصفحة الرئيسية
     */
    public function home(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->render('customer/home', [
            'title' => 'الرئيسية - متجر الأزياء'
        ]);
    }

    /**
     * صفحة المتجر
     */
    public function shop(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->render('customer/shop', [
            'title' => 'المتجر - تسوق الآن'
        ]);
    }

    /**
     * صفحة تفاصيل المنتج
     */
    public function productDetail(string $id): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->render('customer/product-detail', [
            'title' => 'تفاصيل المنتج',
            'product_id' => $id
        ]);
    }

    /**
     * صفحة سلة التسوق
     */
    public function cartPage(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->render('customer/cart', [
            'title' => 'سلة التسوق'
        ]);
    }

    /**
     * صفحة إتمام الشراء
     */
    public function checkout(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->render('customer/checkout', [
            'title' => 'إتمام الشراء'
        ]);
    }

    // ===================== لوحة التحكم (Admin) =====================

    public function adminDashboard(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->render('admin/dashboard', ['title' => 'لوحة التحكم - المشرف']);
    }

    public function adminProducts(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->render('admin/products', ['title' => 'إدارة المنتجات']);
    }

    public function adminOrders(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->render('admin/orders', ['title' => 'إدارة الطلبات']);
    }

    public function adminAccounting(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->render('admin/accounting', ['title' => 'النظام المحاسبي']);
    }

    public function adminCRM(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->render('admin/crm', ['title' => 'خدمة العملاء - CRM']);
    }
}
