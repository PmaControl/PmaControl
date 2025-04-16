#!/bin/bash
#
# Script : monitor_mysql.sh
# Objectif : Redémarrer MySQL s'il est arrêté et vérifier dans les logs si mysqld a été tué par l'OOM killer.
#
# Attention : Ce script doit être exécuté avec les privilèges root.
#
# Vous pouvez programmer son exécution régulière via cron ou systemd.
#

# Nom du service MSQL (ajustez si besoin : par exemple "mysqld" ou "mariadb")
SERVICE="mariadb"

# Fichier de log propre au script (optionnel)
MONITOR_LOG="/var/log/mysql_monitor.log"

# Vérifier si le service est actif
if ! systemctl is-active --quiet "$SERVICE"; then
    echo "$(date): Le service $SERVICE n'est pas actif. Tentative de redémarrage." | tee -a "$MONITOR_LOG"
    
    #drop du cache system
    sync; echo 3 | sudo tee /proc/sys/vm/drop_caches
    systemctl restart "$SERVICE"
    #service "$SERVICE" start
    #sleep 5
    if systemctl is-active --quiet "$SERVICE"; then
        echo "$(date): Redémarrage réussi de $SERVICE." | tee -a "$MONITOR_LOG"
        swapoff -a && swapon -a

	# Rechercher dans le log une trace indiquant que mysqld a été tué par l'OOM killer
	# On recherche une ligne qui mentionne "killed process" suivi de "mysqld" ou "mysql", ou "Out of memory"
	    dmesg -T | egrep -i 'killed process' | tee -a "$MONITOR_LOG"
    else
        echo "$(date): Échec du redémarrage de $SERVICE. Veuillez vérifier manuellement !" | tee -a "$MONITOR_LOG"
    fi
fi


# https://www.managedserver.eu/Improve-mysql-and-mariadb-performance-with-memory-allocators-like-jemalloc-and-tcmalloc/#1_Frammentazione_della_memoria
# <= change malloc by jemalloc ?