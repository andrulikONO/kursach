<?php
declare(strict_types=1);

namespace Kursach;

use PDO;

final class ProductsController
{
  public static function list(AuthContext $auth): void
  {
    Permissions::require($auth, 'products.read');

    $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
    $type = isset($_GET['type']) ? trim((string)$_GET['type']) : '';
    $minPrice = isset($_GET['minPrice']) && $_GET['minPrice'] !== '' ? (int)$_GET['minPrice'] : null;
    $maxPrice = isset($_GET['maxPrice']) && $_GET['maxPrice'] !== '' ? (int)$_GET['maxPrice'] : null;

    $sql = 'SELECT p.id, p.title, p.price, p.type, p.city, p.contact_phone
            FROM products p
            WHERE p.status = \'active\'';
    $params = [];

    if ($q !== '') {
      $sql .= ' AND (p.title ILIKE :q OR p.description ILIKE :q)';
      $params[':q'] = '%' . $q . '%';
    }
    if ($type !== '') {
      $sql .= ' AND p.type = :type';
      $params[':type'] = $type;
    }
    if ($minPrice !== null) {
      $sql .= ' AND p.price >= :minPrice';
      $params[':minPrice'] = $minPrice;
    }
    if ($maxPrice !== null) {
      $sql .= ' AND p.price <= :maxPrice';
      $params[':maxPrice'] = $maxPrice;
    }

    $sql .= ' ORDER BY p.created_at DESC LIMIT 60';

    $pdo = Db::pdo();
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $items = $st->fetchAll(PDO::FETCH_ASSOC);

    Response::json(['items' => $items]);
  }

  public static function listMine(AuthContext $auth): void
  {
    if ($auth->isGuest()) {
      Response::json(['error' => 'Unauthorized'], 401);
    }
    Permissions::require($auth, 'profile.read');

    $pdo = Db::pdo();
    $st = $pdo->prepare(
      'SELECT id, title, price, type, city, description, status, contact_phone, created_at
       FROM products WHERE user_id = :uid ORDER BY created_at DESC'
    );
    $st->execute([':uid' => $auth->userId]);
    Response::json(['items' => $st->fetchAll(PDO::FETCH_ASSOC)]);
  }

  public static function getOne(AuthContext $auth, int $id): void
  {
    Permissions::require($auth, 'products.read');

    $pdo = Db::pdo();
    $st = $pdo->prepare(
      'SELECT id, user_id, title, price, type, city, description, status, contact_phone, created_at
       FROM products WHERE id = :id LIMIT 1'
    );
    $st->execute([':id' => $id]);
    $p = $st->fetch(PDO::FETCH_ASSOC);
    if (!$p || ($p['status'] ?? '') === 'deleted') {
      Response::json(['error' => 'Not Found'], 404);
    }

    Response::json($p);
  }

  public static function create(AuthContext $auth): void
  {
    Permissions::require($auth, 'products.create');
    if ($auth->isGuest() || $auth->userId === null) {
      Response::json(['error' => 'Требуется вход'], 401);
    }
    if ($auth->isBlocked) {
      Response::json(['error' => 'Аккаунт заблокирован: создание объявлений недоступно'], 403);
    }

    $body = Response::readJsonBody();
    $title = trim((string)($body['title'] ?? ''));
    $price = (int)($body['price'] ?? 0);
    $type = isset($body['type']) ? trim((string)$body['type']) : null;
    $city = isset($body['city']) ? trim((string)$body['city']) : null;
    $description = isset($body['description']) ? trim((string)$body['description']) : null;

    if ($title === '' || $price <= 0) {
      Response::json(['error' => 'Validation failed', 'fields' => ['title', 'price']], 422);
    }

    $pdo = Db::pdo();
    $phone = UserRepository::getPhone($pdo, (int)$auth->userId);
    if ($phone === null || $phone === '') {
      Response::json(['error' => 'В профиле не указан телефон'], 422);
    }

    $st = $pdo->prepare(
      'INSERT INTO products (user_id, title, price, type, city, description, contact_phone, status)
       VALUES (:user_id, :title, :price, :type, :city, :description, :phone, \'active\')'
    );
    $st->execute([
      ':user_id' => $auth->userId,
      ':title' => $title,
      ':price' => $price,
      ':type' => $type ?: null,
      ':city' => $city ?: null,
      ':description' => $description ?: null,
      ':phone' => $phone,
    ]);
    $newId = (int)$pdo->lastInsertId('products_id_seq');

    Response::json(['id' => $newId], 201);
  }

  public static function delete(AuthContext $auth, int $id): void
  {
    Permissions::require($auth, 'products.delete');

    $pdo = Db::pdo();
    $st = $pdo->prepare('UPDATE products SET status = \'deleted\', updated_at = NOW() WHERE id = :id AND status = \'active\'');
    $st->execute([':id' => $id]);
    if ($st->rowCount() === 0) {
      Response::json(['error' => 'Not Found'], 404);
    }
    Response::json(['ok' => true]);
  }
}
