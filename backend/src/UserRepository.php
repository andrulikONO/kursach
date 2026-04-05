<?php
declare(strict_types=1);

namespace Kursach;

use PDO;

final class UserRepository
{
  public static function boolish(mixed $v): bool
  {
    return $v === true || $v === 1 || $v === '1' || $v === 't' || $v === 'true';
  }

  public static function isLoginTaken(PDO $pdo, string $login): bool
  {
    $st = $pdo->prepare('SELECT 1 FROM users WHERE LOWER(login) = LOWER(:l) LIMIT 1');
    $st->execute([':l' => $login]);
    return (bool)$st->fetchColumn();
  }

  public static function isEmailTaken(PDO $pdo, string $email): bool
  {
    $st = $pdo->prepare('SELECT 1 FROM users WHERE LOWER(email) = LOWER(:e) LIMIT 1');
    $st->execute([':e' => $email]);
    return (bool)$st->fetchColumn();
  }

  /**
   * @return array{roles: string[], is_blocked: bool}|null
   */
  public static function getAuthSnapshot(PDO $pdo, int $userId): ?array
  {
    $st = $pdo->prepare('SELECT is_blocked FROM users WHERE id = :id LIMIT 1');
    $st->execute([':id' => $userId]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
      return null;
    }
    $blocked = self::boolish($row['is_blocked']);

    $st2 = $pdo->prepare(
      'SELECT r.code FROM user_roles ur
       INNER JOIN roles r ON r.id = ur.role_id
       WHERE ur.user_id = :id'
    );
    $st2->execute([':id' => $userId]);
    $codes = $st2->fetchAll(PDO::FETCH_COLUMN);
    $roles = array_values(array_map('strval', $codes ?: []));
    if ($roles === []) {
      $roles = ['user'];
    }

    return ['roles' => $roles, 'is_blocked' => $blocked];
  }

  /**
   * @return array{id: int, password_hash: string, is_blocked: bool}|null
   */
  public static function findByLoginOrEmail(PDO $pdo, string $identifier): ?array
  {
    $q = trim($identifier);
    if ($q === '') {
      return null;
    }
    $st = $pdo->prepare(
      'SELECT id, password_hash, is_blocked FROM users
       WHERE LOWER(login) = LOWER(:q) OR LOWER(email) = LOWER(:q2)
       LIMIT 1'
    );
    $st->execute([':q' => $q, ':q2' => $q]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
      return null;
    }
    return [
      'id' => (int)$row['id'],
      'password_hash' => (string)$row['password_hash'],
      'is_blocked' => self::boolish($row['is_blocked']),
    ];
  }

  /**
   * @return int new user id
   */
  public static function create(
    PDO $pdo,
    string $firstName,
    string $lastName,
    string $email,
    string $login,
    string $passwordHash,
    bool $ageConfirmed,
    string $gender,
    bool $acceptedRules,
    string $phone
  ): int {
    $st = $pdo->prepare(
      'INSERT INTO users (
        first_name, last_name, email, login, password_hash, phone,
        age_confirmed, gender, accepted_rules, theme_preference, is_blocked
      ) VALUES (
        :fn, :ln, :em, :lg, :ph, :phone,
        :age, :gender, :rules, :theme, FALSE
      )'
    );
    $st->execute([
      ':fn' => $firstName,
      ':ln' => $lastName,
      ':em' => $email,
      ':lg' => $login,
      ':ph' => $passwordHash,
      ':phone' => $phone,
      ':age' => $ageConfirmed,
      ':gender' => $gender,
      ':rules' => $acceptedRules,
      ':theme' => 'light',
    ]);
    return (int)$pdo->lastInsertId('users_id_seq');
  }

  public static function assignRoleByCode(PDO $pdo, int $userId, string $roleCode): void
  {
    $ins = $pdo->prepare(
      'INSERT INTO user_roles (user_id, role_id)
       SELECT :u, r.id FROM roles r WHERE r.code = :c LIMIT 1
       ON CONFLICT (user_id, role_id) DO NOTHING'
    );
    $ins->execute([':u' => $userId, ':c' => $roleCode]);
  }

  public static function setBlocked(PDO $pdo, int $userId, bool $blocked): void
  {
    $st = $pdo->prepare('UPDATE users SET is_blocked = :b, updated_at = NOW() WHERE id = :id');
    $st->execute([':b' => $blocked, ':id' => $userId]);
  }

  /**
   * @return array<string, mixed>|null
   */
  public static function getProfile(PDO $pdo, int $userId): ?array
  {
    $st = $pdo->prepare(
      'SELECT id, first_name, last_name, email, login, phone, is_blocked, created_at
       FROM users WHERE id = :id LIMIT 1'
    );
    $st->execute([':id' => $userId]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
      return null;
    }
    $snap = self::getAuthSnapshot($pdo, $userId);
    $row['roles'] = $snap['roles'] ?? [];
    $row['is_blocked'] = self::boolish($row['is_blocked']);
    return $row;
  }

  public static function getPhone(PDO $pdo, int $userId): ?string
  {
    $st = $pdo->prepare('SELECT phone FROM users WHERE id = :id LIMIT 1');
    $st->execute([':id' => $userId]);
    $p = $st->fetchColumn();
    return $p !== false ? (string)$p : null;
  }

  /**
   * @return list<array<string, mixed>>
   */
  public static function listUsers(PDO $pdo): array
  {
    $sql = "SELECT u.id, u.login, u.email, u.phone, u.is_blocked, u.created_at,
            COALESCE(string_agg(r.code, ',' ORDER BY r.code), '') AS role_codes
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            GROUP BY u.id
            ORDER BY u.id";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  }
}
