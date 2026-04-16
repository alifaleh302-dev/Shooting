<?php
/**
 * ============================================
 * متحكم سلة التسوق
 * Cart Controller - API Endpoints
 * ============================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;

class CartController extends Controller
{
    private Cart $cart;

    public function __construct()
    {
        parent::__construct();
        $this->cart = new Cart();
    }

    /**
     * جلب محتويات السلة
     * GET /api/cart?customer_id=X أو session_id=X
     */
    public function index(): void
    {
        $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        $sessionId = $_GET['session_id'] ?? null;

        if (!$customerId && !$sessionId) {
            $this->jsonError('يرجى تحديد معرف العميل أو الجلسة', 400);
        }

        $data = $this->cart->getContents($customerId, $sessionId);
        $this->jsonSuccess($data, 'محتويات السلة');
    }

    /**
     * إضافة منتج إلى السلة
     * POST /api/cart
     */
    public function store(): void
    {
        $data = $this->getRequestData();
        
        if (empty($data['inventory_id']) || empty($data['quantity'])) {
            $this->jsonError('يرجى تحديد المنتج والكمية', 400);
        }

        $customerId = $data['customer_id'] ?? null;
        $sessionId = $data['session_id'] ?? null;

        $result = $this->cart->addItem(
            (int)$data['inventory_id'],
            (int)$data['quantity'],
            $customerId ? (int)$customerId : null,
            $sessionId
        );

        if ($result) {
            $contents = $this->cart->getContents(
                $customerId ? (int)$customerId : null,
                $sessionId
            );
            $this->jsonSuccess($contents, 'تمت إضافة المنتج إلى السلة');
        } else {
            $this->jsonError('فشل الإضافة - الكمية غير متوفرة', 400);
        }
    }

    /**
     * تحديث كمية عنصر في السلة
     * PUT /api/cart/{id}
     */
    public function update(string $id): void
    {
        $data = $this->getRequestData();
        
        if (!isset($data['quantity'])) {
            $this->jsonError('يرجى تحديد الكمية', 400);
        }

        $result = $this->cart->updateQuantity((int)$id, (int)$data['quantity']);
        
        if ($result) {
            $this->jsonSuccess(null, 'تم تحديث الكمية');
        } else {
            $this->jsonError('فشل التحديث', 400);
        }
    }

    /**
     * حذف عنصر من السلة
     * DELETE /api/cart/{id}
     */
    public function destroy(string $id): void
    {
        $this->cart->delete((int)$id);
        $this->jsonSuccess(null, 'تم حذف العنصر من السلة');
    }

    /**
     * تفريغ السلة بالكامل
     * DELETE /api/cart/clear
     */
    public function clear(): void
    {
        $data = $this->getRequestData();
        $customerId = $data['customer_id'] ?? null;
        $sessionId = $data['session_id'] ?? null;

        $this->cart->clearCart(
            $customerId ? (int)$customerId : null,
            $sessionId
        );
        $this->jsonSuccess(null, 'تم تفريغ السلة');
    }
}
