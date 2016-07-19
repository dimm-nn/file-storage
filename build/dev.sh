#!/usr/bin/env bash

{
    source _common.sh
} 2>> /dev/null
source $(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/_common.sh

section "Используемое окружение"
echo "Development"