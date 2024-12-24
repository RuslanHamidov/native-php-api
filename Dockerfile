FROM php:8.1-fpm

# Install necessary extensions
RUN docker-php-ext-install pdo pdo_mysql
