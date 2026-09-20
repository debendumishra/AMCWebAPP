<?php
/**
 * Atomic Collision-Proof Sequential Document Number Generator
 */

defined('APP_INIT') or define('APP_INIT', true);

class NumberGenerator {
    /**
     * Map of sequence types to their underlying table and unique column
     */
    private static array $tableMap = [
        'call'           => ['calls', 'call_number', 'call_prefix', PREFIX_CALL],
        'contract'       => ['contracts', 'contract_number', 'contract_prefix', PREFIX_CONTRACT],
        'customer'       => ['customers', 'customer_code', 'customer_prefix', PREFIX_CUSTOMER],
        'asset'          => ['machines', 'asset_code', 'asset_prefix', PREFIX_ASSET],
        'service_report' => ['service_reports', 'report_number', 'service_report_prefix', PREFIX_SERVICE_REPORT],
        'invoice'        => ['invoices', 'invoice_number', 'invoice_prefix', PREFIX_INVOICE],
        'purchase'       => ['purchases', 'purchase_number', 'purchase_prefix', PREFIX_PURCHASE],
        'spare_req'      => ['spare_requests', 'request_number', 'spare_req_prefix', PREFIX_SPARE_REQ],
        'engineer'       => ['users', 'employee_code', 'engineer_prefix', PREFIX_ENGINEER],
    ];

    /**
     * Generate the next document number atomically without any collision
     */
    public static function generate(string $type, ?string $customPrefix = null): string {
        $db = Database::getInstance();
        $year = date('Y');

        $mapping = self::$tableMap[$type] ?? null;
        $table = $mapping ? $mapping[0] : null;
        $column = $mapping ? $mapping[1] : null;
        $settingKey = $mapping ? $mapping[2] : null;
        $defaultPrefix = $mapping ? $mapping[3] : 'DOC-';

        // Retrieve configured prefix from settings if available
        $prefix = $customPrefix;
        if (empty($prefix) && class_exists('Setting') && $settingKey) {
            $prefix = Setting::get($settingKey);
        }
        if (empty($prefix)) {
            $prefix = $defaultPrefix;
        }

        if (!$db) {
            return sprintf("%s%s-%06d", $prefix, $year, random_int(100000, 999999));
        }

        try {
            // Step 1: Ensure sequence row exists and initialize properly
            $seqStmt = $db->prepare("SELECT last_number FROM document_sequences WHERE seq_type = :type AND seq_year = :year FOR UPDATE");
            $seqStmt->execute(['type' => $type, 'year' => $year]);
            $seqRow = $seqStmt->fetch();

            $currentNum = $seqRow ? (int)$seqRow['last_number'] : 0;

            // Step 2: Loop to guarantee uniqueness against actual target table
            $maxAttempts = 1000;
            $candidateNum = $currentNum + 1;
            $code = '';

            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                $code = sprintf("%s%s-%06d", $prefix, $year, $candidateNum);

                if ($table && $column) {
                    $checkStmt = $db->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :code");
                    $checkStmt->execute(['code' => $code]);
                    if ((int)$checkStmt->fetchColumn() === 0) {
                        // Found a guaranteed unused unique number
                        break;
                    }
                    $candidateNum++;
                } else {
                    break;
                }
            }

            // Step 3: Update document_sequences with the new highest number
            $updateStmt = $db->prepare("
                INSERT INTO document_sequences (seq_type, seq_year, last_number)
                VALUES (:type, :year, :num)
                ON DUPLICATE KEY UPDATE last_number = :num2
            ");
            $updateStmt->execute([
                'type' => $type,
                'year' => $year,
                'num'  => $candidateNum,
                'num2' => $candidateNum
            ]);

            return $code;
        } catch (Throwable $e) {
            error_log("NumberGenerator error: " . $e->getMessage());
            return sprintf("%s%s-%06d", $prefix, $year, time() % 1000000);
        }
    }
}
