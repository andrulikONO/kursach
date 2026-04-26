<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/Env.php';
require_once __DIR__ . '/../src/Db.php';

use Kursach\Db;

$pdo = Db::pdo();

$pdo->exec("
  ALTER TABLE products
    ADD COLUMN IF NOT EXISTS category_slug VARCHAR(64),
    ADD COLUMN IF NOT EXISTS listing_kind VARCHAR(16) NOT NULL DEFAULT 'sale'
");

$pdo->exec("
  DO $$
  BEGIN
    IF NOT EXISTS (
      SELECT 1
      FROM pg_constraint
      WHERE conname = 'products_listing_kind_check'
    ) THEN
      ALTER TABLE products
      ADD CONSTRAINT products_listing_kind_check
      CHECK (listing_kind IN ('sale', 'buy'));
    END IF;
  END
  $$;
");

$pdo->exec("
  CREATE TABLE IF NOT EXISTS product_comments (
    id SERIAL PRIMARY KEY,
    product_id INTEGER NOT NULL REFERENCES products (id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users (id) ON DELETE SET NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
  )
");

$pdo->exec("CREATE INDEX IF NOT EXISTS idx_product_comments_product ON product_comments (product_id, created_at DESC)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_products_category_slug ON products (category_slug)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_products_listing_kind ON products (listing_kind)");

echo "Migrations complete.\n";
