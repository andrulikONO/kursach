-- PostgreSQL: объявления, пользователи, роли (гость = без записи в БД)
-- Кодировка UTF-8

CREATE TABLE roles (
  id         SERIAL PRIMARY KEY,
  code       VARCHAR(64) NOT NULL UNIQUE,
  name       VARCHAR(128) NOT NULL,
  description VARCHAR(255),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

INSERT INTO roles (code, name, description) VALUES
('user', 'Пользователь', 'Просмотр каталога и размещение объявлений'),
('admin', 'Администратор', 'Удаление объявлений и блокировка пользователей');

CREATE TABLE users (
  id              SERIAL PRIMARY KEY,
  first_name      VARCHAR(100) NOT NULL,
  last_name       VARCHAR(100) NOT NULL,
  email           VARCHAR(255) NOT NULL UNIQUE,
  login           VARCHAR(100) NOT NULL UNIQUE,
  password_hash   VARCHAR(255) NOT NULL,
  phone           VARCHAR(32) NOT NULL,
  age_confirmed   BOOLEAN NOT NULL DEFAULT FALSE,
  gender          VARCHAR(20) NOT NULL CHECK (gender IN ('MALE', 'FEMALE')),
  accepted_rules  BOOLEAN NOT NULL DEFAULT FALSE,
  theme_preference VARCHAR(20) NOT NULL DEFAULT 'light',
  is_blocked      BOOLEAN NOT NULL DEFAULT FALSE,
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at      TIMESTAMPTZ
);

CREATE INDEX idx_users_login ON users (login);
CREATE INDEX idx_users_email ON users (email);
CREATE INDEX idx_users_blocked ON users (is_blocked);

CREATE TABLE user_roles (
  user_id INTEGER NOT NULL REFERENCES users (id) ON DELETE CASCADE,
  role_id INTEGER NOT NULL REFERENCES roles (id) ON DELETE CASCADE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  PRIMARY KEY (user_id, role_id)
);

CREATE TABLE products (
  id           SERIAL PRIMARY KEY,
  user_id      INTEGER REFERENCES users (id) ON DELETE SET NULL,
  title        VARCHAR(200) NOT NULL,
  description  TEXT,
  type         VARCHAR(64),
  city         VARCHAR(120),
  price        INTEGER NOT NULL CHECK (price > 0),
  contact_phone VARCHAR(32),
  status       VARCHAR(32) NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'deleted')),
  created_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at   TIMESTAMPTZ
);

CREATE INDEX idx_products_status_created ON products (status, created_at DESC);
CREATE INDEX idx_products_user ON products (user_id);

COMMENT ON COLUMN products.contact_phone IS 'Копия телефона из профиля на момент публикации';
COMMENT ON COLUMN users.is_blocked IS 'Если true — пользователь не может создавать новые объявления';
