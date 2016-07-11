FROM php:7.0.7-fpm-alpine

RUN echo 'http://dl-3.alpinelinux.org/alpine/edge/testing' >> /etc/apk/repositories

RUN apk upgrade --update

RUN docker-php-ext-configure mbstring
RUN docker-php-ext-install mbstring zip intl bcmath mcrypt gd

RUN apk add graphicsmagick graphicsmagick-dev libtool \
    && set -xe \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install gmagick-2.0.4RC1 \
    && apk del .build-deps \
    && echo "extension=gmagick.so" > /usr/local/etc/php/conf.d/10-gmagick.ini

RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer --version=1.0.3

RUN apk add gmp php7-gmp

RUN docker-php-ext-install gmp

RUN echo "extension=/usr/lib/php7/modules/gmp.so" > /usr/local/etc/php/conf.d/00_gmp.ini

CMD ["php-fpm"]