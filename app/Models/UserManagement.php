<?php

namespace App\Models;

use App\Core\Database;

class UserManagement
{
    private string $usersTable = 'users';
    private string $rolesTable = 'roles';
    private string $rolePermissionTable = 'role_permission';
    private string $permissionsTable = 'permissions';

    public function all(?string $search = null): array
    {
        $db = Database::connect();

        $sql = "
            SELECT
                u.id,
                u.name,
                u.email,
                u.role_id,
                r.name AS role_name,
                GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ', ') AS role_permissions
            FROM {$this->usersTable} u
            LEFT JOIN {$this->rolesTable} r ON r.id = u.role_id
            LEFT JOIN {$this->rolePermissionTable} rp ON rp.role_id = r.id
            LEFT JOIN {$this->permissionsTable} p ON p.id = rp.permission_id
        ";

        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= " WHERE u.name LIKE :search OR u.email LIKE :search OR r.name LIKE :search ";
            $params['search'] = "%{$search}%";
        }

        $sql .= "
            GROUP BY u.id, u.name, u.email, u.role_id, r.name
            ORDER BY u.id DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT id, name, email, role_id FROM {$this->usersTable} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT id, name, email, role_id FROM {$this->usersTable} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByEmailAndId(string $email, int $id): ?array
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT id FROM {$this->usersTable} WHERE email = :email AND id != :id LIMIT 1");
        $stmt->execute([
            'email' => $email,
            'id' => $id,
        ]);

        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(array $data): bool
    {
        $db = Database::connect();

        $stmt = $db->prepare("INSERT INTO {$this->usersTable} (name, email, password, role_id) VALUES (:name, :email, :password, :role_id)");

        return $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $data['role_id'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $db = Database::connect();

        $sql = "UPDATE {$this->usersTable} SET name = :name, email = :email, role_id = :role_id";
        $params = [
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
        ];

        if (!empty($data['password'])) {
            $sql .= ', password = :password';
            $params['password'] = $data['password'];
        }

        $sql .= ' WHERE id = :id';

        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $db = Database::connect();

        $stmt = $db->prepare("DELETE FROM {$this->usersTable} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function rolesWithPermissions(): array
    {
        $db = Database::connect();

        $stmt = $db->query(" 
            SELECT
                r.id,
                r.name,
                GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ', ') AS permissions
            FROM {$this->rolesTable} r
            LEFT JOIN {$this->rolePermissionTable} rp ON rp.role_id = r.id
            LEFT JOIN {$this->permissionsTable} p ON p.id = rp.permission_id
            GROUP BY r.id, r.name
            ORDER BY r.name ASC
        ");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function roleExists(int $roleId): bool
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT id FROM {$this->rolesTable} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $roleId]);

        return (bool) $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function roles(): array
    {
        $db = Database::connect();

        $stmt = $db->query("SELECT id, name FROM {$this->rolesTable} ORDER BY name ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function allPermissions(): array
    {
        $db = Database::connect();

        $stmt = $db->query("SELECT id, name FROM {$this->permissionsTable} ORDER BY name ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function permissionIdsByRoleId(int $roleId): array
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT permission_id FROM {$this->rolePermissionTable} WHERE role_id = :role_id");
        $stmt->execute(['role_id' => $roleId]);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function syncRolePermissions(int $roleId, array $permissionIds): void
    {
        $db = Database::connect();
        $db->beginTransaction();

        try {
            $deleteStmt = $db->prepare("DELETE FROM {$this->rolePermissionTable} WHERE role_id = :role_id");
            $deleteStmt->execute(['role_id' => $roleId]);

            $permissionIds = array_values(array_unique(array_filter(array_map('intval', $permissionIds), static fn($id) => $id > 0)));

            if (!empty($permissionIds)) {
                $insertStmt = $db->prepare("INSERT INTO {$this->rolePermissionTable} (role_id, permission_id) VALUES (:role_id, :permission_id)");

                foreach ($permissionIds as $permissionId) {
                    $insertStmt->execute([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ]);
                }
            }

            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $exception;
        }
    }

    public function permissionsByRoleId(int $roleId): array
    {
        $db = Database::connect();

        $stmt = $db->prepare(" 
            SELECT p.name
            FROM {$this->permissionsTable} p
            INNER JOIN {$this->rolePermissionTable} rp ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id
            ORDER BY p.name ASC
        ");
        $stmt->execute(['role_id' => $roleId]);

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
