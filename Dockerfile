FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod headers rewrite

WORKDIR /var/www/html

COPY . /var/www/html/

RUN mkdir -p /var/www/html/logs /var/www/html/uploads/avatars \
    && chown -R www-data:www-data /var/www/html/logs /var/www/html/uploads

ENV PORT=80

EXPOSE 80

CMD printf "Listen %s\n" "${PORT}" > /etc/apache2/ports.conf \
    && printf "%s\n" \
        "<VirtualHost *:${PORT}>" \
        "    ServerName localhost" \
        "    DocumentRoot /var/www/html" \
        "    DirectoryIndex index.html index.php" \
        "    RewriteEngine On" \
        "    RewriteCond %{REQUEST_FILENAME} !-f" \
        "    RewriteCond %{REQUEST_FILENAME} !-d" \
        "    RewriteCond %{DOCUMENT_ROOT}/%{REQUEST_URI}.html -f" \
        "    RewriteRule ^(.+)$ \$1.html [L]" \
        "    <Directory /var/www/html>" \
        "        Options -Indexes +FollowSymLinks" \
        "        AllowOverride All" \
        "        Require all granted" \
        "    </Directory>" \
        "    ErrorLog \${APACHE_LOG_DIR}/error.log" \
        "    CustomLog \${APACHE_LOG_DIR}/access.log combined" \
        "</VirtualHost>" \
        > /etc/apache2/sites-available/000-default.conf \
    && apache2-foreground
