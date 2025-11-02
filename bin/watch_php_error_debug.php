#!/usr/bin/php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// === CONFIGURATION ===
$envFile      = '/srv/www/pmacontrol/configuration/telegram.php';
$logDir       = '/srv/www/pmacontrol/tmp/log';
$cacheFile    = '/tmp/php_error_cache.json';
$cacheTtl     = 300; // 5 minutes
$telegramApi  = 'https://api.telegram.org/bot';
$excludeFiles = ['glial.log', 'sql.log']; // à ignorer

echo "[DEBUG] Chargement configuration...\n";

// === Charger Telegram ===
if (file_exists($envFile)) {
    echo "[DEBUG] Chargement du fichier d’environnement : $envFile\n";
    include $envFile;
} else {
    echo "[ERREUR] Fichier d’environnement $envFile introuvable !\n";
}

if (empty($TELEGRAM_TOKEN) || empty($TELEGRAM_CHAT_ID)) {
    fwrite(STDERR, "[ERREUR] TELEGRAM_TOKEN ou TELEGRAM_CHAT_ID manquant dans $envFile\n");
    exit(1);
}

echo "[DEBUG] Token et Chat ID chargés correctement.\n";

// === Envoi Telegram ===
function sendTelegram(string $text): void {
    global $TELEGRAM_TOKEN, $TELEGRAM_CHAT_ID, $telegramApi;

    echo "[DEBUG] Envoi Telegram (" . strlen($text) . " caractères)...\n";

    $maxLen  = 4000;
    $baseUrl = $telegramApi . $TELEGRAM_TOKEN . '/sendMessage';

    for ($offset = 0; $offset < strlen($text); $offset += $maxLen) {
        $chunk = substr($text, $offset, $maxLen);

        $cmd = sprintf(
            '/usr/bin/curl -s -X POST %s -d chat_id=%s -d text=%s',
            escapeshellarg($baseUrl),
            escapeshellarg($TELEGRAM_CHAT_ID),
            escapeshellarg($chunk)
        );

        echo "[DEBUG] Commande CURL : $cmd\n";
        $output = shell_exec($cmd);

        if ($output === null) {
            echo "[ERREUR] Impossible d’exécuter curl.\n";
            continue;
        }

        $json = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "[WARN] JSON mal formé : " . json_last_error_msg() . "\n";
        }

        echo "[DEBUG] Réponse Telegram brute : $output\n";
    }
}

// === Cache (déduplication) ===
$cache = [];
if (file_exists($cacheFile)) {
    echo "[DEBUG] Chargement du cache depuis $cacheFile\n";
    $cache = json_decode(file_get_contents($cacheFile), true) ?: [];
} else {
    echo "[DEBUG] Aucun cache trouvé, initialisation vide.\n";
}

function purgeCache(array &$cache, int $ttl): void {
    echo "[DEBUG] Purge du cache (TTL=$ttl)...\n";
    $countBefore = count($cache);
    $now = time();
    foreach ($cache as $k => $ts) {
        if ($now - $ts > $ttl) {
            unset($cache[$k]);
            echo "[DEBUG] Clé expirée : $k\n";
        }
    }
    echo "[DEBUG] Cache nettoyé (" . $countBefore . " → " . count($cache) . ")\n";
}

// === Lister les fichiers à surveiller au démarrage ===
function listLogFiles(string $logDir, array $exclude): array {
    echo "[DEBUG] Scan du dossier : $logDir\n";
    $files = [];
    foreach (glob($logDir . '/*.log') as $f) {
        $base = basename($f);
        if (in_array($base, $exclude, true)) {
            echo "[DEBUG] Ignoré : $base\n";
            continue;
        }
        $size = filesize($f);
        $files[$f] = $size;
        echo "[DEBUG] Fichier à surveiller : $f (taille initiale: $size)\n";
    }
    return $files;
}

$logFiles = listLogFiles($logDir, $excludeFiles);

if (empty($logFiles)) {
    fwrite(STDERR, "[WARN] Aucun fichier .log à surveiller dans $logDir (hors exclusions)\n");
}

sendTelegram("👀 Surveillance démarrée sur: $logDir (exclusions: " . implode(', ', $excludeFiles) . ")");
echo "[DEBUG] Démarrage de la surveillance via inotifywait...\n";

// === Lancer inotifywait sur le répertoire ===
$cmd = sprintf("inotifywait -m -e modify --format '%%w %%e %%f' %s", escapeshellarg($logDir));
echo "[DEBUG] Commande exécutée : $cmd\n";
$proc = popen($cmd, 'r');
if (!$proc) {
    fwrite(STDERR, "[ERREUR] Impossible de lancer inotifywait. Vérifie qu'il est installé.\n");
    exit(1);
}

while (($line = fgets($proc)) !== false) {
    $line = trim($line);
    echo "[DEBUG] Événement reçu : $line\n";

    if ($line === '') continue;

    $parts = explode(' ', $line, 3);
    if (count($parts) < 3) {
        echo "[WARN] Ligne inattendue : $line\n";
        continue;
    }

    [$watchedPath, $event, $filename] = $parts;
    $filename = trim($filename);
    $event = trim($event);
    $fullpath = rtrim($watchedPath, '/') . '/' . $filename;

    echo "[DEBUG] Fichier modifié : $fullpath (événement: $event)\n";

    if (in_array($filename, $excludeFiles, true)) {
        echo "[DEBUG] Ignoré (fichier exclu) : $filename\n";
        continue;
    }

    if (!isset($logFiles[$fullpath])) {
        $logFiles[$fullpath] = file_exists($fullpath) ? filesize($fullpath) : 0;
        echo "[INFO] Nouveau fichier détecté : $fullpath\n";
    }

    usleep(300000);

    $fp = @fopen($fullpath, 'r');
    if (!$fp) {
        echo "[ERREUR] Impossible d’ouvrir $fullpath\n";
        continue;
    }

    $lastOffset = $logFiles[$fullpath] ?? 0;
    echo "[DEBUG] Lecture depuis offset : $lastOffset\n";
    fseek($fp, $lastOffset);

    $newErrors = [];
    while (($errLine = fgets($fp)) !== false) {
       if (preg_match('/PHP\s+(Warning|Fatal\s+error|Parse\s+error|Notice|Error):\s+(.*)$/i', $errLine, $m)) {    
            $errText = $m[1];
            echo "[DEBUG] Ligne d’erreur détectée : $errText\n";
            $newErrors[] = [
                'full' => $errLine,
                'key'  => $errText,
            ];
        }
    }

    $newOffset = ftell($fp);
    echo "[DEBUG] Nouvel offset de $fullpath : $newOffset\n";
    $logFiles[$fullpath] = $newOffset;
    fclose($fp);

    if (empty($newErrors)) {
        echo "[DEBUG] Aucune nouvelle erreur trouvée.\n";
        continue;
    }

    purgeCache($cache, $cacheTtl);
    $now = time();
    $toSend = [];

    foreach ($newErrors as $err) {
        $hash = md5($fullpath . '|' . $err['key']);
        if (!isset($cache[$hash])) {
            $cache[$hash] = $now;
            $toSend[] = $err['full'];
            echo "[DEBUG] Nouvelle erreur unique : $err[key]\n";
        } else {
            echo "[DEBUG] Erreur déjà connue (ignorée) : $err[key]\n";
        }
    }

    if (!empty($toSend)) {
        $msg = "📄 Fichier: " . basename($fullpath) . "\n"
             . "Chemin: $fullpath\n\n"
             . implode("", $toSend);
        echo "[DEBUG] Envoi de " . count($toSend) . " erreur(s) à Telegram.\n";
        sendTelegram($msg);
    } else {
        echo "[DEBUG] Rien à envoyer (tout déjà vu).\n";
    }

    file_put_contents($cacheFile, json_encode($cache));
    echo "[DEBUG] Cache sauvegardé dans $cacheFile (" . count($cache) . " entrées)\n";
}

pclose($proc);
echo "[DEBUG] Fin du processus principal.\n";
