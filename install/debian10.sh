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
apt-get install -y jq openssh-client

cd /tmp
git clone https://github.com/PmaControl/Toolkit.git

cd Toolkit
chmod +x install-mariadb.sh


curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | bash -s -- --mariadb-server-version="mariadb-10.6"


./install-mariadb.sh -v 10.6 -p $password -d /srv/mysql -r


apt-get -y install php7.3 apache2 graphviz php7.3-mysql php7.3-ldap php7.3-json php7.3-curl php7.3-cli php7.3-mbstring php7.3-intl php7.3-fpm libapache2-mod-php7.3 php7.3-gd php7.3-xml

apt-get -y install mariadb-plugin-rocksdb 



#apt-get install beanstalkd

service mysql restart

mysql -e  "INSTALL SONAME 'ha_rocksdb'"


a2enmod proxy_fcgi setenvif
a2enconf php7.3-fpm

a2enmod rewrite


sed -i 's/\/var\/www/\/srv\/www/g' /etc/apache2/apache2.conf

sed -i 's/\/var\/www\/html/\/srv\/www/g' /etc/apache2/sites-enabled/000-default.conf

awk '/AllowOverride/ && ++i==3 {sub(/None/,"All")}1' /etc/apache2/apache2.conf > /tmp/xfgh && mv /tmp/xfgh /etc/apache2/apache2.conf
pmactrl_harden_apache_docroot

mkdir -p /srv/www/
cd /srv/www/

curl -sS https://getcomposer.org/installer | php --
mv composer.phar /usr/local/bin/composer

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

composer install -n

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
