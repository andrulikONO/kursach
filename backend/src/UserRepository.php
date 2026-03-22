<?php
declare(strict_types=1);

namespace Kursach;

use PDO;

final class UserRepository
{
  public static function isLoginTaken(PDO $pdo, string $login): bool
  {
    $st = $pdo->prepare('SELECT 1 FROM users WHERE login = :l LIMIT 1');
    $st->execute([':l' => $login]);
    return (bool)$st->fetchColumn();
  }

  public static function isEmailTaken(PDO $pdo, string $email): bool
  {
    $st = $pdo->prepare('SELECT 1 FROM users WHERE email = :e LIMIT 1');
    $st->execute([':e' => $email]);
    return (bool)$st->fetchColumn();
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
    bool $acceptedRules
  ): int {
    $st = $pdo->prepare(
      'INSERT INTO users (
        first_name, last_name, email, login, password_hash,
        age_confirmed, gender, accepted_rules, theme_preference, status
      ) VALUES (
        :fn, :ln, :em, :lg, :ph,
        :age, :gender, :rules, :theme, :status
      )'
    );
    $st->execute([
      ':fn' => $firstName,
      ':ln' => $lastName,
      ':em' => $email,
      ':lg' => $login,
      ':ph' => $passwordHash,
      ':age' => $ageConfirmed ? 1 : 0,
      ':gender' => $gender,
      ':rules' => $acceptedRules ? 1 : 0,
      ':theme' => 'light',
      ':status' => 'active',
    ]);
    return (int)$pdo->lastInsertId();
  }

  public static function assignRoleByCode(PDO $pdo, int $userId, string $roleCode): void
  {
    $st = $pdo->prepare('SELECT id FROM roles WHERE code = :c LIMIT 1');
    $st->execute([':c' => $roleCode]);
    $rid = $st->fetchColumn();
    if (!$rid) {
      return;
    }
    $ins = $pdo->prepare('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:u, :r)');
    $ins->execute([':u' => $userId, ':r' => (int)$rid]);
  }
}
