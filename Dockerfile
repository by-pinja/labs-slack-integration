FROM php:7.1-apache

RUN apt-get update \
    && apt-get install -y \
        zlib1g-dev \
        nano \
        git \
    && a2enmod rewrite remoteip

RUN docker-php-ext-install -j$(nproc) zip \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql bcmath

COPY ./ /labs-slack-integration
COPY ./build-config/apache2.conf /etc/apache2/conf-enabled/type.conf
COPY ./build-config/php.ini /usr/local/etc/php/
COPY ./build-config/php-cli.ini /usr/local/etc/php/

WORKDIR /labs-slack-integration

RUN rm -rf /labs-slack-integration/var \
    && mkdir /labs-slack-integration/var \
    && rm -rf /var/www/html/ \
    && ln -s /labs-slack-integration/public/ /var/www/html \
    && rm -rf /labs-slack-integration/.env \
    && ln -s /secret/.env /labs-slack-integration/.env \
    && chmod +x /labs-slack-integration/docker-entrypoint.sh

ENTRYPOINT ["/labs-slack-integration/docker-entrypoint.sh"]
