#!/usr/bin/env bash
set -Eeuo pipefail

export DEBIAN_FRONTEND=noninteractive
export UCF_FORCE_CONFOLD=1
export UCF_FORCE_CONFFNEW=1
export NEEDRESTART_MODE=a
export COMPOSER_ALLOW_SUPERUSER=1

PMACTRL_INSTALL_DIR="${PMACTRL_INSTALL_DIR:-/srv/www/pmacontrol}"
PMACTRL_REPO_URL="${PMACTRL_REPO_URL:-https://github.com/PmaControl/PmaControl.git}"
PMACTRL_GIT_BRANCH="${PMACTRL_GIT_BRANCH:-commercial}"
PMACTRL_WEBROOT="${PMACTRL_WEBROOT:-/pmacontrol/}"
PMACTRL_DB_NAME="${PMACTRL_DB_NAME:-pmacontrol}"
PMACTRL_DB_USER="${PMACTRL_DB_USER:-pmacontrol}"
PMACTRL_DB_HOST="${PMACTRL_DB_HOST:-127.0.0.1}"
PMACTRL_DB_PORT="${PMACTRL_DB_PORT:-3306}"
PMACTRL_DB_PASSWORD="${PMACTRL_DB_PASSWORD:-}"
PMACTRL_ADMIN_LOGIN="${PMACTRL_ADMIN_LOGIN:-admin}"
PMACTRL_ADMIN_EMAIL="${PMACTRL_ADMIN_EMAIL:-admin@example.com}"
PMACTRL_ADMIN_FIRSTNAME="${PMACTRL_ADMIN_FIRSTNAME:-PmaControl}"
PMACTRL_ADMIN_LASTNAME="${PMACTRL_ADMIN_LASTNAME:-Admin}"
PMACTRL_ADMIN_COUNTRY="${PMACTRL_ADMIN_COUNTRY:-France}"
PMACTRL_ADMIN_CITY="${PMACTRL_ADMIN_CITY:-Paris}"
PMACTRL_ADMIN_PASSWORD="${PMACTRL_ADMIN_PASSWORD:-}"
PMACTRL_ORGANIZATION="${PMACTRL_ORGANIZATION:-PmaControl}"
PMACTRL_WEBSERVICE_USER="${PMACTRL_WEBSERVICE_USER:-webservice}"
PMACTRL_WEBSERVICE_PASSWORD="${PMACTRL_WEBSERVICE_PASSWORD:-}"
PMACTRL_FORCE_REINSTALL="${PMACTRL_FORCE_REINSTALL:-0}"
PMACTRL_DRY_RUN="${PMACTRL_DRY_RUN:-0}"
PMACTRL_KEEP_CONFIG="${PMACTRL_KEEP_CONFIG:-0}"
PMACTRL_RUN_PHPUNIT="${PMACTRL_RUN_PHPUNIT:-0}"
PMACTRL_EXPECT_UBUNTU_VERSION="${PMACTRL_EXPECT_UBUNTU_VERSION:-26.04}"
PMACTRL_SKIP_OS_CHECK="${PMACTRL_SKIP_OS_CHECK:-0}"
PMACTRL_CONFIG_FILE="${PMACTRL_CONFIG_FILE:-}"

GENERATED_CONFIG_FILE=""

usage()
{
    cat <<'USAGE'
Usage: install/ubuntu26.04.sh [options]

Options:
  -h              Print this help.
  -n              Dry-run mode; print commands without changing the system.
  -f              Force reinstall when PMACTRL_INSTALL_DIR already exists.
  -d PATH         Installation directory (default: /srv/www/pmacontrol).
  -r URL          Git repository URL.
  -b BRANCH       Git branch to clone or checkout (default: commercial).
  -p PASSWORD     Initial PmaControl admin password.
  -c FILE         Existing install JSON config to pass to install.sh.

Environment:
  PMACTRL_INSTALL_DIR, PMACTRL_REPO_URL, PMACTRL_GIT_BRANCH
  PMACTRL_DB_NAME, PMACTRL_DB_USER, PMACTRL_DB_HOST, PMACTRL_DB_PORT, PMACTRL_DB_PASSWORD
  PMACTRL_ADMIN_LOGIN, PMACTRL_ADMIN_EMAIL, PMACTRL_ADMIN_PASSWORD
  PMACTRL_ORGANIZATION, PMACTRL_WEBROOT
  PMACTRL_FORCE_REINSTALL=1, PMACTRL_DRY_RUN=1, PMACTRL_KEEP_CONFIG=1
  PMACTRL_RUN_PHPUNIT=1, PMACTRL_SKIP_OS_CHECK=1
USAGE
}

while getopts 'hnfd:r:b:p:c:' flag; do
    case "${flag}" in
        h) usage; exit 0 ;;
        n) PMACTRL_DRY_RUN=1 ;;
        f) PMACTRL_FORCE_REINSTALL=1 ;;
        d) PMACTRL_INSTALL_DIR="${OPTARG}" ;;
        r) PMACTRL_REPO_URL="${OPTARG}" ;;
        b) PMACTRL_GIT_BRANCH="${OPTARG}" ;;
        p) PMACTRL_ADMIN_PASSWORD="${OPTARG}" ;;
        c) PMACTRL_CONFIG_FILE="${OPTARG}" ;;
        *) usage; exit 1 ;;
    esac
done

log()
{
    printf '[ubuntu26.04] %s\n' "$*"
}

run()
{
    if [[ "${PMACTRL_DRY_RUN}" == "1" ]]; then
        printf '[ubuntu26.04:dry-run]'
        printf ' %q' "$@"
        printf '\n'
        return 0
    fi

    "$@"
}

run_shell()
{
    if [[ "${PMACTRL_DRY_RUN}" == "1" ]]; then
        printf '[ubuntu26.04:dry-run] bash -c %q\n' "$1"
        return 0
    fi

    bash -c "$1"
}

random_secret()
{
    if command -v openssl >/dev/null 2>&1; then
        openssl rand -base64 32 | tr -d '\n'
        return 0
    fi

    tr -dc 'A-Za-z0-9' </dev/urandom | head -c 32
}

sql_escape()
{
    printf '%s' "$1" | sed "s/'/''/g"
}

application_config_exists()
{
    [[ -f "${PMACTRL_INSTALL_DIR}/configuration/db.config.ini.php" ]]
}

cleanup()
{
    if [[ -n "${GENERATED_CONFIG_FILE}" && "${PMACTRL_KEEP_CONFIG}" != "1" && -f "${GENERATED_CONFIG_FILE}" ]]; then
        rm -f "${GENERATED_CONFIG_FILE}"
    fi
}
trap cleanup EXIT

require_root()
{
    if [[ "${PMACTRL_DRY_RUN}" == "1" ]]; then
        return 0
    fi

    if [[ "${EUID}" -ne 0 ]]; then
        echo "This script must be run as root." >&2
        exit 1
    fi
}

require_ubuntu_2604()
{
    if [[ "${PMACTRL_SKIP_OS_CHECK}" == "1" ]]; then
        log "skipping OS check because PMACTRL_SKIP_OS_CHECK=1"
        return 0
    fi

    if [[ ! -r /etc/os-release ]]; then
        echo "Missing /etc/os-release" >&2
        exit 1
    fi

    # shellcheck disable=SC1091
    . /etc/os-release

    if [[ "${ID:-}" != "ubuntu" || "${VERSION_ID:-}" != "${PMACTRL_EXPECT_UBUNTU_VERSION}" ]]; then
        echo "Unsupported OS: ${PRETTY_NAME:-unknown}. Expected Ubuntu ${PMACTRL_EXPECT_UBUNTU_VERSION}." >&2
        exit 1
    fi
}

install_packages()
{
    run apt-get update
    run apt-get -y upgrade
    run apt-get install -y \
        apache2 \
        apt-transport-https \
        bc \
        ca-certificates \
        composer \
        cron \
        curl \
        dnsutils \
        dos2unix \
        git \
        gnupg \
        graphviz \
        jq \
        libcairo2 \
        lsb-release \
        mariadb-client \
        mariadb-plugin-rocksdb \
        mariadb-server \
        net-tools \
        openssh-client \
        php \
        php-cli \
        php-curl \
        php-fpm \
        php-gd \
        php-gmp \
        php-intl \
        php-ldap \
        php-mbstring \
        php-mysql \
        php-ssh2 \
        php-xml \
        rsync \
        skopeo \
        sudo \
        sysbench \
        tig \
        unzip \
        wget \
        zip
}

detect_php_version()
{
    php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;'
}

configure_php()
{
    local php_version

    if [[ "${PMACTRL_DRY_RUN}" == "1" ]]; then
        php_version="default"
    else
        php_version="$(detect_php_version)"
    fi

    run a2enmod proxy_fcgi setenvif rewrite

    if [[ "${PMACTRL_DRY_RUN}" == "1" || -f "/etc/apache2/conf-available/php${php_version}-fpm.conf" ]]; then
        run a2enconf "php${php_version}-fpm"
    fi

    for sapi in apache2 cli fpm; do
        local ini="/etc/php/${php_version}/${sapi}/php.ini"
        if [[ "${PMACTRL_DRY_RUN}" == "1" || -f "${ini}" ]]; then
            run sed -i 's#^;*date.timezone =.*#date.timezone = Europe/Paris#g' "${ini}"
        fi
    done

    run systemctl enable --now apache2
    if [[ "${PMACTRL_DRY_RUN}" == "1" || -e "/lib/systemd/system/php${php_version}-fpm.service" ]]; then
        run systemctl enable --now "php${php_version}-fpm"
    fi
}

configure_apache()
{
    run install -d -m 0755 /srv/www
    run sed -i 's#/var/www/html#/srv/www#g' /etc/apache2/sites-enabled/000-default.conf
    run sed -i 's#/var/www#/srv/www#g' /etc/apache2/apache2.conf
    run_shell "awk '/AllowOverride/ && ++i==3 {sub(/None/,\"All\")}1' /etc/apache2/apache2.conf > /tmp/apache2.conf.pmacontrol && mv /tmp/apache2.conf.pmacontrol /etc/apache2/apache2.conf"
    run systemctl restart apache2
}

configure_mariadb()
{
    run systemctl enable --now mariadb
    run mysql -e "INSTALL SONAME 'ha_rocksdb';" || true

    if application_config_exists && [[ "${PMACTRL_FORCE_REINSTALL}" != "1" && -z "${PMACTRL_DB_PASSWORD}" ]]; then
        log "existing application config found; leaving MariaDB credentials unchanged"
        return 0
    fi

    if [[ -z "${PMACTRL_DB_PASSWORD}" ]]; then
        PMACTRL_DB_PASSWORD="$(random_secret)"
    fi

    local db_user_sql
    local db_password_sql
    db_user_sql="$(sql_escape "${PMACTRL_DB_USER}")"
    db_password_sql="$(sql_escape "${PMACTRL_DB_PASSWORD}")"

    run mysql <<SQL
CREATE USER IF NOT EXISTS '${db_user_sql}'@'127.0.0.1' IDENTIFIED BY '${db_password_sql}';
ALTER USER '${db_user_sql}'@'127.0.0.1' IDENTIFIED BY '${db_password_sql}';
GRANT ALL ON *.* TO '${db_user_sql}'@'127.0.0.1' WITH GRANT OPTION;
FLUSH PRIVILEGES;
SQL
}

prepare_repository()
{
    local install_parent
    install_parent="$(dirname "${PMACTRL_INSTALL_DIR}")"

    run install -d -m 0755 "${install_parent}"

    if [[ -d "${PMACTRL_INSTALL_DIR}" && "${PMACTRL_FORCE_REINSTALL}" == "1" ]]; then
        log "force reinstall removes ${PMACTRL_INSTALL_DIR}"
        run rm -rf "${PMACTRL_INSTALL_DIR}"
    fi

    if [[ ! -d "${PMACTRL_INSTALL_DIR}" ]]; then
        run git clone --branch "${PMACTRL_GIT_BRANCH}" --single-branch "${PMACTRL_REPO_URL}" "${PMACTRL_INSTALL_DIR}"
    elif [[ -d "${PMACTRL_INSTALL_DIR}/.git" ]]; then
        log "using existing repository ${PMACTRL_INSTALL_DIR}"
        run git -C "${PMACTRL_INSTALL_DIR}" fetch origin "${PMACTRL_GIT_BRANCH}"
        if git -C "${PMACTRL_INSTALL_DIR}" show-ref --verify --quiet "refs/heads/${PMACTRL_GIT_BRANCH}"; then
            run git -C "${PMACTRL_INSTALL_DIR}" checkout "${PMACTRL_GIT_BRANCH}"
        else
            run git -C "${PMACTRL_INSTALL_DIR}" checkout -b "${PMACTRL_GIT_BRANCH}" FETCH_HEAD
        fi
        run git -C "${PMACTRL_INSTALL_DIR}" pull --ff-only origin "${PMACTRL_GIT_BRANCH}"
    else
        log "using existing non-git directory ${PMACTRL_INSTALL_DIR}"
    fi

    run chown -R www-data:www-data "${PMACTRL_INSTALL_DIR}"
}

install_composer_dependencies()
{
    run sudo -u www-data composer --working-dir="${PMACTRL_INSTALL_DIR}" install --no-interaction
}

write_install_config()
{
    if [[ -n "${PMACTRL_CONFIG_FILE}" ]]; then
        return 0
    fi

    if [[ -z "${PMACTRL_ADMIN_PASSWORD}" ]]; then
        PMACTRL_ADMIN_PASSWORD="$(random_secret)"
    fi

    if [[ -z "${PMACTRL_WEBSERVICE_PASSWORD}" ]]; then
        PMACTRL_WEBSERVICE_PASSWORD="$(random_secret)"
    fi

    if [[ "${PMACTRL_DRY_RUN}" == "1" ]]; then
        PMACTRL_CONFIG_FILE="/root/pmacontrol-install.dry-run.json"
        log "would write generated install config to ${PMACTRL_CONFIG_FILE}"
        return 0
    fi

    GENERATED_CONFIG_FILE="$(mktemp /root/pmacontrol-install.XXXXXX.json)"
    PMACTRL_CONFIG_FILE="${GENERATED_CONFIG_FILE}"
    chmod 0600 "${PMACTRL_CONFIG_FILE}"

    cat > "${PMACTRL_CONFIG_FILE}" <<EOF
{
  "mysql": {
    "ip": "${PMACTRL_DB_HOST}",
    "port": ${PMACTRL_DB_PORT},
    "user": "${PMACTRL_DB_USER}",
    "password": "${PMACTRL_DB_PASSWORD}",
    "database": "${PMACTRL_DB_NAME}"
  },
  "organization": ["${PMACTRL_ORGANIZATION}"],
  "webroot": "${PMACTRL_WEBROOT}",
  "ldap": {
    "enabled": false,
    "url": "localhost",
    "port": 389,
    "bind dn": "",
    "bind passwd": "",
    "user base": "",
    "group base": "",
    "mapping group": {
      "Member": "CN=",
      "Administrator": "CN=",
      "SuperAdministrator": "CN="
    }
  },
  "user": {
    "Member": null,
    "Administrator": null,
    "Super administrator": [
      {
        "email": "${PMACTRL_ADMIN_EMAIL}",
        "firstname": "${PMACTRL_ADMIN_FIRSTNAME}",
        "lastname": "${PMACTRL_ADMIN_LASTNAME}",
        "country": "${PMACTRL_ADMIN_COUNTRY}",
        "city": "${PMACTRL_ADMIN_CITY}",
        "login": "${PMACTRL_ADMIN_LOGIN}",
        "password": "${PMACTRL_ADMIN_PASSWORD}"
      }
    ]
  },
  "webservice": [{
    "user": "${PMACTRL_WEBSERVICE_USER}",
    "host": "%",
    "password": "${PMACTRL_WEBSERVICE_PASSWORD}",
    "organization": "${PMACTRL_ORGANIZATION}"
  }],
  "ssh": [{
    "user": "pmacontrol",
    "private key": "",
    "public key": ""
  }]
}
EOF
}

run_application_install()
{
    if application_config_exists && [[ "${PMACTRL_FORCE_REINSTALL}" != "1" ]]; then
        log "existing application config found; skipping install.sh. Set PMACTRL_FORCE_REINSTALL=1 to rerun."
        return 0
    fi

    write_install_config
    run chmod +x "${PMACTRL_INSTALL_DIR}/install.sh"
    run bash -c "cd '${PMACTRL_INSTALL_DIR}' && ./install.sh -c '${PMACTRL_CONFIG_FILE}'"
}

install_cli_wrapper()
{
    if [[ ! -f "${PMACTRL_INSTALL_DIR}/glial" ]]; then
        log "missing glial wrapper; skipping /usr/local/bin/pmacontrol"
        return 0
    fi

    run cp -a "${PMACTRL_INSTALL_DIR}/glial" /usr/local/bin/pmacontrol
    run sed -i "s#php App/Webroot/index.php#php ${PMACTRL_INSTALL_DIR}/App/Webroot/index.php#g" /usr/local/bin/pmacontrol
    run chmod 0755 /usr/local/bin/pmacontrol
}

run_phpunit()
{
    if [[ "${PMACTRL_RUN_PHPUNIT}" != "1" ]]; then
        return 0
    fi

    run bash -c "cd '${PMACTRL_INSTALL_DIR}' && ./vendor/bin/phpunit --testsuite 'PmaControl Test Suite'"
}

print_summary()
{
    log "installation finished"
    log "URL path: ${PMACTRL_WEBROOT}"
    log "database user: ${PMACTRL_DB_USER}"

    if [[ -n "${PMACTRL_ADMIN_PASSWORD}" ]]; then
        log "admin login: ${PMACTRL_ADMIN_LOGIN}"
        log "admin password: ${PMACTRL_ADMIN_PASSWORD}"
    fi

    if [[ -n "${GENERATED_CONFIG_FILE}" && "${PMACTRL_KEEP_CONFIG}" == "1" ]]; then
        log "kept generated config: ${GENERATED_CONFIG_FILE}"
    fi
}

main()
{
    require_root
    require_ubuntu_2604
    install_packages
    prepare_repository
    configure_mariadb
    configure_php
    configure_apache
    install_composer_dependencies
    run_application_install
    install_cli_wrapper
    run_phpunit
    run systemctl restart apache2
    print_summary
}

main "$@"
