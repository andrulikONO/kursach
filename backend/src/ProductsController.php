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
    $category = isset($_GET['category']) ? trim((string)$_GET['category']) : '';
    $listingKind = isset($_GET['listingKind']) ? trim((string)$_GET['listingKind']) : '';
    $minPrice = isset($_GET['minPrice']) && $_GET['minPrice'] !== '' ? (int)$_GET['minPrice'] : null;
    $maxPrice = isset($_GET['maxPrice']) && $_GET['maxPrice'] !== '' ? (int)$_GET['maxPrice'] : null;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['perPage']) ? min(60, max(1, (int)$_GET['perPage'])) : 25;

    $fromWhere = "FROM products p\nWHERE p.status = 'active'";
    $params = [];

    if ($q !== '') {
      $fromWhere .= ' AND (p.title ILIKE :q OR p.description ILIKE :q)';
      $params[':q'] = '%' . $q . '%';
    }
    if ($type !== '') {
      $fromWhere .= ' AND p.type = :type';
      $params[':type'] = $type;
    }
    if ($category !== '') {
      $fromWhere .= ' AND p.category_slug = :category';
      $params[':category'] = $category;
    }
    if ($listingKind !== '') {
      $fromWhere .= ' AND p.listing_kind = :listingKind';
      $params[':listingKind'] = ProductCatalog::normalizeListingKind($listingKind);
    }
    if ($minPrice !== null) {
      $fromWhere .= ' AND p.price >= :minPrice';
      $params[':minPrice'] = $minPrice;
    }
    if ($maxPrice !== null) {
      $fromWhere .= ' AND p.price <= :maxPrice';
      $params[':maxPrice'] = $maxPrice;
    }

    $pdo = Db::pdo();

    $countSql = 'SELECT COUNT(*) ' . $fromWhere;
    $st = $pdo->prepare($countSql);
    $st->execute($params);
    $total = (int)$st->fetchColumn();

    $maxPage = $total > 0 ? (int)ceil($total / $perPage) : 1;
    if ($page > $maxPage) {
      $page = $maxPage;
    }

    $offset = ($page - 1) * $perPage;
    $listSql = "SELECT p.id, p.title, p.price, p.type, p.city, p.category_slug, p.listing_kind, p.created_at\n"
      . $fromWhere
      . ' ORDER BY p.created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset;

    $st = $pdo->prepare($listSql);
    $st->execute($params);
    $items = $st->fetchAll(PDO::FETCH_ASSOC);

    Response::json([
      'items' => $items,
      'total' => $total,
      'page' => $page,
      'perPage' => $perPage,
    ]);
  }

  public static function listMine(AuthContext $auth): void
  {
    if ($auth->isGuest()) {
      Response::json(['error' => 'Unauthorized'], 401);
    }
    Permissions::require($auth, 'profile.read');

    $pdo = Db::pdo();
    $st = $pdo->prepare(
      "SELECT id, title, price, type, city, description, status, contact_phone, category_slug, listing_kind, created_at
       FROM products
       WHERE user_id = :uid
       ORDER BY created_at DESC"
    );
    $st->execute([':uid' => $auth->userId]);
    Response::json(['items' => $st->fetchAll(PDO::FETCH_ASSOC)]);
  }

  public static function getOne(AuthContext $auth, int $id): void
  {
    Permissions::require($auth, 'products.read');

    $pdo = Db::pdo();
    $st = $pdo->prepare(
      "SELECT p.id, p.user_id, p.title, p.price, p.type, p.city, p.description, p.status,
              p.category_slug, p.listing_kind, p.created_at,
              u.login AS seller_login, u.first_name AS seller_first_name, u.last_name AS seller_last_name
       FROM products p
       LEFT JOIN users u ON u.id = p.user_id
       WHERE p.id = :id
       LIMIT 1"
    );
    $st->execute([':id' => $id]);
    $p = $st->fetch(PDO::FETCH_ASSOC);
    if (!$p || ($p['status'] ?? '') === 'deleted') {
      Response::json(['error' => 'Not Found'], 404);
    }

    Response::json($p);
  }

  public static function getPhone(AuthContext $auth, int $id): void
  {
    Permissions::require($auth, 'products.read');

    $pdo = Db::pdo();
    $st = $pdo->prepare(
      "SELECT contact_phone
       FROM products
       WHERE id = :id AND status = 'active'
       LIMIT 1"
    );
    $st->execute([':id' => $id]);
    $phone = $st->fetchColumn();
    if ($phone === false) {
      Response::json(['error' => 'Not Found'], 404);
    }

    Response::json(['phone' => (string)$phone]);
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
    $categorySlug = trim((string)($body['categorySlug'] ?? ''));
    $listingKind = ProductCatalog::normalizeListingKind((string)($body['listingKind'] ?? 'sale'));
    $city = isset($body['city']) ? trim((string)$body['city']) : null;
    $description = isset($body['description']) ? trim((string)$body['description']) : null;
    $type = ProductCatalog::getCategoryName($categorySlug);

    if ($title === '' || $price <= 0 || $type === null) {
      Response::json(['error' => 'Validation failed', 'fields' => ['title', 'price', 'categorySlug']], 422);
    }

    $pdo = Db::pdo();
    $phone = UserRepository::getPhone($pdo, (int)$auth->userId);
    if ($phone === null || $phone === '') {
      Response::json(['error' => 'В профиле не указан телефон'], 422);
    }

    $st = $pdo->prepare(
      "INSERT INTO products (user_id, title, price, type, city, description, contact_phone, status, category_slug, listing_kind)
       VALUES (:user_id, :title, :price, :type, :city, :description, :phone, 'active', :category_slug, :listing_kind)"
    );
    $st->execute([
      ':user_id' => $auth->userId,
      ':title' => $title,
      ':price' => $price,
      ':type' => $type,
      ':city' => $city ?: null,
      ':description' => $description ?: null,
      ':phone' => $phone,
      ':category_slug' => $categorySlug,
      ':listing_kind' => $listingKind,
    ]);
    $newId = (int)$pdo->lastInsertId('products_id_seq');

    Response::json(['id' => $newId], 201);
  }

  public static function listComments(AuthContext $auth, int $id): void
  {
    Permissions::require($auth, 'products.read');

    $pdo = Db::pdo();
    $st = $pdo->prepare("SELECT 1 FROM products WHERE id = :id AND status = 'active' LIMIT 1");
    $st->execute([':id' => $id]);
    if (!$st->fetchColumn()) {
      Response::json(['error' => 'Not Found'], 404);
    }

    $st = $pdo->prepare(
      "SELECT pc.id, pc.body, pc.created_at, u.login, u.first_name, u.last_name
       FROM product_comments pc
       LEFT JOIN users u ON u.id = pc.user_id
       WHERE pc.product_id = :id
       ORDER BY pc.created_at DESC"
    );
    $st->execute([':id' => $id]);
    Response::json(['items' => $st->fetchAll(PDO::FETCH_ASSOC)]);
  }

  public static function createComment(AuthContext $auth, int $id): void
  {
    Permissions::require($auth, 'comments.create');
    if ($auth->isGuest() || $auth->userId === null) {
      Response::json(['error' => 'Unauthorized'], 401);
    }
    if ($auth->isBlocked) {
      Response::json(['error' => 'Аккаунт заблокирован'], 403);
    }

    $body = Response::readJsonBody();
    $text = trim((string)($body['body'] ?? ''));
    if ($text === '') {
      Response::json(['error' => 'Validation failed', 'fields' => ['body']], 422);
    }

    $pdo = Db::pdo();
    $st = $pdo->prepare("SELECT 1 FROM products WHERE id = :id AND status = 'active' LIMIT 1");
    $st->execute([':id' => $id]);
    if (!$st->fetchColumn()) {
      Response::json(['error' => 'Not Found'], 404);
    }

    $st = $pdo->prepare(
      'INSERT INTO product_comments (product_id, user_id, body)
       VALUES (:product_id, :user_id, :body)'
    );
    $st->execute([
      ':product_id' => $id,
      ':user_id' => $auth->userId,
      ':body' => $text,
    ]);

    Response::json(['ok' => true], 201);
  }

  public static function delete(AuthContext $auth, int $id): void
  {
    Permissions::require($auth, 'products.delete');

    $pdo = Db::pdo();
    $st = $pdo->prepare("UPDATE products SET status = 'deleted', updated_at = NOW() WHERE id = :id AND status = 'active'");
    $st->execute([':id' => $id]);
    if ($st->rowCount() === 0) {
      Response::json(['error' => 'Not Found'], 404);
    }
    Response::json(['ok' => true]);
  }
}
