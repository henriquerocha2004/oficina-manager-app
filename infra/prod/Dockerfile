FROM php:8.5-fpm-alpine AS php-builder

RUN apk add --no-cache \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    oniguruma-dev \
    libxml2-dev \
    postgresql-dev \
    zip \
    unzip \
    git \
    curl \
    bash \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo_pgsql mbstring exif pcntl bcmath gd intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

FROM node:22-alpine AS frontend-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./vite.config.js
RUN npm run build

FROM php:8.5-fpm-alpine AS app-runtime

RUN apk add --no-cache \
    icu \
    libpng \
    libjpeg-turbo \
    libwebp \
    oniguruma \
    libxml2 \
    postgresql-libs \
    curl \
    bash

WORKDIR /var/www

COPY --from=php-builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=php-builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d
COPY --from=php-builder /var/www /var/www
COPY --from=frontend-builder /app/public/build /var/www/public/build
COPY infra/prod/php.ini /usr/local/etc/php/conf.d/99-production.ini
COPY infra/prod/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY infra/prod/healthcheck.sh /usr/local/bin/healthcheck.sh

RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/healthcheck.sh \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

USER www-data

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]

FROM nginx:1.28-alpine AS web-runtime

WORKDIR /var/www

COPY infra/prod/nginx/prod.conf /etc/nginx/conf.d/default.conf
COPY --from=php-builder /var/www/public /var/www/public
COPY --from=frontend-builder /app/public/build /var/www/public/build

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
