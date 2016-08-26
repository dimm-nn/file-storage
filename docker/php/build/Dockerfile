FROM php:7.0.7-fpm-alpine

RUN echo '@testing http://dl-cdn.alpinelinux.org/alpine/edge/testing' >> /etc/apk/repositories
RUN echo '@community http://dl-cdn.alpinelinux.org/alpine/edge/community' >> /etc/apk/repositories

RUN apk upgrade --update

RUN apk --no-cache add \
    git

RUN set -xe \
    && apk add --no-cache \
       graphicsmagick@community \
       graphicsmagick-dev@community \
       libtool \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install gmagick-2.0.4RC1 \
    && echo "extension=gmagick.so" > /usr/local/etc/php/conf.d/10-gmagick.ini

RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer --version=1.0.3

RUN apk add --no-cache \
    gmp \
    php7-gmp@testing \
    && docker-php-ext-install gmp \
    && echo "extension=/usr/lib/php7/modules/gmp.so" > /usr/local/etc/php/conf.d/00_gmp.ini

RUN apk del .build-deps

CMD ["php-fpm"]