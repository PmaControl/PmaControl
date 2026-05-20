<?php

// === Configuration ===

// Fichier d’environnement à charger (ex: /root/.telegram_env)
$envFile = '/srv/www/pmacontrol/configuration/telegram.php';
$errorLog = '/srv/www/pmacontrol/tmp/log/error_php.log';  // chemin à adapter
$cacheFile = '/tmp/php_error_cache.json';
$cacheTtl  = 300; // 5 minutes
$telegramApi = 'https://api.telegram.org/bot';

// === Charger les variables Telegram ===
if (file_exists($envFile)) {

    include $envFile;
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

}
if (empty($TELEGRAM_TOKEN) || empty($TELEGRAM_CHAT_ID)) {
    fwrite(STDERR, "Erreur: TELEGRAM_TOKEN ou TELEGRAM_CHAT_ID manquant dans $envFile\n");
    exit(1);
}

if (!file_exists($logFile)) {
    // Peut créer un fichier vide si nécessaire
    touch($logFile);
}


// === Fonction d'envoi Telegram avec découpage si message trop long ===
function sendTelegram(string $text): void {
    global $TELEGRAM_TOKEN, $TELEGRAM_CHAT_ID, $telegramApi;

    $maxLen = 4000;
    $baseUrl = $telegramApi . $TELEGRAM_TOKEN . '/sendMessage';

    for ($offset = 0; $offset < strlen($text); $offset += $maxLen) {
        $chunk = substr($text, $offset, $maxLen);

        // Construction de la commande curl avec capture de sortie et code HTTP
        $cmd = sprintf(
            '/usr/bin/curl -s -X POST %s ' .
            "-d chat_id=%s -d text=%s",
            escapeshellarg($baseUrl),
            escapeshellarg($TELEGRAM_CHAT_ID),
            escapeshellarg($chunk)
        );

        echo $cmd."\n";

        // Exécution et récupération du résultat
        $output = shell_exec($cmd);
        if ($output === null) {
            echo "[!] Erreur : impossible d’exécuter curl.\n";
            continue;
        }

        // Séparation du corps JSON et du code HTTP
        if (preg_match('/(.*?) HTTP_CODE:(\d{3})$/s', $output, $m)) {
            $body = trim($m[1]);
            $httpCode = (int)$m[2];
        } else {
            $body = trim($output);
            $httpCode = 0;
        }

        // Vérification du retour JSON
        $ok = false;
        $errMsg = '';
        $json = json_decode($body, true);
        if (is_array($json)) {
            $ok = $json['ok'] ?? false;
            if (!$ok) $errMsg = $json['description'] ?? 'Erreur inconnue';
        }

        // Affichage du résultat
        if ($httpCode === 200 && $ok) {
            echo "[Telegram ✅] Message envoyé avec succès (" . strlen($chunk) . " caractères)\n";
        } else {
            echo "[Telegram ❌] Erreur HTTP $httpCode";
            if ($errMsg) echo " : $errMsg";
            echo "\nRéponse brute : $body\n";
        }

        usleep(500000); // pause 0.5s entre messages
    }
}

sendTelegram("Init $errorLog");

// === Charger ou initialiser le cache ===
$cache = [];
if (file_exists($cacheFile)) {
    $json = file_get_contents($cacheFile);
    $cache = json_decode($json, true) ?: [];
}

// === Fonction pour purger le cache expiré ===
function purgeCache(array &$cache, int $ttl): void {
    $now = time();
    foreach ($cache as $key => $ts) {
        if ($now - $ts > $ttl) unset($cache[$key]);
    }
}

$lastOffset = 0;
if (file_exists($errorLog)) {
    $lastOffset = filesize($errorLog); // commencer à la fin du fichier existant
}



// === Surveillance du fichier avec inotifywait ===
echo "Surveillance de $errorLog...\n";

$cmd = sprintf("inotifywait -m -e modify %s", escapeshellarg($errorLog));
$proc = popen($cmd, 'r');
if (!$proc) {
    fwrite(STDERR, "Impossible de lancer inotifywait.\n");
    exit(1);
}


while (($line = fgets($proc)) !== false) {
    if (!str_contains($line, 'MODIFY')) continue;
    sleep(1);

    // Ouvrir le fichier et se placer à la dernière position
    $fp = fopen($errorLog, 'r');
    if (!$fp) continue;
    fseek($fp, $lastOffset);

    $newErrors = [];
    while (($errLine = fgets($fp)) !== false) {
        if (preg_match('/\] (PHP (Warning|Fatal|Parse|Notice|Error): .+)/', $errLine, $m)) {
            $errType = $m[1]; // sans date
            $newErrors[$errLine] = $errType;
        }
    }

    $lastOffset = ftell($fp); // mettre à jour la position
    fclose($fp);

    // Filtrer uniquement les erreurs nouvelles
    purgeCache($cache, $cacheTtl);
    $unique = [];
    $now = time();
    foreach ($newErrors as $full => $key) {
        if (!isset($cache[$key])) {
            $cache[$key] = $now;
            $unique[] = $full;
        }
    }

    if ($unique) {
        $msg = implode("\n", $unique);
        sendTelegram($msg);
        echo date('[Y-m-d H:i:s] ') . count($unique) . " nouvelle(s) erreur(s) envoyée(s).\n";
    }

    file_put_contents($cacheFile, json_encode($cache));
}

pclose($proc);

