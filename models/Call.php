<?php
/**
 * Service Call Model
 */

defined('APP_INIT') or define('APP_INIT', true);

class Call extends Model {
    protected string $table = 'calls';

    /**
     * Get calls with detailed joined metadata
     */
    public function getDetailedList(array $filters = []): array {
        $sql = "
            SELECT cl.*, c.company_name, c.customer_code, c.mobile as customer_mobile,
                   loc.location_name, loc.address as location_address, loc.city,
                   m.asset_code, m.make, m.model, m.serial_number,
                   u.name as engineer_name, u.mobile as engineer_mobile,
                   p.name as problem_name, pc.name as category_name
            FROM calls cl
            JOIN customers c ON cl.customer_id = c.id
            JOIN customer_locations loc ON cl.location_id = loc.id
            LEFT JOIN machines m ON cl.machine_id = m.id
            LEFT JOIN users u ON cl.assigned_engineer_id = u.id
            LEFT JOIN problems p ON cl.problem_id = p.id
            LEFT JOIN problem_categories pc ON cl.problem_category_id = pc.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND cl.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['engineer_id'])) {
            $sql .= " AND cl.assigned_engineer_id = :engineer_id";
            $params['engineer_id'] = $filters['engineer_id'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND cl.customer_id = :customer_id";
            $params['customer_id'] = $filters['customer_id'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND cl.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        $sql .= " ORDER BY CASE cl.priority 
                    WHEN 'CRITICAL' THEN 1 
                    WHEN 'HIGH' THEN 2 
                    WHEN 'MEDIUM' THEN 3 
                    ELSE 4 END ASC, cl.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get completed service call history for an engineer with date filtering & service reports
     */
    public function getEngineerHistory(int $engineerId, array $filters = [], ?int $limit = 10): array {
        $sql = "
            SELECT cl.*, c.company_name, c.mobile as customer_mobile, c.email as customer_email,
                   loc.location_name, loc.address as location_address, loc.city,
                   m.asset_code, m.make, m.model, m.serial_number,
                   sr.report_number, sr.diagnosis, sr.action_taken, sr.customer_signed_name, sr.signed_at,
                   sr.customer_signature_data,
                   (SELECT COUNT(*) FROM spare_requests srq WHERE srq.call_id = cl.id AND srq.status IN ('APPROVED', 'ISSUED', 'USED')) as spares_count
            FROM calls cl
            JOIN customers c ON cl.customer_id = c.id
            JOIN customer_locations loc ON cl.location_id = loc.id
            LEFT JOIN machines m ON cl.machine_id = m.id
            LEFT JOIN service_reports sr ON sr.call_id = cl.id
            WHERE cl.assigned_engineer_id = :engineer_id
              AND cl.status IN ('RESOLVED', 'CLOSED')
        ";
        $params = ['engineer_id' => $engineerId];

        if (!empty($filters['from_date'])) {
            $sql .= " AND (DATE(COALESCE(sr.signed_at, cl.resolved_at, cl.updated_at)) >= :from_date)";
            $params['from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND (DATE(COALESCE(sr.signed_at, cl.resolved_at, cl.updated_at)) <= :to_date)";
            $params['to_date'] = $filters['to_date'];
        }

        $sql .= " ORDER BY COALESCE(sr.signed_at, cl.resolved_at, cl.updated_at) DESC, cl.id DESC";

        if ($limit !== null && $limit > 0) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find single call with complete joined metadata
     */
    public function getDetailed(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT cl.*, c.company_name, c.customer_code, c.mobile as customer_mobile, c.email as customer_email,
                   c.billing_address, c.gstin as customer_gstin,
                   loc.location_name, loc.address as location_address, loc.city, loc.latitude as loc_lat, loc.longitude as loc_lng,
                   m.asset_code, m.asset_tag, m.make, m.model, m.serial_number, m.processor, m.ram, m.storage, m.status as machine_status,
                   u.name as engineer_name, u.mobile as engineer_mobile, u.employee_code as engineer_code,
                   p.name as problem_name, pc.name as category_name,
                   ct.contract_number, cty.name as contract_type_name, cty.is_spares_covered, cty.is_labour_covered
            FROM calls cl
            JOIN customers c ON cl.customer_id = c.id
            JOIN customer_locations loc ON cl.location_id = loc.id
            LEFT JOIN machines m ON cl.machine_id = m.id
            LEFT JOIN users u ON cl.assigned_engineer_id = u.id
            LEFT JOIN problems p ON cl.problem_id = p.id
            LEFT JOIN problem_categories pc ON cl.problem_category_id = pc.id
            LEFT JOIN contracts ct ON cl.contract_id = ct.id
            LEFT JOIN contract_types cty ON ct.contract_type_id = cty.id
            WHERE cl.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Transition Call Status with GPS stamp and audit timeline
     */
    public function transitionStatus(int $callId, string $newStatus, ?string $remarks = null, ?float $lat = null, ?float $lng = null): bool {
        $call = $this->find($callId);
        if (!$call) return false;

        $oldStatus = $call['status'];
        $updateData = ['status' => $newStatus];

        if ($newStatus === STATUS_ARRIVED && empty($call['response_at'])) {
            $updateData['response_at'] = date('Y-m-d H:i:s');
        }

        if ($newStatus === STATUS_RESOLVED && empty($call['resolved_at'])) {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        }

        if ($newStatus === STATUS_CLOSED && empty($call['closed_at'])) {
            $updateData['closed_at'] = date('Y-m-d H:i:s');
        }

        $this->update($callId, $updateData);

        // Record Status History
        $stmt = $this->db->prepare("
            INSERT INTO call_status_history (call_id, old_status, new_status, changed_by, remarks, latitude, longitude, ip_address, created_at)
            VALUES (:call_id, :old_status, :new_status, :changed_by, :remarks, :lat, :lng, :ip, NOW())
        ");
        $stmt->execute([
            'call_id'    => $callId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => Auth::id(),
            'remarks'    => $remarks,
            'lat'        => $lat,
            'lng'        => $lng,
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        if (in_array($newStatus, [STATUS_RESOLVED, STATUS_CLOSED])) {
            $this->db->prepare("UPDATE pm_schedules SET status = 'COMPLETED' WHERE call_id = :cid")
                     ->execute(['cid' => $callId]);
        }

        return true;
    }

    /**
     * Get Call Status History Timeline
     */
    public function getTimeline(int $callId): array {
        $stmt = $this->db->prepare("
            SELECT csh.*, u.name as user_name, r.name as role_name
            FROM call_status_history csh
            LEFT JOIN users u ON csh.changed_by = u.id
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE csh.call_id = :cid
            ORDER BY csh.id ASC
        ");
        $stmt->execute(['cid' => $callId]);
        return $stmt->fetchAll();
    }

    /**
     * Search calls for Fast Search
     */
    public function search(string $query): array {
        $q = "%{$query}%";
        $stmt = $this->db->prepare("
            SELECT cl.id, cl.call_number, cl.status, cl.priority, cl.reported_issue,
                   c.company_name
            FROM calls cl
            JOIN customers c ON cl.customer_id = c.id
            WHERE cl.call_number LIKE :q1 OR cl.reported_issue LIKE :q2 OR c.company_name LIKE :q3
            ORDER BY cl.id DESC
            LIMIT 10
        ");
        $stmt->execute(['q1' => $q, 'q2' => $q, 'q3' => $q]);
        return $stmt->fetchAll();
    }
}
