<?php
declare(strict_types=1);

namespace Kursach;

final class AuthContext
{
  public function __construct(
    public readonly ?int $userId,
    /** @var string[] */
    public readonly array $roles,
    public readonly bool $isBlocked = false
  ) {}

  public function isGuest(): bool
  {
    return $this->userId === null;
  }

  public function hasRole(string $code): bool
  {
    $c = strtolower($code);
    foreach ($this->roles as $r) {
      if (strtolower($r) === $c) {
        return true;
      }
    }
    return false;
  }
}
