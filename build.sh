#!/usr/bin/env bash

readonly DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
readonly CONTAINER_PREFIX=$(echo $(basename ${DIR}) | sed 's/[^a-zA-Z0-9_]//g');
readonly CONTAINER_PHP="${CONTAINER_PREFIX}_php_1";
readonly SERVICE_PHP="php";

# Форматирование текста
readonly RED=`tput setaf 1`
readonly GREED=`tput setaf 2`
readonly RESET=`tput sgr0`
readonly BOLD=`tput bold`

# Окружения
readonly ENV_PRODUCTION="Production"
readonly ENV_DEVELOPMENT="Development"
readonly ENV_TEST="Test"

env=${ENV_DEVELOPMENT}

cat <<EOF
  Доступные окружения:
  --dev|-d      Development (Используется по умолчанию)
  --prod|-p     Production
  --test|-t     Test
EOF

case "$1" in
-d|--dev)
    env=${ENV_DEVELOPMENT}
    ;;
-p|--prod)
    env=${ENV_PRODUCTION}
    ;;
-t|--test)
    env=${ENV_TEST}
    ;;
esac

echo "${GREED}Иcпользуемое окружение: ${env} ${RESET}"

read -r -p "${BOLD}Пересоздать контенеры? [y/N] ${RESET}" response
response=${response,,}
if [[ ${response} =~ ^(yes|y)$ ]]
then
    {
        declare -A MAP=(
            [${ENV_PRODUCTION}]="production-compose.yml"
            [${ENV_DEVELOPMENT}]="docker-compose.yml"
            [${ENV_TEST}]="test-compose.yml"
        )

        docker-compose -f ${MAP[${ENV_DEVELOPMENT}]} up -d --force-recreate
    }
fi

read -r -p "${BOLD}Установить зависимости? [y/N] ${RESET}" response
response=${response,,}
if [[ ${response} =~ ^(yes|y)$ ]]
then
    {
        declare -A MAP=(
            [${ENV_PRODUCTION}]="--no-dev"
            [${ENV_DEVELOPMENT}]=""
            [${ENV_TEST}]="--no-dev"
        )

        docker-compose run --rm ${SERVICE_PHP} composer install --prefer-dist --optimize-autoloader ${MAP[${ENV_DEVELOPMENT}]}
    }
fi

read -r -p "${BOLD}Инициализировать приложение? [y/N] ${RESET}" response
response=${response,,}
if [[ ${response} =~ ^(yes|y)$ ]]
then
    {
        declare -A MAP=(
            [${ENV_PRODUCTION}]="Production"
            [${ENV_DEVELOPMENT}]="Development"
            [${ENV_TEST}]="Test"
        )

        docker exec ${CONTAINER_PHP} php ./init --env=${MAP[${ENV_DEVELOPMENT}]} --overwrite=y
    }
fi