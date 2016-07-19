#!/usr/bin/env bash

{
    source _common.sh
} 2>> /dev/null
source $(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/_common.sh

section "Используемое окружение"
echo "Production"

section "Выкачиваем docker образы"
docker-compose -f ${DIR}/docker-compose.yml pull

section "Устанавливаем composer зависимости"
docker exec ${CONTAINER_PHP} composer install --prefer-dist --optimize-autoloader --no-dev