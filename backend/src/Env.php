<?php
declare(strict_types=1);

namespace Kursach;

final class Env
{
  private static bool $loaded = false;

  public static function loadDotenv(?string $path = null): void
  {
    if (self::$loaded) return;
    self::$loaded = true;

    $path = $path ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) return;

    foreach ($lines as $line) {
      $line = trim($line);
      if ($line === '' || str_starts_with($line, '#')) continue;
      $pos = strpos($line, '=');
      if ($pos === false) continue;
      $k = trim(substr($line, 0, $pos));
      $v = trim(substr($line, $pos + 1));
      $v = trim($v, "\"'");
      if ($k !== '' && getenv($k) === false) {
        putenv($k . '=' . $v);
        $_ENV[$k] = $v;
      }
    }
  }

  public static function get(string $key, ?string $default = null): ?string
  {
    self::loadDotenv();
    $v = $_ENV[$key] ?? getenv($key);
    if ($v === false || $v === null || $v === '') return $default;
    return (string)$v;
  }
}

