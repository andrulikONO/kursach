<?php
declare(strict_types=1);

namespace Kursach;

final class Response
{
  public static function json(mixed $data, int $status = 200): void
  {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
  }

  public static function readJsonBody(): array
  {
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) {
      self::json(['error' => 'Invalid JSON'], 400);
    }
    return $data;
  }
}

