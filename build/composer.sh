#!/usr/bin/env bash

{
    source common.sh # For IDE
    . "${BASH_SOURCE%/*}/common.sh"
} 2>> /dev/null

section "Install composer dependencies"

docker exec ${CONTAINER_PHP} composer config -g github-oauth.github.com 2272fe6f5a939dddb1f30a5176eaee15f990836c

case ${ENV} in
    ${ENV_DEV})
    docker exec ${CONTAINER_PHP} composer install
    ;;
    ${ENV_PROD})
    docker exec ${CONTAINER_PHP} composer install --prefer-dist --optimize-autoloader --no-dev
    ;;
esac