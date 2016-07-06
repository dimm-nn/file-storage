FROM php:7.0.7-fpm-alpine

RUN echo 'http://dl-3.alpinelinux.org/alpine/edge/testing' >> /etc/apk/repositories

RUN apk upgrade --update

RUN apk update

RUN apk add graphicsmagick graphicsmagick-dev libtool

RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer --version=1.0.3 \
    && composer global require --prefer-dist "fxp/composer-asset-plugin"

RUN docker-php-ext-configure mbstring
RUN docker-php-ext-install mbstring zip intl bcmath mcrypt gd

RUN set -xe \
   && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
   && pecl install gmagick-2.0.4RC1 \
   && apk del .build-deps

RUN echo "extension=gmagick.so" > /usr/local/etc/php/conf.d/10-gmagick.ini

CMD ["php-fpm"]