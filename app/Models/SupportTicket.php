<?php
/**
 * ============================================
 * نموذج تذاكر الدعم الفني - متوافق مع PostgreSQL
 * Support Ticket Model - PostgreSQL Compatible
 * ============================================
 * التغييرات: FIELD() → ORDER BY CASE WHEN, NOW() → CURRENT_TIMESTAMP
 * TIMESTAMPDIFF → EXTRACT(EPOCH FROM ...)
 */

namespace App\Models;

use App\Core\Model;

class SupportTicket extends Model
{
    protected string $table = 'support_tickets';
    
    protected array $fillable = [
        'ticket_number', 'customer_id', 'order_id', 'subject',
        'category', 'priority', 'status', 'assigned_to'
    ];

    /**
     * إنشاء تذكرة دعم جديدة
     */
    public function createTicket(array $data, string $initialMessage): array
    {
        $data['ticket_number'] = $this->generateTicketNumber();
        $ticketId = $this->create($data);

        $this->addReply($ticketId, 'customer', $data['customer_name'] ?? 'عميل', $initialMessage);

        return [
            'ticket_id'     => $ticketId,
            'ticket_number' => $data['ticket_number']
        ];
    }

    /**
     * إضافة رد على التذكرة
     */
    public function addReply(int $ticketId, string $senderType, string $senderName, string $message, ?string $attachment = null): int
    {
        $sql = "INSERT INTO ticket_replies (ticket_id, sender_type, sender_name, message, attachment) 
                VALUES (:ticket_id, :sender_type, :sender_name, :message, :attachment)
                RETURNING id";
        $stmt = $this->db->query($sql, [
            'ticket_id'   => $ticketId,
            'sender_type' => $senderType,
            'sender_name' => $senderName,
            'message'     => $message,
            'attachment'   => $attachment
        ]);

        // تحديث حالة التذكرة
        $newStatus = ($senderType === 'admin') ? 'waiting_customer' : 'in_progress';
        $this->update($ticketId, ['status' => $newStatus]);

        $result = $stmt->fetch();
        return (int)($result['id'] ?? 0);
    }

    /**
     * جلب التذكرة مع ردودها
     */
    public function getWithReplies(int $ticketId): array|false
    {
        $sql = "SELECT st.*, 
                    st.category::text as category,
                    st.priority::text as priority,
                    st.status::text as status,
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    c.email as customer_email
                FROM {$this->table} st
                JOIN customers c ON st.customer_id = c.id
                WHERE st.id = :id";
        $ticket = $this->db->fetchOne($sql, ['id' => $ticketId]);

        if (!$ticket) return false;

        $sql = "SELECT *, sender_type::text as sender_type FROM ticket_replies WHERE ticket_id = :ticket_id ORDER BY created_at ASC";
        $ticket['replies'] = $this->db->fetchAll($sql, ['ticket_id' => $ticketId]);

        return $ticket;
    }

    /**
     * جلب تذاكر عميل معين
     */
    public function getByCustomer(int $customerId): array
    {
        $sql = "SELECT *, category::text as category, priority::text as priority, status::text as status
                FROM {$this->table} 
                WHERE customer_id = :cid 
                ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, ['cid' => $customerId]);
    }

    /**
     * جلب التذاكر المفتوحة (للإدارة)
     * PostgreSQL: FIELD() غير مدعومة - نستخدم CASE WHEN بدلاً منها
     */
    public function getOpenTickets(): array
    {
        $sql = "SELECT st.*, 
                    st.category::text as category,
                    st.priority::text as priority,
                    st.status::text as status,
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    (SELECT COUNT(*) FROM ticket_replies WHERE ticket_id = st.id) as reply_count
                FROM {$this->table} st
                JOIN customers c ON st.customer_id = c.id
                WHERE st.status::text NOT IN ('resolved', 'closed')
                ORDER BY 
                    CASE st.priority::text
                        WHEN 'urgent' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                        ELSE 5
                    END,
                    st.created_at ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * حل التذكرة
     * PostgreSQL: CURRENT_TIMESTAMP بدلاً من NOW()
     */
    public function resolveTicket(int $ticketId, string $resolvedBy): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'resolved', resolved_at = CURRENT_TIMESTAMP WHERE id = :id";
        return $this->db->query($sql, ['id' => $ticketId])->rowCount() > 0;
    }

    /**
     * إحصائيات التذاكر
     * PostgreSQL: EXTRACT(EPOCH FROM ...) / 3600 بدلاً من TIMESTAMPDIFF(HOUR, ...)
     */
    public function getStats(): array
    {
        $stats = [];

        $stats['by_status'] = $this->db->fetchAll(
            "SELECT status::text as status, COUNT(*) as count FROM {$this->table} GROUP BY status"
        );

        $stats['by_priority'] = $this->db->fetchAll(
            "SELECT priority::text as priority, COUNT(*) as count FROM {$this->table} WHERE status::text NOT IN ('resolved', 'closed') GROUP BY priority"
        );

        // PostgreSQL: حساب متوسط وقت الحل بالساعات
        $stats['avg_resolution_time'] = $this->db->fetchOne(
            "SELECT AVG(EXTRACT(EPOCH FROM (resolved_at - created_at)) / 3600) as avg_hours 
             FROM {$this->table} WHERE resolved_at IS NOT NULL"
        );

        return $stats;
    }

    /**
     * توليد رقم تذكرة فريد
     */
    private function generateTicketNumber(): string
    {
        return 'TKT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
