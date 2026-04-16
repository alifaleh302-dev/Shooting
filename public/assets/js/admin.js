/**
 * ============================================
 * وظائف JavaScript لوحة التحكم
 * Admin Dashboard JavaScript
 * ============================================
 */

/**
 * إرسال طلب API للإدارة
 */
async function adminAPI(endpoint, method = 'GET', data = null) {
    const options = {
        method,
        headers: { 'Content-Type': 'application/json' }
    };
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    const response = await fetch(`/api${endpoint}`, options);
    return await response.json();
}

/**
 * تأكيد الحذف
 */
function confirmDelete(message = 'هل أنت متأكد من الحذف؟') {
    return confirm(message);
}

/**
 * تنسيق التاريخ بالعربية
 */
function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('ar-SA', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
}

/**
 * تنسيق المبلغ
 */
function formatAmount(amount) {
    return Number(amount).toLocaleString('ar-SA', { minimumFractionDigits: 2 }) + ' ر.س';
}
