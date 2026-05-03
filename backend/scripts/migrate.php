<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/Env.php';
require_once __DIR__ . '/../src/Db.php';

use Kursach\Db;

$pdo = Db::pdo();

$pdo->exec("
  ALTER TABLE users
    ADD COLUMN IF NOT EXISTS fio VARCHAR(255),
    ADD COLUMN IF NOT EXISTS role VARCHAR(64) NOT NULL DEFAULT 'user',
    ADD COLUMN IF NOT EXISTS phone VARCHAR(32),
    ADD COLUMN IF NOT EXISTS email VARCHAR(255)
");

$pdo->exec("
  UPDATE users
  SET fio = TRIM(COALESCE(first_name, '') || ' ' || COALESCE(last_name, ''))
  WHERE (fio IS NULL OR fio = '')
");

$pdo->exec("
  UPDATE users u
  SET role = r.code
  FROM (
    SELECT ur.user_id, MIN(r.code) AS code
    FROM user_roles ur
    INNER JOIN roles r ON r.id = ur.role_id
    GROUP BY ur.user_id
  ) r
  WHERE u.id = r.user_id
");

$pdo->exec("
  CREATE TABLE IF NOT EXISTS revoked_tokens (
    token TEXT PRIMARY KEY,
    user_id INTEGER NOT NULL,
    revoked_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
  )
");

$pdo->exec("
  CREATE TABLE IF NOT EXISTS realtime_events (
    id BIGSERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    event_type VARCHAR(64) NOT NULL,
    payload JSONB NOT NULL DEFAULT '{}'::jsonb,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
  )
");

$pdo->exec("
  CREATE TABLE IF NOT EXISTS chat_messages (
    id BIGSERIAL PRIMARY KEY,
    sender_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    receiver_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    body TEXT NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
  )
");

$pdo->exec("CREATE INDEX IF NOT EXISTS idx_events_user_id_id ON realtime_events (user_id, id)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_chat_pair_time ON chat_messages (sender_id, receiver_id, created_at DESC)");

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
