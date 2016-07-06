FROM php:7.0.7-fpm-alpine

RUN echo 'http://dl-3.alpinelinux.org/alpine/edge/testing' >> /etc/apk/repositories

RUN apk upgrade --update

RUN apk update

RUN apk add git

RUN apk add graphicsmagick graphicsmagick-dev

RUN apk add php7-gmagick

RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer --version=1.0.3

RUN composer global require --prefer-dist "fxp/composer-asset-plugin"

RUN docker-php-ext-configure mbstring
RUN docker-php-ext-install mbstring zip intl bcmath mcrypt gd

CMD ["php-fpm"]