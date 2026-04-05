<?php
declare(strict_types=1);

namespace Kursach;

/**
 * Клиент передаёт только идентификатор: Authorization: Demo user:123
 * Роли и блокировка всегда читаются из БД (нельзя подделать admin в заголовке).
 */
final class Auth
{
  public static function fromRequest(): AuthContext
  {
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $h = is_string($h) ? trim($h) : '';

    if ($h === '' || !preg_match('/^Demo\s+user:(\d+)\b/i', $h, $m)) {
      return new AuthContext(null, ['guest'], false);
    }

    $userId = (int)$m[1];
    if ($userId <= 0) {
      return new AuthContext(null, ['guest'], false);
    }

    try {
      $pdo = Db::pdo();
      $snap = UserRepository::getAuthSnapshot($pdo, $userId);
      if ($snap === null) {
        return new AuthContext(null, ['guest'], false);
      }
      return new AuthContext($userId, $snap['roles'], $snap['is_blocked']);
    } catch (\Throwable) {
      return new AuthContext(null, ['guest'], false);
    }
  }
}
