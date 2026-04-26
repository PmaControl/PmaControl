#!/bin/bash
set -euo pipefail

DEV_MOD=0
VERSION_MARIADB="11.8"
VERSION_PHP="8.5"
GIT_BRANCH="commercial"
INSTALL_CONFIG_FILE=""
SSH_KEY_DIR=""
SSH_PRIVATE_KEY_FILE=""
SSH_PUBLIC_KEY_FILE=""
RESET_EXISTING_CHECKOUT=0
FORCE_REINSTALL=0

generate_password()
{
    local generated=""

    if command -v openssl >/dev/null 2>&1; then
        generated=$(openssl rand -base64 48 | tr -d '/+=' | tr -d '\n' | head -c 32 || true)
    fi

    if [[ ${#generated} -lt 32 ]]; then
        generated=$(LC_ALL=C tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 32 || true)
    fi

    if [[ ${#generated} -lt 32 ]]; then
        echo "Unable to generate a secure password." >&2
        exit 1
    fi

    printf '%s\n' "${generated}"
}

cleanup_install_ssh_key()
{
    if [[ -n "${SSH_KEY_DIR}" && -d "${SSH_KEY_DIR}" ]]; then
        rm -rf "${SSH_KEY_DIR}"
    fi
}

json_escape_string()
{
    printf '%s' "$1" | jq -Rs .
}

json_escape_file()
{
    jq -Rs . < "$1"
}

generate_install_ssh_key()
{
    local hostname_value

    cleanup_install_ssh_key
    SSH_KEY_DIR=$(mktemp -d /tmp/pmacontrol-install-ssh.XXXXXX)
    chmod 700 "${SSH_KEY_DIR}"
    SSH_PRIVATE_KEY_FILE="${SSH_KEY_DIR}/id_rsa"
    SSH_PUBLIC_KEY_FILE="${SSH_PRIVATE_KEY_FILE}.pub"
    hostname_value=$(hostname -f 2>/dev/null || hostname)

    ssh-keygen -q -t rsa -b 4096 -m PEM -N "" -C "pmacontrol@${hostname_value}" -f "${SSH_PRIVATE_KEY_FILE}"
    chmod 600 "${SSH_PRIVATE_KEY_FILE}"
    chmod 644 "${SSH_PUBLIC_KEY_FILE}"
}

pwd_pmacontrol=""
pwd_admin=""
pwd_webservice=""

while getopts 'hp:v:dP:rF' flag; do
  case "${flag}" in
    h)
        echo "options:"
        echo "-d                      development mode, we may ask you questions"
        echo "-p                      specify password for PmaControl admin"
        echo "-v                      specify version of MariaDB"
        echo "-P                      specify version of PHP"
        echo "-r                      reset an existing checkout to origin/${GIT_BRANCH}"
        echo "-F                      force destructive reinstall of /srv/www/pmacontrol"
        exit 0
    ;;
    p) pwd_admin="${OPTARG}" ;;
    d) DEV_MOD="1" ;;
    v) VERSION_MARIADB="${OPTARG}" ;;
    P) VERSION_PHP="${OPTARG}" ;;
    r) RESET_EXISTING_CHECKOUT="1" ;;
    F) FORCE_REINSTALL="1" ;;
    *) echo "Unexpected option ${flag}"; exit 1 ;;
  esac
done

pwd_pmacontrol=$(generate_password)
if [[ -z "${pwd_admin}" ]]; then
    pwd_admin=$(generate_password)
fi
pwd_webservice=$(generate_password)

export DEBIAN_FRONTEND=noninteractive
export UCF_FORCE_CONFOLD=1
export UCF_FORCE_CONFFNEW=1
export NEEDRESTART_MODE=a

cleanup_install_config()
{
    if [[ -n "${INSTALL_CONFIG_FILE}" && -f "${INSTALL_CONFIG_FILE}" ]]; then
        rm -f "${INSTALL_CONFIG_FILE}"
    fi
}

cleanup_install_artifacts()
{
    cleanup_install_config
    cleanup_install_ssh_key
}

trap cleanup_install_artifacts EXIT

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
        sudo \
        openssh-client
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

get_repository_url()
{
    if [[ $DEV_MOD -eq 1 ]]; then
        set +e
        ssh -T git@github.com >/dev/null 2>&1
        ret=$?
        set -e

        if [[ $ret -eq 1 ]]; then
            echo "git@github.com:PmaControl/PmaControl.git"
            return 0
        fi
    fi

    echo "https://github.com/PmaControl/PmaControl.git"
}

clone_repo()
{
    mkdir -p /srv/www
    local repo_dir="/srv/www/pmacontrol"
    local repo_url
    repo_url=$(get_repository_url)

    if [[ -e "${repo_dir}" && $FORCE_REINSTALL -eq 1 ]]; then
        rm -rf "${repo_dir}"
    fi

    if [[ -e "${repo_dir}" && ! -d "${repo_dir}/.git" ]]; then
        echo "${repo_dir} already exists but is not a git checkout."
        echo "Move it away or rerun with -F for a destructive reinstall."
        exit 1
    fi

    if [[ -d "${repo_dir}/.git" ]]; then
        cd "${repo_dir}"
        git fetch origin "${GIT_BRANCH}"

        if [[ $RESET_EXISTING_CHECKOUT -eq 1 ]]; then
            git checkout -B "${GIT_BRANCH}" "origin/${GIT_BRANCH}"
            git reset --hard "origin/${GIT_BRANCH}"
        else
            git checkout "${GIT_BRANCH}" || git checkout -b "${GIT_BRANCH}" "origin/${GIT_BRANCH}"
            if ! git merge --ff-only "origin/${GIT_BRANCH}"; then
                echo "Existing checkout has local changes or divergent commits."
                echo "Resolve them manually or rerun with -r to reset tracked files."
                exit 1
            fi
        fi
    else
        cd /srv/www
        git clone --branch "${GIT_BRANCH}" --single-branch "${repo_url}" pmacontrol
    fi

    chown -R www-data:www-data "${repo_dir}"
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
    INSTALL_CONFIG_FILE=$(mktemp /tmp/pmacontrol-install-config.XXXXXX)
    chmod 600 "${INSTALL_CONFIG_FILE}"

    local empty_json
    local mysql_password_json
    local ssh_private_key_json
    local ssh_public_key_json
    local admin_password_json
    local webservice_password_json

    generate_install_ssh_key
    empty_json=$(json_escape_string "")
    mysql_password_json=$(json_escape_string "${pwd_pmacontrol}")
    ssh_private_key_json=$(json_escape_file "${SSH_PRIVATE_KEY_FILE}")
    ssh_public_key_json=$(json_escape_file "${SSH_PUBLIC_KEY_FILE}")
    admin_password_json=$(json_escape_string "${pwd_admin}")
    webservice_password_json=$(json_escape_string "${pwd_webservice}")

    cat > "${INSTALL_CONFIG_FILE}" <<EOF
{
  "mysql": {
    "ip": "127.0.0.1",
    "port": 3306,
    "user": "pmacontrol",
    "password": ${mysql_password_json},
    "database": "pmacontrol"
  },
  "organization": [
    "68Koncept"
  ],
  "webroot": "/pmacontrol/",
  "ldap": {
    "enabled": false,
    "url": "",
    "port": 389,
    "bind dn": "",
    "bind passwd": ${empty_json},
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
        "email": "nicolas.dupont@france.com",
        "firstname": "Nicolas",
        "lastname": "DUPONT",
        "country": "France",
        "city": "Paris",
        "login": "admin",
        "password": ${admin_password_json}
      }
    ]
  },
  "webservice": [{
    "user": "webservice",
    "host": "%",
    "password": ${webservice_password_json},
    "organization": "68Koncept"
  }],
  "ssh": [{
    "user": "pmacontrol",
    "private key": ${ssh_private_key_json},
    "public key": ${ssh_public_key_json}
  }]
}
EOF
}

run_pmacontrol_install()
{
    cd /srv/www/pmacontrol
    chmod +x install.sh
    if [[ -z "${INSTALL_CONFIG_FILE}" || ! -f "${INSTALL_CONFIG_FILE}" ]]; then
        echo "Install config file is missing."
        exit 1
    fi
    ./install.sh -c "${INSTALL_CONFIG_FILE}"
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
    echo "# Account Webservice on PmaControl"
    echo "Login : webservice"
    echo "Password : ${pwd_webservice}"
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
