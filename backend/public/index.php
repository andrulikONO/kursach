<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/Env.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/UserValidator.php';
require_once __DIR__ . '/../src/UserRepository.php';
require_once __DIR__ . '/../src/AuthContext.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Permissions.php';
require_once __DIR__ . '/../src/ProductsController.php';
require_once __DIR__ . '/../src/AuthController.php';
require_once __DIR__ . '/../src/AdminController.php';

use Kursach\Response;
use Kursach\Auth;
use Kursach\ProductsController;
use Kursach\AuthController;
use Kursach\AdminController;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
  http_response_code(204);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

try {
  $auth = Auth::fromRequest();

  if ($path === '/api/health' && $method === 'GET') {
    Response::json(['ok' => true]);
  }

  if ($path === '/api/auth/check-availability' && $method === 'GET') {
    AuthController::checkAvailability();
  }

  if ($path === '/api/auth/register' && $method === 'POST') {
    AuthController::register();
  }

  if ($path === '/api/auth/login' && $method === 'POST') {
    AuthController::login();
  }

  if ($path === '/api/me' && $method === 'GET') {
    AuthController::me($auth);
  }

  if ($path === '/api/my/products' && $method === 'GET') {
    ProductsController::listMine($auth);
  }

  if ($path === '/api/products' && $method === 'GET') {
    ProductsController::list($auth);
  }

  if (preg_match('#^/api/products/(\d+)$#', $path, $m) && $method === 'GET') {
    ProductsController::getOne($auth, (int)$m[1]);
  }

  if ($path === '/api/products' && $method === 'POST') {
    ProductsController::create($auth);
  }

  if (preg_match('#^/api/products/(\d+)$#', $path, $m) && $method === 'DELETE') {
    ProductsController::delete($auth, (int)$m[1]);
  }

  if ($path === '/api/admin/users' && $method === 'GET') {
    AdminController::listUsers($auth);
  }

  if (preg_match('#^/api/admin/users/(\d+)/block$#', $path, $m) && $method === 'POST') {
    AdminController::blockUser($auth, (int)$m[1]);
  }

  if (preg_match('#^/api/admin/users/(\d+)/unblock$#', $path, $m) && $method === 'POST') {
    AdminController::unblockUser($auth, (int)$m[1]);
  }

  Response::json(['error' => 'Not Found'], 404);
} catch (Throwable $e) {
  Response::json([
    'error' => 'Server error',
    'details' => $e->getMessage(),
  ], 500);
}
