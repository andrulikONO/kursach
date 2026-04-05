FROM php:8.2-apache

# libpq-dev — заголовки libpq-fe.h и pg_config для сборки pdo_pgsql
RUN apt-get update \
  && apt-get install -y --no-install-recommends libpq-dev \
  && docker-php-ext-install pdo_pgsql \
  && rm -rf /var/lib/apt/lists/* \
  && a2enmod rewrite

COPY docker/backend/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/backend/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
# На Windows в файле часто CRLF — ядро Linux тогда пишет «no such file or directory» при exec
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
  && chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html
COPY backend/ /var/www/html/

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

EXPOSE 80
