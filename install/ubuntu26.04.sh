#!/usr/bin/env bash
set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=install/lib/harden_network.sh
. "${SCRIPT_DIR}/lib/harden_network.sh"
# shellcheck source=install/lib/harden_apache.sh
. "${SCRIPT_DIR}/lib/harden_apache.sh"

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
PMACTRL_HARDEN_DB_BIND="${PMACTRL_HARDEN_DB_BIND:-1}"
PMACTRL_DB_BIND_ADDRESS="${PMACTRL_DB_BIND_ADDRESS:-127.0.0.1,::1}"
PMACTRL_RPCBIND_POLICY="${PMACTRL_RPCBIND_POLICY:-disable}"
PMACTRL_HARDEN_APACHE_DOCROOT="${PMACTRL_HARDEN_APACHE_DOCROOT:-1}"
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
PMACTRL_SKIP_UPGRADE="${PMACTRL_SKIP_UPGRADE:-0}"
PMACTRL_CREDENTIALS_FILE="${PMACTRL_CREDENTIALS_FILE:-/root/pmacontrol-credentials.txt}"
PMACTRL_CONFIG_FILE="${PMACTRL_CONFIG_FILE:-}"

GENERATED_CONFIG_FILE=""
CREDENTIALS_WRITTEN=0

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
  PMACTRL_HARDEN_DB_BIND=1, PMACTRL_DB_BIND_ADDRESS=127.0.0.1,::1
  PMACTRL_RPCBIND_POLICY=disable|mask|leave
  PMACTRL_HARDEN_APACHE_DOCROOT=1
  PMACTRL_ADMIN_LOGIN, PMACTRL_ADMIN_EMAIL, PMACTRL_ADMIN_PASSWORD
  PMACTRL_ORGANIZATION, PMACTRL_WEBROOT
  PMACTRL_FORCE_REINSTALL=1, PMACTRL_DRY_RUN=1, PMACTRL_KEEP_CONFIG=1
  PMACTRL_RUN_PHPUNIT=1, PMACTRL_SKIP_OS_CHECK=1, PMACTRL_SKIP_UPGRADE=1
  PMACTRL_CREDENTIALS_FILE=/root/pmacontrol-credentials.txt
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

    local secret
    secret="$(od -An -N32 -tx1 /dev/urandom | tr -d ' \n')"
    printf '%s' "${secret:0:32}"
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
    if [[ "${PMACTRL_SKIP_UPGRADE}" == "1" ]]; then
        log "skipping apt-get upgrade because PMACTRL_SKIP_UPGRADE=1"
    else
        run apt-get -y upgrade
    fi
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
        php-zip \
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
    pmactrl_harden_apache_docroot
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

    jq -n \
        --arg db_host "${PMACTRL_DB_HOST}" \
        --argjson db_port "${PMACTRL_DB_PORT}" \
        --arg db_user "${PMACTRL_DB_USER}" \
        --arg db_password "${PMACTRL_DB_PASSWORD}" \
        --arg db_name "${PMACTRL_DB_NAME}" \
        --arg organization "${PMACTRL_ORGANIZATION}" \
        --arg webroot "${PMACTRL_WEBROOT}" \
        --arg admin_email "${PMACTRL_ADMIN_EMAIL}" \
        --arg admin_firstname "${PMACTRL_ADMIN_FIRSTNAME}" \
        --arg admin_lastname "${PMACTRL_ADMIN_LASTNAME}" \
        --arg admin_country "${PMACTRL_ADMIN_COUNTRY}" \
        --arg admin_city "${PMACTRL_ADMIN_CITY}" \
        --arg admin_login "${PMACTRL_ADMIN_LOGIN}" \
        --arg admin_password "${PMACTRL_ADMIN_PASSWORD}" \
        --arg webservice_user "${PMACTRL_WEBSERVICE_USER}" \
        --arg webservice_password "${PMACTRL_WEBSERVICE_PASSWORD}" \
        '{
            mysql: {
                ip: $db_host,
                port: $db_port,
                user: $db_user,
                password: $db_password,
                database: $db_name
            },
            organization: [$organization],
            webroot: $webroot,
            ldap: {
                enabled: false,
                url: "localhost",
                port: 389,
                "bind dn": "",
                "bind passwd": "",
                "user base": "",
                "group base": "",
                "mapping group": {
                    Member: "CN=",
                    Administrator: "CN=",
                    SuperAdministrator: "CN="
                }
            },
            user: {
                Member: null,
                Administrator: null,
                "Super administrator": [
                    {
                        email: $admin_email,
                        firstname: $admin_firstname,
                        lastname: $admin_lastname,
                        country: $admin_country,
                        city: $admin_city,
                        login: $admin_login,
                        password: $admin_password
                    }
                ]
            },
            webservice: [
                {
                    user: $webservice_user,
                    host: "%",
                    password: $webservice_password,
                    organization: $organization
                }
            ],
            ssh: [
                {
                    user: "pmacontrol",
                    "private key": "",
                    "public key": ""
                }
            ]
        }' > "${PMACTRL_CONFIG_FILE}"
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

write_credentials_file()
{
    if application_config_exists \
        && [[ "${PMACTRL_FORCE_REINSTALL}" != "1" ]] \
        && [[ -z "${PMACTRL_DB_PASSWORD}" ]] \
        && [[ -z "${PMACTRL_ADMIN_PASSWORD}" ]] \
        && [[ -z "${PMACTRL_WEBSERVICE_PASSWORD}" ]]; then
        log "existing application config found and no new secrets generated; leaving credentials file unchanged"
        return 0
    fi

    if [[ "${PMACTRL_DRY_RUN}" == "1" ]]; then
        log "would write credentials summary to ${PMACTRL_CREDENTIALS_FILE}"
        return 0
    fi

    if [[ -z "${PMACTRL_CREDENTIALS_FILE}" ]]; then
        return 0
    fi

    install -m 0600 /dev/null "${PMACTRL_CREDENTIALS_FILE}"
    {
        printf 'PmaControl install credentials\n'
        printf 'install_dir=%s\n' "${PMACTRL_INSTALL_DIR}"
        printf 'webroot=%s\n' "${PMACTRL_WEBROOT}"
        printf 'db_user=%s\n' "${PMACTRL_DB_USER}"
        if [[ -n "${PMACTRL_DB_PASSWORD}" ]]; then
            printf 'db_password=%s\n' "${PMACTRL_DB_PASSWORD}"
        fi
        if [[ -n "${PMACTRL_ADMIN_PASSWORD}" ]]; then
            printf 'admin_login=%s\n' "${PMACTRL_ADMIN_LOGIN}"
            printf 'admin_password=%s\n' "${PMACTRL_ADMIN_PASSWORD}"
        fi
        if [[ -n "${PMACTRL_WEBSERVICE_PASSWORD}" ]]; then
            printf 'webservice_user=%s\n' "${PMACTRL_WEBSERVICE_USER}"
            printf 'webservice_password=%s\n' "${PMACTRL_WEBSERVICE_PASSWORD}"
        fi
    } > "${PMACTRL_CREDENTIALS_FILE}"
    CREDENTIALS_WRITTEN=1
}

print_summary()
{
    log "installation finished"
    log "URL path: ${PMACTRL_WEBROOT}"
    log "database user: ${PMACTRL_DB_USER}"

    if [[ "${CREDENTIALS_WRITTEN}" == "1" ]]; then
        log "admin login: ${PMACTRL_ADMIN_LOGIN}"
        log "credentials file: ${PMACTRL_CREDENTIALS_FILE}"
    elif [[ "${PMACTRL_DRY_RUN}" == "1" ]]; then
        log "admin login: ${PMACTRL_ADMIN_LOGIN}"
        log "credentials file: ${PMACTRL_CREDENTIALS_FILE} (dry-run, not written)"
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
    pmactrl_harden_mariadb_bind
    pmactrl_harden_rpcbind
    configure_php
    configure_apache
    install_composer_dependencies
    run_application_install
    install_cli_wrapper
    run_phpunit
    write_credentials_file
    run systemctl restart apache2
    print_summary
}

main "$@"
