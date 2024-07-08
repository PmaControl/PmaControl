!/bin/bash

password=$(date +%s | sha256sum | base64 | head -c 32 ; echo)

dnf -y update
dnf -y upgrade
dnf -y install zip unzip
dnf -y install curl
dnf -y install bc
dnf -y install wget
dnf -y install gnupg
dnf -y install  git 
dnf -y install net-tools
dnf -y install bind-utils

#dnf -y install epel-release
#dnf -y install sysbench


cd /tmp
git clone https://github.com/Volodymyr7771/Toolkit2.git

cd Toolkit
chmod +x install-mariadb-v2.sh


curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | bash -s -- --mariadb-server-version="mariadb-10.11"

echo "password: $password"
./install-mariadb-v2.sh -v 10.11 -p $password -d /srv/mysql -r


dnf -y install httpd php graphviz php-cli php-fpm php-mysqlnd php-ldap php-json php-curl php-mbstring php-intl php-gd php-xml php-gmp 

dnf -y install MariaDB-rocksdb-engine

dnf -y install logrotate

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


#service mysql restart
systemctl start mysql
systemctl enable mysql
systemctl restart mysql

mysql -e  "INSTALL SONAME 'ha_rocksdb'"


#a2enmod proxy_fcgi setenvif
#a2enconf php8.2-fpm
#a2enmod rewrite

dnf -y install httpd php php-fpm 

tee -a /etc/httpd/conf.modules.d/00-proxy_fcgi.conf <<EOL
LoadModule proxy_module modules/mod_proxy.so
LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so
LoadModule setenvif_module modules/mod_setenvif.so
EOL

tee -a /etc/httpd/conf.d/php-fpm.conf <<EOL
<FilesMatch \.php$>
    SetHandler "proxy:unix:/run/php-fpm/www.sock|fcgi://localhost"
</FilesMatch>
EOL

tee -a /etc/httpd/conf/httpd.conf <<EOL
# Enable rewrite module
LoadModule rewrite_module modules/mod_rewrite.so
EOL


tee -a /etc/httpd/conf/httpd.conf <<EOL
#VirtualHost
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "/srv/www"
    <Directory "/srv/www/pmacontrol">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOL

echo "Apache modules and PHP-FPM configuration applied successfully."


# Restart Apache again to apply the PHP-FPM configuration
systemctl restart httpd


#sed -i 's/\/var\/www/\/srv\/www/g' /etc/apache2/apache2.conf
#sed -i 's/\/var\/www/\/srv\/www/g' /etc/httpd/conf/httpd.conf

#sed -i 's/\/var\/www\/html/\/srv\/www/g' /etc/apache2/sites-enabled/000-default.conf
sed -i 's/\/var\/www\/html/\/srv\/www/g' /etc/httpd/conf/httpd.conf


# Change the default site configuration if applicable
sed -i 's/\/var\/www\/html/\/srv\/www/g' /etc/httpd/conf.d/welcome.conf


#awk '/AllowOverride/ && ++i==3 {sub(/None/,"All")}1' /etc/apache2/apache2.conf > /tmp/xfgh && mv /tmp/xfgh /etc/apache2/apache2.conf
awk '/AllowOverride/ && ++i==3 {sub(/None/,"All")}1' /etc/httpd/conf/httpd.conf > /tmp/xfgh && mv -f /tmp/xfgh /etc/httpd/conf/httpd.conf


mkdir -p /srv/www/
cd /srv/www/


#curl -sS https://getcomposer.org/installer | php --
#mv composer.phar /usr/local/bin/composer

dnf install -y composer

cd /srv/www/

ssh -T git@github.com
ret=$(echo $?)

if [[ $ret -eq 1 ]]; then
  #git clone git@github.com:PmaControl/PmaControl.git pmacontrol
  git clone git@github.com:Volodymyr7771/PmaControl.git pmacontrol
else
  #git clone https://github.com/PmaControl/PmaControl.git pmacontrol
  git clone https://github.com/Volodymyr7771/PmaControl.git pmacontrol
fi

cd pmacontrol

#git pull origin develop
git config core.fileMode false


#export COMPOSER_ALLOW_SUPERUSER=1
#composer install -n

#service apache2 restart
service httpd restart


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
    "private key": "-----BEGIN RSA PRIVATE KEY-----\nMIIJKQIBAAKCAgEAsLxsW/pqk8VkCh/eUuhXusDLyG72sWz7uJk6Y1V/3lQRXbCX\n8orlGSlpcBwtMnVOAMUdul4/NQ9swDJqfSYMx5+s4hgswi>
    "public key": "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQCwvGxb+mqTxWQKH95S6Fe6wMvIbvaxbPu4mTpjVX/eVBFdsJfyiuUZKWlwHC0ydU4AxR26Xj81D2zAMmp9JgzHn6ziGCzCIPCqWLA2a7woY/>
  }]
}

EOF

chmod +x install.sh

#cd /tmp

./install.sh -c /tmp/config.json

#cd /srv/www/pmacontrol
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

