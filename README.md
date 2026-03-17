# Kursach: сайт объявлений (скелет)

Проект разделён на две части:

- `frontend/` — Vue (каталог, карточка товара, форма подачи, фильтр + “тип вещи”)
- `backend/` — PHP (API + работа с БД через PDO)
- `db/` — SQL схема и описание ролей

## 1) Backend (PHP)

### Настройка

1. Скопируй пример переменных окружения:
   - `backend/.env.example` → `backend/.env`
2. Заполни доступ к MySQL:
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`

### Запуск

Запуск встроенного сервера PHP:

```bash
cd backend/public
php -S localhost:8080
```

Проверка:
- `GET /api/health`
- `GET /api/products`

### Роли (важно)

Создание объявления защищено разрешением `products.create`.
Чтобы быстро протестировать в скелете, бэкенд понимает demo-заголовок:

- `Authorization: Demo user:1 roles:seller`

Фронтенд в DEV-режиме **сам добавляет** demo-заголовок (см. `frontend/src/lib/api.js`), чтобы форма подачи работала сразу.

## 2) Frontend (Vue)

### Установка и запуск

```bash
cd frontend
npm install
npm run dev
```

Vite настроен на прокси `/api` → `http://localhost:8080`.

### Страницы

- `/` — каталог (список + фильтры)
- `/product/:id` — карточка товара
- `/new` — форма подачи объявления


