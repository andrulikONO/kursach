<?php
declare(strict_types=1);

namespace Kursach;

/**
 * Правила как в UserAuthApp (RegistrationServlet + validation.js).
 */
final class UserValidator
{
  public static function isStrongPassword(?string $password): bool
  {
    if ($password === null || $password === '') {
      return false;
    }
    return strlen($password) >= 6
      && preg_match('/[a-z]/', $password) === 1
      && preg_match('/[A-Z]/', $password) === 1
      && preg_match('/\d/', $password) === 1
      && preg_match('/[^A-Za-z0-9]/', $password) === 1
      && preg_match('/\s/', $password) !== 1;
  }

  /**
   * @return array<string, string> field => message (пустой = ок)
   */
  public static function validateRegistrationPayload(array $body): array
  {
    $errors = [];

    $firstName = isset($body['firstName']) ? trim((string)$body['firstName']) : '';
    $lastName = isset($body['lastName']) ? trim((string)$body['lastName']) : '';
    $email = isset($body['email']) ? trim((string)$body['email']) : '';
    $login = isset($body['login']) ? trim((string)$body['login']) : '';
    $password = isset($body['password']) ? (string)$body['password'] : '';
    $confirmPassword = isset($body['confirmPassword']) ? (string)$body['confirmPassword'] : '';
    $ageConfirmed = $body['ageConfirmed'] ?? null;
    $gender = isset($body['gender']) ? (string)$body['gender'] : '';
    $acceptRules = $body['acceptRules'] ?? null;

    if ($firstName === '' || !preg_match('/^[A-Za-z-]{2,15}$/', $firstName)) {
      $errors['firstName'] = 'Имя: от 2 до 15 символов, только латинские буквы и дефис';
    }
    if ($lastName === '' || !preg_match('/^[A-Za-z-]{2,15}$/', $lastName)) {
      $errors['lastName'] = 'Фамилия: от 2 до 15 символов, только латинские буквы и дефис';
    }
    if ($email === '') {
      $errors['email'] = 'Введите email';
    } elseif (!preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email)) {
      $errors['email'] = 'Введите корректный email';
    }
    if ($login === '' || strlen($login) < 6) {
      $errors['login'] = 'Логин: не менее 6 символов';
    }
    if ($password === '') {
      $errors['password'] = 'Введите пароль';
    } elseif (!self::isStrongPassword($password)) {
      $errors['password'] = 'Пароль: мин. 6 символов, строчная, заглавная, цифра и спецсимвол (без пробелов)';
    }
    if ($confirmPassword === '' || $confirmPassword !== $password) {
      $errors['confirmPassword'] = 'Пароли не совпадают';
    }

    // Как в RegistrationServlet: только "true" / "false" (в JSON — bool или строки)
    if (
      $ageConfirmed !== true && $ageConfirmed !== false
      && $ageConfirmed !== 'true' && $ageConfirmed !== 'false'
    ) {
      $errors['ageConfirmed'] = 'Выберите один из вариантов';
    }

    if ($gender !== 'MALE' && $gender !== 'FEMALE') {
      $errors['gender'] = 'Укажите пол';
    }

    if ($acceptRules !== true && $acceptRules !== 'true' && $acceptRules !== 1 && $acceptRules !== '1') {
      $errors['acceptRules'] = 'Необходимо согласие с правилами';
    }

    return $errors;
  }
}
