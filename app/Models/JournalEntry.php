<?php
/**
 * ============================================
 * نموذج القيود المحاسبية - متوافق مع PostgreSQL
 * Journal Entry Model - PostgreSQL Compatible
 * ============================================
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class JournalEntry extends Model
{
    protected string $table = 'journal_entries';
    
    protected array $fillable = [
        'entry_number', 'entry_date', 'description', 'reference_type',
        'reference_id', 'total_debit', 'total_credit', 'status', 'created_by'
    ];

    /**
     * إنشاء قيد محاسبي لعملية بيع (تلقائياً عند إنشاء طلب)
     */
    public function createSalesEntry(int $orderId, array $orderData): int
    {
        $entryData = [
            'entry_number'  => $this->generateEntryNumber(),
            'entry_date'    => date('Y-m-d'),
            'description'   => "قيد بيع - طلب رقم: {$orderData['order_number']}",
            'reference_type' => 'order',
            'reference_id'  => $orderId,
            'total_debit'   => $orderData['total_amount'],
            'total_credit'  => $orderData['total_amount'],
            'status'        => 'posted',
            'created_by'    => 'النظام (تلقائي)'
        ];

        $entryId = $this->create($entryData);

        $debitAccountCode = ($orderData['payment_method'] === 'cash_on_delivery') ? '1200' : '1110';

        // بنود القيد
        $lines = [
            [
                'journal_entry_id' => $entryId,
                'account_id'       => $this->getAccountIdByCode($debitAccountCode),
                'debit'            => $orderData['total_amount'],
                'credit'           => 0,
                'description'      => 'تحصيل قيمة المبيعات'
            ],
            [
                'journal_entry_id' => $entryId,
                'account_id'       => $this->getAccountIdByCode('4100'),
                'debit'            => 0,
                'credit'           => $orderData['subtotal'] - ($orderData['discount_amount'] ?? 0),
                'description'      => 'إيرادات المبيعات'
            ],
            [
                'journal_entry_id' => $entryId,
                'account_id'       => $this->getAccountIdByCode('2200'),
                'debit'            => 0,
                'credit'           => $orderData['vat_amount'],
                'description'      => 'ضريبة القيمة المضافة المستحقة'
            ]
        ];

        if (($orderData['shipping_cost'] ?? 0) > 0) {
            $lines[] = [
                'journal_entry_id' => $entryId,
                'account_id'       => $this->getAccountIdByCode('4200'),
                'debit'            => 0,
                'credit'           => $orderData['shipping_cost'],
                'description'      => 'إيرادات الشحن'
            ];
        }

        foreach ($lines as $line) {
            $sql = "INSERT INTO journal_entry_lines (journal_entry_id, account_id, debit, credit, description) 
                    VALUES (:journal_entry_id, :account_id, :debit, :credit, :description)";
            $this->db->query($sql, $line);
        }

        $this->updateAccountBalances($lines);

        return $entryId;
    }

    /**
     * إنشاء قيد مصروف
     */
    public function createExpenseEntry(array $expenseData): int
    {
        $entryData = [
            'entry_number'  => $this->generateEntryNumber(),
            'entry_date'    => $expenseData['expense_date'],
            'description'   => "مصروف: {$expenseData['description']}",
            'reference_type' => 'expense',
            'total_debit'   => $expenseData['amount'],
            'total_credit'  => $expenseData['amount'],
            'status'        => 'posted',
            'created_by'    => $expenseData['created_by'] ?? 'النظام'
        ];

        $entryId = $this->create($entryData);

        $lines = [
            [
                'journal_entry_id' => $entryId,
                'account_id'       => $expenseData['account_id'],
                'debit'            => $expenseData['amount'],
                'credit'           => 0,
                'description'      => $expenseData['description']
            ],
            [
                'journal_entry_id' => $entryId,
                'account_id'       => $this->getAccountIdByCode('1110'),
                'debit'            => 0,
                'credit'           => $expenseData['amount'],
                'description'      => 'سداد مصروف'
            ]
        ];

        foreach ($lines as $line) {
            $sql = "INSERT INTO journal_entry_lines (journal_entry_id, account_id, debit, credit, description) 
                    VALUES (:journal_entry_id, :account_id, :debit, :credit, :description)";
            $this->db->query($sql, $line);
        }

        $this->updateAccountBalances($lines);

        return $entryId;
    }

    /**
     * جلب معرف الحساب من الرمز
     */
    private function getAccountIdByCode(string $code): int
    {
        $result = $this->db->fetchOne(
            "SELECT id FROM accounts WHERE account_code = :code",
            ['code' => $code]
        );
        return $result ? (int)$result['id'] : 0;
    }

    /**
     * تحديث أرصدة الحسابات المتأثرة
     */
    private function updateAccountBalances(array $lines): void
    {
        foreach ($lines as $line) {
            $sql = "UPDATE accounts SET balance = balance + :debit - :credit WHERE id = :id";
            $this->db->query($sql, [
                'debit'  => $line['debit'],
                'credit' => $line['credit'],
                'id'     => $line['account_id']
            ]);
        }
    }

    /**
     * توليد رقم قيد فريد
     */
    private function generateEntryNumber(): string
    {
        return 'JE-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }

    /**
     * جلب القيد مع بنوده
     */
    public function getWithLines(int $entryId): array|false
    {
        $entry = $this->find($entryId);
        if (!$entry) return false;

        $sql = "SELECT jel.*, a.account_code, a.name as account_name 
                FROM journal_entry_lines jel
                JOIN accounts a ON jel.account_id = a.id
                WHERE jel.journal_entry_id = :entry_id";
        $entry['lines'] = $this->db->fetchAll($sql, ['entry_id' => $entryId]);

        return $entry;
    }

    /**
     * ميزان المراجعة
     * PostgreSQL: CASE WHEN بدون مشاكل
     */
    public function trialBalance(): array
    {
        $sql = "SELECT a.account_code, a.name, a.type::text as type, a.balance,
                    CASE WHEN a.balance > 0 THEN a.balance ELSE 0 END as debit_balance,
                    CASE WHEN a.balance < 0 THEN ABS(a.balance) ELSE 0 END as credit_balance
                FROM accounts a 
                WHERE a.balance != 0
                ORDER BY a.account_code";
        return $this->db->fetchAll($sql);
    }
}
