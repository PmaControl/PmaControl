#!/bin/bash



while getopts 'hc:d' flag; do
  case "${flag}" in
    h) 
        echo "auto install PmaControl"
        echo "example : ./install -c config_sample/config.sample.json"
        echo " "
        echo "options:"
        echo "-h                      print this help"
        echo "-c config.json          set config file"
        echo "-d                      mode debug"
        exit 0
    ;;
    c) CONFIG_FILE="${OPTARG}";;
    d) DEBUG=true ;;
    *) echo "Unexpected option ${flag}" 
	exit 0
    ;;
  esac
done


#check OSTYPE

case "$OSTYPE" in
  solaris*) OS="SOLARIS" ;;
  darwin*)  OS="OSX" ;; 
  linux*)   OS="LINUX" ;;
  bsd*)     OS="BSD" ;;
  msys*)    OS="WINDOWS" ;;
  *)        OS="unknown: $OSTYPE" ;;
esac

if [ "$OS" != "LINUX" ]
then
    echo "This OS is not supported : $OS"
    exit 1;
else
    echo "This OS is supported : $OS"
fi


#Check distribution GNU / Linux

dist=`egrep '^ID=' /etc/os-release | awk -F '=' '{print $2}'i | sed 's/"//g'`

DIOK="OK"
case "$dist" in
  Ubuntu* | Debian*) DISTRIB="Debian" ;;
  redhat* | centos* | rhel*) DISTRIB="RedHat" ;;
  *)       
        DISTRIB=$dist
        DIOK="KO"
 ;;
esac

if [ "$DIOK" != "OK" ]
then
    dist=`lsb_release -is`

    DIOK="OK"

    case "$dist" in
      Ubuntu* | Debian*) DISTRIB="Debian" ;;
      Redhat* | CentOS*) DISTRIB="RedHat" ;;
      *)       
            DISTRIB=$dist
            DIOK="KO"
     ;;
    esac
fi

if [ "$DIOK" != "OK" ]
then
    echo "This distribution GNU/Linux is not supported : $DISTRIB"
    exit 1;
else
    echo "This distribution GNU/Linux is supported : $DISTRIB"
fi

#check version supported
# TODO


workFolder=$(readlink -f $(dirname $0))
path=`echo $workFolder | awk -F"/" ' { print $(NF) } '`

cp -a config_sample/*.config.php configuration/
cp -a config_sample/*.ini.php configuration/
cp -a config_sample/*.ini configuration/

cat > configuration/db.config.ini.php << EOF
[noname]
gg=fake_data
gt=just the time to generate a good one
EOF

cat > configuration/db.config.php << EOF
<?php
 if (! defined('DB_DEFAULT'))
 {
     define("DB_DEFAULT", "pmacontrol");
 }
EOF

cat >  configuration/webroot.config.php << EOF
<?php

/*
 * if you use a direrct DNS set : define('WWW_ROOT', "/");
 * if you dev in local or other use : define('WWW_ROOT', "/path_to_the_final_directory/");
 * example : http://127.0.0.1/directory/myapplication/ => define('WWW_ROOT', "/directory/myapplication/");
 * Don't forget the final "/"
 */


if (! defined('WWW_ROOT'))
{
    define('WWW_ROOT', "/$path/");
}
EOF

#echo 'vm.swappiness = 1' >> /etc/sysctl.conf
#sysctl -p
#cat /proc/sys/vm/swappiness
# add pmacontrol user and crontab

case "$dist" in
      Debian*) user="www-data" ;;
      Redhat* | rhel*) user="apache" ;;
      *)       user="www-data" ;;
esac

# Define correct right on tmp and data
chown -R $user: tmp/
chown -R $user: data/

find tmp/ -type d -exec chmod 0755 {} \;
find tmp/ -type f -exec chmod 0644 {} \;
find data/ -type d -exec chmod 0755 {} \;
find data/ -type f -exec chmod 0644 {} \;

# install crontab for user apache

#write out current crontab
echo '# crontab for pmacontrol' > mycron
#echo new cron into cron file

pmacontrol_path=`pwd`

echo $pmacontrol_path 

echo "* * * * * cd $pmacontrol_path && ./glial agent check_daemon" >> mycron
echo "05 */4 * * * cd $pmacontrol_path && ./glial control service" >> mycron
#install new cron file
crontab -u $user mycron
rm mycron


#check composer && composer install

if test -f ./vendor/glial/glial/Glial/Bootstrap.php; then
   echo "Glial Installed !"
else
    composer -V foo >/dev/null 2>&1 || { echo >&2 "PmaControl require composer but it's not installed.  Aborting."; echo "To install composer : ";echo ""; echo "        curl -sS https://getcomposer.org/installer | php";  echo "        \$ mv composer.phar /usr/local/bin/composer"; echo ""; exit 1;}
    composer install
    echo "Composer Installed !"
fi

if [ -f "$CONFIG_FILE" ]; then

    echo "install from config file"

	php App/Webroot/index.php install webroot $CONFIG_FILE
	if [ $? != 0 ]; then
	    exit 1
	fi

	php App/Webroot/index.php install index $CONFIG_FILE
	if [ $? != 0 ]; then
	    exit 2
	fi

	php App/Webroot/index.php install createOrganisation $CONFIG_FILE
	if [ $? != 0 ]; then
	    exit 3
	fi

	php App/Webroot/index.php install createAdmin $CONFIG_FILE
	if [ $? != 0 ]; then
	   exit 4
	fi

	php App/Webroot/index.php ldap updateFromInstall $CONFIG_FILE
	if [ $? != 0 ]; then
	   exit 5
	fi

	php App/Webroot/index.php webservice addAccount $CONFIG_FILE
	if [ $? != 0 ]; then
	   exit 6
	fi

	#php App/Webroot/index.php ssh add $CONFIG_FILE
	#if [ $? != 0 ]; then
	#   exit 7
	#fi

	#php App/Webroot/index.php ssh associate $CONFIG_FILE
	#if [ $? != 0 ]; then
	#   exit 7
	#fi

else
    echo "install not from config file"
    
	php App/Webroot/index.php install index
	if [ $? != 0 ]; then
	    exit 1
	fi

	php App/Webroot/index.php install createOrganisation
	if [ $? != 0 ]; then
	    exit 2
	fi

	php App/Webroot/index.php install createAdmin
	if [ $? != 0 ]; then
	   exit 3
	fi
fi

php App/Webroot/index.php control createTsTable

if [ $? != 0 ]; then
    exit 8
fi

php App/Webroot/index.php agent updateServerList

if [ $? != 0 ]; then
    exit 4
fi

php App/Webroot/index.php administration generate_model

if [ $? != 0 ]; then
    exit 5
fi

php App/Webroot/index.php administration admin_table

if [ $? != 0 ]; then
    exit 6
fi

mkdir -p vendor/esysteme/mysql-sys/gen
chown $user:$user -R vendor/esysteme/mysql-sys/gen

chown $user:$user -R .


if [ -f "$CONFIG_FILE" ]; then
        echo "Change chown and chmod for $CONFIG_FILE"
        chown root:root $CONFIG_FILE
        chmod 600 $CONFIG_FILE
fi




chmod 600 configuration/crypt.config.php
