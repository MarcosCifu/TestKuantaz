# Use an official PHP runtime as a parent image
FROM php:8.2-fpm

ENV COMPOSER_ALLOW_SUPERUSER 1
# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    bash

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/

COPY ../ .
RUN cp .env.example .env
RUN rm -rf composer.lock
RUN rm -rf package-lock.json
RUN rm -rf vendor
RUN composer install
# RUN composer install --no-scripts
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs
RUN npm install npm@10.2.4 -g
RUN npm install
# RUN npm run dev
RUN php artisan key:generate
RUN php artisan storage:link

EXPOSE 80

RUN ln -sf /bin/bash /bin/sh

CMD php artisan serve --host 0.0.0.0 --port 80

