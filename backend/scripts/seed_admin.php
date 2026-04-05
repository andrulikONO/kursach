<?php
/**
 * Создаёт администратора (логин admin). Запуск внутри контейнера backend:
 *   php scripts/seed_admin.php
 * Пароль задаётся переменной ADMIN_PASSWORD или по умолчанию Admin123!
 */
declare(strict_types=1);

require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/Env.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/UserRepository.php';

use Kursach\Db;
use Kursach\UserRepository;

$pass = getenv('ADMIN_PASSWORD') ?: 'Admin123!';
$login = getenv('ADMIN_LOGIN') ?: 'admin';
$email = getenv('ADMIN_EMAIL') ?: 'admin@local.test';
$phone = getenv('ADMIN_PHONE') ?: '+79990000000';

$pdo = Db::pdo();

$st = $pdo->prepare('SELECT id FROM users WHERE login = :l LIMIT 1');
$st->execute([':l' => $login]);
$existing = $st->fetchColumn();

$hash = password_hash($pass, PASSWORD_DEFAULT);
if ($hash === false) {
  fwrite(STDERR, "password_hash failed\n");
  exit(1);
}

if ($existing) {
  $id = (int)$existing;
  $u = $pdo->prepare('UPDATE users SET password_hash = :h, is_blocked = FALSE WHERE id = :id');
  $u->execute([':h' => $hash, ':id' => $id]);
  echo "Admin user exists, password updated. id={$id}\n";
} else {
  $id = UserRepository::create(
    $pdo,
    'Admin',
    'User',
    $email,
    $login,
    $hash,
    true,
    'MALE',
    true,
    $phone
  );
  echo "Admin user created. id={$id}\n";
}

UserRepository::assignRoleByCode($pdo, $id, 'user');
UserRepository::assignRoleByCode($pdo, $id, 'admin');
echo "Roles user+admin assigned. Login: {$login}\n";
