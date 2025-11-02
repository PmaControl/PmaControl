#!/bin/bash
# === Configuration ===
SCRIPT_PATH="/srv/www/pmacontrol/bin/watch_php_error.php"
CHECK_INTERVAL=5    # secondes entre vérifications
PROCESS_NAME="watch_php_error.php"

# === Fonction : redémarrer le script ===
restart_script() {
    echo "[INFO] Nouveau code détecté, redémarrage..."
    
    # Tuer le processus existant
    pkill -f "$PROCESS_NAME" 2>/dev/null
    
    # Attendre un peu pour éviter les collisions
    sleep 2
    
    # Redémarrer le nouveau code
    nohup php "$SCRIPT_PATH" >/tmp/php_error_watcher.log 2>&1 &
    
    echo "[OK] Nouveau processus lancé (PID: $!)"
}

# === Boucle de surveillance ===
if [ ! -f "$SCRIPT_PATH" ]; then
    echo "[ERREUR] Fichier $SCRIPT_PATH introuvable"
    exit 1
fi

# Date initiale
last_mtime=$(stat -c %Y "$SCRIPT_PATH")

echo "[INFO] Surveillance de $SCRIPT_PATH (mtime: $last_mtime)"

# S’assurer qu’un seul processus tourne
if pgrep -f "$PROCESS_NAME" >/dev/null; then
    echo "[INFO] Processus déjà actif"
else
    echo "[INFO] Aucun processus actif, lancement initial..."
    nohup php "$SCRIPT_PATH" >/tmp/php_error_watcher.log 2>&1 &
fi

while true; do
    sleep "$CHECK_INTERVAL"
    
    if [ ! -f "$SCRIPT_PATH" ]; then
        echo "[WARN] Fichier disparu, on continue d’attendre..."
        continue
    fi

    current_mtime=$(stat -c %Y "$SCRIPT_PATH")

    if [ "$current_mtime" -ne "$last_mtime" ]; then
        echo "[CHANGE] Fichier modifié ($(date)), redémarrage..."
        last_mtime=$current_mtime
        restart_script
    fi
done
