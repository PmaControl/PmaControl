#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=install/lib/harden_apache.sh
. "${SCRIPT_DIR}/lib/harden_apache.sh"
# shellcheck source=install/lib/install_secrets.sh
. "${SCRIPT_DIR}/lib/install_secrets.sh" || exit 1
trap cleanup_install_ssh_key EXIT
trap 'cleanup_install_ssh_key; exit 129' HUP
trap 'cleanup_install_ssh_key; exit 130' INT
trap 'cleanup_install_ssh_key; exit 143' TERM

password=$(date +%s | sha256sum | base64 | head -c 32 ; echo)
PMACTRL_HARDEN_APACHE_DOCROOT="${PMACTRL_HARDEN_APACHE_DOCROOT:-1}"

apt-get update
apt-get -y upgrade
apt-get -y install lsb-release
apt-get -y install zip unzip
apt-get -y install curl
apt-get -y install bc
apt-get -y install wget
apt install -y gnupg
apt-get install -y wget gnupg2 lsb-release
apt install -y git 
apt install -y tig
apt install -y curl
apt-get install -y net-tools
apt install -y dnsutils
apt install -y skopeo
apt install -y jq openssh-client

cd /tmp
git clone https://github.com/PmaControl/Toolkit.git

cd Toolkit
chmod +x install-mariadb.sh


curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | bash -s -- --mariadb-server-version="mariadb-10.11"


./install-mariadb.sh -v 10.11 -p $password -d /srv/mysql -r


apt-get -y install php8.1 apache2 graphviz  php8.1-ldap php-json php8.1-curl php8.1-cli php8.1-mbstring php8.1-intl php8.1-fpm libapache2-mod-php8.1 php8.1-xml 
apt -y install php8.1-mysql php8.1-gd php8.1-gmp php8.1-zip

apt-get -y install phpi8.1-ldap
apt-get -y install mariadb-plugin-rocksdb 

apt-get -y install logrotate

cat > /etc/logrotate.d/pmacontrol << EOF
{
    /srv/www/pmacontrol/tmp/log/glial.log {
            daily
            missingok
            rotate 7
            notifempty
            create 0640 www-data www-data
            sharedscripts
    }
    /srv/www/pmacontrol/tmp/log/sql.log {
            daily
            missingok
            rotate 7
            notifempty
            create 0640 www-data www-data
            sharedscripts
    }
    /srv/www/pmacontrol/tmp/log/error_php.log {
            daily
            missingok
            rotate 7
            notifempty
            create 0640 www-data www-data
            sharedscripts
    }
}

EOF




#apt-get install beanstalkd

service mysql restart

mysql -e  "INSTALL SONAME 'ha_rocksdb'"


a2enmod proxy_fcgi setenvif
a2enconf php8.1-fpm

a2enmod rewrite


sed -i 's/\/var\/www/\/srv\/www/g' /etc/apache2/apache2.conf

sed -i 's/\/var\/www\/html/\/srv\/www/g' /etc/apache2/sites-enabled/000-default.conf

awk '/AllowOverride/ && ++i==3 {sub(/None/,"All")}1' /etc/apache2/apache2.conf > /tmp/xfgh && mv /tmp/xfgh /etc/apache2/apache2.conf
pmactrl_harden_apache_docroot

mkdir -p /srv/www/
cd /srv/www/

#curl -sS https://getcomposer.org/installer | php --
#mv composer.phar /usr/local/bin/composer

apt-get install -y composer

cd /srv/www/


ssh -T git@github.com
ret=$(echo $?)

if [[ $ret -eq 1 ]]; then
  git clone git@github.com:PmaControl/PmaControl.git pmacontrol
else
  git clone https://github.com/PmaControl/PmaControl.git pmacontrol
fi

cd pmacontrol

git pull origin develop
git config core.fileMode false


#export COMPOSER_ALLOW_SUPERUSER=1
#composer install -n

service apache2 restart


pwd_pmacontrol=$(date +%s | sha256sum | base64 | head -c 32 ; echo)
sleep 1
pwd_admin=$(date +%s | sha256sum | base64 | head -c 32 ; echo)


mysql -e "GRANT ALL ON *.* TO pmacontrol@'127.0.0.1' IDENTIFIED BY '${pwd_pmacontrol}' WITH GRANT OPTION;"

generate_install_ssh_key
ssh_private_key_json=$(json_escape_file "${SSH_PRIVATE_KEY_FILE}")
ssh_public_key_json=$(json_escape_file "${SSH_PUBLIC_KEY_FILE}")


cat > /tmp/config.json << EOF
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
  }]
,
  "ssh": [{
    "user": "pmacontrol",
    "private key": ${ssh_private_key_json},
    "public key": ${ssh_public_key_json}
  }]
}

EOF


chmod +x install.sh
./install.sh -c /tmp/config.json

echo "Save these credentials"
echo "#########################################################"
echo "# Account MySQL
echo "Login : pmacontrol"
echo "Password : ${pwd_pmacontrol}"
echo "#########################################################"
echo "# Account SuperAdmin on PmaControl
echo "Login : admin"
echo "Password : ${pwd_admin}"
echo "#########################################################"



PWD=$(pwd)
cp -a glial pmacontrol
sed "s#php App/Webroot/index.php#php ${PWD}/App/Webroot/index.php#g" -i pmacontrol
mv pmacontrol /usr/local/bin/pmacontrol
