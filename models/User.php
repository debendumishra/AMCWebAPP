<?php
/**
 * User Model
 */

defined('APP_INIT') or define('APP_INIT', true);

class User extends Model {
    protected string $table = 'users';

    /**
     * Find user by email with role information
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name, r.id as role_id
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = :email AND u.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Get all technicians / engineers
     */
    public function getEngineers(): array {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email, u.mobile, u.employee_code, u.skills,
                   (SELECT COUNT(*) FROM calls c WHERE c.assigned_engineer_id = u.id AND c.status NOT IN ('RESOLVED', 'CLOSED', 'CANCELLED')) as active_calls_count
            FROM users u
            WHERE u.role_id = :role_engineer AND u.is_active = 1 AND u.deleted_at IS NULL
            ORDER BY u.name ASC
        ");
        $stmt->execute(['role_engineer' => ROLE_ENGINEER]);
        return $stmt->fetchAll();
    }

    /**
     * Record login attempt
     */
    public function recordLoginAttempt(?int $userId, string $email, string $status): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_history (user_id, email_attempted, ip_address, user_agent, status, attempted_at)
                VALUES (:user_id, :email, :ip, :ua, :status, NOW())
            ");
            $stmt->execute([
                'user_id' => $userId,
                'email'   => $email,
                'ip'      => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'status'  => $status
            ]);
        } catch (Exception $e) {
            // Ignore login history logging failures
        }
    }
}
