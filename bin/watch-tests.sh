#!/bin/bash

# Variables Telegram
# === Chemin vers le fichier de configuration PHP ===
TELEGRAM_CONFIG="./configuration/telegram.php"

# === Extraction du token et du chat_id depuis le fichier PHP ===
if [ -f "$TELEGRAM_CONFIG" ]; then
    TELEGRAM_TOKEN=$(grep -oP '\$TELEGRAM_TOKEN\s*=\s*"\K[^"]+' "$TELEGRAM_CONFIG")
    TELEGRAM_CHAT_ID=$(grep -oP '\$TELEGRAM_CHAT_ID\s*=\s*"\K[^"]+' "$TELEGRAM_CONFIG")
else
    echo "❌ Fichier de configuration Telegram introuvable : $TELEGRAM_CONFIG"
    exit 1
fi


# Fonction pour envoyer un message Telegram
send_telegram() {
    local message="$1"
    curl -s -X POST "https://api.telegram.org/bot$TELEGRAM_TOKEN/sendMessage" \
        -d chat_id="$TELEGRAM_CHAT_ID" \
        -d text="$message"
}

# Répertoire à surveiller
WATCH_DIR="./App/ ./tests"

LAST_STATUS_FILE="/tmp/last_test_status.txt"

# Initialiser le fichier si pas existant
if [ ! -f "$LAST_STATUS_FILE" ]; then
    echo "UNKNOWN" > "$LAST_STATUS_FILE"
fi

# Boucle infinie pour surveiller les fichiers
inotifywait -m -r -e close_write --format '%w%f' $WATCH_DIR | while read FILE
do
    echo "Changement détecté dans $FILE, lancement des tests..."

    # Exécution des tests
    OUTPUT=$(./vendor/bin/phpunit --testdox 2>&1)
    echo "$OUTPUT"

    # Déterminer le statut actuel
    if echo "$OUTPUT" | grep -qE "FAILURES!|ERRORS!"; then
        CURRENT_STATUS="FAILURE"
    else
        CURRENT_STATUS="SUCCESS"
    fi

    # Lire le dernier statut
    LAST_STATUS=$(cat "$LAST_STATUS_FILE")

    # Comparer et envoyer message si nécessaire
    if [ "$CURRENT_STATUS" != "$LAST_STATUS" ]; then
        if [ "$CURRENT_STATUS" = "FAILURE" ]; then
            send_telegram "❌ Tests échoués dans $FILE :\n$(echo "$OUTPUT" | head -n 40)"
        else
            send_telegram "✅ Tests OK après modification dans $FILE"
        fi
    fi

    # Mettre à jour le dernier statut
    echo "$CURRENT_STATUS" > "$LAST_STATUS_FILE"
done


# Boucle infinie pour surveiller les fichiers
inotifywait -m -r -e close_write --format '%w%f' $WATCH_DIR | while read FILE
do
    echo "Changement détecté dans $FILE, lancement des tests..."
    
    # Exécution des tests
    OUTPUT=$(./vendor/bin/phpunit --testdox 2>&1)
    echo "$OUTPUT"

    # Si tests échouent
    if echo "$OUTPUT" | grep -qE "FAILURES!|ERRORS!"; then
        send_telegram "❌ Tests échoués dans $FILE :\n$(echo "$OUTPUT" | head -n 20)"
    else
        send_telegram "✅ Tests OK après modification dans $FILE"
    fi
done
