<?php
/**
 * ============================================
 * متحكم المحاسبة
 * Accounting Controller - API Endpoints
 * ============================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\JournalEntry;
use App\Models\Invoice;

class AccountingController extends Controller
{
    private JournalEntry $journal;
    private Invoice $invoice;

    public function __construct()
    {
        parent::__construct();
        $this->journal = new JournalEntry();
        $this->invoice = new Invoice();
    }

    /**
     * شجرة الحسابات
     * GET /api/accounting/accounts
     */
    public function chartOfAccounts(): void
    {
        $sql = "SELECT * FROM accounts ORDER BY account_code";
        $accounts = $this->db->fetchAll($sql);
        $this->jsonSuccess($accounts, 'شجرة الحسابات');
    }

    /**
     * جلب جميع القيود المحاسبية
     * GET /api/accounting/entries
     */
    public function entries(): void
    {
        $pagination = $this->getPagination();
        $data = $this->journal->paginate($pagination['page'], $pagination['per_page'], 'entry_date', 'DESC');
        $this->jsonSuccess($data, 'القيود المحاسبية');
    }

    /**
     * جلب قيد مع بنوده
     * GET /api/accounting/entries/{id}
     */
    public function showEntry(string $id): void
    {
        $entry = $this->journal->getWithLines((int)$id);
        if (!$entry) {
            $this->jsonError('القيد غير موجود', 404);
        }
        $this->jsonSuccess($entry, 'تفاصيل القيد');
    }

    /**
     * إنشاء قيد يدوي
     * POST /api/accounting/entries
     */
    public function createEntry(): void
    {
        $data = $this->getRequestData();
        
        $errors = $this->validateRequired($data, ['entry_date', 'description', 'lines']);
        if (!empty($errors)) {
            $this->jsonError('بيانات غير مكتملة', 422, $errors);
        }

        // التحقق من توازن القيد (مدين = دائن)
        $totalDebit = array_sum(array_column($data['lines'], 'debit'));
        $totalCredit = array_sum(array_column($data['lines'], 'credit'));
        
        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            $this->jsonError('القيد غير متوازن - المدين لا يساوي الدائن', 400);
        }

        try {
            $entryData = [
                'entry_number'  => 'JE-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                'entry_date'    => $data['entry_date'],
                'description'   => $data['description'],
                'reference_type' => 'manual',
                'total_debit'   => $totalDebit,
                'total_credit'  => $totalCredit,
                'status'        => 'posted',
                'created_by'    => $data['created_by'] ?? 'مشرف'
            ];

            $entryId = $this->journal->create($entryData);

            // إضافة بنود القيد
            foreach ($data['lines'] as $line) {
                $sql = "INSERT INTO journal_entry_lines (journal_entry_id, account_id, debit, credit, description) 
                        VALUES (:entry_id, :account_id, :debit, :credit, :desc)";
                $this->db->query($sql, [
                    'entry_id'   => $entryId,
                    'account_id' => $line['account_id'],
                    'debit'      => $line['debit'] ?? 0,
                    'credit'     => $line['credit'] ?? 0,
                    'desc'       => $line['description'] ?? ''
                ]);
            }

            $entry = $this->journal->getWithLines($entryId);
            $this->jsonSuccess($entry, 'تم إنشاء القيد بنجاح', 201);
        } catch (\Throwable $e) {
            // Catch Throwable (covers both Exception and Error)
            $this->jsonError(
                'فشل إنشاء القيد: ' . $e->getMessage()
                . ' [' . basename($e->getFile()) . ':' . $e->getLine() . ']',
                500
            );
        }
    }

    /**
     * تسجيل مصروف جديد
     * POST /api/accounting/expenses
     */
    public function recordExpense(): void
    {
        $data = $this->getRequestData();
        
        $errors = $this->validateRequired($data, ['account_id', 'category', 'amount', 'expense_date', 'description']);
        if (!empty($errors)) {
            $this->jsonError('بيانات غير مكتملة', 422, $errors);
        }

        try {
            // تسجيل المصروف
            $sql = "INSERT INTO expenses (account_id, category, amount, description, expense_date, payment_method, created_by) 
                    VALUES (:account_id, :category, :amount, :description, :expense_date, :payment_method, :created_by)";
            $this->db->query($sql, [
                'account_id'     => $data['account_id'],
                'category'       => $data['category'],
                'amount'         => $data['amount'],
                'description'    => $data['description'],
                'expense_date'   => $data['expense_date'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'created_by'     => $data['created_by'] ?? 'مشرف'
            ]);

            // إنشاء القيد المحاسبي للمصروف
            $this->journal->createExpenseEntry($data);

            $this->jsonSuccess(null, 'تم تسجيل المصروف والقيد المحاسبي بنجاح', 201);
        } catch (\Throwable $e) {
            $this->jsonError('فشل تسجيل المصروف: ' . $e->getMessage()
                . ' [' . basename($e->getFile()) . ':' . $e->getLine() . ']', 500);
        }
    }

    /**
     * جلب الفواتير
     * GET /api/accounting/invoices
     */
    public function invoices(): void
    {
        $pagination = $this->getPagination();
        $data = $this->invoice->paginate($pagination['page'], $pagination['per_page'], 'created_at', 'DESC');
        $this->jsonSuccess($data, 'الفواتير');
    }

    /**
     * جلب فاتورة مفصلة
     * GET /api/accounting/invoices/{id}
     */
    public function showInvoice(string $id): void
    {
        $invoice = $this->invoice->getFullInvoice((int)$id);
        if (!$invoice) {
            $this->jsonError('الفاتورة غير موجودة', 404);
        }
        $this->jsonSuccess($invoice, 'تفاصيل الفاتورة');
    }

    /**
     * تقرير المبيعات
     * GET /api/accounting/reports/sales?start_date=...&end_date=...
     */
    public function salesReport(): void
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $data = $this->invoice->salesReport($startDate, $endDate);
        $this->jsonSuccess($data, "تقرير المبيعات من {$startDate} إلى {$endDate}");
    }

    /**
     * تقرير الأرباح
     * GET /api/accounting/reports/profit?start_date=...&end_date=...
     */
    public function profitReport(): void
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $data = $this->invoice->profitReport($startDate, $endDate);
        $this->jsonSuccess($data, 'تقرير الأرباح والخسائر');
    }

    /**
     * ميزان المراجعة
     * GET /api/accounting/reports/trial-balance
     */
    public function trialBalance(): void
    {
        $data = $this->journal->trialBalance();
        $this->jsonSuccess($data, 'ميزان المراجعة');
    }
}
