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
        
        // ✅ Главный админ (ID=1) не может быть заблокирован
        if ($userId === 1) {
            Response::json(['error' => 'Главный администратор не может быть заблокирован'], 403);
        }
        
        // ✅ Нельзя заблокировать самого себя
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
        
        // ✅ Главный админ не может быть разблокирован (он всегда активен)
        if ($userId === 1) {
            Response::json(['error' => 'Главный администратор всегда активен'], 403);
        }
        
        $pdo = Db::pdo();
        $st = $pdo->prepare('SELECT 1 FROM users WHERE id = :id LIMIT 1');
        $st->execute([':id' => $userId]);
        if (!$st->fetchColumn()) {
            Response::json(['error' => 'Пользователь не найден'], 404);
        }
        
        UserRepository::setBlocked($pdo, $userId, false);
        Response::json(['ok' => true, 'userId' => $userId, 'is_blocked' => false]);
    }

    // ✅ НОВОЕ: Назначить роль пользователю
    public static function assignRole(AuthContext $auth, int $userId): void
    {
        Permissions::require($auth, 'users.roles');
        
        $body = Response::readJsonBody();
        $roleCode = trim((string)($body['roleCode'] ?? ''));
        
        if ($roleCode === '') {
            Response::json(['error' => 'Роль не указана'], 422);
        }
        
        // ✅ Роль admin может быть назначена только пользователю ID=1
        if ($roleCode === 'admin' && $userId !== 1) {
            Response::json(['error' => 'Роль администратора может быть назначена только главному админу'], 403);
        }
        
        // ✅ Нельзя назначать роли самому себе
        if ($auth->userId !== null && $auth->userId === $userId) {
            Response::json(['error' => 'Нельзя назначать роли самому себе'], 400);
        }
        
        $pdo = Db::pdo();
        
        // Проверяем существование роли
        $st = $pdo->prepare('SELECT id FROM roles WHERE code = :code LIMIT 1');
        $st->execute([':code' => $roleCode]);
        if (!$st->fetchColumn()) {
            Response::json(['error' => 'Роль не найдена'], 404);
        }
        
        // Проверяем существование пользователя
        $st = $pdo->prepare('SELECT 1 FROM users WHERE id = :id LIMIT 1');
        $st->execute([':id' => $userId]);
        if (!$st->fetchColumn()) {
            Response::json(['error' => 'Пользователь не найден'], 404);
        }
        
        // Назначаем роль
        UserRepository::assignRoleByCode($pdo, $userId, $roleCode);
        UserRepository::updatePrimaryRole($pdo, $userId);
        $snap = UserRepository::getAuthSnapshot($pdo, $userId);
        Realtime::push($pdo, $userId, 'role_changed', [
            'roles' => $snap['roles'] ?? ['user'],
            'role' => ($snap['roles'][0] ?? 'user'),
        ]);

        Response::json(['ok' => true, 'userId' => $userId, 'role' => $roleCode]);
    }

    // ✅ НОВОЕ: Удалить роль у пользователя
    public static function removeRole(AuthContext $auth, int $userId, string $roleCode): void
    {
        Permissions::require($auth, 'users.roles');
        
        // ✅ Нельзя удалить роль admin у пользователя ID=1
        if ($roleCode === 'admin' && $userId === 1) {
            Response::json(['error' => 'Нельзя удалить роль администратора у главного админа'], 403);
        }
        
        // ✅ Нельзя удалять роли самому себе
        if ($auth->userId !== null && $auth->userId === $userId) {
            Response::json(['error' => 'Нельзя удалять роли самому себе'], 400);
        }
        
        $pdo = Db::pdo();
        
        // Проверяем существование роли
        $st = $pdo->prepare('SELECT id FROM roles WHERE code = :code LIMIT 1');
        $st->execute([':code' => $roleCode]);
        if (!$st->fetchColumn()) {
            Response::json(['error' => 'Роль не найдена'], 404);
        }
        
        // Удаляем связь
        $st = $pdo->prepare('
            DELETE FROM user_roles 
            WHERE user_id = :user_id 
            AND role_id = (SELECT id FROM roles WHERE code = :code)
        ');
        $st->execute([':user_id' => $userId, ':code' => $roleCode]);
        UserRepository::updatePrimaryRole($pdo, $userId);
        $snap = UserRepository::getAuthSnapshot($pdo, $userId);
        Realtime::push($pdo, $userId, 'role_changed', [
            'roles' => $snap['roles'] ?? ['user'],
            'role' => ($snap['roles'][0] ?? 'user'),
        ]);

        Response::json(['ok' => true, 'userId' => $userId, 'role' => $roleCode]);
    }

    // ✅ НОВОЕ: Получить список всех ролей
    public static function listRoles(AuthContext $auth): void
    {
        Permissions::require($auth, 'users.roles');
        
        $pdo = Db::pdo();
        $st = $pdo->query('SELECT code, name, description FROM roles ORDER BY id');
        $roles = $st->fetchAll(PDO::FETCH_ASSOC);
        
        Response::json(['roles' => $roles]);
    }

    public static function deleteUser(AuthContext $auth, int $userId): void
    {
        Permissions::require($auth, 'users.block');
        if ($userId === 1 || ($auth->userId !== null && $auth->userId === $userId)) {
            Response::json(['error' => 'Forbidden'], 403);
        }

        $pdo = Db::pdo();
        $token = 'Demo user:' . $userId;
        $rt = $pdo->prepare('INSERT INTO revoked_tokens (token, user_id) VALUES (:t, :uid) ON CONFLICT (token) DO NOTHING');
        $rt->execute([':t' => $token, ':uid' => $userId]);
        Realtime::push($pdo, $userId, 'account_deleted', ['userId' => $userId]);

        $st = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $st->execute([':id' => $userId]);
        Response::json(['ok' => true, 'userId' => $userId]);
    }
}
