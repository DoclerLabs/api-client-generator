FROM php:7.4-cli-alpine as dependencies

WORKDIR /dependencies

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY composer.json /dependencies
COPY composer.lock /dependencies

RUN composer install

FROM php:7.4-cli-alpine

ARG API_CLIENT_GENERATOR_VERSION
ENV API_CLIENT_GENERATOR_VERSION=$API_CLIENT_GENERATOR_VERSION

COPY . /generator
COPY --from=dependencies /dependencies/vendor /generator/vendor

RUN chmod +x /generator/bin/api-client-generator

WORKDIR /generator/bin

CMD ["php", "api-client-generator", "generate"]
