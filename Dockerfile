FROM keinos/php8-jit

USER root

RUN apk add --update --no-cache \
	bash curl wget rsync ca-certificates openssl openssh git tzdata openntpd \
	libxrender fontconfig libc6-compat \
	mysql-client gnupg binutils-gold autoconf \
	g++ gcc gnupg libgcc linux-headers make

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
	&& chmod 755 /usr/bin/composer

# GD
RUN apk add --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev libxpm-dev \
	&& docker-php-ext-configure gd \
        --with-freetype=/usr/include/ \
        --with-jpeg=/usr/include/ \
        --with-xpm=/usr/include/ \
	&& NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) \
	&& docker-php-ext-install -j${NPROC} gd \
	&& apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev

# ZIP
RUN apk add --update --no-cache zlib-dev libzip-dev zip \
	&& docker-php-ext-install zip

# mysqli
RUN docker-php-ext-install mysqli \
	&& docker-php-ext-enable mysqli

# xDebug
RUN pecl install xdebug-3.0.3 \
	&& docker-php-ext-enable xdebug

# memcached
ENV MEMCACHED_DEPS zlib-dev libmemcached-dev
RUN apk add --no-cache --update libmemcached-dev \
	&& mkdir -p /usr/src/php/php-src-master/ext/memcached \
	&& curl -fsSL https://pecl.php.net/get/memcached | tar xvz -C "/usr/src/php/php-src-master/ext/memcached" --strip 1 \
	&& docker-php-ext-install memcached

# redis
RUN mkdir -p /usr/src/php/php-src-master/ext/redis \
	&& curl -fsSL https://pecl.php.net/get/redis | tar xvz -C "/usr/src/php/php-src-master/ext/redis" --strip 1 \
	&& docker-php-ext-install redis

ADD .docker/php/docker-php-enable-jit.ini /usr/local/etc/php/conf.d/docker-php-enable-jit.ini
ADD .docker/php/docker-php-ext-redis.ini /usr/local/etc/php/conf.d/docker-php-ext-redis.ini

# Set working directory
WORKDIR /var/www
