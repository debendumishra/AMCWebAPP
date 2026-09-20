<?php
/**
 * Machine / IT Asset Model
 */

defined('APP_INIT') or define('APP_INIT', true);

class Machine extends Model {
    protected string $table = 'machines';

    /**
     * Get machines with customer, location, and asset type info
     */
    public function getDetailedList(array $filters = []): array {
        $sql = "
            SELECT m.*, c.company_name, c.customer_code, cl.location_name, cl.city,
                   at.name as asset_type_name, at.icon as asset_type_icon,
                   (SELECT ct.contract_number FROM contract_machines cm 
                    JOIN contracts ct ON cm.contract_id = ct.id 
                    WHERE cm.machine_id = m.id AND cm.status = 'ACTIVE' LIMIT 1) as active_contract_number
            FROM machines m
            JOIN customers c ON m.customer_id = c.id
            JOIN customer_locations cl ON m.location_id = cl.id
            JOIN asset_types at ON m.asset_type_id = at.id
            WHERE m.deleted_at IS NULL
        ";
        $params = [];

        if (!empty($filters['customer_id'])) {
            $sql .= " AND m.customer_id = :customer_id";
            $params['customer_id'] = $filters['customer_id'];
        }

        if (!empty($filters['location_id'])) {
            $sql .= " AND m.location_id = :location_id";
            $params['location_id'] = $filters['location_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND m.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " ORDER BY m.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find machine by QR Code Token
     */
    public function findByQrToken(string $token): ?array {
        $stmt = $this->db->prepare("
            SELECT m.*, c.company_name, c.customer_code, c.mobile as customer_mobile, c.email as customer_email,
                   cl.location_name, cl.address as location_address, cl.city,
                   at.name as asset_type_name, at.icon as asset_type_icon,
                   ct.contract_number, ct.title as contract_title, ct.status as contract_status,
                   ct.start_date as contract_start, ct.end_date as contract_end,
                   cty.is_spares_covered, cty.is_labour_covered, cty.name as contract_type_name
            FROM machines m
            JOIN customers c ON m.customer_id = c.id
            JOIN customer_locations cl ON m.location_id = cl.id
            JOIN asset_types at ON m.asset_type_id = at.id
            LEFT JOIN contract_machines cm ON cm.machine_id = m.id AND cm.status = 'ACTIVE'
            LEFT JOIN contracts ct ON cm.contract_id = ct.id AND ct.status = 'ACTIVE'
            LEFT JOIN contract_types cty ON ct.contract_type_id = cty.id
            WHERE m.qr_code_token = :token AND m.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Search machines for Fast Search
     */
    public function search(string $query): array {
        $q = "%{$query}%";
        $stmt = $this->db->prepare("
            SELECT m.id, m.asset_code, m.asset_tag, m.serial_number, m.make, m.model, m.status, m.department, m.assigned_employee,
                   c.id as customer_id, c.company_name, cl.location_name
            FROM machines m
            JOIN customers c ON m.customer_id = c.id
            JOIN customer_locations cl ON m.location_id = cl.id
            WHERE m.deleted_at IS NULL AND (
                m.serial_number LIKE :q1 OR m.asset_code LIKE :q2 OR m.asset_tag LIKE :q3 OR m.make LIKE :q4 OR m.model LIKE :q5 OR m.ip_address LIKE :q6
            )
            LIMIT 10
        ");
        $stmt->execute(['q1' => $q, 'q2' => $q, 'q3' => $q, 'q4' => $q, 'q5' => $q, 'q6' => $q]);
        return $stmt->fetchAll();
    }

    /**
     * Powerful multi-field instant search across 5,000+ assets with customer & location filters
     */
    public function searchAssets(string $query = '', ?int $customerId = null, int $limit = 30, ?int $locationId = null): array {
        $sql = "
            SELECT m.*, c.company_name, c.customer_code, cl.location_name, cl.city,
                   at.name as asset_type_name, at.icon as asset_type_icon,
                   (SELECT ct.contract_number FROM contract_machines cm 
                    JOIN contracts ct ON cm.contract_id = ct.id 
                    WHERE cm.machine_id = m.id AND cm.status = 'ACTIVE' LIMIT 1) as active_contract_number
            FROM machines m
            JOIN customers c ON m.customer_id = c.id
            JOIN customer_locations cl ON m.location_id = cl.id
            JOIN asset_types at ON m.asset_type_id = at.id
            WHERE m.deleted_at IS NULL
        ";
        $params = [];

        if (!empty($customerId)) {
            $sql .= " AND m.customer_id = :cid";
            $params['cid'] = $customerId;
        }

        if (!empty($locationId)) {
            $sql .= " AND m.location_id = :lid";
            $params['lid'] = $locationId;
        }

        if (!empty($query)) {
            $q = "%{$query}%";
            $sql .= " AND (
                m.asset_code LIKE :q1 
                OR m.serial_number LIKE :q2 
                OR m.make LIKE :q3 
                OR m.model LIKE :q4 
                OR m.asset_tag LIKE :q5 
                OR m.department LIKE :q6 
                OR m.assigned_employee LIKE :q7 
                OR m.ip_address LIKE :q8 
                OR m.processor LIKE :q9 
                OR at.name LIKE :q10
            )";
            for ($i = 1; $i <= 10; $i++) {
                $params["q{$i}"] = $q;
            }
        }

        $sql .= " ORDER BY m.id DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get machine service call history & downtime timeline
     */
    public function getServiceHistory(int $machineId): array {
        $stmt = $this->db->prepare("
            SELECT cl.*, u.name as engineer_name, sr.report_number, sr.pdf_path
            FROM calls cl
            LEFT JOIN users u ON cl.assigned_engineer_id = u.id
            LEFT JOIN service_reports sr ON sr.call_id = cl.id
            WHERE cl.machine_id = :mid
            ORDER BY cl.id DESC
        ");
        $stmt->execute(['mid' => $machineId]);
        return $stmt->fetchAll();
    }
}
