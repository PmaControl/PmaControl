#!/usr/bin/php
<?php
// === CONFIGURATION ===
$envFile     = '/srv/www/pmacontrol/configuration/telegram.php';
$logFile     = '/srv/www/pmacontrol/tmp/log/sql.log';
$cacheFile   = '/tmp/sql_error_cache.json';
$cacheTtl    = 300; // 5 min
$telegramApi = 'https://api.telegram.org/bot';

// === Charger variables Telegram ===
if (file_exists($envFile)) {
    include $envFile;
}
if (empty($TELEGRAM_TOKEN) || empty($TELEGRAM_CHAT_ID)) {
    fwrite(STDERR, "Erreur: TELEGRAM_TOKEN ou TELEGRAM_CHAT_ID manquant dans $envFile\n");
    exit(1);
}

// === Fonction d'envoi Telegram ===
function sendTelegram(string $text): void {
    global $TELEGRAM_TOKEN, $TELEGRAM_CHAT_ID, $telegramApi;
    $maxLen = 4000;
    $baseUrl = $telegramApi . $TELEGRAM_TOKEN . '/sendMessage';
    for ($offset = 0; $offset < strlen($text); $offset += $maxLen) {
        $chunk = substr($text, $offset, $maxLen);
        $cmd = sprintf(
            '/usr/bin/curl -s -X POST %s -d chat_id=%s -d parse_mode=MarkdownV2 -d text=%s',
            escapeshellarg($baseUrl),
            escapeshellarg($TELEGRAM_CHAT_ID),
            escapeshellarg($chunk)
        );
        shell_exec($cmd);
        usleep(500000);
    }
}

// === Gestion du cache fichier:ligne ===
$cache = [];
if (file_exists($cacheFile)) {
    $cache = json_decode(file_get_contents($cacheFile), true) ?: [];
}
function purgeCache(array &$cache, int $ttl): void {
    $now = time();
    foreach ($cache as $key => $ts) {
        if ($now - $ts > $ttl) unset($cache[$key]);
    }
}

// === Initialisation ===
echo "Surveillance de $logFile ...\n";
$cmd = sprintf("inotifywait -m -e modify %s", escapeshellarg($logFile));
$proc = popen($cmd, 'r');
if (!$proc) {
    fwrite(STDERR, "Impossible de lancer inotifywait.\n");
    exit(1);
}
$lastOffset = filesize($logFile);

// === Boucle principale ===
while (($line = fgets($proc)) !== false) {
    if (!str_contains($line, 'MODIFY')) continue;
    sleep(1);

    $fp = fopen($logFile, 'r');
    if (!$fp) continue;
    fseek($fp, $lastOffset);
    $buffer = stream_get_contents($fp);
    $lastOffset = ftell($fp);
    fclose($fp);

    purgeCache($cache, $cacheTtl);
    $now = time();

    // === Détection des blocs d’erreur ===
    if (preg_match_all(
        '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s*\[ERROR\]\s*\n(.*?)\n(\/srv\/www\/glial\/Glial\/Sgbd\/Sql\/Sql\.php:\d+)\s*\n(UPDATE|INSERT|DELETE|SELECT)[\s\S]+?(?=\n\[|\z)/m',
        $buffer,
        $matches,
        PREG_SET_ORDER
    )) {
        foreach ($matches as $m) {
            $datetime = trim($m[1]);
            $message  = trim($m[2]);
            $fileline = trim($m[3]);
            $sqlStart = strpos($buffer, $m[4]);
            $sql = '';
            if ($sqlStart !== false) {
                $sql = substr($buffer, $sqlStart, 500);
            }

            $key = "$fileline|$message";
            if (!isset($cache[$key])) {
                $cache[$key] = $now;

                // Message Telegram propre
                $msg  = "*Erreur SQL détectée*\n";
                $msg .= "`$datetime`\n";
                $msg .= "📄 `$fileline`\n";
                $msg .= "❗ $message\n";
                if ($sql) {
                    $msg .= "\n*Requête SQL (abrégée)*:\n```\n" . trim($sql) . "\n```";
                }

                sendTelegram($msg);
                echo date('[Y-m-d H:i:s] ') . "Erreur envoyée ($fileline)\n";
            }
        }
    }

    file_put_contents($cacheFile, json_encode($cache));
}
pclose($proc);
