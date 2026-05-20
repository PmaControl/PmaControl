# Installation Report: PmaControl on `10.68.68.78`

Date: `2026-04-02`  
Target host: `10.68.68.78`  
Hostname: `debian13-srv-136`  
OS: `Debian GNU/Linux 13 (trixie)`  

## Objective

Install PmaControl on `10.68.68.78` and make `/srv/www/pmacontrol/install/debian13.sh` work on:

- Debian 13
- MariaDB 11.8
- PHP 8.5

## Final Status

Installation completed successfully.

Working components:

- Apache: `active`
- MariaDB: `active`
- PHP-FPM `8.5`: `active`
- Cron: `active`
- HTTP entrypoint:
  - `http://10.68.68.78/pmacontrol/`

Observed HTTP response:

- `301 Moved Permanently`
- redirect target: `/pmacontrol/en/server/main`

This confirms Apache and the PmaControl front controller are both responding.

## Versions Installed

- Debian: `13.4`
- MariaDB: `11.8.6-MariaDB-0+deb13u1`
- PHP CLI: `8.5.4`
- Apache: `2.4.66`

## Changes Applied To `install/debian13.sh`

The Debian 13 installer was updated so it works on the target host.

### 1. Native MariaDB 11.8 on Debian 13

The script now installs MariaDB from the native Debian 13 repository:

- `mariadb-server`
- `mariadb-client`
- `mariadb-plugin-rocksdb`

This matches the target requirement and avoids an unnecessary external MariaDB repo.

### 2. PHP 8.5 via Sury on Debian 13

Debian 13 provides PHP 8.4 by default.  
To satisfy the requested PHP 8.5 requirement, the script now adds:

- `https://packages.sury.org/php`

and installs:

- `php8.5`
- `php8.5-cli`
- `php8.5-fpm`
- `libapache2-mod-php8.5`
- `php8.5-mysql`
- `php8.5-curl`
- `php8.5-ldap`
- `php8.5-mbstring`
- `php8.5-intl`
- `php8.5-gd`
- `php8.5-xml`
- `php8.5-gmp`

### 3. Full non-interactive package installation

The script now exports:

- `DEBIAN_FRONTEND=noninteractive`
- `UCF_FORCE_CONFOLD=1`
- `UCF_FORCE_CONFFNEW=1`
- `NEEDRESTART_MODE=a`

This was necessary because the first run got stuck during package configuration in a `ucf`-driven postinst path.

### 4. `/srv/www` creation before Apache rewrite

The script now ensures `/srv/www` exists before rewriting Apache paths.

### 5. `cron` added to base packages

`install.sh` expects `crontab`.  
The first run reported:

- `./install.sh: line 157: crontab: command not found`
- `./install.sh: line 162: crontab: command not found`

To prevent that on future runs, `cron` was added to the base packages in `install/debian13.sh`.

## Problems Encountered

### Problem 1. Missing `/etc/sysctl.conf`

The first run encountered a missing sysctl file path assumption.

Fix:

- explicitly create `/etc/sysctl.conf`
- then append `vm.swappiness=1` only if needed

### Problem 2. `software-properties-common` package mismatch

The original package list assumed an older Debian package pattern.

Fix:

- remove `software-properties-common`
- keep the smaller Debian 13-compatible package set

### Problem 3. Package installation blocked in `ucf`

The first real blocker was an interactive package configuration path while installing PHP packages.

Observed state:

- `apt-get install ... php8.5-*`
- `dpkg --configure --pending`
- `php8.5-curl.postinst`
- `ucf ... /etc/php/8.5/mods-available/curl.ini`

Fix:

- enforce non-interactive env in the script
- recover with `dpkg --configure -a`

### Problem 4. Broken unrelated package: `php8.4-litespeed`

The target host had an unrelated package state causing `dpkg` failures:

- `php8.4-litespeed`

Observed error:

- `php8.4-litespeed.service failed because a timeout was exceeded`
- package remained half-configured

Fix:

- disable the service
- purge `php8.4-litespeed`
- rerun:
  - `dpkg --configure -a`
  - `apt-get -f install -y`

This package is not required for PmaControl and was the main package-system blocker once Apache/PHP 8.5 was otherwise correct.

## Installation Sequence Executed

After the package-system cleanup:

1. install base packages
2. install MariaDB 11.8
3. install PHP 8.5 + Apache
4. configure Apache for `/srv/www`
5. clone `https://github.com/PmaControl/PmaControl.git`
6. run `composer install`
7. configure MySQL local access for PmaControl
8. generate `/tmp/config.json`
9. run `/srv/www/pmacontrol/install.sh -c /tmp/config.json`
10. restart Apache
11. install cron
12. verify services and HTTP

## Verification

Verified locally on the target host:

- `systemctl is-active mariadb` => `active`
- `systemctl is-active apache2` => `active`
- `systemctl is-active php8.5-fpm` => `active`
- `systemctl is-active cron` => `active`
- `php8.5 -v` => `8.5.4`
- `mysql -Nse "SELECT VERSION()"` => `11.8.6-MariaDB-0+deb13u1 from Debian`
- `curl -I http://127.0.0.1/pmacontrol/` => redirect to `/pmacontrol/en/server/main`
- `curl -I http://10.68.68.78/pmacontrol/` => same result

Generated configuration files detected under:

- `/srv/www/pmacontrol/configuration/`

including:

- `db.config.ini.php`
- `auth.config.php`
- `crypt.config.php`
- `pmacontrol.config.php`

## Generated Credentials

Installer output returned:

### MySQL account

- Login: `pmacontrol`
- Password: `ZDdlMmNiZGFlMmU3OTA5MDgxZDhhODgw`

### PmaControl SuperAdmin

- Login: `admin`
- Password: `ZDdlMmNiZGFlMmU3OTA5MDgxZDhhODgw`

## Recommendation

The current `install/debian13.sh` is now suitable for Debian 13 on hosts like `10.68.68.78`.

Recommended follow-up:

- keep `cron` in the installer permanently
- keep PHP 8.5 on Sury for Debian 13 until Debian ships the requested version natively
- document that a stray `php8.4-litespeed` package can break `dpkg` on reused hosts
- if this installer is reused on non-clean hosts, consider adding a preflight check for:
  - broken packages
  - half-configured PHP service packages

## Conclusion

PmaControl is installed and reachable on `10.68.68.78`.

Main technical adjustments required for Debian 13 were:

- MariaDB 11.8 from Debian native packages
- PHP 8.5 from Sury
- non-interactive package handling
- explicit `cron` dependency
- recovery from an unrelated broken `php8.4-litespeed` package on the target
