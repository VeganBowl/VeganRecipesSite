FROM gliderlabs/alpine:3.1

RUN apk add --update \
    bash \
    curl \
    gd \
    php-cli \
    php-phar \
    php-json \
    php-ctype \
    php-openssl \
    php-intl \
    php-gd \
  && rm -rf /var/cache/apk/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
