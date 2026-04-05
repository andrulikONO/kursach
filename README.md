# Kursach: объявления (Vue + PHP + PostgreSQL)

## Возможности

- **Роли:** гость (без входа), **пользователь** (`user`), **администратор** (`admin`). Права проверяются на сервере по данным из БД.
- **Пользователь:** каталог, карточка объявления, подача объявления; **телефон для объявления** подставляется из профиля (поле при регистрации).
- **Администратор:** удаление объявлений (`DELETE /api/products/:id`), блокировка пользователей (блокировка запрещает создавать новые объявления), страница `/admin`.
- **База данных:** PostgreSQL. SQL-схема: `docker/postgres/init.sql`.

## Запуск в Docker

Пошагово описано в **[DOCKER.md](./DOCKER.md)**.

Кратко:

```bash
docker compose up -d --build
```

После старта администратор создаётся автоматически: логин **`admin`**, пароль **`Admin123!`** (см. `docker-compose.yml`).

- Сайт: http://localhost:3000  
- API: http://localhost:8080  

Проверка сценариев: **[TESTING.md](./TESTING.md)**.

## Локальная разработка без Docker

1. Установите PostgreSQL, выполните скрипт **`docker/postgres/init.sql`** (создаст таблицы в выбранной БД).
2. `backend/.env.example` → `backend/.env`, укажите `DB_*`.
3. Бэкенд:

```bash
cd backend/public
php -S localhost:8080
```

4. Фронт:

```bash
cd frontend
npm install
npm run dev
```

Vite проксирует `/api` на `http://localhost:8080` (см. `frontend/vite.config.js`).

## API (кратко)

| Метод | Путь | Описание |
|--------|------|----------|
| GET | `/api/health` | Проверка |
| GET | `/api/products` | Каталог |
| GET | `/api/products/{id}` | Карточка |
| POST | `/api/products` | Создать (user, не заблокирован) |
| DELETE | `/api/products/{id}` | Удалить (admin) |
| POST | `/api/auth/register` | Регистрация |
| POST | `/api/auth/login` | Вход |
| GET | `/api/me` | Профиль + роли |
| GET | `/api/my/products` | Мои объявления |
| GET | `/api/admin/users` | Список пользователей (admin) |
| POST | `/api/admin/users/{id}/block` | Заблокировать (admin) |
| POST | `/api/admin/users/{id}/unblock` | Разблокировать (admin) |

Авторизация: заголовок **`Authorization: Demo user:<числовой_id>`** (строка выдаётся после логина/регистрации). Роли на сервере читаются только из БД.

## Структура

- `frontend/` — Vue 3 (Vite)
- `backend/` — PHP, точка входа `public/index.php`
- `docker/` — Dockerfile’ы, nginx, SQL для Postgres
- `docker-compose.yml` — оркестрация
