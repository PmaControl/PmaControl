<?php

/*
 * Test all configuration to be sure everything correctly installed
 * developement => true
 * production => false
 * default : true
 */

if (!defined('LDAP_URL')) {
    define("LDAP_URL", "ldap.68koncept.com");
}

if (!defined('LDAP_PORT')) {
    define("LDAP_PORT", 389);
}

//connexion
if (!defined('LDAP_BIND_DN')) {
    define("LDAP_BIND_DN", "CN=readonly,CN=Users,DC=pws,DC=com");
}

if (!defined('LDAP_BIND_PASSWD')) {
    define("LDAP_BIND_PASSWD", "password");
}


//used for user search
if (!defined('LDAP_ROOT_DN')) {
    define("LDAP_ROOT_DN", "OU=www.68koncept.com,DC=intra68K,DC=68K");
}


//used for group search
if (!defined('LDAP_ROOT_DN_SEARCH')) {
    define("LDAP_ROOT_DN_SEARCH", "OU=Utilisateurs,DC=intra68K,DC=68K");
}


//if ldap used
if (!defined('LDAP_CHECK')) {
    define("LDAP_CHECK", false);
}


/************************/

// in case we can find country and city in LDAP


if (!defined('LDAP_DEFAULT_COUNTRY')) {
    define("LDAP_DEFAULT_COUNTRY", '28');
}


if (!defined('LDAP_DEFAULT_CITY')) {
    define("LDAP_DEFAULT_CITY", '192348');
}




