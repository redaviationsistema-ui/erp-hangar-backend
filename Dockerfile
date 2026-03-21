FROM php:8.4-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    curl

RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

# 🔥 CREAR CARPETAS ANTES DE COMPOSER
RUN mkdir -p bootstrap/cache storage/framework storage/logs

# 🔥 DAR PERMISOS ANTES
RUN chmod -R 777 bootstrap/cache storage

# 🔥 AHORA SÍ composer
RUN composer install --no-dev --optimize-autoloader

# Limpieza (opcional pero bien)
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan cache:clear

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000