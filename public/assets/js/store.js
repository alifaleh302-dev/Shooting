/**
 * ============================================
 * وظائف JavaScript العامة للمتجر
 * Store General JavaScript Functions
 * ============================================
 */

const StoreAPI = '/api';

/**
 * إرسال طلب API عام
 * General API request helper
 */
async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = {
        method,
        headers: { 'Content-Type': 'application/json' }
    };
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(`${StoreAPI}${endpoint}`, options);
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'خطأ في الاتصال بالسيرفر' };
    }
}

/**
 * إضافة منتج إلى السلة
 * Add product to cart
 */
async function addToCart(inventoryId, quantity = 1) {
    const customer = getLoggedInCustomer();
    
    const data = {
        inventory_id: inventoryId,
        quantity: quantity,
        customer_id: customer?.id || null,
        session_id: customer ? null : getSessionId()
    };

    const result = await apiRequest('/cart', 'POST', data);
    
    if (result.success) {
        showNotification('تمت إضافة المنتج إلى السلة', 'success');
        updateCartCount(result.data?.count || 0);
    } else {
        showNotification(result.message || 'فشل الإضافة', 'danger');
    }
    
    return result;
}

/**
 * تحديث عداد السلة في الشريط العلوي
 * Update cart count badge
 */
function updateCartCount(count) {
    const badge = document.getElementById('cart-count');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    }
}

/**
 * الحصول على بيانات العميل المسجل
 * Get logged in customer data
 */
function getLoggedInCustomer() {
    const data = localStorage.getItem('customer');
    return data ? JSON.parse(data) : null;
}

/**
 * الحصول على معرف الجلسة (للزوار)
 * Get or create session ID for guests
 */
function getSessionId() {
    let sessionId = localStorage.getItem('guest_session');
    if (!sessionId) {
        sessionId = 'guest_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('guest_session', sessionId);
    }
    return sessionId;
}

/**
 * عرض إشعار منبثق
 * Show floating notification
 */
function showNotification(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-floating alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    // إزالة تلقائية بعد 3 ثوانٍ
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

/**
 * تسجيل الخروج
 * Logout
 */
function logout() {
    localStorage.removeItem('customer');
    location.reload();
}

/**
 * تنسيق السعر
 * Format price
 */
function formatPrice(price) {
    return Number(price).toLocaleString('ar-SA') + ' ر.س';
}
