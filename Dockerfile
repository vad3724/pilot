FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx \
    && rm -f /etc/nginx/conf.d/default.conf /etc/nginx/sites-enabled/default \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY index.php ./index.php
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm -F & nginx -g 'daemon off;'"]

