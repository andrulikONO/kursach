<?php
declare(strict_types=1);

namespace Kursach;

use PDO;

final class AuthController
{
  public static function checkAvailability(): void
  {
    $type = isset($_GET['type']) ? trim((string)$_GET['type']) : '';
    $value = isset($_GET['value']) ? trim((string)$_GET['value']) : '';

    if ($value === '') {
      Response::json(['available' => false, 'message' => 'Поле не может быть пустым'], 200);
    }

    try {
      $pdo = Db::pdo();
      if ($type === 'login') {
        if (strlen($value) < 6) {
          Response::json(['available' => true, 'message' => ''], 200);
        }
        $exists = UserRepository::isLoginTaken($pdo, $value);
        Response::json([
          'available' => !$exists,
          'message' => $exists ? 'Логин уже занят' : '',
        ], 200);
      } elseif ($type === 'email') {
        $exists = UserRepository::isEmailTaken($pdo, $value);
        Response::json([
          'available' => !$exists,
          'message' => $exists ? 'Email уже занят' : '',
        ], 200);
      } else {
        Response::json(['available' => false, 'message' => 'Неверный тип проверки'], 400);
      }
    } catch (\Throwable $e) {
      Response::json(['available' => false, 'message' => 'Ошибка проверки'], 500);
    }
  }

  public static function register(): void
  {
    $body = Response::readJsonBody();

    $firstName = isset($body['firstName']) ? trim((string)$body['firstName']) : '';
    $lastName = isset($body['lastName']) ? trim((string)$body['lastName']) : '';
    $email = isset($body['email']) ? trim((string)$body['email']) : '';
    $login = isset($body['login']) ? trim((string)$body['login']) : '';
    $password = isset($body['password']) ? (string)$body['password'] : '';
    $confirmPassword = isset($body['confirmPassword']) ? (string)$body['confirmPassword'] : '';
    $ageConfirmedRaw = $body['ageConfirmed'] ?? null;
    $gender = isset($body['gender']) ? (string)$body['gender'] : '';
    $acceptRules = $body['acceptRules'] ?? null;

    $errors = UserValidator::validateRegistrationPayload($body);
    if ($errors !== []) {
      Response::json(['error' => 'validation', 'fields' => $errors], 422);
    }

    $ageConfirmed = ($ageConfirmedRaw === true || $ageConfirmedRaw === 'true');

    $accepted = ($acceptRules === true || $acceptRules === 'true' || $acceptRules === 1 || $acceptRules === '1');
    if (!$accepted) {
      Response::json(['error' => 'validation', 'fields' => ['acceptRules' => 'Необходимо согласие с правилами']], 422);
    }

    $phone = UserValidator::normalizePhone(trim((string)($body['phone'] ?? '')));

    $pdo = Db::pdo();
    try {
      if (UserRepository::isLoginTaken($pdo, $login)) {
        Response::json(['error' => 'Логин уже занят.', 'fields' => ['login' => 'Логин уже занят']], 409);
      }
      if (UserRepository::isEmailTaken($pdo, $email)) {
        Response::json(['error' => 'Email уже зарегистрирован.', 'fields' => ['email' => 'Email уже занят']], 409);
      }

      $hash = password_hash($password, PASSWORD_DEFAULT);
      if ($hash === false) {
        Response::json(['error' => 'Ошибка хеширования пароля'], 500);
      }

      $pdo->beginTransaction();
      $id = UserRepository::create(
        $pdo,
        $firstName,
        $lastName,
        $email,
        $login,
        $hash,
        $ageConfirmed,
        $gender,
        true,
        $phone
      );
      UserRepository::assignRoleByCode($pdo, $id, 'user');

      $pdo->commit();

      Response::json([
        'ok' => true,
        'userId' => $id,
        'login' => $login,
        'email' => $email,
        'demoAuth' => 'Demo user:' . $id,
      ], 201);
    } catch (\Throwable $e) {
      if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
      }
      Response::json(['error' => 'Ошибка при регистрации', 'details' => $e->getMessage()], 500);
    }
  }

  public static function login(): void
  {
    $body = Response::readJsonBody();
    $identifier = trim((string)($body['identifier'] ?? $body['login'] ?? ''));
    $password = (string)($body['password'] ?? '');

    if ($identifier === '' || $password === '') {
      Response::json(['error' => 'validation', 'fields' => ['identifier' => 'Укажите логин или email', 'password' => 'Введите пароль']], 422);
    }

    $pdo = Db::pdo();
    $u = UserRepository::findByLoginOrEmail($pdo, $identifier);
    if ($u === null || !password_verify($password, $u['password_hash'])) {
      Response::json(['error' => 'Неверный логин или пароль'], 401);
    }

    Response::json([
      'ok' => true,
      'userId' => $u['id'],
      'demoAuth' => 'Demo user:' . $u['id'],
    ]);
  }

  public static function me(AuthContext $auth): void
  {
    if ($auth->isGuest()) {
      Response::json(['error' => 'Unauthorized'], 401);
    }
    Permissions::require($auth, 'profile.read');

    $pdo = Db::pdo();
    $profile = UserRepository::getProfile($pdo, (int)$auth->userId);
    if ($profile === null) {
      Response::json(['error' => 'Not Found'], 404);
    }

    Response::json($profile);
  }

  public static function updateMe(AuthContext $auth): void
  {
    if ($auth->isGuest()) {
      Response::json(['error' => 'Unauthorized'], 401);
    }
    Permissions::require($auth, 'profile.read');

    $body = Response::readJsonBody();
    $email = trim((string)($body['email'] ?? ''));
    $phone = trim((string)($body['phone'] ?? ''));
    $fio = trim((string)($body['fio'] ?? ''));

    if ($email === '' || $fio === '') {
      Response::json(['error' => 'validation', 'fields' => ['email' => 'required', 'fio' => 'required']], 422);
    }

    $pdo = Db::pdo();
    $profile = UserRepository::updateProfile($pdo, (int)$auth->userId, $email, $phone, $fio);
    if ($profile === null) {
      Response::json(['error' => 'Not Found'], 404);
    }

    Response::json($profile);
  }
}
