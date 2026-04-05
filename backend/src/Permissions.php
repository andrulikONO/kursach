<?php
declare(strict_types=1);

namespace Kursach;

/**
 * Роли: guest (не вошёл), user, admin (из БД).
 * Гость не хранится в таблице roles.
 */
final class Permissions
{
  public static function rolePermissions(): array
  {
    return [
      'guest' => ['products.read'],
      'user' => ['products.read', 'products.create', 'profile.read'],
      'admin' => [
        'products.read',
        'products.create',
        'profile.read',
        'products.delete',
        'users.block',
        'users.list',
      ],
    ];
  }

  public static function can(AuthContext $auth, string $permission): bool
  {
    $map = self::rolePermissions();
    foreach ($auth->roles as $role) {
      $role = strtolower((string)$role);
      $perms = $map[$role] ?? [];
      if (in_array($permission, $perms, true)) {
        return true;
      }
    }
    return false;
  }

  public static function require(AuthContext $auth, string $permission): void
  {
    if (!self::can($auth, $permission)) {
      Response::json(['error' => 'Forbidden', 'permission' => $permission, 'roles' => $auth->roles], 403);
    }
  }
}
