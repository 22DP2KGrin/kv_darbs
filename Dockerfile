FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod headers rewrite

WORKDIR /var/www/html

COPY . /var/www/html/

RUN mkdir -p /var/www/html/logs /var/www/html/uploads/avatars \
    && chown -R www-data:www-data /var/www/html/logs /var/www/html/uploads

ENV PORT=80

EXPOSE 80

CMD sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf \
    && sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf \
    && apache2-foreground
