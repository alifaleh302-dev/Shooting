<?php
/**
 * ============================================
 * نموذج العملاء - متوافق مع PostgreSQL
 * Customer Model - PostgreSQL Compatible
 * ============================================
 */

namespace App\Models;

use App\Core\Model;

class Customer extends Model
{
    protected string $table = 'customers';
    
    protected array $fillable = [
        'first_name', 'last_name', 'email', 'password_hash',
        'phone', 'gender', 'date_of_birth', 'avatar', 'is_active'
    ];

    /**
     * تسجيل عميل جديد
     */
    public function register(array $data): int|false
    {
        $existing = $this->findWhere('email', $data['email']);
        if ($existing) return false;

        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);

        return $this->create($data);
    }

    /**
     * تسجيل الدخول
     */
    public function login(string $email, string $password): array|false
    {
        $customer = $this->findWhere('email', $email);
        
        if (!$customer || !$customer['is_active']) return false;
        if (!password_verify($password, $customer['password_hash'])) return false;

        // تحديث آخر تسجيل دخول - CURRENT_TIMESTAMP بدلاً من NOW()
        $this->db->query(
            "UPDATE {$this->table} SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id",
            ['id' => $customer['id']]
        );

        unset($customer['password_hash']);
        return $customer;
    }

    /**
     * جلب سجل مشتريات العميل
     */
    public function getPurchaseHistory(int $customerId): array
    {
        $sql = "SELECT o.*, 
                    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
                FROM orders o 
                WHERE o.customer_id = :customer_id 
                ORDER BY o.created_at DESC";
        return $this->db->fetchAll($sql, ['customer_id' => $customerId]);
    }

    /**
     * جلب إحصائيات العميل
     */
    public function getStats(int $customerId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_spent,
                    AVG(total_amount) as avg_order_value,
                    MAX(created_at) as last_order_date
                FROM orders 
                WHERE customer_id = :customer_id AND status != 'cancelled'";
        
        $stats = $this->db->fetchOne($sql, ['customer_id' => $customerId]);
        
        $customer = $this->find($customerId);
        $stats['loyalty_points'] = $customer['loyalty_points'] ?? 0;

        return $stats;
    }

    /**
     * تحديث نقاط الولاء
     */
    public function updateLoyaltyPoints(int $customerId, int $points): bool
    {
        $sql = "UPDATE {$this->table} SET loyalty_points = loyalty_points + :points WHERE id = :id";
        return $this->db->query($sql, ['points' => $points, 'id' => $customerId])->rowCount() > 0;
    }

    /**
     * تحديث إحصائيات العميل بعد طلب جديد
     */
    public function updateOrderStats(int $customerId, float $amount): void
    {
        $sql = "UPDATE {$this->table} 
                SET total_orders = total_orders + 1, 
                    total_spent = total_spent + :amount 
                WHERE id = :id";
        $this->db->query($sql, ['amount' => $amount, 'id' => $customerId]);
    }
}
