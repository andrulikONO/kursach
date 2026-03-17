<?php
declare(strict_types=1);

namespace Kursach;

final class AuthContext
{
  public function __construct(
    public readonly ?int $userId,
    /** @var string[] */
    public readonly array $roles
  ) {}

  public function isGuest(): bool
  {
    return $this->userId === null;
  }
}

final class Auth
{
  /**
   * Скелет авторизации.
   * Сейчас поддерживает два режима:
   * - гость (нет Authorization)
   * - "demo" через заголовок: Authorization: Demo user:2 roles:seller,customer
   *
   * Позже можно заменить на JWT/сессии.
   */
  public static function fromRequest(): AuthContext
  {
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $h = is_string($h) ? trim($h) : '';
    if ($h === '' || stripos($h, 'Demo ') !== 0) {
      return new AuthContext(null, ['guest']);
    }

    $payload = trim(substr($h, 5));
    $parts = preg_split('/\s+/', $payload) ?: [];

    $userId = null;
    $roles = [];
    foreach ($parts as $p) {
      if (str_starts_with($p, 'user:')) {
        $userId = (int)substr($p, 5);
      } elseif (str_starts_with($p, 'roles:')) {
        $raw = substr($p, 6);
        $roles = array_values(array_filter(array_map('trim', explode(',', $raw))));
      }
    }

    if (!$roles) $roles = ['user'];
    return new AuthContext($userId ?: null, $roles);
  }
}

