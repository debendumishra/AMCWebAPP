<?php
/**
 * Invoicing, GST & Payment Models
 */

defined('APP_INIT') or define('APP_INIT', true);

class Invoice extends Model {
    protected string $table = 'invoices';

    public function getDetailedList(): array {
        $stmt = $this->db->query("
            SELECT inv.*, c.company_name, c.customer_code, c.gstin as customer_gstin,
                   ct.contract_number, cl.call_number
            FROM invoices inv
            JOIN customers c ON inv.customer_id = c.id
            LEFT JOIN contracts ct ON inv.contract_id = ct.id
            LEFT JOIN calls cl ON inv.call_id = cl.id
            ORDER BY inv.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getFullInvoice(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT inv.*, c.company_name, c.customer_code, c.contact_person, c.mobile, c.email,
                   c.billing_address, c.city, c.state, c.pincode, c.gstin as customer_gstin, c.pan as customer_pan,
                   ct.contract_number, ct.title as contract_title,
                   cl.call_number
            FROM invoices inv
            JOIN customers c ON inv.customer_id = c.id
            LEFT JOIN contracts ct ON inv.contract_id = ct.id
            LEFT JOIN calls cl ON inv.call_id = cl.id
            WHERE inv.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $inv = $stmt->fetch();
        if (!$inv) return null;

        // Items
        $iStmt = $this->db->prepare("SELECT * FROM invoice_items WHERE invoice_id = :id");
        $iStmt->execute(['id' => $id]);
        $inv['items'] = $iStmt->fetchAll();

        // Payments
        $pStmt = $this->db->prepare("SELECT * FROM payments WHERE invoice_id = :id ORDER BY id DESC");
        $pStmt->execute(['id' => $id]);
        $inv['payments'] = $pStmt->fetchAll();

        return $inv;
    }
}
