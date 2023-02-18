FROM php:8-fpm

COPY ./docker/php-fpm/scripts/* /tmp/

# install DEB dependecies
RUN apt-get update \
  && DEBIAN_FRONTEND=noninteractive apt-get -qq install -y --no-install-recommends \
  iputils-ping \
  git \
  mariadb-client-10.5 \
  yarnpkg \
  mc \
  vim \
  wget \
  zip \
  unzip \
  libzip-dev \
  libicu-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  libfreetype6-dev

# install PHP DB dependences
RUN docker-php-ext-install mysqli pdo_mysql

# configure and install PHP Intl library
RUN docker-php-ext-configure intl \
 && docker-php-ext-install intl

# configure and install PHP ZIP library
RUN docker-php-ext-configure zip \
 && docker-php-ext-install zip

# configure and install PHP GD library
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j "$(nproc)" gd

# install composer
RUN /tmp/install-composer.sh

# cleanup
RUN rm -rf /var/lib/apt/lists/*