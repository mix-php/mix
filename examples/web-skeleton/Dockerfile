FROM phpswoole/swoole:4.8-php8.0 as Build

ARG timezone

ENV APP_DEBUG=false

RUN sed -i "s@http://deb.debian.org@https://repo.huaweicloud.com@g" /etc/apt/sources.list \
    && sed -i "s@http://security.debian.org@https://repo.huaweicloud.com@g" /etc/apt/sources.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
    libonig-dev \
    wget \
    libzip-dev \
    libpng-dev \
    libicu-dev \
    libbz2-dev \
    procps \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    rm -rf /var/lib/apt/lists/*
RUN set -ex \
    && pecl update-channels \
    && pecl install redis-stable \
    && docker-php-ext-enable redis \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure bcmath --enable-bcmath \
    && docker-php-ext-configure mbstring --enable-mbstring \
    && docker-php-ext-configure mysqli --with-mysqli \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
    gd \
    bcmath \
    intl \
    pcntl \
    mysqli \
    pdo_mysql \
    mbstring \
    iconv \
    bz2 \
    sockets \
    zip \
    opcache \
    exif

WORKDIR /app
COPY . /app

EXPOSE 9501

RUN composer install --no-dev -o

ENTRYPOINT ["php", "/app/bin/swoole.php", "start"]
