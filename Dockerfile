# Use Alpine Linux as base image
FROM php:7.4-fpm-alpine AS build

# Set environment variables
ENV APP_DIR="/var/www/html" \
    APP_PORT="80" \
    DB_HOST="10.10.1.141" \
    DB_DATABASE="project_lpms" \
    DB_USERNAME="root" \
    DB_PASSWORD="Hagbes_1234"

# Set working directory
WORKDIR $APP_DIR

# Copy application code
COPY . .

# Install Composer
RUN apk --no-cache add \
        curl \
        git \
        bash \
        zip \
        unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions and other necessary packages
RUN apk --no-cache add \
        tzdata \
        postgresql-dev \
        libzip-dev \
        mariadb-dev \
    && docker-php-ext-install pdo_pgsql zip mysqli \
    && cp /usr/share/zoneinfo/UTC /etc/localtime \
    && echo "UTC" > /etc/timezone
#install php

RUN apk add --no-cache php8 \
    php8-common \
    php8-fpm \
    php8-pdo \
    php8-opcache \
    php8-zip \
    php8-phar \
    php8-iconv \
    php8-cli \
    php8-curl \
    php8-openssl \
    php8-mbstring \
    php8-tokenizer \
    php8-fileinfo \
    php8-json \
    php8-xml \
    php8-xmlwriter \
    php8-simplexml \
    php8-dom \
    php8-pdo_mysql \
    php8-pdo_sqlite \
    php8-tokenizer \
    php8-pecl-redis


# ---- Runtime Stage ----
FROM php:7.4-fpm-alpine AS runtime

# Set environment variables
ENV APP_DIR="/var/www/html" \
    APP_PORT="80"

# Set working directory
WORKDIR $APP_DIR

# Copy application code and dependencies from build stage
COPY --from=build $APP_DIR .

# Expose the port where the application is running
EXPOSE $APP_PORT

# CMD to run your application
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html"]
