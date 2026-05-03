<?php
declare(strict_types=1);

namespace Kursach;

use PDO;

final class Realtime
{
  public static function push(PDO $pdo, int $userId, string $eventType, array $payload): void
  {
    $st = $pdo->prepare(
      'INSERT INTO realtime_events (user_id, event_type, payload) VALUES (:uid, :type, :payload::jsonb)'
    );
    $st->execute([
      ':uid' => $userId,
      ':type' => $eventType,
      ':payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
  }
}
