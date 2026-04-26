#!/bin/bash
set -euo pipefail

DEV_MOD=0
VERSION_MARIADB="11.8"
VERSION_PHP="8.5"
GIT_BRANCH="commercial"

generate_password()
{
    local secret=""

    if command -v openssl >/dev/null 2>&1; then
        secret="$(openssl rand -hex 16 2>/dev/null || true)"
    fi

    if [[ -z "${secret}" ]]; then
        secret="$(od -An -N16 -tx1 /dev/urandom | tr -d ' \n')"
    fi

    printf '%s\n' "${secret}"
}

password="$(generate_password)"
pwd_pmacontrol="$(generate_password)"
pwd_admin="$(generate_password)"

while getopts 'hp:v:dP:' flag; do
  case "${flag}" in
    h)
        echo "options:"
        echo "-d                      development mode, we may ask you questions"
        echo "-p                      specify password for PmaControl admin"
        echo "-v                      specify version of MariaDB"
        echo "-P                      specify version of PHP"
        exit 0
    ;;
    p) pwd_admin="${OPTARG}" ;;
    d) DEV_MOD="1" ;;
    v) VERSION_MARIADB="${OPTARG}" ;;
    P) VERSION_PHP="${OPTARG}" ;;
    *) echo "Unexpected option ${flag}"; exit 1 ;;
  esac
done

export DEBIAN_FRONTEND=noninteractive
export UCF_FORCE_CONFOLD=1
export UCF_FORCE_CONFFNEW=1
export NEEDRESTART_MODE=a

get_os_codename()
{
    if [[ -r /etc/os-release ]]; then
        # shellcheck disable=SC1091
        . /etc/os-release
        if [[ -n "${VERSION_CODENAME:-}" ]]; then
            echo "${VERSION_CODENAME}"
            return 0
        fi
    fi

    lsb_release -sc
}

require_root()
{
    if [[ "${EUID}" -ne 0 ]]; then
        echo "This script must be run as root."
        exit 1
    fi
}

install_base_packages()
{
    apt-get update
    apt-get -y upgrade
    apt-get install -y \
        apt-transport-https \
        ca-certificates \
        curl \
        wget \
        gnupg \
        gnupg2 \
        lsb-release \
        zip \
        unzip \
        bc \
        git \
        tig \
        net-tools \
        dnsutils \
        cron \
        sysbench \
        skopeo \
        jq \
        sudo
}

install_php_sury()
{
    local distro_codename
    distro_codename=$(get_os_codename)

    install -d -m 0755 /etc/apt/keyrings
    curl -fsSL https://packages.sury.org/php/apt.gpg -o /etc/apt/keyrings/php-sury.gpg
    chmod 0644 /etc/apt/keyrings/php-sury.gpg

    cat > /etc/apt/sources.list.d/php-sury.list <<EOF
deb [signed-by=/etc/apt/keyrings/php-sury.gpg] https://packages.sury.org/php/ ${distro_codename} main
EOF

    apt-get update
}

install_mariadb_repository()
{
    local repo_setup_script
    repo_setup_script=$(mktemp)

    if ! curl -fsSL https://r.mariadb.com/downloads/mariadb_repo_setup -o "${repo_setup_script}"; then
        rm -f "${repo_setup_script}"
        echo "Unable to download MariaDB repository setup script."
        exit 1
    fi

    if ! bash "${repo_setup_script}" --mariadb-server-version="mariadb-${VERSION_MARIADB}"; then
        rm -f "${repo_setup_script}"
        echo "Unable to configure MariaDB ${VERSION_MARIADB} repository."
        exit 1
    fi

    rm -f "${repo_setup_script}"
    apt-get update
}

resolve_mariadb_package_version()
{
    apt-cache madison mariadb-server \
        | awk -v requested="${VERSION_MARIADB}" '
            BEGIN {
                gsub(/\./, "\\.", requested)
                pattern = "(^|:)" requested "([.-]|$)"
            }
            $3 ~ pattern { print $3; exit }
        '
}

install_mariadb()
{
    local mariadb_package_version
    local installed_mariadb_version

    install_mariadb_repository
    mariadb_package_version=$(resolve_mariadb_package_version)

    if [[ -z "${mariadb_package_version}" ]]; then
        echo "MariaDB ${VERSION_MARIADB} is not available in the configured APT repositories."
        echo "Configure a repository that provides MariaDB ${VERSION_MARIADB} or choose an available version with -v."
        exit 1
    fi

    apt-get install -y \
        "mariadb-server=${mariadb_package_version}" \
        "mariadb-client=${mariadb_package_version}" \
        "mariadb-plugin-rocksdb=${mariadb_package_version}"

    if command -v mariadb >/dev/null 2>&1; then
        installed_mariadb_version=$(mariadb --version)
    else
        installed_mariadb_version=$(mysql --version)
    fi

    if [[ "${installed_mariadb_version}" != *"${VERSION_MARIADB}"* ]]; then
        echo "Installed MariaDB version does not match requested version ${VERSION_MARIADB}: ${installed_mariadb_version}"
        exit 1
    fi

    systemctl enable mariadb
    systemctl restart mariadb
}

install_php()
{
    local php_version="$1"

    install_php_sury

    apt-get install -y \
        apache2 \
        graphviz \
        libcairo2 \
        composer \
        "php${php_version}" \
        "php${php_version}-mysql" \
        "php${php_version}-ldap" \
        "php${php_version}-curl" \
        "php${php_version}-cli" \
        "php${php_version}-mbstring" \
        "php${php_version}-intl" \
        "php${php_version}-fpm" \
        "libapache2-mod-php${php_version}" \
        "php${php_version}-gd" \
        "php${php_version}-xml" \
        "php${php_version}-gmp" \
        php-json

    a2enmod proxy_fcgi setenvif rewrite
    a2enconf "php${php_version}-fpm"

    sed -i 's#;date.timezone =#date.timezone = Europe/Paris#g' "/etc/php/${php_version}/fpm/php.ini"
    sed -i 's#;date.timezone =#date.timezone = Europe/Paris#g' "/etc/php/${php_version}/apache2/php.ini"
    sed -i 's#;date.timezone =#date.timezone = Europe/Paris#g' "/etc/php/${php_version}/cli/php.ini"
}

configure_apache()
{
    mkdir -p /srv/www
    sed -i 's#/var/www#/srv/www#g' /etc/apache2/apache2.conf
    sed -i 's#/var/www/html#/srv/www#g' /etc/apache2/sites-enabled/000-default.conf
    awk '/AllowOverride/ && ++i==3 {sub(/None/,"All")}1' /etc/apache2/apache2.conf > /tmp/apache2.conf.pmacontrol
    mv /tmp/apache2.conf.pmacontrol /etc/apache2/apache2.conf
    systemctl restart apache2
}

clone_repo()
{
    mkdir -p /srv/www
    cd /srv/www

    if [[ -d /srv/www/pmacontrol ]]; then
        rm -rf /srv/www/pmacontrol
    fi

    if [[ $DEV_MOD -eq 1 ]]; then
        set +e
        ssh -T git@github.com >/dev/null 2>&1
        ret=$?
        set -e

        if [[ $ret -eq 1 ]]; then
            git clone --branch "${GIT_BRANCH}" --single-branch git@github.com:PmaControl/PmaControl.git pmacontrol
        else
            git clone --branch "${GIT_BRANCH}" --single-branch https://github.com/PmaControl/PmaControl.git pmacontrol
        fi
    else
        git clone --branch "${GIT_BRANCH}" --single-branch https://github.com/PmaControl/PmaControl.git pmacontrol
    fi

    chown -R www-data:www-data /srv/www/pmacontrol
    chown -R www-data:www-data /var/www || true
}

install_php_dependencies()
{
    cd /srv/www/pmacontrol

    if [[ $DEV_MOD -eq 1 ]]; then
        git config core.fileMode false
    fi

    sudo -u www-data composer install --no-interaction
}

configure_mysql()
{
    mysql -e "INSTALL SONAME 'ha_rocksdb';" || true
    mysql -e "GRANT ALL ON *.* TO pmacontrol@'127.0.0.1' IDENTIFIED BY '${pwd_pmacontrol}' WITH GRANT OPTION;"
}

write_install_config()
{
    cat > /tmp/config.json <<EOF
{
  "mysql": {
    "ip": "127.0.0.1",
    "port": 3306,
    "user": "pmacontrol",
    "password": "${pwd_pmacontrol}",
    "database": "pmacontrol"
  },
  "organization": [
    "68Koncept"
  ],
  "webroot": "/pmacontrol/",
  "ldap": {
    "enabled": false,
    "url": "pmacontrol.68koncept.com",
    "port": 389,
    "bind dn": "CN=pmacontrol-auth,OU=Utilisateurs,OU=No_delegation,DC=intra,DC=pmacontrol",
    "bind passwd": "secret_password",
    "user base": "OU=pmacontrol.com,DC=intra,DC=pmacontrol",
    "group base": "OU=pmacontrol.com,DC=intra,DC=pmacontrol",
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
        "email": "nicolas.dupont@france.com",
        "firstname": "Nicolas",
        "lastname": "DUPONT",
        "country": "France",
        "city": "Paris",
        "login": "admin",
        "password": "${pwd_admin}"
      }
    ]
  },
  "webservice": [{
    "user": "webservice",
    "host": "%",
    "password": "QDRWSHGqdrtwhqetrHthTH",
    "organization": "68Koncept"
  }],
  "ssh": [{
    "user": "pmacontrol",
    "private key": "-----BEGIN RSA PRIVATE KEY-----\nMIIJKQIBAAKCAgEAsLxsW/pqk8VkCh/eUuhXusDLyG72sWz7uJk6Y1V/3lQRXbCX\n8orlGSlpcBwtMnVOAMUdul4/NQ9swDJqfSYMx5+s4hgswiDwqliwNmu8KGP7gseq\ntpB1apOsIGKby8KVkqwpmxyFs4W+dKwcxmPlw+1b5w5aro6keIbcomKAFNqq1nzR\nARBfL+AUEEZKjkK1o3vfzEhYL8nO+zpMzv2TMcbTumw+jjHC+DzKtUILBo/LjjkC\nwyWKva6QArS125itvIMT5pUW6X72RgWByKIUzCJrR+HzWO9zl8FQQeRlZjtCp+9C\n7HwMPiKH4upN2FfwWXSEa+NyYFUuNyjOCdbrRpgX0FfChE4XFklSNhMXdKMu\n-----END RSA PRIVATE KEY-----\n",
    "public key": "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQCwvGxb+mqTxWQKH95S6Fe6wMvIbvaxbPu4mTpjVX/eVBFdsJfyiuUZKWlwHC0ydU4AxR26Xj81D2zAMmp9JgzHn6ziGCzCIPCqWLA2a7woY/uCx6q2kHVqk6wgYpvLwpWSrCmbHIWzhb50rBzGY+XD7VvnDlqujqR4htyiYoAU2qrWfNEs5NseGEcQaiRMHe57lw2UTXGbj3Ked+h+n/XngRLV4D01DzaQZ8k45dREe32rUmJZJ3hvE3FI57ICEnVtnrQ8+lQrAoYP0jnYT7eXcIvjHDgyMXKc7fEAyp3b2QG+4J/HxL6K+elFJErLQ2yQlDR9afadnTsBJxFBA2/6yx42Lrp0pMprxKOvhSiMKNiDrP73Jt7d8Z5Z89YN+414Vo2M9713O54IB5H2r88qtdY4fuLzK4d4V39vz6ii5H2aEXIJVsbafLCn/qzbjp7IpoqvuB/3Smp2XW2RnWcZB1NY6diTQkS3MKpblDJILv5UtKN9RCyhRmRHFIM5RyTN21Euuei5bX6WhvEsL7jGo6JDmnXi3tzdAeTUbhPgOd2lX4LECBg9wbhzsezN47S6IGf+72sD/6BCJewKCZ8iheM34pEewDJdUSrg06LDLOr1TrRfaoV1qSsWNDtJVrfae/NTo4oKggxNkkDFkfeHm1pBej37dbMqzDVsKcNoCw=="
  }]
}
EOF
}

run_pmacontrol_install()
{
    cd /srv/www/pmacontrol
    chmod +x install.sh
    ./install.sh -c /tmp/config.json
}

install_cli_wrapper()
{
    cd /srv/www/pmacontrol
    local pwd_repo
    pwd_repo=$(pwd)
    cp -a glial pmacontrol
    sed -i "s#php App/Webroot/index.php#php ${pwd_repo}/App/Webroot/index.php#g" pmacontrol
    mv pmacontrol /usr/local/bin/pmacontrol
}

print_credentials()
{
    echo "Save these credentials"
    echo "#########################################################"
    echo "# Account MySQL"
    echo "Login : pmacontrol"
    echo "Password : ${pwd_pmacontrol}"
    echo "#########################################################"
    echo "# Account SuperAdmin on PmaControl"
    echo "Login : admin"
    echo "Password : ${pwd_admin}"
    echo "#########################################################"
}

main()
{
    require_root

    sysctl vm.swappiness=1
    touch /etc/sysctl.conf
    grep -qxF "vm.swappiness=1" /etc/sysctl.conf || echo "vm.swappiness=1" >> /etc/sysctl.conf

    install_base_packages
    install_mariadb
    install_php "${VERSION_PHP}"
    configure_apache
    clone_repo
    install_php_dependencies
    configure_mysql
    write_install_config
    run_pmacontrol_install
    install_cli_wrapper
    systemctl restart apache2
    print_credentials
}

main "$@"
