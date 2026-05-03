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
    $phone = isset($body['phone']) ? trim((string)$body['phone']) : '';

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

    $ageOk = $ageConfirmed === true || $ageConfirmed === 'true' || $ageConfirmed === 1 || $ageConfirmed === '1';
    if (!$ageOk) {
      $errors['ageConfirmed'] = 'Подтвердите, что вам есть 18 лет';
    }

    if ($gender !== 'MALE' && $gender !== 'FEMALE') {
      $errors['gender'] = 'Укажите пол';
    }

    if ($acceptRules !== true && $acceptRules !== 'true' && $acceptRules !== 1 && $acceptRules !== '1') {
      $errors['acceptRules'] = 'Необходимо согласие с правилами';
    }

    if ($phone === '' || !self::isValidPhone($phone)) {
      $errors['phone'] = 'Телефон: формат +7XXXXXXXXXX (10 цифр после +7)';
    }

    return $errors;
  }

  /** Нормализует к виду +7XXXXXXXXXX */
  public static function normalizePhone(string $raw): string
  {
    $d = preg_replace('/\D+/', '', $raw);
    if (str_starts_with($d, '8') && strlen($d) === 11) {
      $d = '7' . substr($d, 1);
    }
    if (str_starts_with($d, '7') && strlen($d) === 11) {
      return '+' . $d;
    }
    if (strlen($d) === 10) {
      return '+7' . $d;
    }
    return trim($raw);
  }

  public static function isValidPhone(string $raw): bool
  {
    $n = self::normalizePhone($raw);
    return (bool)preg_match('/^\+7\d{10}$/', $n);
  }
}
