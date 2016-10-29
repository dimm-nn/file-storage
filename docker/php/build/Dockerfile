FROM php:7.0.9-fpm-alpine

RUN echo 'http://dl-3.alpinelinux.org/alpine/edge/community' >> /etc/apk/repositories \
        && apk add --no-cache --virtual .persistent-deps \
	    git \
	    graphicsmagick \
	    gmp

RUN set -xe \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        graphicsmagick-dev \
        libtool \
        gmp-dev \
    && docker-php-ext-install gmp \
    && pecl install gmagick-2.0.4RC1 \
    && docker-php-ext-enable gmagick \
    && apk del .build-deps


RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer --version=1.0.3


CMD ["php-fpm"]