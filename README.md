# PmaControl

![Alt text](https://avatars0.githubusercontent.com/u/34713040?s=200&v=4 "Logo")


_UI & CLI Tools for DBA (monitoring / backup / install / cleaner ...)_

This software is distributed under Free Software Lisense : GNU / GPL v3 (http://www.gnu.org/licenses/gpl-3.0.en.html)


**Distribution based on Linux. (don't work with windows!)**

* Automatic discovery and monitoring
* Monitoring : Master/Slave, Galera Cluster, HA Proxy
* Query analyzer
* Backup system (Xtrabackup / mysqldump / mydumper) with different storage area
* Manage array of servers (like it's was only one)
* Manage user
* Pluging : Cleaner

## Deployment

### Install server

have a look on : https://github.com/Esysteme/Debian/blob/master/ubuntu_server.bash


### Dependencies to install

The current branch targets **PHP 8.2 or newer** (matching the Composer constraint `php >8.2`).
Make sure the PHP runtime you install exposes the CLI and PHP-FPM/SAPI variant used by
your web server (Apache `mod_php`, PHP-FPM through Nginx, etc.).

**Required PHP extensions**

* `ext-curl`
* `ext-gd`
* `ext-mysqlnd`
* `ext-openssl`
* `ext-pcntl`
* `ext-posix`
* `ext-ssh2` (used for monitoring system and backup)

**System packages**

* MariaDB 10.11 (or compatible MySQL server)
* `graphviz` (builds replication graphs, including multi-master and Galera cluster)
* `curl` (used for fetching translations)
* A web server such as Apache 2.4 or Nginx, configured with PHP 8.2

> **Heads-up**
> * PHP 8.2 dropped the old `mcrypt` extension, so code paths relying on it have been
>   refactored to use OpenSSL; ensure you load `ext-openssl` instead of trying to install
>   legacy PHP 5 packages.
> * When running under Apache, enable `rewrite` and point the DocumentRoot to
>   `App/Webroot/`. The previous `a2enmod php5` guidance no longer applies.

in [mysqld] section

plugin-load=ha_rocksdb

### Install composer

* `$ curl -sS https://getcomposer.org/installer | php`
* `$ mv composer.phar /usr/local/bin/composer`


### Deploy this project

* `git clone https://github.com/PmaControl/PmaControl.git pmacontrol`


### Install dependencies
* `cd pmacontrol`
* `git config core.fileMode false`
* `composer install --no-interaction`

### Run automated tests (optional but recommended)

* `./vendor/bin/phpunit`

### Auto install

* ./install

## You are ready !


* go to http://127.0.0.1/pmacontrol/

## Screenshots

## To develop

`git config --global --add merge.ff false`
