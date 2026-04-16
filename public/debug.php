<?php
/**
 * TEMPORARY DEBUG ENDPOINT - remove after debugging.
 * Tests the exact code path of createEntry() with verbose error display.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';

$appConfig = require __DIR__ . '/../config/app.php';

try {
    $db = \App\Core\Database::getInstance();
    $data = [
        'entry_date' => '2026-04-16',
        'description' => 'debug test',
        'lines' => [
            ['account_id' => 17, 'debit' => 500, 'credit' => 0, 'description' => 'exp'],
            ['account_id' => 3, 'debit' => 0, 'credit' => 500, 'description' => 'cash'],
        ],
    ];

    $totalDebit = array_sum(array_column($data['lines'], 'debit'));
    $totalCredit = array_sum(array_column($data['lines'], 'credit'));

    $journal = new \App\Models\JournalEntry();
    $entryData = [
        'entry_number'  => 'JE-DBG-' . strtoupper(substr(uniqid(), -5)),
        'entry_date'    => $data['entry_date'],
        'description'   => $data['description'],
        'reference_type' => 'manual',
        'total_debit'   => $totalDebit,
        'total_credit'  => $totalCredit,
        'status'        => 'posted',
        'created_by'    => 'debug',
    ];

    echo json_encode(['step' => '1 about to create entry', 'data' => $entryData], JSON_UNESCAPED_UNICODE) . "\n";

    $entryId = $journal->create($entryData);
    echo json_encode(['step' => '2 entry created', 'id' => $entryId]) . "\n";

    foreach ($data['lines'] as $i => $line) {
        $sql = "INSERT INTO journal_entry_lines (journal_entry_id, account_id, debit, credit, description)
                VALUES (:entry_id, :account_id, :debit, :credit, :desc)";
        $db->query($sql, [
            'entry_id'   => $entryId,
            'account_id' => $line['account_id'],
            'debit'      => $line['debit'] ?? 0,
            'credit'     => $line['credit'] ?? 0,
            'desc'       => $line['description'] ?? '',
        ]);
        echo json_encode(['step' => '3 line ' . $i . ' inserted']) . "\n";
    }

    $entry = $journal->getWithLines($entryId);
    echo json_encode(['step' => '4 getWithLines', 'entry' => $entry], JSON_UNESCAPED_UNICODE) . "\n";

    // cleanup
    $db->query("DELETE FROM journal_entries WHERE id = :id", ['id' => $entryId]);
    echo json_encode(['step' => '5 cleanup done']) . "\n";

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ], JSON_UNESCAPED_UNICODE);
}
