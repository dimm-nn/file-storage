FROM php:7.0.6-fpm

RUN apt-get update

RUN apt-get install -y \
    git g++ libssl-dev libmcrypt-dev \
    zlibc zlib1g zlib1g-dev \
    libicu52 libicu-dev \
    libgd2-xpm-dev \
    --no-install-recommends

RUN docker-php-ext-configure mbstring
RUN docker-php-ext-install mbstring zip intl bcmath mcrypt gd

RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer --version=1.0.3

RUN composer global require --prefer-dist "fxp/composer-asset-plugin:~1.1.4"

RUN mkdir -p /var/log/php-fpm
RUN ln -sf /dev/stdout /var/log/php-fpm/access.log
RUN ln -sf /dev/stderr /var/log/php-fpm/error.log

RUN rm -r /var/lib/apt/lists/*

WORKDIR /www

CMD ["php-fpm"]