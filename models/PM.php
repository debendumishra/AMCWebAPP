<?php
/**
 * Preventive Maintenance & System Settings Models
 */

defined('APP_INIT') or define('APP_INIT', true);

class PM extends Model {
    protected string $table = 'pm_schedules';

    public function getDetailedList(array $filters = []): array {
        $sql = "
            SELECT pm.*, c.company_name, m.asset_code, m.make, m.model, m.serial_number,
                   ct.contract_number, cl.call_number, cl.status as call_status, u.name as engineer_name
            FROM pm_schedules pm
            JOIN contracts ct ON pm.contract_id = ct.id
            JOIN customers c ON pm.customer_id = c.id
            JOIN machines m ON pm.machine_id = m.id
            LEFT JOIN calls cl ON pm.call_id = cl.id
            LEFT JOIN users u ON pm.assigned_engineer_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND pm.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['customer_id'])) {
            $sql .= " AND pm.customer_id = :cid";
            $params['cid'] = (int)$filters['customer_id'];
        }
        if (!empty($filters['contract_id'])) {
            $sql .= " AND pm.contract_id = :ctid";
            $params['ctid'] = (int)$filters['contract_id'];
        }

        $sql .= " ORDER BY pm.schedule_date ASC, pm.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getDueSchedules(): array {
        $stmt = $this->db->query("
            SELECT pm.*, c.company_name, m.asset_code, m.make, m.model, m.location_id
            FROM pm_schedules pm
            JOIN customers c ON pm.customer_id = c.id
            JOIN machines m ON pm.machine_id = m.id
            WHERE pm.status = 'PENDING' AND pm.schedule_date <= CURDATE()
            ORDER BY pm.schedule_date ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Generate PM Schedules for a specific Contract
     */
    public function generateSchedulesForContract(int $contractId): int {
        $db = $this->db;
        $ctStmt = $db->prepare("
            SELECT ct.*, cty.pm_frequency
            FROM contracts ct
            JOIN contract_types cty ON ct.contract_type_id = cty.id
            WHERE ct.id = :id
        ");
        $ctStmt->execute(['id' => $contractId]);
        $contract = $ctStmt->fetch();
        if (!$contract) return 0;

        $machinesStmt = $db->prepare("
            SELECT cm.machine_id, m.customer_id
            FROM contract_machines cm
            JOIN machines m ON cm.machine_id = m.id
            WHERE cm.contract_id = :cid AND cm.status = 'ACTIVE'
        ");
        $machinesStmt->execute(['cid' => $contractId]);
        $machines = $machinesStmt->fetchAll();

        if (empty($machines)) return 0;

        $freq = strtoupper($contract['pm_frequency'] ?? 'QUARTERLY');
        $stepMonths = match($freq) {
            'MONTHLY'                 => 1,
            'BI_MONTHLY'              => 2,
            'QUARTERLY'               => 3,
            'HALF_YEARLY', 'SEMI_ANNUAL' => 6,
            'YEARLY', 'ANNUAL'        => 12,
            default                   => 3
        };

        $startDate = new DateTime($contract['start_date']);
        $endDate = new DateTime($contract['end_date']);
        $createdCount = 0;

        foreach ($machines as $mach) {
            $curDate = clone $startDate;
            // Schedule visits periodically starting from start_date + stepMonths
            $curDate->modify("+{$stepMonths} months");

            while ($curDate <= $endDate) {
                $schedDateStr = $curDate->format('Y-m-d');

                // Check if schedule already exists
                $chkStmt = $db->prepare("
                    SELECT id FROM pm_schedules
                    WHERE contract_id = :cid AND machine_id = :mid AND schedule_date = :sdate
                ");
                $chkStmt->execute([
                    'cid'   => $contractId,
                    'mid'   => $mach['machine_id'],
                    'sdate' => $schedDateStr
                ]);

                if (!$chkStmt->fetch()) {
                    $ins = $db->prepare("
                        INSERT INTO pm_schedules (contract_id, customer_id, machine_id, schedule_date, status)
                        VALUES (:cid, :custid, :mid, :sdate, 'PENDING')
                    ");
                    $ins->execute([
                        'cid'    => $contractId,
                        'custid' => $mach['customer_id'],
                        'mid'    => $mach['machine_id'],
                        'sdate'  => $schedDateStr
                    ]);
                    $createdCount++;
                }

                $curDate->modify("+{$stepMonths} months");
            }
        }

        return $createdCount;
    }

    /**
     * Sync and generate missing PM schedules across all active AMC contracts
     */
    public function syncAllContractSchedules(): int {
        $contracts = $this->db->query("SELECT id FROM contracts WHERE status = 'ACTIVE'")->fetchAll();
        $total = 0;
        foreach ($contracts as $c) {
            $total += $this->generateSchedulesForContract((int)$c['id']);
        }
        return $total;
    }

    /**
     * Generate a service call for a single PM schedule
     */
    public function generateSingleCall(int $pmScheduleId, int $userId): int {
        $db = $this->db;
        $stmt = $db->prepare("
            SELECT pm.*, c.company_name, m.asset_code, m.make, m.model, m.location_id
            FROM pm_schedules pm
            JOIN customers c ON pm.customer_id = c.id
            JOIN machines m ON pm.machine_id = m.id
            WHERE pm.id = :id
        ");
        $stmt->execute(['id' => $pmScheduleId]);
        $pm = $stmt->fetch();
        if (!$pm || !empty($pm['call_id'])) {
            return (int)($pm['call_id'] ?? 0);
        }

        require_once ROOT_PATH . '/models/Call.php';
        $callModel = new Call();
        $callNo = NumberGenerator::generate('call');
        
        $callId = $callModel->create([
            'call_number'            => $callNo,
            'call_type'              => 'PM',
            'priority'               => 'LOW',
            'customer_id'            => $pm['customer_id'],
            'location_id'            => $pm['location_id'],
            'machine_id'             => $pm['machine_id'],
            'contract_id'            => $pm['contract_id'],
            'caller_name'            => 'System PM Scheduler',
            'caller_mobile'          => '9876543210',
            'reported_issue'         => "Scheduled Preventive Maintenance (PM) inspection for asset {$pm['asset_code']} ({$pm['make']} {$pm['model']}).",
            'status'                 => 'NEW',
            'sla_response_deadline'  => date('Y-m-d H:i:s', strtotime('+4 hours')),
            'sla_resolution_deadline'=> date('Y-m-d H:i:s', strtotime('+48 hours')),
            'created_by'             => $userId
        ]);

        $db->prepare("UPDATE pm_schedules SET status = 'GENERATED', call_id = :cid WHERE id = :pid")
           ->execute(['cid' => $callId, 'pid' => $pmScheduleId]);

        return $callId;
    }
}
