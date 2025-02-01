FROM php:8.3.15-fpm

ENV LANG=C.UTF-8
ENV TERM=xterm-256color

WORKDIR /var/www

RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install apt-utils -y

RUN apt-get update && apt-get install -y \
    build-essential \
    libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev \
    libzip-dev libpq-dev libgmp-dev libffi-dev libssl-dev \
    libfreetype6 libfreetype6-dev \
    locales \
    jpegoptim optipng pngquant gifsicle \
    nano \
    zip unzip \
    git \
    curl

RUN docker-php-ext-install gd \
      && docker-php-ext-configure gd --with-freetype --with-jpeg \
	  && docker-php-ext-install -j$(nproc) gd

RUN pecl install redis

RUN docker-php-ext-install exif opcache zip pdo pdo_mysql mysqli
RUN docker-php-ext-enable opcache zip redis exif

RUN docker-php-source delete
RUN apt-get autoremove --purge -y && apt-get autoclean -y && apt-get clean -y

COPY ./config/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

USER www

COPY --chown=www:www . /var/www
