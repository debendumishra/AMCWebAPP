<?php
/**
 * Purchase & Supplier Models
 */

defined('APP_INIT') or define('APP_INIT', true);

class Supplier extends Model {
    protected string $table = 'suppliers';
}

class Purchase extends Model {
    protected string $table = 'purchases';

    public function getDetailedList(): array {
        $stmt = $this->db->query("
            SELECT p.*, s.name as supplier_name, s.mobile as supplier_mobile, s.gstin as supplier_gstin,
                   (SELECT COUNT(*) FROM purchase_items pi WHERE pi.purchase_id = p.id) as items_count
            FROM purchases p
            JOIN suppliers s ON p.supplier_id = s.id
            ORDER BY p.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getFullPurchase(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, s.name as supplier_name, s.contact_person, s.mobile as supplier_mobile,
                   s.email as supplier_email, s.gstin as supplier_gstin, s.address as supplier_address
            FROM purchases p
            JOIN suppliers s ON p.supplier_id = s.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $pur = $stmt->fetch();
        if (!$pur) return null;

        $iStmt = $this->db->prepare("
            SELECT pi.*, si.name as spare_name, si.sku, si.unit
            FROM purchase_items pi
            JOIN spare_items si ON pi.spare_item_id = si.id
            WHERE pi.purchase_id = :pid
        ");
        $iStmt->execute(['pid' => $id]);
        $pur['items'] = $iStmt->fetchAll();

        return $pur;
    }
}
