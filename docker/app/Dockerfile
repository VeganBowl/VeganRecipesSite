FROM gliderlabs/alpine:3.1

RUN apk add --update \
    bash \
    curl \
    php-cli \
    php-phar \
    php-json \
    php-ctype \
    php-openssl \
    php-intl \
  && rm -rf /var/cache/apk/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
