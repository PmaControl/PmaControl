#!/bin/bash

password=$(date +%s | sha256sum | base64 | head -c 32 ; echo)

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

cd /tmp
git clone https://github.com/PmaControl/Toolkit.git

cd Toolkit
chmod +x install-mariadb.sh


curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | bash -s -- --mariadb-server-version="mariadb-10.11"


./install-mariadb.sh -v 10.11 -p $password -d /srv/mysql -r


apt-get -y install php8.1 apache2 graphviz  php8.1-ldap php-json php8.1-curl php8.1-cli php8.1-mbstring php8.1-intl php8.1-fpm libapache2-mod-php8.1 php8.1-xml 
apt -y install php8.1-mysql php8.1-gd php8.1-gmp


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
    "private key": "-----BEGIN RSA PRIVATE KEY-----\nMIIJKQIBAAKCAgEAsLxsW/pqk8VkCh/eUuhXusDLyG72sWz7uJk6Y1V/3lQRXbCX\n8orlGSlpcBwtMnVOAMUdul4/NQ9swDJqfSYMx5+s4hgswiDwqliwNmu8KGP7gseq\ntpB1apOsIGKby8KVkqwpmxyFs4W+dKwcxmPlw+1b5w5aro6keIbcomKAFNqq1nzR\nARBfL+AUEEZKjkK1o3vfzEhYL8nO+zpMzv2TMcbTumw+jjHC+DzKtUILBo/LjjkC\nwyWKva6QArS125itvIMT5pUW6X72RgWByKIUzCJrR+HzWO9zl8FQQeRlZjtCp+9C\n7HwMPiKH4upN2FfwWXSEa+NyYFUuNyjOCdbrRpgX0FfChE4XFklSNhMXdKMu\n-----END RSA PRIVATE KEY-----\n",
    "public key": "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQCwvGxb+mqTxWQKH95S6Fe6wMvIbvaxbPu4mTpjVX/eVBFdsJfyiuUZKWlwHC0ydU4AxR26Xj81D2zAMmp9JgzHn6ziGCzCIPCqWLA2a7woY/uCx6q2kHVqk6wgYpvLwpWSrCmbHIWzhb50rBzGY+XD7VvnDlqujqR4htyiYoAU2qrWfNEs5NseGEcQaiRMHe57lw2UTXGbj3Ked+h+n/XngRLV4D01DzaQZ8k45dREe32rUmJZJ3hvE3FI57ICEnVtnrQ8+lQrAoYP0jnYT7eXcIvjHDgyMXKc7fEAyp3b2QG+4J/HxL6K+elFJErLQ2yQlDR9afadnTsBJxFBA2/6yx42Lrp0pMprxKOvhSiMKNiDrP73Jt7d8Z5Z89YN+414Vo2M9713O54IB5H2r88qtdY4fuLzK4d4V39vz6ii5H2aEXIJVsbafLCn/qzbjp7IpoqvuB/3Smp2XW2RnWcZB1NY6diTQkS3MKpblDJILv5UtKN9RCyhRmRHFIM5RyTN21Euuei5bX6WhvEsL7jGo6JDmnXi3tzdAeTUbhPgOd2lX4LECBg9wbhzsezN47S6IGf+72sD/6BCJewKCZ8iheM34pEewDJdUSrg06LDLOr1TrRfaoV1qSsWNDtJVrfae/NTo4oKggxNkkDFkfeHm1pBej37dbMqzDVsKcNoCw=="
  }]
}

EOF



./install -c /tmp/config.json

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
