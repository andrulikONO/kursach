<?php
declare(strict_types=1);

namespace Kursach;

/**
 * Клиент передаёт только идентификатор: Authorization: Demo user:123
 * Роли и блокировка всегда читаются из БД (нельзя подделать admin в заголовке).
 */
final class Auth
{
  public static function readAuthorizationHeader(): string
  {
    $candidates = [
      $_SERVER['HTTP_AUTHORIZATION'] ?? null,
      $_SERVER['HTTP_X_AUTHORIZATION'] ?? null,
      $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null,
      $_SERVER['Authorization'] ?? null,
    ];

    foreach ($candidates as $value) {
      if (is_string($value) && trim($value) !== '') {
        return trim($value);
      }
    }

    if (function_exists('getallheaders')) {
      try {
        $headers = getallheaders();
        if (is_array($headers)) {
          foreach ($headers as $key => $value) {
            if (strcasecmp((string)$key, 'Authorization') === 0 && is_string($value) && trim($value) !== '') {
              return trim($value);
            }
          }
        }
      } catch (\Throwable) {
        // Игнорируем и обрабатываем как гостя ниже.
      }
    }

    return '';
  }

  public static function fromRequest(): AuthContext
  {
    $h = self::readAuthorizationHeader();

    if ($h === '' || !preg_match('/^Demo\s+user:(\d+)\b/i', $h, $m)) {
      return new AuthContext(null, ['guest'], false);
    }

    $userId = (int)$m[1];
    if ($userId <= 0) {
      return new AuthContext(null, ['guest'], false);
    }

    try {
      $pdo = Db::pdo();
      $revoked = $pdo->prepare('SELECT 1 FROM revoked_tokens WHERE token = :t LIMIT 1');
      $revoked->execute([':t' => $h]);
      if ($revoked->fetchColumn()) {
        return new AuthContext(null, ['guest'], false);
      }

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
