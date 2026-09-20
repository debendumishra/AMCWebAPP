<?php
/**
 * AMC Contract Model
 */

defined('APP_INIT') or define('APP_INIT', true);

class Contract extends Model {
    protected string $table = 'contracts';

    public function getDetailedList(): array {
        $stmt = $this->db->query("
            SELECT ct.*, c.company_name, c.customer_code, c.mobile as customer_mobile,
                   cty.name as contract_type_name, cty.code as contract_type_code,
                   cty.is_spares_covered, cty.is_labour_covered,
                   (SELECT COUNT(*) FROM contract_machines cm WHERE cm.contract_id = ct.id AND cm.status = 'ACTIVE') as machines_count,
                   DATEDIFF(ct.end_date, CURDATE()) as days_to_expire
            FROM contracts ct
            JOIN customers c ON ct.customer_id = c.id
            JOIN contract_types cty ON ct.contract_type_id = cty.id
            ORDER BY ct.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getContractMachines(int $contractId): array {
        $stmt = $this->db->prepare("
            SELECT cm.*, m.asset_code, m.make, m.model, m.serial_number, m.department,
                   cl.location_name, at.name as asset_type_name
            FROM contract_machines cm
            JOIN machines m ON cm.machine_id = m.id
            JOIN customer_locations cl ON m.location_id = cl.id
            JOIN asset_types at ON m.asset_type_id = at.id
            WHERE cm.contract_id = :cid
            ORDER BY m.id DESC
        ");
        $stmt->execute(['cid' => $contractId]);
        return $stmt->fetchAll();
    }

    public function getExpiringContracts(int $days = 30): array {
        $stmt = $this->db->prepare("
            SELECT ct.*, c.company_name, c.mobile, cty.name as contract_type_name,
                   DATEDIFF(ct.end_date, CURDATE()) as days_remaining
            FROM contracts ct
            JOIN customers c ON ct.customer_id = c.id
            JOIN contract_types cty ON ct.contract_type_id = cty.id
            WHERE ct.status = 'ACTIVE' AND ct.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY ct.end_date ASC
        ");
        $stmt->execute(['days' => $days]);
        return $stmt->fetchAll();
    }
}
