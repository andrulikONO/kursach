<?php
declare(strict_types=1);
namespace Kursach;

use PDO;

final class TicketsController
{
    public static function list(AuthContext $auth): void
    {
        $isSupport = Permissions::can($auth, 'tickets.read');
        $pdo = Db::pdo();

        if ($isSupport) {
            $sql = "SELECT t.*, u.login as user_login, u.email as user_email 
                    FROM tickets t 
                    LEFT JOIN users u ON t.user_id = u.id 
                    ORDER BY t.created_at DESC LIMIT 100";
            $st = $pdo->prepare($sql);
            $st->execute();
        } else {
            Permissions::require($auth, 'tickets.read_own');
            $sql = "SELECT * FROM tickets WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 50";
            $st = $pdo->prepare($sql);
            $st->execute([':user_id' => $auth->userId]);
        }

        $tickets = $st->fetchAll(PDO::FETCH_ASSOC);
        Response::json(['tickets' => $tickets]);
    }

    public static function getOne(AuthContext $auth, int $id): void
    {
        $pdo = Db::pdo();
        
        $st = $pdo->prepare("SELECT * FROM tickets WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $ticket = $st->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            Response::json(['error' => 'Not Found'], 404);
        }

        if (!Permissions::can($auth, 'tickets.read') && $ticket['user_id'] !== $auth->userId) {
            Response::json(['error' => 'Forbidden'], 403);
        }

        $st = $pdo->prepare("
            SELECT tr.*, u.login as author_login 
            FROM ticket_responses tr 
            LEFT JOIN users u ON tr.user_id = u.id 
            WHERE tr.ticket_id = :ticket_id 
            ORDER BY tr.created_at ASC
        ");
        $st->execute([':ticket_id' => $id]);
        $responses = $st->fetchAll(PDO::FETCH_ASSOC);

        Response::json([
            'ticket' => $ticket,
            'responses' => $responses
        ]);
    }

    public static function create(AuthContext $auth): void
    {
        Permissions::require($auth, 'tickets.create');
        
        if ($auth->isGuest()) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $body = Response::readJsonBody();
        $subject = trim((string)($body['subject'] ?? ''));
        $message = trim((string)($body['message'] ?? ''));
        $priority = $body['priority'] ?? 'normal';

        if ($subject === '' || $message === '') {
            Response::json(['error' => 'Validation failed', 'fields' => ['subject', 'message']], 422);
        }

        $pdo = Db::pdo();
        $st = $pdo->prepare("
            INSERT INTO tickets (user_id, subject, message, priority, status) 
            VALUES (:user_id, :subject, :message, :priority, 'open')
        ");
        $st->execute([
            ':user_id' => $auth->userId,
            ':subject' => $subject,
            ':message' => $message,
            ':priority' => in_array($priority, ['low', 'normal', 'high', 'urgent'], true) ? $priority : 'normal'
        ]);

        $id = (int)$pdo->lastInsertId();
        Response::json(['id' => $id], 201);
    }

    public static function respond(AuthContext $auth, int $ticketId): void
    {
        Permissions::require($auth, 'tickets.respond');
        
        $body = Response::readJsonBody();
        $message = trim((string)($body['message'] ?? ''));
        $isInternal = (bool)($body['isInternal'] ?? false);

        if ($message === '') {
            Response::json(['error' => 'Message is required'], 422);
        }

        $pdo = Db::pdo();
        
        $st = $pdo->prepare("SELECT id FROM tickets WHERE id = :id LIMIT 1");
        $st->execute([':id' => $ticketId]);
        if (!$st->fetch()) {
            Response::json(['error' => 'Not Found'], 404);
        }

        $st = $pdo->prepare("
            INSERT INTO ticket_responses (ticket_id, user_id, message, is_internal) 
            VALUES (:ticket_id, :user_id, :message, :is_internal)
        ");
        $st->execute([
            ':ticket_id' => $ticketId,
            ':user_id' => $auth->userId,
            ':message' => $message,
            ':is_internal' => $isInternal
        ]);

        $st = $pdo->prepare("SELECT user_id FROM tickets WHERE id = :id");
        $st->execute([':id' => $ticketId]);
        $ticket = $st->fetch();
        
        if ($ticket && $ticket['user_id'] !== $auth->userId) {
            $pdo->prepare("UPDATE tickets SET status = 'pending', updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $ticketId]);
        }

        Response::json(['ok' => true]);
    }

    public static function assign(AuthContext $auth, int $ticketId): void
    {
        Permissions::require($auth, 'tickets.manage');
        
        $body = Response::readJsonBody();
        $assignedTo = isset($body['assignedTo']) ? (int)$body['assignedTo'] : null;

        $pdo = Db::pdo();
        $pdo->prepare("UPDATE tickets SET assigned_to = :assigned_to, updated_at = NOW() WHERE id = :id")
            ->execute([':assigned_to' => $assignedTo, ':id' => $ticketId]);

        Response::json(['ok' => true]);
    }
}