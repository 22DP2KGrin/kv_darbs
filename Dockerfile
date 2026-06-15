FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod headers rewrite

WORKDIR /var/www/html

COPY . /var/www/html/

RUN mkdir -p /var/www/html/logs /var/www/html/uploads/avatars \
    && chown -R www-data:www-data /var/www/html/logs /var/www/html/uploads

EXPOSE 80
