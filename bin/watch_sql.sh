#!/bin/bash

# --- CONFIG ---
WATCH_FILE="/tmp/input.sql"          # fichier SQL à surveiller
OUTPUT_FILE="/tmp/output.txt"        # fichier où poster le résultat

MYSQL_USER="codex"
MYSQL_PASS="codex"
MYSQL_DB="pmacontrol"

# TCP mode
MYSQL_HOST="127.0.0.1"
MYSQL_PORT="3306"

# Socket mode
MYSQL_SOCKET="/var/run/mysqld/mysqld.sock"
USE_SOCKET=0   # 1 = utiliser socket local, 0 = TCP
# --------------


echo "[INFO] Watching file: $WATCH_FILE"
echo "[INFO] Output file:   $OUTPUT_FILE"
echo "[INFO] Waiting for modifications..."

# Boucle infinie avec détection d'événements
while true; do
    # Attend un changement sur le fichier
    inotifywait -e close_write "$WATCH_FILE" >/dev/null 2>&1

    echo "[INFO] Change detected at $(date '+%Y-%m-%d %H:%M:%S')"

    # Met "working..." dans le fichier output
    echo "working..." > "$OUTPUT_FILE"

    # Récupère le contenu SQL
    SQL=$(cat "$WATCH_FILE")

    # Exécute la requête
    if [ "$USE_SOCKET" -eq 1 ]; then
        RESULT=$(mysql \
            --socket="$MYSQL_SOCKET" \
            -u"$MYSQL_USER" -p"$MYSQL_PASS" \
            "$MYSQL_DB" -e "$SQL" 2>&1)
    else
        RESULT=$(mysql \
            -h"$MYSQL_HOST" -P"$MYSQL_PORT" \
            -u"$MYSQL_USER" -p"$MYSQL_PASS" \
            "$MYSQL_DB" -e "$SQL" 2>&1)
    fi

    # Remplace "working..." par le résultat
    echo "$RESULT" > "$OUTPUT_FILE"
done
