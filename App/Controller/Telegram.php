<?php

namespace App\Controller;

use App\Library\Debug;
use Glial\I18n\I18n;
use Glial\Synapse\Controller;
use Glial\Sgbd\Sgbd;

class Telegram extends Controller
{
    public function index($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id, token, chat_id, insert_at, updated_at
            FROM telegram_bot ORDER BY insert_at DESC";
        $res = $db->sql_query($sql);

        $data['bots'] = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['bots'][] = $row;
        }

        $this->set('data', $data);
    }

    public function add()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $data = [
            'bot' => [
                'token' => $_POST['telegram_bot']['token'] ?? '',
                'chat_id' => $_POST['telegram_bot']['chat_id'] ?? '',
            ],
            'errors' => [],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = trim($data['bot']['token']);
            $chatId = trim($data['bot']['chat_id']);

            if ($token === '') {
                $data['errors'][] = I18n::getTranslation(__("Token is required."));
            }

            if ($chatId === '') {
                $data['errors'][] = I18n::getTranslation(__("Chat id is required."));
            }

            if (empty($data['errors'])) {
                $validCredentials = $this->validateTelegramCredentials($token, $chatId);
                if ($validCredentials !== true) {
                    $data['errors'][] = $validCredentials;
                }
            }

            if (empty($data['errors'])) {
                $record = [
                    'telegram_bot' => [
                        'token' => $token,
                        'chat_id' => $chatId,
                        'insert_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                ];

                if ($db->sql_save($record)) {
                    $title = I18n::getTranslation(__("Telegram bot added"));
                    $msg = I18n::getTranslation(__("The bot has been stored successfully."));
                    set_flash("success", $title, $msg);
                    header('location: ' . LINK . 'telegram/index');
                    exit;
                }

                $data['errors'][] = I18n::getTranslation(__("Unable to save the bot: ")) . $db->sql_error();
            }
        }

        $this->set('data', $data);
    }

    public function view($param)
    {
        $bot = $this->getBotFromParam($param);

        $data = [
            'bot' => $bot,
        ];

        $this->set('data', $data);
    }

    public function delete($param)
    {
        $this->view = false;

        $bot = $this->getBotFromParam($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "DELETE FROM telegram_bot WHERE id = " . intval($bot['id']) . " LIMIT 1";

        $db->sql_query($sql);

        $title = I18n::getTranslation(__("Telegram bot deleted"));
        $msg = I18n::getTranslation(__("The bot has been removed."));
        set_flash("success", $title, $msg);

        if (defined('IS_CLI') && IS_CLI === true) {
            echo "Telegram bot {$bot['id']} deleted.\n";
            return;
        }

        $target = LINK . "telegram/index";

        $redirectParam = $_GET['redirect'] ?? '';
        if (!empty($redirectParam)) {
            $redirectParam = ltrim($redirectParam, '/');
            if ($redirectParam !== '') {
                $target = LINK . $redirectParam;
            }
        } elseif (!empty($_SERVER['HTTP_REFERER'])) {
            $target = $_SERVER['HTTP_REFERER'];
        }

        header("location: " . $target);
        exit;
    }

    private function getBotFromParam($param): array
    {
        $id = intval($param[0] ?? 0);
        if ($id <= 0) {
            throw new \Exception("PMACTRL-TELEGRAM-001: Missing or invalid bot id.");
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id, token, chat_id, insert_at, updated_at
            FROM telegram_bot WHERE id = " . $db->sql_real_escape_string($id) . " LIMIT 1";

        $res = $db->sql_query($sql);
        $bot = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if (empty($bot)) {
            throw new \Exception("PMACTRL-TELEGRAM-002: Telegram bot not found.");
        }

        return $bot;
    }

    private function validateTelegramCredentials(string $token, string $chatId)
    {
        if (!function_exists('curl_init')) {
            return I18n::getTranslation(__("PHP cURL extension is required to validate Telegram credentials."));
        }

        $endpoint = "https://api.telegram.org/bot{$token}/getChat";

        $payload = [
            'chat_id' => $chatId,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return I18n::getTranslation(__("Unable to contact Telegram API: ")) . $error;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);

        if ($httpCode !== 200 || empty($json['ok'])) {
            $description = $json['description'] ?? I18n::getTranslation(__("Unknown error returned by Telegram."));
            return I18n::getTranslation(__("Telegram rejected the provided token/chat_id: ")) . $description;
        }

        return true;
    }

    public static function broadcast(string $message, string $parseMode = 'HTML'): void
    {
        $text = trim($message);
        if ($text === '') {
            return;
        }

        $bots = self::getAllBots();
        foreach ($bots as $bot) {
            self::sendTelegramMessage($bot['token'], $bot['chat_id'], $text, $parseMode);
        }
    }

    private static function getAllBots(): array
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT token, chat_id FROM telegram_bot";
        $res = $db->sql_query($sql);

        $bots = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            if (!empty($row['token']) && !empty($row['chat_id'])) {
                $bots[] = $row;
            }
        }

        return $bots;
    }

    private static function sendTelegramMessage(string $token, string $chatId, string $text, string $parseMode = 'HTML'): void
    {
        if (!function_exists('curl_init')) {
            return;
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        Debug::debug($response, "TELEGRAM_RAW_RESPONSE");

        if ($response === false) {
            echo "[TELEGRAM] Failed to send message to {$chatId}\n";
            return;
        }

        $json = json_decode($response, true);
        if (empty($json['ok'])) {
            $description = $json['description'] ?? 'Unknown error';
            echo "[TELEGRAM] API rejected message for {$chatId}: {$description}\n";
        }
    }
}
