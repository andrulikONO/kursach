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

    $sql = "SELECT p.id, p.title, p.price, p.type, p.city
            FROM products p
            WHERE p.status = 'active'";
    $params = [];

    if ($q !== '') {
      $sql .= " AND (p.title LIKE :q OR p.description LIKE :q)";
      $params[':q'] = '%' . $q . '%';
    }
    if ($type !== '') {
      $sql .= " AND p.type = :type";
      $params[':type'] = $type;
    }
    if ($minPrice !== null) {
      $sql .= " AND p.price >= :minPrice";
      $params[':minPrice'] = $minPrice;
    }
    if ($maxPrice !== null) {
      $sql .= " AND p.price <= :maxPrice";
      $params[':maxPrice'] = $maxPrice;
    }

    $sql .= " ORDER BY p.created_at DESC LIMIT 60";

    $pdo = Db::pdo();
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $items = $st->fetchAll(PDO::FETCH_ASSOC);

    Response::json(['items' => $items]);
  }

  public static function getOne(AuthContext $auth, int $id): void
  {
    Permissions::require($auth, 'products.read');

    $pdo = Db::pdo();
    $st = $pdo->prepare("SELECT id, title, price, type, city, description, status, created_at FROM products WHERE id = :id LIMIT 1");
    $st->execute([':id' => $id]);
    $p = $st->fetch(PDO::FETCH_ASSOC);
    if (!$p) Response::json(['error' => 'Not Found'], 404);

    Response::json($p);
  }

  public static function create(AuthContext $auth): void
  {
    Permissions::require($auth, 'products.create');

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
    $st = $pdo->prepare(
      "INSERT INTO products (user_id, title, price, type, city, description, status)
       VALUES (:user_id, :title, :price, :type, :city, :description, 'active')"
    );
    $st->execute([
      ':user_id' => $auth->userId,
      ':title' => $title,
      ':price' => $price,
      ':type' => $type ?: null,
      ':city' => $city ?: null,
      ':description' => $description ?: null,
    ]);

    $id = (int)$pdo->lastInsertId();
    Response::json(['id' => $id], 201);
  }
}

