<?php
/**
 * ============================================
 * متحكم الطلبات
 * Order Controller - API Endpoints
 * ============================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Cart;

class OrderController extends Controller
{
    private Order $order;

    public function __construct()
    {
        parent::__construct();
        $this->order = new Order();
    }

    /**
     * جلب جميع الطلبات (للإدارة)
     * GET /api/orders
     */
    public function index(): void
    {
        $pagination = $this->getPagination();
        $data = $this->order->paginate($pagination['page'], $pagination['per_page'], 'created_at', 'DESC');
        $this->jsonSuccess($data, 'تم جلب الطلبات بنجاح');
    }

    /**
     * جلب تفاصيل طلب واحد
     * GET /api/orders/{id}
     */
    public function show(string $id): void
    {
        $order = $this->order->getFullDetails((int)$id);
        
        if (!$order) {
            $this->jsonError('الطلب غير موجود', 404);
        }

        $this->jsonSuccess($order, 'تفاصيل الطلب');
    }

    /**
     * إنشاء طلب جديد (من سلة التسوق)
     * POST /api/orders
     */
    public function store(): void
    {
        $data = $this->getRequestData();
        
        // التحقق من البيانات المطلوبة
        $errors = $this->validateRequired($data, [
            'customer_id', 'shipping_name', 'shipping_phone', 
            'shipping_address', 'shipping_city', 'payment_method'
        ]);
        if (!empty($errors)) {
            $this->jsonError('بيانات غير مكتملة', 422, $errors);
        }

        // جلب عناصر السلة
        $cart = new Cart();
        $cartContents = $cart->getContents((int)$data['customer_id']);

        if (empty($cartContents['items'])) {
            $this->jsonError('سلة التسوق فارغة', 400);
        }

        // تحويل عناصر السلة إلى عناصر طلب
        $orderItems = [];
        foreach ($cartContents['items'] as $item) {
            $orderItems[] = [
                'product_id'   => $item['product_id'],
                'inventory_id' => $item['inventory_id'],
                'product_name' => $item['product_name'],
                'color_name'   => $item['color_name'],
                'size_name'    => $item['size_name'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['sale_price'] ?? $item['base_price'] + $item['additional_price']
            ];
        }

        try {
            $result = $this->order->createOrder($data, $orderItems);
            
            // تفريغ السلة بعد الطلب الناجح
            $cart->clearCart((int)$data['customer_id']);

            $this->jsonSuccess($result, 'تم إنشاء الطلب بنجاح', 201);
        } catch (\Exception $e) {
            $this->jsonError('فشل إنشاء الطلب: ' . $e->getMessage(), 500);
        }
    }

    /**
     * تحديث حالة الطلب
     * PUT /api/orders/{id}/status
     */
    public function updateStatus(string $id): void
    {
        $data = $this->getRequestData();
        
        if (empty($data['status'])) {
            $this->jsonError('يرجى تحديد الحالة الجديدة', 400);
        }

        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        if (!in_array($data['status'], $validStatuses)) {
            $this->jsonError('حالة غير صالحة', 400);
        }

        try {
            $result = $this->order->updateStatus(
                (int)$id, 
                $data['status'], 
                $data['notes'] ?? '',
                $data['changed_by'] ?? 'مشرف'
            );

            if ($result) {
                $order = $this->order->getFullDetails((int)$id);
                $this->jsonSuccess($order, 'تم تحديث حالة الطلب');
            } else {
                $this->jsonError('فشل تحديث الحالة', 500);
            }
        } catch (\Exception $e) {
            $this->jsonError('خطأ: ' . $e->getMessage(), 500);
        }
    }

    /**
     * طلبات عميل معين
     * GET /api/orders/customer/{id}
     */
    public function byCustomer(string $id): void
    {
        $pagination = $this->getPagination();
        $data = $this->order->getByCustomer((int)$id, $pagination['page'], $pagination['per_page']);
        $this->jsonSuccess($data, 'طلبات العميل');
    }

    /**
     * إحصائيات الطلبات (لوحة التحكم)
     * GET /api/orders/stats
     */
    public function stats(): void
    {
        $data = $this->order->getDashboardStats();
        
        // إضافة إحصائيات التذاكر المفتوحة
        $ticketModel = new \App\Models\SupportTicket();
        $ticketStats = $ticketModel->getStats();
        $data['open_tickets'] = array_reduce($ticketStats['by_status'], function($carry, $item) {
            if (in_array($item['status'], ['open', 'waiting_admin', 'in_progress'])) {
                return $carry + (int)$item['count'];
            }
            return $carry;
        }, 0);

        // إضافة إحصائيات المخزون المنخفض
        $lowStock = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM inventory WHERE quantity <= low_stock_threshold"
        );
        $data['low_stock_count'] = (int)$lowStock['count'];

        // إضافة آخر 5 طلبات
        $recentOrders = $this->order->paginate(1, 5, 'created_at', 'DESC');
        $data['recent_orders'] = $recentOrders['data'];

        $this->jsonSuccess($data, 'إحصائيات لوحة التحكم الشاملة');
    }
}
