<?php
/**
 * Customer & Location Models
 */

defined('APP_INIT') or define('APP_INIT', true);

class Customer extends Model {
    protected string $table = 'customers';

    /**
     * Get customers with counts of locations, machines and active contracts
     */
    public function getSummaryList(): array {
        $stmt = $this->db->query("
            SELECT c.*,
                   (SELECT COUNT(*) FROM customer_locations cl WHERE cl.customer_id = c.id) as locations_count,
                   (SELECT COUNT(*) FROM machines m WHERE m.customer_id = c.id AND m.deleted_at IS NULL) as machines_count,
                   (SELECT COUNT(*) FROM contracts ct WHERE ct.customer_id = c.id AND ct.status = 'ACTIVE') as active_contracts_count,
                   (SELECT COUNT(*) FROM calls cll WHERE cll.customer_id = c.id AND cll.status NOT IN ('RESOLVED', 'CLOSED', 'CANCELLED')) as open_calls_count
            FROM customers c
            WHERE c.deleted_at IS NULL
            ORDER BY c.id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Search customers across name, code, phone, email, and GSTIN
     */
    public function search(string $query): array {
        $q = "%{$query}%";
        $stmt = $this->db->prepare("
            SELECT id, customer_code, company_name, contact_person, mobile, email, city, status
            FROM customers
            WHERE deleted_at IS NULL AND (
                company_name LIKE :q1 OR customer_code LIKE :q2 OR mobile LIKE :q3 OR email LIKE :q4 OR gstin LIKE :q5
            )
            LIMIT 10
        ");
        $stmt->execute(['q1' => $q, 'q2' => $q, 'q3' => $q, 'q4' => $q, 'q5' => $q]);
        return $stmt->fetchAll();
    }
}

class CustomerLocation extends Model {
    protected string $table = 'customer_locations';

    public function getByCustomerId(int $customerId): array {
        $stmt = $this->db->prepare("
            SELECT cl.*,
                   (SELECT COUNT(*) FROM machines m WHERE m.location_id = cl.id AND m.deleted_at IS NULL) as machines_count
            FROM customer_locations cl
            WHERE cl.customer_id = :cid
            ORDER BY cl.id ASC
        ");
        $stmt->execute(['cid' => $customerId]);
        return $stmt->fetchAll();
    }
}
