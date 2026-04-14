# Запуск проекта через Docker

Стек: **PostgreSQL 16**, **PHP 8.2 + Apache** (бэкенд), **Nginx** (собранный Vue-фронт).  
Капча в этом репозитории **не используется** (в отличие от старого UserAuthApp).

## Требования

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (или Docker Engine + Compose v2)

## Быстрый старт

Из **корня репозитория** (где лежит `docker-compose.yml`):

```bash
docker compose build
docker compose up -d
```

Подождите, пока Postgres станет `healthy` (обычно 10–30 секунд).

### Адреса

| Сервис    | -------------- URL--------------- |
|-----------| --------------------------------- |
| Сайт (UI)             | http://localhost:3000 |
| API напрямую (бэкенд) | http://localhost:8080 |
| pgadmin               | http://localhost:5050 |
Фронт проксирует запросы **`/api/*`** на контейнер `backend`, поэтому в браузере удобно работать только с **http://localhost:3000**.

## Инициализация базы

Схема создаётся автоматически из файла **`docker/postgres/init.sql`** при **первом** создании тома `pgdata`.

Если нужно пересоздать БД с нуля:

```bash
docker compose down -v
docker compose up -d
```

**Внимание:** `docker compose down -v` удалит все данные в PostgreSQL.

## Учётная запись администратора

При **каждом** старте контейнера `backend` скрипт `docker/backend/docker-entrypoint.sh`:

1. ждёт доступность PostgreSQL;
2. запускает `php scripts/seed_admin.php` (создаёт пользователя или обновляет пароль).

Учётные данные по умолчанию задаются в `docker-compose.yml`:

- логин: **`admin`**
- пароль: **`Admin123!`**

Поменять пароль/логин можно переменными окружения сервиса `backend`: `ADMIN_LOGIN`, `ADMIN_PASSWORD`, `ADMIN_EMAIL`, `ADMIN_PHONE`.

Повторный ручной запуск (если нужно):

```bash
docker compose exec backend php scripts/seed_admin.php
```

Администратору назначаются роли **`user`** и **`admin`** в `user_roles`.

## Переменные окружения бэкенда

В `docker-compose.yml` для сервиса `backend` уже заданы:

- `DB_HOST=db`
- `DB_PORT=5432`
- `DB_NAME=classifieds`
- `DB_USER=classifieds`
- `DB_PASS=classifieds`

Локальная отладка без Docker: скопируйте `backend/.env.example` в `backend/.env` и укажите свой PostgreSQL.

## Логи и отладка

```bash
docker compose logs -f backend
docker compose logs -f db
docker compose logs -f frontend
```

Остановка:

```bash
docker compose down
```

## Подключение к PostgreSQL с хоста

Порт **5432** проброшен наружу. Пример (psql на вашей машине):

```bash
psql -h localhost -p 5432 -U classifieds -d classifieds
```

Пароль: `classifieds` (как в `docker-compose.yml`).

## Типичные проблемы

1. **Порт 5432 или 3000 уже занят** — измените проброс портов в `docker-compose.yml` (секция `ports`).
2. **Фронт не видит API** — открывайте приложение через **http://localhost:3000**, а не через `file://`.
3. **`exec ... docker-entrypoint.sh: no such file or directory` у `backend`** — у скрипта были окончания строк Windows (CRLF). В образе это уже исправляется при сборке; выполните **`docker compose build --no-cache backend`** и снова **`docker compose up -d`**. В Git для `*.sh` задано `eol=lf` в `.gitattributes`.
4. **403 на /api/admin/** — войдите под админом (учётка создаётся при старте `backend`, см. выше).

Подробная проверка сценариев — в файле **`TESTING.md`**.
