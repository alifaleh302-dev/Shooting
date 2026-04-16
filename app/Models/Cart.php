<?php
/**
 * ============================================
 * نموذج سلة التسوق
 * Cart Model
 * ============================================
 */

namespace App\Models;

use App\Core\Model;

class Cart extends Model
{
    protected string $table = 'cart';
    
    protected array $fillable = ['customer_id', 'session_id', 'inventory_id', 'quantity'];

    /**
     * إضافة منتج إلى السلة
     * Add item to cart
     */
    public function addItem(int $inventoryId, int $quantity, ?int $customerId = null, ?string $sessionId = null): bool
    {
        // التحقق من توفر المخزون
        $inventory = $this->db->fetchOne(
            "SELECT quantity FROM inventory WHERE id = :id",
            ['id' => $inventoryId]
        );
        if (!$inventory || $inventory['quantity'] < $quantity) return false;

        // التحقق من وجود المنتج بالسلة مسبقاً
        $condition = $customerId 
            ? "customer_id = :owner AND inventory_id = :inv_id"
            : "session_id = :owner AND inventory_id = :inv_id";
        $owner = $customerId ?: $sessionId;

        $existing = $this->db->fetchOne(
            "SELECT id, quantity FROM {$this->table} WHERE {$condition}",
            ['owner' => $owner, 'inv_id' => $inventoryId]
        );

        if ($existing) {
            // تحديث الكمية
            $newQty = $existing['quantity'] + $quantity;
            if ($newQty > $inventory['quantity']) return false;
            
            $sql = "UPDATE {$this->table} SET quantity = :qty WHERE id = :id";
            return $this->db->query($sql, ['qty' => $newQty, 'id' => $existing['id']])->rowCount() > 0;
        }

        // إضافة عنصر جديد
        return $this->create([
            'customer_id'  => $customerId,
            'session_id'   => $sessionId,
            'inventory_id' => $inventoryId,
            'quantity'     => $quantity
        ]) > 0;
    }

    /**
     * جلب محتويات السلة مع تفاصيل المنتجات
     * Get cart contents with product details
     */
    public function getContents(?int $customerId = null, ?string $sessionId = null): array
    {
        $condition = $customerId 
            ? "c.customer_id = :owner" 
            : "c.session_id = :owner";
        $owner = $customerId ?: $sessionId;

        $sql = "SELECT c.id as cart_id, c.quantity, c.inventory_id,
                    p.id as product_id, p.name as product_name, p.main_image,
                    p.base_price, p.sale_price,
                    cl.name as color_name, cl.hex_code,
                    s.name as size_name,
                    i.additional_price, i.quantity as stock_available,
                    (COALESCE(p.sale_price, p.base_price) + i.additional_price) * c.quantity as line_total
                FROM {$this->table} c
                JOIN inventory i ON c.inventory_id = i.id
                JOIN products p ON i.product_id = p.id
                JOIN colors cl ON i.color_id = cl.id
                JOIN sizes s ON i.size_id = s.id
                WHERE {$condition}
                ORDER BY c.created_at DESC";

        $items = $this->db->fetchAll($sql, ['owner' => $owner]);

        // حساب المجموع
        $subtotal = array_sum(array_column($items, 'line_total'));

        return [
            'items'    => $items,
            'count'    => count($items),
            'subtotal' => round($subtotal, 2)
        ];
    }

    /**
     * تحديث كمية عنصر في السلة
     * Update cart item quantity
     */
    public function updateQuantity(int $cartId, int $quantity): bool
    {
        if ($quantity <= 0) return $this->delete($cartId);

        $sql = "UPDATE {$this->table} SET quantity = :qty WHERE id = :id";
        return $this->db->query($sql, ['qty' => $quantity, 'id' => $cartId])->rowCount() > 0;
    }

    /**
     * تفريغ السلة بالكامل
     * Clear entire cart
     */
    public function clearCart(?int $customerId = null, ?string $sessionId = null): bool
    {
        $condition = $customerId 
            ? "customer_id = :owner" 
            : "session_id = :owner";
        $owner = $customerId ?: $sessionId;

        $sql = "DELETE FROM {$this->table} WHERE {$condition}";
        return $this->db->query($sql, ['owner' => $owner])->rowCount() >= 0;
    }

    /**
     * نقل سلة الزائر إلى حساب العميل بعد التسجيل
     * Merge guest cart into customer account
     */
    public function mergeGuestCart(string $sessionId, int $customerId): void
    {
        $sql = "UPDATE {$this->table} SET customer_id = :cid, session_id = NULL WHERE session_id = :sid";
        $this->db->query($sql, ['cid' => $customerId, 'sid' => $sessionId]);
    }
}
