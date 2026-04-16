<?php
/**
 * ============================================
 * النواة: النموذج الأساسي
 * Core: Base Model
 * ============================================
 * الفئة الأساسية لجميع النماذج - توفر عمليات CRUD الأساسية
 * متوافق مع PostgreSQL
 */

namespace App\Core;

class Model
{
    /** @var Database كائن قاعدة البيانات */
    protected Database $db;

    /** @var string اسم الجدول - يتم تحديده في النموذج الفرعي */
    protected string $table = '';

    /** @var string اسم المفتاح الرئيسي */
    protected string $primaryKey = 'id';

    /** @var array الحقول القابلة للتعبئة */
    protected array $fillable = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * جلب جميع السجلات
     * Fetch all records
     */
    public function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}";
        return $this->db->fetchAll($sql);
    }

    /**
     * جلب سجلات مع ترقيم
     * Fetch records with pagination
     */
    public function paginate(int $page = 1, int $perPage = 20, string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        
        // جلب إجمالي السجلات
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $total = $this->db->fetchOne($countSql)['total'];

        // جلب البيانات - PostgreSQL يدعم LIMIT/OFFSET مباشرة
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction} LIMIT $1 OFFSET $2";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$perPage, $offset]);
        $data = $stmt->fetchAll();

        return [
            'data'       => $data,
            'total'      => (int)$total,
            'page'       => $page,
            'per_page'   => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * البحث عن سجل بالمعرف
     * Find a record by ID
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    /**
     * البحث بشرط معين
     * Find records by a condition
     */
    public function where(string $column, mixed $value, string $operator = '='): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value";
        return $this->db->fetchAll($sql, ['value' => $value]);
    }

    /**
     * البحث عن أول سجل بشرط معين
     * Find first record by condition
     */
    public function findWhere(string $column, mixed $value, string $operator = '='): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value LIMIT 1";
        return $this->db->fetchOne($sql, ['value' => $value]);
    }

    /**
     * إنشاء سجل جديد
     * Create a new record
     * يستخدم RETURNING id للحصول على المعرف في PostgreSQL
     */
    public function create(array $data): int
    {
        // تصفية الحقول المسموحة فقط
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        
        $columns = implode(', ', array_keys($filtered));
        $placeholders = ':' . implode(', :', array_keys($filtered));

        // استخدام RETURNING id بدلاً من lastInsertId() لتوافق أفضل مع PostgreSQL
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders}) RETURNING {$this->primaryKey}";
        $stmt = $this->db->query($sql, $filtered);
        $result = $stmt->fetch();

        return (int)($result[$this->primaryKey] ?? 0);
    }

    /**
     * تحديث سجل
     * Update a record
     */
    public function update(int $id, array $data): bool
    {
        // تصفية الحقول المسموحة فقط
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        
        if (empty($filtered)) return false;
        
        $setParts = [];
        foreach (array_keys($filtered) as $key) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setString = implode(', ', $setParts);

        $filtered['id'] = $id;
        $sql = "UPDATE {$this->table} SET {$setString} WHERE {$this->primaryKey} = :id";
        
        return $this->db->query($sql, $filtered)->rowCount() > 0;
    }

    /**
     * حذف سجل
     * Delete a record
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->query($sql, ['id' => $id])->rowCount() > 0;
    }

    /**
     * حذف ناعم (تعطيل بدلاً من الحذف)
     * Soft delete (disable instead of deleting)
     */
    public function softDelete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET is_active = 0, deleted_at = CURRENT_TIMESTAMP WHERE {$this->primaryKey} = :id";
        return $this->db->query($sql, ['id' => $id])->rowCount() > 0;
    }

    /**
     * عد السجلات
     * Count records
     */
    public function count(string $condition = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($condition) {
            $sql .= " WHERE {$condition}";
        }
        return (int)$this->db->fetchOne($sql, $params)['total'];
    }
}
