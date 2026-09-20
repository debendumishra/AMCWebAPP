<?php
/**
 * Spare Parts & Inventory Ledger Models
 */

defined('APP_INIT') or define('APP_INIT', true);

class Spare extends Model {
    protected string $table = 'spare_items';

    public function getDetailedList(): array {
        $stmt = $this->db->query("
            SELECT si.*, sc.name as category_name, sb.name as brand_name,
                   COALESCE(inv.quantity, 0) as current_stock,
                   COALESCE(inv.reserved_quantity, 0) as reserved_stock
            FROM spare_items si
            JOIN spare_categories sc ON si.category_id = sc.id
            LEFT JOIN spare_brands sb ON si.brand_id = sb.id
            LEFT JOIN inventory inv ON inv.spare_item_id = si.id
            ORDER BY si.id DESC
        ");
        return $stmt->fetchAll();
    }
}

class Inventory extends Model {
    protected string $table = 'inventory';

    /**
     * Record an immutable inventory transaction and update stock quantity
     */
    public function recordTransaction(
        int $spareItemId,
        string $txnType,
        int $qtyIn,
        int $qtyOut,
        float $unitPrice,
        ?string $refType = null,
        ?string $refId = null,
        ?string $remarks = null,
        string $warehouse = 'Main Store'
    ): bool {
        $shouldCommit = false;
        try {
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
                $shouldCommit = true;
            }

            // Fetch current stock with row lock
            $stmt = $this->db->prepare("SELECT quantity FROM inventory WHERE spare_item_id = :item_id AND warehouse_name = :wh FOR UPDATE");
            $stmt->execute(['item_id' => $spareItemId, 'wh' => $warehouse]);
            $row = $stmt->fetch();

            $currentStock = $row ? (int)$row['quantity'] : 0;
            $newBalance = $currentStock + $qtyIn - $qtyOut;

            if ($newBalance < 0 && $txnType !== TXN_ADJUSTMENT) {
                if ($shouldCommit && $this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                return false; // Prevent negative stock for regular issues
            }

            // Upsert inventory balance
            $upStmt = $this->db->prepare("
                INSERT INTO inventory (spare_item_id, warehouse_name, quantity, updated_at)
                VALUES (:item_id, :wh, :new_qty, NOW())
                ON DUPLICATE KEY UPDATE quantity = :new_qty2, updated_at = NOW()
            ");
            $upStmt->execute([
                'item_id'  => $spareItemId,
                'wh'       => $warehouse,
                'new_qty'  => $newBalance,
                'new_qty2' => $newBalance
            ]);

            // Insert immutable stock ledger entry
            $ledgerStmt = $this->db->prepare("
                INSERT INTO inventory_transactions 
                (spare_item_id, warehouse_name, transaction_type, reference_type, reference_id, quantity_in, quantity_out, unit_price, balance_after, remarks, created_by, created_at)
                VALUES (:item_id, :wh, :txn_type, :ref_type, :ref_id, :qty_in, :qty_out, :unit_price, :balance, :remarks, :user_id, NOW())
            ");
            $ledgerStmt->execute([
                'item_id'    => $spareItemId,
                'wh'         => $warehouse,
                'txn_type'   => $txnType,
                'ref_type'   => $refType,
                'ref_id'     => $refId,
                'qty_in'     => $qtyIn,
                'qty_out'    => $qtyOut,
                'unit_price' => $unitPrice,
                'balance'    => $newBalance,
                'remarks'    => $remarks,
                'user_id'    => Auth::id()
            ]);

            if ($shouldCommit && $this->db->inTransaction()) {
                $this->db->commit();
            }
            return true;
        } catch (Exception $e) {
            if ($shouldCommit && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Inventory transaction error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get stock ledger history
     */
    public function getLedgerHistory(int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT it.*, si.name as spare_name, si.sku, si.unit, u.name as user_name
            FROM inventory_transactions it
            JOIN spare_items si ON it.spare_item_id = si.id
            LEFT JOIN users u ON it.created_by = u.id
            ORDER BY it.id DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get pending engineer spare requisitions with product details & stock position
     */
    public function getRequisitions(): array {
        $stmt = $this->db->query("
            SELECT sr.*, cl.call_number, u.name as engineer_name, u.mobile as engineer_mobile,
                   c.company_name, m.asset_code, m.make, m.model,
                   ct.contract_number, cty.name as contract_type_name, cty.is_spares_covered
            FROM spare_requests sr
            JOIN calls cl ON sr.call_id = cl.id
            JOIN users u ON sr.engineer_id = u.id
            JOIN customers c ON cl.customer_id = c.id
            LEFT JOIN machines m ON sr.machine_id = m.id
            LEFT JOIN contracts ct ON cl.contract_id = ct.id
            LEFT JOIN contract_types cty ON ct.contract_type_id = cty.id
            ORDER BY sr.id DESC
        ");
        $requisitions = $stmt->fetchAll();

        if (empty($requisitions)) {
            return [];
        }

        // Fetch items and stock positions for all requisitions
        $reqIds = array_column($requisitions, 'id');
        $placeholders = implode(',', array_fill(0, count($reqIds), '?'));

        $itemStmt = $this->db->prepare("
            SELECT sri.*, si.name as spare_name, si.sku, si.capacity_spec, si.selling_price, si.min_stock_level,
                   sc.name as category_name, sb.name as brand_name,
                   COALESCE(inv.quantity, 0) as available_stock,
                   COALESCE(inv.reserved_quantity, 0) as reserved_stock
            FROM spare_request_items sri
            JOIN spare_items si ON sri.spare_item_id = si.id
            LEFT JOIN spare_categories sc ON si.category_id = sc.id
            LEFT JOIN spare_brands sb ON si.brand_id = sb.id
            LEFT JOIN inventory inv ON inv.spare_item_id = si.id
            WHERE sri.spare_request_id IN ($placeholders)
            ORDER BY sri.id ASC
        ");
        $itemStmt->execute($reqIds);
        $allItems = $itemStmt->fetchAll();

        // Group items by spare_request_id
        $itemsByReq = [];
        foreach ($allItems as $item) {
            $itemsByReq[$item['spare_request_id']][] = $item;
        }

        foreach ($requisitions as &$req) {
            $req['items'] = $itemsByReq[$req['id']] ?? [];
            $req['items_count'] = count($req['items']);
        }

        return $requisitions;
    }

    public function getRequisitionDetails(int $reqId): ?array {
        $stmt = $this->db->prepare("
            SELECT sr.*, cl.call_number, u.name as engineer_name, c.company_name,
                   ct.contract_number, cty.name as contract_type_name, cty.is_spares_covered
            FROM spare_requests sr
            JOIN calls cl ON sr.call_id = cl.id
            JOIN users u ON sr.engineer_id = u.id
            JOIN customers c ON cl.customer_id = c.id
            LEFT JOIN contracts ct ON cl.contract_id = ct.id
            LEFT JOIN contract_types cty ON ct.contract_type_id = cty.id
            WHERE sr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $reqId]);
        $req = $stmt->fetch();
        if (!$req) return null;

        $iStmt = $this->db->prepare("
            SELECT sri.*, si.name as spare_name, si.sku, si.selling_price, si.capacity_spec, si.min_stock_level,
                   COALESCE(inv.quantity, 0) as available_stock,
                   COALESCE(inv.reserved_quantity, 0) as reserved_stock
            FROM spare_request_items sri
            JOIN spare_items si ON sri.spare_item_id = si.id
            LEFT JOIN inventory inv ON inv.spare_item_id = si.id
            WHERE sri.spare_request_id = :rid
        ");
        $iStmt->execute(['rid' => $reqId]);
        $req['items'] = $iStmt->fetchAll();

        return $req;
    }
}
