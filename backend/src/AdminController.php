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
}
