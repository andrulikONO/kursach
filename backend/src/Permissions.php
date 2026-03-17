<?php
declare(strict_types=1);

namespace Kursach;

final class Permissions
{
  /**
   * Матрица доступа "роль -> разрешения".
   * Легко расширяется добавлением ролей/разрешений.
   *
   * Разрешения:
   * - products.read: просмотр каталога и карточки
   * - products.create: создание объявления
   * - products.moderate: модерация (на будущее)
   * - admin.all: полный доступ (на будущее)
   */
  public static function rolePermissions(): array
  {
    return [
      'guest' => ['products.read'],
      'customer' => ['products.read'],
      'seller' => ['products.read', 'products.create'],
      'moderator' => ['products.read', 'products.moderate'],
      'admin' => ['admin.all'],
    ];
  }

  public static function can(AuthContext $auth, string $permission): bool
  {
    $map = self::rolePermissions();
    foreach ($auth->roles as $role) {
      $role = strtolower($role);
      $perms = $map[$role] ?? [];
      if (in_array('admin.all', $perms, true)) return true;
      if (in_array($permission, $perms, true)) return true;
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

