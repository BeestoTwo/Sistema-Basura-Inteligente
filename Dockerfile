# Imagen para desplegar en Render (PHP 8.3 + Apache)
FROM php:8.3-apache

RUN docker-php-ext-install mysqli

COPY . /var/www/html/

# En el servidor la configuración se toma de las variables de entorno
RUN cp -n /var/www/html/config.example.php /var/www/html/config.php \
    && chown -R www-data:www-data /var/www/html

# Render indica el puerto en la variable PORT (por defecto 10000)
ENV PORT=10000
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf

EXPOSE 10000
