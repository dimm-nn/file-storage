#!/usr/bin/env bash

readonly ENV_DEV="Development"
readonly ENV_PROD="Production"

ENV=${ENV_DEV}

for parameter in "$@"
do
    parameterArray=(${parameter//=/ })

    case "${parameterArray[0]}" in
    --prefix)
        CONTAINER_PREFIX="${parameterArray[1]}"
    ;;
    --env)
        ENV="${parameterArray[1]}"
    ;;
    esac
done

readonly DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)

if [ -z ${CONTAINER_PREFIX} ];
then
    readonly CONTAINER_PREFIX=$(echo $(basename ${DIR}) | sed 's/[^a-zA-Z0-9]//g' | awk '{print tolower($0)}');
fi;

readonly CONTAINER_PHP="${CONTAINER_PREFIX}_php_1";
readonly CONTAINER_MYSQL="${CONTAINER_PREFIX}_mysql_1";
readonly DOCKER_NETWORK="${CONTAINER_PREFIX}_default";

# Text formatting
readonly RED=`tput setaf 1`
readonly GREEN=`tput setaf 2`
readonly YELLOW=`tput setaf 3`
readonly BLUE=`tput setaf 4`
readonly MAGENTA=`tput setaf 5`
readonly CYAN=`tput setaf 6`
readonly RESET=`tput sgr0`
readonly BOLD=`tput bold`

function section()
{
    echo "${BOLD}${GREEN}"
    echo "---------------------------------------"
    echo $1
    echo "---------------------------------------"
    echo "${RESET}"
}