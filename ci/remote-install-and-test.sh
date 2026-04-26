#!/usr/bin/env bash
set -Eeuo pipefail

export DEBIAN_FRONTEND=noninteractive
export UCF_FORCE_CONFOLD=1
export UCF_FORCE_CONFFNEW=1
export NEEDRESTART_MODE=a
export COMPOSER_ALLOW_SUPERUSER=1

TARGET_OS="${TARGET_OS:-unknown}"
LOG_FILE="/root/pmacontrol-ci.log"
exec > >(tee -a "${LOG_FILE}") 2>&1

if [[ ! -d /srv/www/pmacontrol ]]; then
    echo "Missing /srv/www/pmacontrol" >&2
    exit 1
fi

# shellcheck disable=SC1091
. /etc/os-release
OS_KEY="${ID}-${VERSION_ID}"

install_php_repo_debian13() {
    install -d -m 0755 /etc/apt/keyrings
    curl -fsSL https://packages.sury.org/php/apt.gpg -o /etc/apt/keyrings/php-sury.gpg
    chmod 0644 /etc/apt/keyrings/php-sury.gpg
    cat > /etc/apt/sources.list.d/php-sury.list <<EOF
deb [signed-by=/etc/apt/keyrings/php-sury.gpg] https://packages.sury.org/php/ trixie main
EOF
}

case "${OS_KEY}" in
    debian-12)
        PHP_VERSION="8.2"
        ;;
    debian-13)
        PHP_VERSION="8.5"
        install_php_repo_debian13
        ;;
    ubuntu-24.04)
        PHP_VERSION="8.3"
        ;;
    ubuntu-26.04)
        PHP_VERSION=""
        ;;
    *)
        echo "Unsupported guest OS: ${OS_KEY}" >&2
        exit 1
        ;;
esac

if [[ "${OS_KEY}" == "ubuntu-26.04" ]]; then
    cd /srv/www/pmacontrol
    PMACTRL_INSTALL_DIR=/srv/www/pmacontrol \
    PMACTRL_GIT_BRANCH="${GITHUB_REF_NAME:-commercial}" \
    PMACTRL_FORCE_REINSTALL=0 \
    PMACTRL_RUN_PHPUNIT=0 \
    bash install/ubuntu26.04.sh

    HTTP_CODE="$(curl -s -o /tmp/pmacontrol-home.html -w '%{http_code}' http://127.0.0.1/pmacontrol/ || true)"
    case "${HTTP_CODE}" in
        200|301|302)
            ;;
        *)
            echo "Unexpected HTTP status from local install: ${HTTP_CODE}" >&2
            exit 1
            ;;
    esac

    ./vendor/bin/phpunit --testsuite "PmaControl Test Suite"
    echo "CI install success on ${OS_KEY} for commit ${GIT_COMMIT:-unknown}"
    exit 0
fi

apt-get update
apt-get install -y \
    apache2 \
    bc \
    ca-certificates \
    composer \
    cron \
    curl \
    dnsutils \
    git \
    graphviz \
    jq \
    libcairo2 \
    lsb-release \
    mariadb-client \
    mariadb-plugin-rocksdb \
    mariadb-server \
    net-tools \
    rsync \
    sudo \
    unzip \
    wget \
    zip

apt-get install -y \
    "php${PHP_VERSION}" \
    "php${PHP_VERSION}-cli" \
    "php${PHP_VERSION}-curl" \
    "php${PHP_VERSION}-fpm" \
    "php${PHP_VERSION}-gd" \
    "php${PHP_VERSION}-gmp" \
    "php${PHP_VERSION}-intl" \
    "php${PHP_VERSION}-ldap" \
    "php${PHP_VERSION}-mbstring" \
    "php${PHP_VERSION}-mysql" \
    "php${PHP_VERSION}-xml" \
    "libapache2-mod-php${PHP_VERSION}"

if command -v update-alternatives >/dev/null 2>&1 && [[ -x "/usr/bin/php${PHP_VERSION}" ]]; then
    update-alternatives --set php "/usr/bin/php${PHP_VERSION}" || true
    update-alternatives --set phar "/usr/bin/phar${PHP_VERSION}" || true
    update-alternatives --set phar.phar "/usr/bin/phar.phar${PHP_VERSION}" || true
fi

systemctl enable --now mariadb
systemctl enable --now apache2
systemctl enable --now "php${PHP_VERSION}-fpm"

if ! mysql -NBe "SHOW ENGINES" | awk '$1 == "ROCKSDB" && ($2 == "YES" || $2 == "DEFAULT") {found=1} END {exit !found}'; then
    mysql -e "INSTALL SONAME 'ha_rocksdb';" || true
fi
if ! mysql -NBe "SHOW ENGINES" | awk '$1 == "ROCKSDB" && ($2 == "YES" || $2 == "DEFAULT") {found=1} END {exit !found}'; then
    echo "ROCKSDB engine is not available after installing mariadb-plugin-rocksdb" >&2
    exit 1
fi

a2enmod proxy_fcgi setenvif rewrite
a2enconf "php${PHP_VERSION}-fpm"

for sapi in apache2 cli fpm; do
    ini="/etc/php/${PHP_VERSION}/${sapi}/php.ini"
    if [[ -f "${ini}" ]]; then
        sed -i 's#;date.timezone =#date.timezone = Europe/Paris#g' "${ini}"
    fi
done

sed -i 's#/var/www#/srv/www#g' /etc/apache2/apache2.conf
sed -i 's#/var/www/html#/srv/www#g' /etc/apache2/sites-enabled/000-default.conf
awk '/AllowOverride/ && ++i==3 {sub(/None/,"All")}1' /etc/apache2/apache2.conf > /tmp/apache2.conf.pmacontrol
mv /tmp/apache2.conf.pmacontrol /etc/apache2/apache2.conf
systemctl restart "php${PHP_VERSION}-fpm"
systemctl restart apache2

mkdir -p /srv/www
chown -R www-data:www-data /srv/www/pmacontrol

PMA_DB_PASS="$(date +%s | sha256sum | base64 | head -c 24)"
ADMIN_PASS="$(date +%s | sha256sum | base64 | head -c 24)"

mysql <<EOF
GRANT ALL ON *.* TO pmacontrol@'127.0.0.1' IDENTIFIED BY '${PMA_DB_PASS}' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF

cat > /tmp/pmacontrol-ci-config.json <<EOF
{
  "mysql": {
    "ip": "127.0.0.1",
    "port": 3306,
    "user": "pmacontrol",
    "password": "${PMA_DB_PASS}",
    "database": "pmacontrol"
  },
  "organization": ["CI"],
  "webroot": "/pmacontrol/",
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
        "email": "ci@example.com",
        "firstname": "CI",
        "lastname": "Runner",
        "country": "France",
        "city": "Paris",
        "login": "admin",
        "password": "${ADMIN_PASS}"
      }
    ]
  },
  "webservice": [{
    "user": "webservice",
    "host": "%",
    "password": "ci-webservice-password",
    "organization": "CI"
  }],
  "ssh": [{
    "user": "pmacontrol",
    "private key": "-----BEGIN RSA PRIVATE KEY-----\\nCI\\n-----END RSA PRIVATE KEY-----\\n",
    "public key": "ssh-rsa CI ci@runner"
  }]
}
EOF

cd /srv/www/pmacontrol
./install.sh -c /tmp/pmacontrol-ci-config.json

HTTP_CODE="$(curl -s -o /tmp/pmacontrol-home.html -w '%{http_code}' http://127.0.0.1/pmacontrol/ || true)"
case "${HTTP_CODE}" in
    200|301|302)
        ;;
    *)
        echo "Unexpected HTTP status from local install: ${HTTP_CODE}" >&2
        exit 1
        ;;
esac

./vendor/bin/phpunit --testsuite "PmaControl Test Suite"

echo "CI install success on ${OS_KEY} for commit ${GIT_COMMIT:-unknown}"
