<?php
/**
 * ============================================
 * متحكم المنتجات
 * Product Controller - API Endpoints
 * ============================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    private Product $product;

    public function __construct()
    {
        parent::__construct();
        $this->product = new Product();
    }

    /**
     * جلب جميع المنتجات مع ترقيم
     * GET /api/products
     */
    public function index(): void
    {
        $pagination = $this->getPagination();
        $data = $this->product->getWithCategory($pagination['page'], $pagination['per_page']);
        $this->jsonSuccess($data, 'تم جلب المنتجات بنجاح');
    }

    /**
     * جلب منتج واحد مع تفاصيل المخزون
     * GET /api/products/{id}
     */
    public function show(string $id): void
    {
        $product = $this->product->getWithInventory((int)$id);
        
        if (!$product) {
            $this->jsonError('المنتج غير موجود', 404);
        }

        $this->jsonSuccess($product, 'تم جلب المنتج بنجاح');
    }

    /**
     * إنشاء منتج جديد
     * POST /api/products
     */
    public function store(): void
    {
        $data = $this->getRequestData();
        
        // التحقق من الحقول المطلوبة
        $errors = $this->validateRequired($data, ['name', 'category_id', 'base_price', 'sku', 'cost_price']);
        if (!empty($errors)) {
            $this->jsonError('بيانات غير مكتملة', 422, $errors);
        }

        // إنشاء الرابط المختصر تلقائياً
        $data['slug'] = $this->generateSlug($data['name']);

        try {
            $productId = $this->product->create($data);
            $product = $this->product->find($productId);
            $this->jsonSuccess($product, 'تم إنشاء المنتج بنجاح', 201);
        } catch (\Exception $e) {
            $this->jsonError('فشل إنشاء المنتج: ' . $e->getMessage(), 500);
        }
    }

    /**
     * تحديث منتج
     * PUT /api/products/{id}
     */
    public function update(string $id): void
    {
        $data = $this->getRequestData();
        
        $existing = $this->product->find((int)$id);
        if (!$existing) {
            $this->jsonError('المنتج غير موجود', 404);
        }

        try {
            $this->product->update((int)$id, $data);
            $product = $this->product->find((int)$id);
            $this->jsonSuccess($product, 'تم تحديث المنتج بنجاح');
        } catch (\Exception $e) {
            $this->jsonError('فشل تحديث المنتج: ' . $e->getMessage(), 500);
        }
    }

    /**
     * حذف منتج (حذف ناعم)
     * DELETE /api/products/{id}
     */
    public function destroy(string $id): void
    {
        $existing = $this->product->find((int)$id);
        if (!$existing) {
            $this->jsonError('المنتج غير موجود', 404);
        }

        $this->product->softDelete((int)$id);
        $this->jsonSuccess(null, 'تم حذف المنتج بنجاح');
    }

    /**
     * البحث في المنتجات
     * GET /api/products/search?q=keyword
     */
    public function search(): void
    {
        $keyword = $_GET['q'] ?? '';
        if (empty($keyword)) {
            $this->jsonError('يرجى إدخال كلمة البحث', 400);
        }

        $pagination = $this->getPagination();
        $data = $this->product->search($keyword, $pagination['page'], $pagination['per_page']);
        $this->jsonSuccess($data, 'نتائج البحث');
    }

    /**
     * المنتجات المميزة
     * GET /api/products/featured
     */
    public function featured(): void
    {
        $limit = (int)($_GET['limit'] ?? 8);
        $data = $this->product->getFeatured($limit);
        $this->jsonSuccess($data, 'المنتجات المميزة');
    }

    /**
     * منتجات حسب التصنيف
     * GET /api/products/category/{id}
     */
    public function byCategory(string $id): void
    {
        $pagination = $this->getPagination();
        $data = $this->product->getByCategory((int)$id, $pagination['page'], $pagination['per_page']);
        $this->jsonSuccess($data, 'منتجات التصنيف');
    }

    /**
     * توليد رابط مختصر من الاسم
     */
    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9\-\s]/', '', $slug);
        $slug = preg_replace('/[\s\-]+/', '-', $slug);
        return $slug . '-' . substr(uniqid(), -4);
    }
}
