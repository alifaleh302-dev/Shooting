<?php
/**
 * ============================================
 * نموذج الطلبات - متوافق مع PostgreSQL
 * Order Model - PostgreSQL Compatible
 * ============================================
 * التغييرات الرئيسية:
 * - NOW() → CURRENT_TIMESTAMP
 * - CURDATE() → CURRENT_DATE
 * - MONTH()/YEAR() → EXTRACT(MONTH FROM) / EXTRACT(YEAR FROM)
 * - DATE() → CAST(... AS DATE) أو ::date
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Order extends Model
{
    protected string $table = 'orders';
    
    protected array $fillable = [
        'customer_id', 'order_number', 'status', 'subtotal',
        'discount_amount', 'vat_amount', 'shipping_cost', 'total_amount',
        'shipping_name', 'shipping_phone', 'shipping_address', 'shipping_city',
        'shipping_method', 'tracking_number', 'payment_method', 'payment_status',
        'coupon_code', 'customer_notes', 'admin_notes'
    ];

    /**
     * إنشاء طلب جديد مع عناصره
     * Create a new order with its items
     */
    public function createOrder(array $orderData, array $items): array
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // 1. توليد رقم الطلب الفريد
            $orderData['order_number'] = $this->generateOrderNumber();
            
            // 2. حساب المبالغ
            $subtotal = 0;
            foreach ($items as &$item) {
                $item['total_price'] = $item['unit_price'] * $item['quantity'];
                $subtotal += $item['total_price'];
            }
            
            $config = require __DIR__ . '/../../config/app.php';
            $vatRate = $config['vat_rate'];
            $vatAmount = round($subtotal * ($vatRate / 100), 2);
            
            $orderData['subtotal'] = $subtotal;
            $orderData['vat_amount'] = $vatAmount;
            $orderData['total_amount'] = $subtotal + $vatAmount + ($orderData['shipping_cost'] ?? 0) - ($orderData['discount_amount'] ?? 0);

            // 3. إنشاء الطلب
            $orderId = $this->create($orderData);

            // 4. إضافة عناصر الطلب وتحديث المخزون
            foreach ($items as $item) {
                // إضافة عنصر الطلب
                $sql = "INSERT INTO order_items (order_id, product_id, inventory_id, product_name, color_name, size_name, quantity, unit_price, total_price) 
                        VALUES (:order_id, :product_id, :inventory_id, :product_name, :color_name, :size_name, :quantity, :unit_price, :total_price)";
                $db->query($sql, [
                    'order_id'     => $orderId,
                    'product_id'   => $item['product_id'],
                    'inventory_id' => $item['inventory_id'],
                    'product_name' => $item['product_name'],
                    'color_name'   => $item['color_name'] ?? null,
                    'size_name'    => $item['size_name'] ?? null,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'total_price'  => $item['total_price']
                ]);

                // تحديث المخزون (خصم الكمية)
                $sql = "UPDATE inventory SET quantity = quantity - :qty WHERE id = :inv_id AND quantity >= :qty2";
                $result = $db->query($sql, [
                    'qty'    => $item['quantity'],
                    'inv_id' => $item['inventory_id'],
                    'qty2'   => $item['quantity']
                ]);

                if ($result->rowCount() === 0) {
                    throw new \Exception("الكمية غير متوفرة للمنتج: {$item['product_name']}");
                }

                // تحديث إجمالي المبيعات للمنتج
                $db->query("UPDATE products SET total_sold = total_sold + :qty WHERE id = :pid", [
                    'qty' => $item['quantity'],
                    'pid' => $item['product_id']
                ]);
            }

            // 5. إنشاء سجل حالة الطلب
            $this->addStatusHistory($orderId, 'pending', 'تم إنشاء الطلب', 'النظام');

            // 6. إنشاء الفاتورة تلقائياً
            $invoiceModel = new Invoice();
            $invoiceId = $invoiceModel->createFromOrder($orderId, $orderData);

            // 7. إنشاء القيد المحاسبي تلقائياً
            $accountingModel = new JournalEntry();
            $accountingModel->createSalesEntry($orderId, $orderData);

            // 8. تحديث إحصائيات العميل
            $customerModel = new Customer();
            $customerModel->updateOrderStats($orderData['customer_id'], $orderData['total_amount']);
            $points = floor($orderData['total_amount'] / 10);
            $customerModel->updateLoyaltyPoints($orderData['customer_id'], $points);

            $db->commit();

            return [
                'order_id'     => $orderId,
                'order_number' => $orderData['order_number'],
                'invoice_id'   => $invoiceId,
                'total_amount' => $orderData['total_amount']
            ];

        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * توليد رقم طلب فريد
     */
    private function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * تحديث حالة الطلب مع التسجيل في السجل
     */
    public function updateStatus(int $orderId, string $status, string $notes = '', string $changedBy = 'النظام'): bool
    {
        // PostgreSQL: تحديث ENUM يتطلب cast صريح
        $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $result = $this->db->query($sql, ['status' => $status, 'id' => $orderId])->rowCount() > 0;
        
        if ($result) {
            $this->addStatusHistory($orderId, $status, $notes, $changedBy);
            
            if ($status === 'delivered') {
                $sql = "UPDATE {$this->table} SET payment_status = 'paid', paid_at = CURRENT_TIMESTAMP WHERE id = :id";
                $this->db->query($sql, ['id' => $orderId]);
            }
        }

        return $result;
    }

    /**
     * إضافة سجل حالة
     */
    private function addStatusHistory(int $orderId, string $status, string $notes, string $changedBy): void
    {
        $sql = "INSERT INTO order_status_history (order_id, status, notes, changed_by) VALUES (:order_id, :status, :notes, :changed_by)";
        $this->db->query($sql, [
            'order_id'   => $orderId,
            'status'     => $status,
            'notes'      => $notes,
            'changed_by' => $changedBy
        ]);
    }

    /**
     * جلب الطلب مع تفاصيله الكاملة
     */
    public function getFullDetails(int $orderId): array|false
    {
        $sql = "SELECT o.*, 
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    c.email as customer_email, c.phone as customer_phone
                FROM {$this->table} o
                JOIN customers c ON o.customer_id = c.id
                WHERE o.id = :id";
        $order = $this->db->fetchOne($sql, ['id' => $orderId]);
        if (!$order) return false;

        $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
        $order['items'] = $this->db->fetchAll($sql, ['order_id' => $orderId]);

        $sql = "SELECT * FROM order_status_history WHERE order_id = :order_id ORDER BY created_at DESC";
        $order['status_history'] = $this->db->fetchAll($sql, ['order_id' => $orderId]);

        $sql = "SELECT * FROM invoices WHERE order_id = :order_id LIMIT 1";
        $order['invoice'] = $this->db->fetchOne($sql, ['order_id' => $orderId]);

        return $order;
    }

    /**
     * جلب طلبات عميل معين
     */
    public function getByCustomer(int $customerId, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE customer_id = :cid";
        $total = $this->db->fetchOne($countSql, ['cid' => $customerId])['total'];

        $sql = "SELECT * FROM {$this->table} 
                WHERE customer_id = :cid 
                ORDER BY created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        return [
            'data'        => $this->db->fetchAll($sql, ['cid' => $customerId]),
            'total'       => (int)$total,
            'page'        => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * إحصائيات الطلبات (لوحة التحكم)
     * PostgreSQL: CURRENT_DATE بدلاً من CURDATE(), EXTRACT بدلاً من MONTH()/YEAR()
     */
    public function getDashboardStats(): array
    {
        $stats = [];

        // إجمالي الطلبات اليوم - PostgreSQL: created_at::date = CURRENT_DATE
        $stats['today_orders'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue 
             FROM {$this->table} WHERE created_at::date = CURRENT_DATE"
        );

        // إجمالي هذا الشهر - PostgreSQL: EXTRACT
        $stats['month_orders'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue 
             FROM {$this->table} 
             WHERE EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE) 
             AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)"
        );

        // الطلبات حسب الحالة
        $stats['by_status'] = $this->db->fetchAll(
            "SELECT status::text, COUNT(*) as count FROM {$this->table} GROUP BY status"
        );

        // أكثر المنتجات مبيعاً
        $stats['top_products'] = $this->db->fetchAll(
            "SELECT p.name, SUM(oi.quantity) as total_qty, SUM(oi.total_price) as total_revenue
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             GROUP BY p.id, p.name
             ORDER BY total_qty DESC
             LIMIT 10"
        );

        return $stats;
    }
}
