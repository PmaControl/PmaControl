#!/usr/bin/env bash
set -Eeuo pipefail

BASE_URL="${1:-http://127.0.0.1}"
APP_PATH="${2:-/pmacontrol/}"

BASE_URL="${BASE_URL%/}"
case "${APP_PATH}" in
    /*) ;;
    *) APP_PATH="/${APP_PATH}" ;;
esac

if [[ "${APP_PATH}" != */ ]]; then
    APP_PATH="${APP_PATH}/"
fi

http_code()
{
    local path="$1"

    curl -sS -o /dev/null -w '%{http_code}' "${BASE_URL}${path}" || true
}

expect_code()
{
    local path="$1"
    local expected="$2"
    local code

    code="$(http_code "${path}")"
    if [[ "${code}" != "${expected}" ]]; then
        echo "Unexpected HTTP status for ${path}: got ${code}, expected ${expected}" >&2
        return 1
    fi
}

expect_code_in()
{
    local path="$1"
    shift
    local code
    local expected

    code="$(http_code "${path}")"
    for expected in "$@"; do
        if [[ "${code}" == "${expected}" ]]; then
            return 0
        fi
    done

    echo "Unexpected HTTP status for ${path}: got ${code}, expected one of: $*" >&2
    return 1
}

expect_app_reachable()
{
    local path="$1"
    local code

    code="$(http_code "${path}")"
    case "${code}" in
        200|301|302)
            return 0
        ;;
    esac

    echo "Unexpected HTTP status for ${path}: got ${code}, expected 200/301/302" >&2
    return 1
}

deny_paths=(
    "/"
    "/?C=N;O=D"
    "${APP_PATH}.env"
    "${APP_PATH}.git/config"
    "${APP_PATH}configuration/"
    "${APP_PATH}configuration/db.config.ini.php"
    "${APP_PATH}install/"
    "${APP_PATH}composer.json"
)

optional_deny_paths=(
    "/infra/"
    "/infra/docs/"
    "/glial/"
    "/save_pmacontrol/"
)

for path in "${deny_paths[@]}"; do
    expect_code "${path}" "403"
done

for path in "${optional_deny_paths[@]}"; do
    expect_code_in "${path}" "403" "404"
done

expect_app_reachable "${APP_PATH}"
expect_app_reachable "${APP_PATH}App/Webroot/"
