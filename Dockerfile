FROM php:8.3-alpine

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
	pdo_mysql \
	gd \
	intl \
	zip \
	opcache \
    pcntl \
    bcmath \
    redis \
    sockets \
    zstd \
    soap \
    excimer

WORKDIR /srv

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY . .

RUN apk add supervisor \
    && composer install \
    && mv container/php.ini $PHP_INI_DIR/php.ini

EXPOSE 8000

ENTRYPOINT ["/usr/bin/supervisord", "-c", "/srv/container/supervisor.conf"]