# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=8.1.3
ARG NGINX_VERSION=1.19

# "php" stage
FROM php:${PHP_VERSION}-fpm-alpine AS symfony_php_base

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		fcgi \
		file \
		gettext \
		git \
		gnu-libiconv \
    	make \
	;

# install gnu-libiconv and set LD_PRELOAD env to make iconv work fully on Alpine image.
# see https://github.com/docker-library/php/issues/240#issuecomment-763112749
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so

ARG APCU_VERSION=5.1.21
ARG MPDECIMAL_VERSION=2.5.1
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		icu-dev \
		libzip-dev \
		zlib-dev \
        mysql-dev \
	; \
    cd /tmp/; \
            curl -sSL -O https://launchpad.net/ubuntu/+archive/primary/+sourcefiles/mpdecimal/${MPDECIMAL_VERSION}-1/mpdecimal_${MPDECIMAL_VERSION}.orig.tar.gz; \
            tar -xzf mpdecimal_${MPDECIMAL_VERSION}.orig.tar.gz; \
                cd mpdecimal-${MPDECIMAL_VERSION}; \
                ./configure; \
                make; \
                make install \
        ; \
	\
	docker-php-ext-configure zip; \
	docker-php-ext-install -j$(nproc) \
		intl \
		zip \
        pdo_mysql\
	; \
	pecl install \
		apcu-${APCU_VERSION} \
        pcov \
    	decimal \
    	redis \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
		apcu \
		opcache \
    	decimal \
    	redis \
	; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .phpexts-rundeps $runDeps; \
	\
	apk del .build-deps

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY docker/php/conf.d/symfony.prod.ini $PHP_INI_DIR/conf.d/symfony.ini

COPY docker/php/conf.d/zz-php-fpm.conf /usr/local/etc/php-fpm.d/

ENV SYMFONY_PHPUNIT_VERSION=9.5

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/api

# copy only specifically what we need
COPY bin bin/
COPY config config/
COPY public public/
COPY src src/

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ARG SECURITY_CHECKER_VERSION=1.2.0
RUN wget -O local-php-security-checker https://github.com/fabpot/local-php-security-checker/releases/download/v${SECURITY_CHECKER_VERSION}/local-php-security-checker_${SECURITY_CHECKER_VERSION}_linux_amd64 \
    && mv local-php-security-checker /usr/bin/local-php-security-checker \
    && chmod +x /usr/bin/local-php-security-checker

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

# for production
FROM symfony_php_base as symfony_php_prod

ENV DATABASE_NAME "$DATABASE_NAME"
ENV DATABASE_HOST "$DATABASE_HOST"
ENV DATABASE_USER "$DATABASE_USER"
ENV DATABASE_PASSWORD "$DATABASE_PASSWORD"

# build for production
ARG APP_ENV=prod

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.json composer.lock symfony.lock ./
RUN set -eux; \
	composer install --prefer-dist --no-dev --no-scripts --no-progress --no-suggest; \
	composer clear-cache

COPY .env ./

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer symfony:dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync

VOLUME /srv/api/var

# for local development
FROM symfony_php_base as symfony_php_dev

ARG XDEBUG_VERSION=3.1.3
RUN set -xe \
		&& apk add --no-cache --virtual .build-deps \
			$PHPIZE_DEPS \
		&& pecl install \
			xdebug-${XDEBUG_VERSION} \
		&& docker-php-ext-enable \
			pcov \
			xdebug \
		&& pecl clear-cache \
		&& runDeps="$( \
			scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
				| tr ',' '\n' \
				| sort -u \
				| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
		)" \
		&& apk add --no-cache --virtual .api-phpexts-rundeps $runDeps \
		&& apk del .build-deps

# Copy Xdebug configuration for remote debugging
COPY docker/php/xdebug/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# "nginx" stage
# depends on the "php" stage above
FROM nginx:${NGINX_VERSION}-alpine AS symfony_nginx_base

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /srv/api/public

FROM symfony_nginx_base as symfony_nginx_prod

COPY --from=symfony_php_prod /srv/api/public ./

FROM symfony_nginx_base as symfony_nginx_dev

COPY --from=symfony_php_dev /srv/api/public ./

