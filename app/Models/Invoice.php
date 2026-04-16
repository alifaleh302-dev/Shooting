<?php
/**
 * ============================================
 * نموذج الفواتير - متوافق مع PostgreSQL
 * Invoice Model - PostgreSQL Compatible
 * ============================================
 */

namespace App\Models;

use App\Core\Model;

class Invoice extends Model
{
    protected string $table = 'invoices';
    
    protected array $fillable = [
        'order_id', 'invoice_number', 'customer_id', 'subtotal',
        'discount_amount', 'vat_rate', 'vat_amount', 'total_amount',
        'status', 'due_date', 'notes'
    ];

    /**
     * إنشاء فاتورة تلقائياً من الطلب
     */
    public function createFromOrder(int $orderId, array $orderData): int
    {
        $invoiceData = [
            'order_id'        => $orderId,
            'invoice_number'  => $this->generateInvoiceNumber(),
            'customer_id'     => $orderData['customer_id'],
            'subtotal'        => $orderData['subtotal'],
            'discount_amount' => $orderData['discount_amount'] ?? 0,
            'vat_rate'        => 15.00,
            'vat_amount'      => $orderData['vat_amount'],
            'total_amount'    => $orderData['total_amount'],
            'status'          => 'sent',
            'due_date'        => date('Y-m-d', strtotime('+30 days'))
        ];

        return $this->create($invoiceData);
    }

    /**
     * توليد رقم فاتورة فريد
     */
    private function generateInvoiceNumber(): string
    {
        return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }

    /**
     * جلب الفاتورة مع تفاصيل الطلب والعميل
     */
    public function getFullInvoice(int $invoiceId): array|false
    {
        $sql = "SELECT inv.*, 
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    c.email as customer_email, c.phone as customer_phone,
                    o.order_number, o.shipping_address, o.shipping_city
                FROM {$this->table} inv
                JOIN customers c ON inv.customer_id = c.id
                JOIN orders o ON inv.order_id = o.id
                WHERE inv.id = :id";
        $invoice = $this->db->fetchOne($sql, ['id' => $invoiceId]);

        if (!$invoice) return false;

        $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
        $invoice['items'] = $this->db->fetchAll($sql, ['order_id' => $invoice['order_id']]);

        return $invoice;
    }

    /**
     * تقرير المبيعات حسب الفترة
     * PostgreSQL: created_at::date بدلاً من DATE(created_at)
     */
    public function salesReport(string $startDate, string $endDate): array
    {
        $sql = "SELECT 
                    created_at::date as date,
                    COUNT(*) as invoice_count,
                    SUM(subtotal) as total_subtotal,
                    SUM(discount_amount) as total_discounts,
                    SUM(vat_amount) as total_vat,
                    SUM(total_amount) as total_revenue
                FROM {$this->table} 
                WHERE status::text != 'cancelled' 
                AND created_at::date BETWEEN :start_date AND :end_date
                GROUP BY created_at::date
                ORDER BY date DESC";

        return $this->db->fetchAll($sql, [
            'start_date' => $startDate,
            'end_date'   => $endDate
        ]);
    }

    /**
     * تقرير الأرباح
     */
    public function profitReport(string $startDate, string $endDate): array
    {
        // إجمالي الإيرادات
        $revenue = $this->db->fetchOne(
            "SELECT COALESCE(SUM(total_amount), 0) as total 
             FROM {$this->table} 
             WHERE status::text = 'paid' AND created_at::date BETWEEN :s AND :e",
            ['s' => $startDate, 'e' => $endDate]
        );

        // تكلفة البضاعة المباعة
        $cogs = $this->db->fetchOne(
            "SELECT COALESCE(SUM(p.cost_price * oi.quantity), 0) as total
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             JOIN orders o ON oi.order_id = o.id
             WHERE o.payment_status::text = 'paid' AND o.created_at::date BETWEEN :s AND :e",
            ['s' => $startDate, 'e' => $endDate]
        );

        // المصاريف
        $expenses = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM expenses 
             WHERE expense_date BETWEEN :s AND :e",
            ['s' => $startDate, 'e' => $endDate]
        );

        return [
            'period'         => ['from' => $startDate, 'to' => $endDate],
            'total_revenue'  => (float)$revenue['total'],
            'cost_of_goods'  => (float)$cogs['total'],
            'gross_profit'   => (float)$revenue['total'] - (float)$cogs['total'],
            'total_expenses' => (float)$expenses['total'],
            'net_profit'     => (float)$revenue['total'] - (float)$cogs['total'] - (float)$expenses['total']
        ];
    }
}
