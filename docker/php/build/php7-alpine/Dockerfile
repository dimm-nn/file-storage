FROM php:7.0.7-fpm-alpine

RUN apk update

RUN apk add --force git

RUN apk add --update imagemagick-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick