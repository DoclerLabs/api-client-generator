FROM composer as dependencies

ARG SATIS_USERNAME
ARG SATIS_PASSWORD

WORKDIR /dependencies

COPY composer.json /dependencies
COPY composer.lock /dependencies

RUN if [ "${SATIS_USERNAME}" != "" ] && [ "${SATIS_PASSWORD}" != "" ]; then composer config http-basic.satis-public.doclerholding.com "${SATIS_USERNAME}" "${SATIS_PASSWORD}"; fi
RUN if [ "${SATIS_USERNAME}" != "" ] && [ "${SATIS_PASSWORD}" != "" ]; then composer config http-basic.satis-private.doclerholding.com "${SATIS_USERNAME}" "${SATIS_PASSWORD}"; fi

RUN composer install

FROM php:7.2-cli

LABEL maintainer="Livejasmin BE teams <livejasmin_BE@doclerholding.com>"

WORKDIR /generator

COPY . /generator
COPY --from=dependencies /dependencies/vendor /generator/vendor

ENV OPENAPI /app/doc/openapi.yaml
ENV OUTPUT_DIR /client
ENV CODE_STYLE /generator/.php_cs.php

VOLUME /app
VOLUME /client

ENTRYPOINT ["/generator/generate.sh"]
