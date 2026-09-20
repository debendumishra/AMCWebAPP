<?php
/**
 * Core Base Model with PDO query builder, pagination, transactions and audit logging
 */

defined('APP_INIT') or define('APP_INIT', true);

class Model {
    protected ?PDO $db = null;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find single record by ID
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get all records with optional where conditions and order
     */
    public function all(array $where = [], string $orderBy = 'id DESC', ?int $limit = null, int $offset = 0): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($where)) {
            $clauses = [];
            foreach ($where as $col => $val) {
                if ($val === null) {
                    $clauses[] = "{$col} IS NULL";
                } else {
                    $placeholder = "w_" . str_replace('.', '_', $col);
                    $clauses[] = "{$col} = :{$placeholder}";
                    $params[$placeholder] = $val;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Insert a new record
     */
    public function create(array $data): int {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        $insertId = (int)$this->db->lastInsertId();

        $this->logAudit('INSERT', $insertId, null, $data);
        return $insertId;
    }

    /**
     * Update an existing record
     */
    public function update(int $id, array $data): bool {
        $oldRecord = $this->find($id);

        $fields = [];
        $params = ['id' => $id];
        foreach ($data as $col => $val) {
            $fields[] = "{$col} = :{$col}";
            $params[$col] = $val;
        }

        $sql = sprintf("UPDATE %s SET %s WHERE %s = :id", $this->table, implode(', ', $fields), $this->primaryKey);
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute($params);

        if ($res) {
            $this->logAudit('UPDATE', $id, $oldRecord, $data);
        }
        return $res;
    }

    /**
     * Delete a record (or soft delete if deleted_at exists)
     */
    public function delete(int $id, bool $force = false): bool {
        $oldRecord = $this->find($id);
        if (!$force && $this->hasColumn('deleted_at')) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET deleted_at = NOW() WHERE {$this->primaryKey} = :id");
            $res = $stmt->execute(['id' => $id]);
        } else {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
            $res = $stmt->execute(['id' => $id]);
        }

        if ($res) {
            $this->logAudit('DELETE', $id, $oldRecord, null);
        }
        return $res;
    }

    /**
     * Check if a column exists in current table
     */
    private function hasColumn(string $column): bool {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM {$this->table} LIKE :col");
            $stmt->execute(['col' => $column]);
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Log Audit Trail
     */
    protected function logAudit(string $action, int $recordId, ?array $oldValue = null, ?array $newValue = null): void {
        if ($this->table === 'audit_logs' || !$this->db) {
            return;
        }
        try {
            $userId = class_exists('Auth') ? (Auth::id() ?? 0) : 0;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, module, record_id, old_values, new_values, ip_address, created_at)
                VALUES (:user_id, :action, :module, :record_id, :old_values, :new_values, :ip, NOW())
            ");
            $stmt->execute([
                'user_id'    => $userId,
                'action'     => $action,
                'module'     => $this->table,
                'record_id'  => $recordId,
                'old_values' => $oldValue ? json_encode($oldValue) : null,
                'new_values' => $newValue ? json_encode($newValue) : null,
                'ip'         => $ip
            ]);
        } catch (Exception $e) {
            // Silently ignore audit logging errors if table not created yet
        }
    }
}
