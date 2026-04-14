<?php
declare(strict_types=1);
namespace Kursach;

use PDO;

final class AdminController
{
    public static function listUsers(AuthContext $auth): void
    {
        Permissions::require($auth, 'users.list');
        $pdo = Db::pdo();
        $items = UserRepository::listUsers($pdo);
        
        foreach ($items as &$row) {
            $row['is_blocked'] = UserRepository::boolish($row['is_blocked']);
            $row['roles'] = $row['role_codes'] !== '' && $row['role_codes'] !== null
                ? explode(',', (string)$row['role_codes'])
                : [];
            unset($row['role_codes']);
        }
        unset($row);
        
        Response::json(['items' => $items]);
    }

    public static function blockUser(AuthContext $auth, int $userId): void
    {
        Permissions::require($auth, 'users.block');
        if ($auth->userId !== null && $auth->userId === $userId) {
            Response::json(['error' => 'Нельзя заблокировать самого себя'], 400);
        }
        $pdo = Db::pdo();
        $st = $pdo->prepare('SELECT 1 FROM users WHERE id = :id LIMIT 1');
        $st->execute([':id' => $userId]);
        if (!$st->fetchColumn()) {
            Response::json(['error' => 'Пользователь не найден'], 404);
        }
        UserRepository::setBlocked($pdo, $userId, true);
        Response::json(['ok' => true, 'userId' => $userId, 'is_blocked' => true]);
    }

    public static function unblockUser(AuthContext $auth, int $userId): void
    {
        Permissions::require($auth, 'users.block');
        $pdo = Db::pdo();
        $st = $pdo->prepare('SELECT 1 FROM users WHERE id = :id LIMIT 1');
        $st->execute([':id' => $userId]);
        if (!$st->fetchColumn()) {
            Response::json(['error' => 'Пользователь не найден'], 404);
        }
        UserRepository::setBlocked($pdo, $userId, false);
        Response::json(['ok' => true, 'userId' => $userId, 'is_blocked' => false]);
    }

    public static function assignRole(AuthContext $auth, int $userId): void
    {
        Permissions::require($auth, 'users.roles');
        
        $body = Response::readJsonBody();
        $roleCode = trim((string)($body['roleCode'] ?? ''));
        
        if ($roleCode === '') {
            Response::json(['error' => 'Роль не указана'], 422);
        }
        
        $pdo = Db::pdo();
        
        $st = $pdo->prepare('SELECT id FROM roles WHERE code = :code LIMIT 1');
        $st->execute([':code' => $roleCode]);
        if (!$st->fetchColumn()) {
            Response::json(['error' => 'Роль не найдена'], 404);
        }
        
        $st = $pdo->prepare('SELECT 1 FROM users WHERE id = :id LIMIT 1');
        $st->execute([':id' => $userId]);
        if (!$st->fetchColumn()) {
            Response::json(['error' => 'Пользователь не найден'], 404);
        }
        
        UserRepository::assignRoleByCode($pdo, $userId, $roleCode);
        
        Response::json(['ok' => true, 'userId' => $userId, 'role' => $roleCode]);
    }

    public static function removeRole(AuthContext $auth, int $userId, string $roleCode): void
    {
        Permissions::require($auth, 'users.roles');
        
        $pdo = Db::pdo();
        
        $st = $pdo->prepare('SELECT id FROM roles WHERE code = :code LIMIT 1');
        $st->execute([':code' => $roleCode]);
        if (!$st->fetchColumn()) {
            Response::json(['error' => 'Роль не найдена'], 404);
        }
        
        $st = $pdo->prepare('
            DELETE FROM user_roles 
            WHERE user_id = :user_id 
            AND role_id = (SELECT id FROM roles WHERE code = :code)
        ');
        $st->execute([':user_id' => $userId, ':code' => $roleCode]);
        
        Response::json(['ok' => true, 'userId' => $userId, 'role' => $roleCode]);
    }

    public static function listRoles(AuthContext $auth): void
    {
        Permissions::require($auth, 'users.roles');
        
        $pdo = Db::pdo();
        $st = $pdo->query('SELECT code, name, description FROM roles ORDER BY id');
        $roles = $st->fetchAll(PDO::FETCH_ASSOC);
        
        Response::json(['roles' => $roles]);
    }
}