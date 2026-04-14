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
('user', 'Пользователь', 'Просмотр каталога, размещение объявлений, создание тикетов'),
('support', 'Техподдержка', 'Просмотр и ответы на тикеты'),
('moderator', 'Модератор', 'Модерация объявлений'),
('admin', 'Администратор', 'Полный доступ');

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

-- Тикеты поддержки
CREATE TABLE tickets (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    subject     VARCHAR(200) NOT NULL,
    message     TEXT NOT NULL,
    status      VARCHAR(32) NOT NULL DEFAULT 'open' CHECK (status IN ('open', 'pending', 'resolved', 'closed')),
    priority    VARCHAR(20) NOT NULL DEFAULT 'normal' CHECK (priority IN ('low', 'normal', 'high', 'urgent')),
    assigned_to INTEGER REFERENCES users (id) ON DELETE SET NULL,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ,
    resolved_at TIMESTAMPTZ
);

CREATE INDEX idx_tickets_user ON tickets (user_id);
CREATE INDEX idx_tickets_status ON tickets (status);
CREATE INDEX idx_tickets_assigned ON tickets (assigned_to);
CREATE INDEX idx_tickets_created ON tickets (created_at DESC);

-- Ответы на тикеты
CREATE TABLE ticket_responses (
    id          SERIAL PRIMARY KEY,
    ticket_id   INTEGER NOT NULL REFERENCES tickets (id) ON DELETE CASCADE,
    user_id     INTEGER REFERENCES users (id) ON DELETE SET NULL,
    message     TEXT NOT NULL,
    is_internal BOOLEAN NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_responses_ticket ON ticket_responses (ticket_id);
CREATE INDEX idx_responses_user ON ticket_responses (user_id);

COMMENT ON TABLE tickets IS 'Обращения в техподдержку';
COMMENT ON COLUMN tickets.assigned_to IS 'Сотрудник, назначенный на тикет';
COMMENT ON COLUMN ticket_responses.is_internal IS 'Внутренняя заметка (не видна пользователю)';