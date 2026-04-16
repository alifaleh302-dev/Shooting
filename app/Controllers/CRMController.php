<?php
/**
 * ============================================
 * متحكم خدمة العملاء (CRM)
 * CRM Controller - API Endpoints
 * ============================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Customer;
use App\Models\SupportTicket;

class CRMController extends Controller
{
    private Customer $customer;
    private SupportTicket $ticket;

    public function __construct()
    {
        parent::__construct();
        $this->customer = new Customer();
        $this->ticket = new SupportTicket();
    }

    // ===================== إدارة العملاء =====================

    /**
     * جلب جميع العملاء
     * GET /api/crm/customers
     */
    public function customers(): void
    {
        $pagination = $this->getPagination();
        $data = $this->customer->paginate($pagination['page'], $pagination['per_page']);
        $this->jsonSuccess($data, 'قائمة العملاء');
    }

    /**
     * جلب بيانات عميل مع إحصائياته
     * GET /api/crm/customers/{id}
     */
    public function showCustomer(string $id): void
    {
        $customer = $this->customer->find((int)$id);
        if (!$customer) {
            $this->jsonError('العميل غير موجود', 404);
        }

        unset($customer['password_hash']);
        $customer['stats'] = $this->customer->getStats((int)$id);
        $customer['purchase_history'] = $this->customer->getPurchaseHistory((int)$id);
        $customer['tickets'] = $this->ticket->getByCustomer((int)$id);

        $this->jsonSuccess($customer, 'بيانات العميل');
    }

    /**
     * تسجيل عميل جديد
     * POST /api/crm/customers/register
     */
    public function registerCustomer(): void
    {
        $data = $this->getRequestData();
        
        $errors = $this->validateRequired($data, ['first_name', 'last_name', 'email', 'password']);
        if (!empty($errors)) {
            $this->jsonError('بيانات غير مكتملة', 422, $errors);
        }

        // التحقق من صحة البريد
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->jsonError('البريد الإلكتروني غير صالح', 422);
        }

        $customerId = $this->customer->register($data);
        if (!$customerId) {
            $this->jsonError('البريد الإلكتروني مسجل مسبقاً', 409);
        }

        $customer = $this->customer->find($customerId);
        unset($customer['password_hash']);
        $this->jsonSuccess($customer, 'تم التسجيل بنجاح', 201);
    }

    /**
     * تسجيل الدخول
     * POST /api/crm/customers/login
     */
    public function loginCustomer(): void
    {
        $data = $this->getRequestData();
        
        $errors = $this->validateRequired($data, ['email', 'password']);
        if (!empty($errors)) {
            $this->jsonError('يرجى إدخال البريد وكلمة المرور', 422, $errors);
        }

        $customer = $this->customer->login($data['email'], $data['password']);
        if (!$customer) {
            $this->jsonError('بيانات الدخول غير صحيحة', 401);
        }

        // بدء الجلسة
        session_start();
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];

        $this->jsonSuccess($customer, 'تم تسجيل الدخول بنجاح');
    }

    // ===================== تذاكر الدعم =====================

    /**
     * جلب جميع التذاكر المفتوحة (للإدارة)
     * GET /api/crm/tickets
     */
    public function tickets(): void
    {
        $data = $this->ticket->getOpenTickets();
        $this->jsonSuccess($data, 'التذاكر المفتوحة');
    }

    /**
     * جلب تذكرة مع ردودها
     * GET /api/crm/tickets/{id}
     */
    public function showTicket(string $id): void
    {
        $ticket = $this->ticket->getWithReplies((int)$id);
        if (!$ticket) {
            $this->jsonError('التذكرة غير موجودة', 404);
        }
        $this->jsonSuccess($ticket, 'تفاصيل التذكرة');
    }

    /**
     * إنشاء تذكرة دعم جديدة
     * POST /api/crm/tickets
     */
    public function createTicket(): void
    {
        $data = $this->getRequestData();
        
        $errors = $this->validateRequired($data, ['customer_id', 'subject', 'message']);
        if (!empty($errors)) {
            $this->jsonError('بيانات غير مكتملة', 422, $errors);
        }

        try {
            $ticketData = [
                'customer_id'   => $data['customer_id'],
                'order_id'      => $data['order_id'] ?? null,
                'subject'       => $data['subject'],
                'category'      => $data['category'] ?? 'general',
                'priority'      => $data['priority'] ?? 'medium',
                'customer_name' => $data['customer_name'] ?? 'عميل'
            ];

            $result = $this->ticket->createTicket($ticketData, $data['message']);
            $this->jsonSuccess($result, 'تم إنشاء التذكرة بنجاح', 201);
        } catch (\Exception $e) {
            $this->jsonError('فشل إنشاء التذكرة: ' . $e->getMessage(), 500);
        }
    }

    /**
     * إضافة رد على تذكرة
     * POST /api/crm/tickets/{id}/reply
     */
    public function replyToTicket(string $id): void
    {
        $data = $this->getRequestData();
        
        if (empty($data['message'])) {
            $this->jsonError('يرجى كتابة الرد', 400);
        }

        try {
            $this->ticket->addReply(
                (int)$id,
                $data['sender_type'] ?? 'admin',
                $data['sender_name'] ?? 'فريق الدعم',
                $data['message']
            );

            $ticket = $this->ticket->getWithReplies((int)$id);
            $this->jsonSuccess($ticket, 'تم إضافة الرد بنجاح');
        } catch (\Exception $e) {
            $this->jsonError('فشل إضافة الرد: ' . $e->getMessage(), 500);
        }
    }

    /**
     * حل/إغلاق تذكرة
     * PUT /api/crm/tickets/{id}/resolve
     */
    public function resolveTicket(string $id): void
    {
        $data = $this->getRequestData();
        $this->ticket->resolveTicket((int)$id, $data['resolved_by'] ?? 'مشرف');
        $this->jsonSuccess(null, 'تم حل التذكرة');
    }

    /**
     * إحصائيات CRM
     * GET /api/crm/stats
     */
    public function stats(): void
    {
        $ticketStats = $this->ticket->getStats();
        
        // إحصائيات العملاء
        $customerStats = $this->db->fetchOne(
            "SELECT COUNT(*) as total_customers, 
                    SUM(total_spent) as total_revenue,
                    AVG(total_spent) as avg_customer_value
             FROM customers WHERE is_active = 1"
        );

        // أفضل العملاء
        $topCustomers = $this->db->fetchAll(
            "SELECT id, first_name, last_name, email, total_orders, total_spent, loyalty_points
             FROM customers 
             WHERE is_active = 1 
             ORDER BY total_spent DESC 
             LIMIT 10"
        );

        $this->jsonSuccess([
            'tickets'       => $ticketStats,
            'customers'     => $customerStats,
            'top_customers' => $topCustomers
        ], 'إحصائيات خدمة العملاء');
    }
}
