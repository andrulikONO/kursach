#!/bin/sh
set -e

echo "Ожидание PostgreSQL (${DB_HOST}:${DB_PORT})..."
i=0
while [ "$i" -lt 90 ]; do
  if php -r "
    try {
      \$pdo = new PDO(
        'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_NAME'),
        getenv('DB_USER'),
        getenv('DB_PASS'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
      );
      \$pdo->query('SELECT 1');
      exit(0);
    } catch (Throwable \$e) {
      exit(1);
    }
  " 2>/dev/null; then
    echo "PostgreSQL доступен."
    break
  fi
  i=$((i + 1))
  sleep 2
done

if [ "$i" -eq 90 ]; then
  echo "Таймаут: не удалось подключиться к БД." >&2
  exit 1
fi

echo "Создание/обновление учётной записи администратора..."
php /var/www/html/scripts/seed_admin.php

exec "$@"
