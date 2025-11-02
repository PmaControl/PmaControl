#!/usr/bin/php
<?php
// === CONFIGURATION ===
$envFile   = '/srv/www/pmacontrol/configuration/telegram.php';
$logFile   = '/srv/www/pmacontrol/tmp/log/sql.log';
$cacheFile = '/tmp/sql_log_cache.json';
$cacheTtl  = 300; // 5 min
$telegramApi = 'https://api.telegram.org/bot';



// === Charger variables Telegram ===
if (file_exists($envFile)) include $envFile;
if (empty($TELEGRAM_TOKEN) || empty($TELEGRAM_CHAT_ID)) {
    fwrite(STDERR, "❌ TELEGRAM_TOKEN ou TELEGRAM_CHAT_ID manquant dans $envFile\n");
    exit(1);
}



function removeAnsiCodes(string $text): string {
    // Supprime toutes les séquences ANSI d'échappement
    return preg_replace('/\x1B\[[0-9;]*[A-Za-z]/', '', $text);
}

// === Fonction d’envoi Telegram ===
function sendTelegram(string $text): void {
    global $TELEGRAM_TOKEN, $TELEGRAM_CHAT_ID, $telegramApi;
    $text = preg_replace('/[[:cntrl:]\x1B\[[0-9;]*[A-Za-z]]/', '', $text); // nettoyage ASCII
    $text = substr($text, 0, 3900); // limite Telegram
    $url = $telegramApi . $TELEGRAM_TOKEN . '/sendMessage';
    $cmd = sprintf(
        '/usr/bin/curl -s -X POST %s -d chat_id=%s -d parse_mode="HTML" -d text=%s',
        escapeshellarg($url),
        escapeshellarg($TELEGRAM_CHAT_ID),
        escapeshellarg($text)
    );
    shell_exec($cmd);
}

// === Cache fichier:ligne ===
$cache = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) ?: [] : [];
function purgeCache(array &$cache, int $ttl): void {
    $now = time();
    foreach ($cache as $k => $t) {
        if ($now - $t > $ttl) unset($cache[$k]);
    }
}

// === Initialisation ===


sendTelegram("🚀 Surveillance active sur `/srv/www/pmacontrol/tmp/log/sql.log`");
purgeCache($cache, 0);
echo "🟢 Surveillance en cours...\n";

$cmd = sprintf("inotifywait -m -e modify %s", escapeshellarg($logFile));
$proc = popen($cmd, 'r');
if (!$proc) {
    fwrite(STDERR, "❌ Impossible de lancer inotifywait.\n");
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
    $newData = stream_get_contents($fp);
    $lastOffset = ftell($fp);
    fclose($fp);

    if (trim($newData) === '') continue;

    $newData = removeAnsiCodes($newData);

    echo "🟡 Nouveau contenu détecté (" . strlen($newData) . " octets)\n";

    
    // Séparer en blocs (2 retours à la ligne ou plus)
    $blocks = preg_split("/\n{2,}/", trim($newData));

    print_r($blocks);


    purgeCache($cache, $cacheTtl);
    $now = time();

    foreach ($blocks as $block) {

        $lines = explode("\n", $block);

        if (count($lines) < 4) continue; // bloc incomplet

        $dateLine   = $lines[0];
        $errorLine  = $lines[1]; // "ERROR: ..."
        $fileLine   = $lines[2]; // "/srv/.../Sql.php:283"
        $sqlLines   = array_slice($lines, 3);
        $sql        = implode("\n", $sqlLines);

        $idx = crc32($fileLine);

        if (isset($cache[$idx])) {
            echo "⏭️  Ignoré (déjà vu) : [$idx] {$fileLine}\n";
            continue;
        }
        $cache[$idx] = $now;

        // Construire le message
        $msg = 
               "$dateLine\n"
             . "<b>$errorLine</b>\n"
             . "📄 <i>$fileLine</i>\n"
             . " \n"
             . substr($sql, 0, 3000);

        sendTelegram($msg);
        echo "📤 Envoi Telegram pour $idx\n";
    }

    file_put_contents($cacheFile, json_encode($cache));
}
pclose($proc);
