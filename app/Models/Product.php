<?php
/**
 * ============================================
 * نموذج المنتجات - متوافق مع PostgreSQL
 * Product Model - PostgreSQL Compatible
 * ============================================
 */

namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected string $table = 'products';
    
    protected array $fillable = [
        'category_id', 'name', 'slug', 'description', 'short_description',
        'base_price', 'sale_price', 'cost_price', 'sku', 'brand',
        'material', 'main_image', 'is_active', 'is_featured'
    ];

    /**
     * جلب المنتجات مع معلومات التصنيف
     * Get products with category information
     */
    public function getWithCategory(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE is_active = 1 AND deleted_at IS NULL";
        $total = $this->db->fetchOne($countSql)['total'];

        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'        => $stmt->fetchAll(),
            'total'       => (int)$total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * جلب منتج مع تفاصيل المخزون (الألوان والأحجام)
     * Get product with inventory details
     */
    public function getWithInventory(int $id): array|false
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        $product = $this->db->fetchOne($sql, ['id' => $id]);

        if (!$product) return false;

        // جلب المتغيرات (المخزون)
        $sql = "SELECT i.*, cl.name as color_name, cl.hex_code, s.name as size_name 
                FROM inventory i 
                JOIN colors cl ON i.color_id = cl.id 
                JOIN sizes s ON i.size_id = s.id 
                WHERE i.product_id = :product_id 
                ORDER BY s.sort_order, cl.name";
        $product['variants'] = $this->db->fetchAll($sql, ['product_id' => $id]);

        // جلب الصور الإضافية
        $sql = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY sort_order";
        $product['images'] = $this->db->fetchAll($sql, ['product_id' => $id]);

        return $product;
    }

    /**
     * البحث في المنتجات - يستخدم ILIKE بدلاً من LIKE (PostgreSQL: بحث غير حساس لحالة الأحرف)
     * Search products - uses ILIKE for case-insensitive search
     */
    public function search(string $keyword, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%{$keyword}%";

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     WHERE is_active = 1 AND deleted_at IS NULL 
                     AND (name ILIKE :keyword OR description ILIKE :keyword2 OR sku ILIKE :keyword3)";
        $total = $this->db->fetchOne($countSql, [
            'keyword' => $searchTerm,
            'keyword2' => $searchTerm,
            'keyword3' => $searchTerm
        ])['total'];

        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 AND p.deleted_at IS NULL 
                AND (p.name ILIKE :keyword OR p.description ILIKE :keyword2 OR p.sku ILIKE :keyword3)
                ORDER BY p.created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        return [
            'data'  => $this->db->fetchAll($sql, [
                'keyword' => $searchTerm,
                'keyword2' => $searchTerm,
                'keyword3' => $searchTerm
            ]),
            'total'       => (int)$total,
            'page'        => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * جلب المنتجات المميزة
     * Get featured products
     */
    public function getFeatured(int $limit = 8): array
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 AND p.is_featured = 1 AND p.deleted_at IS NULL 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * جلب منتجات حسب التصنيف
     * Get products by category
     */
    public function getByCategory(int $categoryId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     WHERE category_id = :cat_id AND is_active = 1 AND deleted_at IS NULL";
        $total = $this->db->fetchOne($countSql, ['cat_id' => $categoryId])['total'];

        $sql = "SELECT * FROM {$this->table} 
                WHERE category_id = :cat_id AND is_active = 1 AND deleted_at IS NULL 
                ORDER BY created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        return [
            'data'  => $this->db->fetchAll($sql, ['cat_id' => $categoryId]),
            'total'       => (int)$total,
            'page'        => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }
}

    /**
     * جلب جميع التصنيفات النشطة
     * Get all active categories
     */
    public function getCategories(): array
    {
        $sql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }
