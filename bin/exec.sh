#!/bin/bash

# Vérification du paramètre
if [ -z "$1" ]; then
    echo "Usage: $0 \"REQUETE_SQL\""
    exit 1
fi

SQL_QUERY="$1"

# Configuration de la connexion
MYSQL_HOST="127.0.0.1"
MYSQL_USER="codex"
MYSQL_PASS="codex"
MYSQL_DB="pmacontrol"

# Exécution de la requête
mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_DB" -e "$SQL_QUERY"
