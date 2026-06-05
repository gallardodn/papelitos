FROM php:8.3-fpm-alpine

RUN apk add --no-cache bash sqlite-dev curl unzip git

RUN docker-php-source extract \
  && cp /usr/src/php/ext/sqlite3/config0.m4 /usr/src/php/ext/sqlite3/config.m4 \
  && docker-php-ext-install pdo_sqlite sqlite3 \
  && docker-php-source delete

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY docker/php-fpm/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php-fpm/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

