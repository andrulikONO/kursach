<?php
declare(strict_types=1);

namespace Kursach;

use PDO;

final class ChatController
{
  public static function listDialogs(AuthContext $auth): void
  {
    if ($auth->isGuest()) {
      Response::json(['error' => 'Unauthorized'], 401);
    }

    $pdo = Db::pdo();
    $sql = "
      SELECT DISTINCT ON (peer_id)
        peer_id,
        u.login AS peer_login,
        u.fio AS peer_fio,
        m.body AS last_message,
        m.created_at AS last_message_at
      FROM (
        SELECT
          id,
          body,
          created_at,
          CASE WHEN sender_id = :uid THEN receiver_id ELSE sender_id END AS peer_id
        FROM chat_messages
        WHERE sender_id = :uid OR receiver_id = :uid
      ) m
      INNER JOIN users u ON u.id = m.peer_id
      ORDER BY peer_id, created_at DESC
    ";
    $st = $pdo->prepare($sql);
    $st->execute([':uid' => $auth->userId]);
    $items = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    usort($items, static fn($a, $b) => strcmp((string)$b['last_message_at'], (string)$a['last_message_at']));

    Response::json(['items' => $items]);
  }

  public static function listMessages(AuthContext $auth): void
  {
    if ($auth->isGuest()) {
      Response::json(['error' => 'Unauthorized'], 401);
    }
    $peerId = (int)($_GET['peerId'] ?? 0);
    if ($peerId <= 0) {
      Response::json(['error' => 'peerId required'], 422);
    }
    $afterId = (int)($_GET['afterId'] ?? 0);

    $pdo = Db::pdo();
    $sql = "
      SELECT id, sender_id, receiver_id, body, created_at
      FROM chat_messages
      WHERE ((sender_id = :uid AND receiver_id = :peer) OR (sender_id = :peer AND receiver_id = :uid))
      " . ($afterId > 0 ? " AND id > :after_id " : "") . "
      ORDER BY created_at ASC, id ASC
    ";
    $st = $pdo->prepare($sql);
    $params = [':uid' => $auth->userId, ':peer' => $peerId];
    if ($afterId > 0) {
      $params[':after_id'] = $afterId;
    }
    $st->execute($params);
    Response::json(['items' => $st->fetchAll(PDO::FETCH_ASSOC) ?: []]);
  }

  public static function sendMessage(AuthContext $auth): void
  {
    if ($auth->isGuest()) {
      Response::json(['error' => 'Unauthorized'], 401);
    }
    $body = Response::readJsonBody();
    $receiverId = (int)($body['receiverId'] ?? 0);
    $text = trim((string)($body['body'] ?? ''));
    if ($receiverId <= 0 || $text === '') {
      Response::json(['error' => 'validation'], 422);
    }

    $pdo = Db::pdo();
    $st = $pdo->prepare('INSERT INTO chat_messages (sender_id, receiver_id, body) VALUES (:s, :r, :b) RETURNING id, created_at');
    $st->execute([':s' => $auth->userId, ':r' => $receiverId, ':b' => $text]);
    $msg = $st->fetch(PDO::FETCH_ASSOC) ?: [];

    Realtime::push($pdo, $receiverId, 'chat_message', [
      'id' => (int)($msg['id'] ?? 0),
      'sender_id' => $auth->userId,
      'receiver_id' => $receiverId,
      'body' => $text,
      'created_at' => $msg['created_at'] ?? null,
    ]);

    Response::json(['ok' => true, 'id' => (int)($msg['id'] ?? 0), 'created_at' => $msg['created_at'] ?? null], 201);
  }
}
