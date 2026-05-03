<?php
declare(strict_types=1);

namespace Kursach;

use PDO;

final class EventsController
{
  public static function stream(AuthContext $auth): void
  {
    if ($auth->isGuest()) {
      Response::json(['error' => 'Unauthorized'], 401);
    }

    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    $lastId = (int)($_GET['lastEventId'] ?? 0);
    $userId = (int)$auth->userId;
    $pdo = Db::pdo();
    $deadline = time() + 25;

    while (time() < $deadline) {
      $st = $pdo->prepare(
        'SELECT id, event_type, payload::text AS payload
         FROM realtime_events
         WHERE user_id = :uid AND id > :last_id
         ORDER BY id ASC
         LIMIT 20'
      );
      $st->execute([':uid' => $userId, ':last_id' => $lastId]);
      $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
      if ($rows !== []) {
        foreach ($rows as $row) {
          $id = (int)$row['id'];
          $type = (string)$row['event_type'];
          $payload = $row['payload'] ?? '{}';
          echo "id: {$id}\n";
          echo "event: {$type}\n";
          echo "data: {$payload}\n\n";
          $lastId = $id;
        }
        @ob_flush();
        @flush();
        return;
      }

      echo ": ping\n\n";
      @ob_flush();
      @flush();
      usleep(500000);
    }
  }
}
