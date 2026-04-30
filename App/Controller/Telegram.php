<?php

namespace App\Controller;

use App\Library\Debug;
use App\Library\Security\CsrfGuard;
use Glial\I18n\I18n;
use Glial\Security\Csrf;
use Glial\Synapse\Controller;
use Glial\Sgbd\Sgbd;

/**
 * Class responsible for telegram workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Telegram extends Controller
{
    private const TELEGRAM_ADD_CSRF_SCOPE = 'telegram.add';
    private const TELEGRAM_ADD_FIELDS = ['token', 'chat_id'];
    private const TELEGRAM_ADD_MAX_FIELD_LENGTH = 255;

/**
 * Render telegram state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/telegram/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Create telegram state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/telegram/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add()
    {
        $data = [
            'bot' => [
                'token' => '',
                'chat_id' => '',
            ],
            'errors' => [],
            'telegram_add_csrf_field' => Csrf::DEFAULT_FIELD,
            'telegram_add_csrf_token' => Csrf::issueToken($_SESSION, self::TELEGRAM_ADD_CSRF_SCOPE),
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $outcome = self::evaluateAddRequest($_POST, $_SERVER, $_SESSION);
            if ($outcome['status'] !== 200) {
                $this->view        = false;
                $this->layout_name = false;
                self::sendTelegramAddError($outcome['status'], $outcome['body'], $outcome['headers']);
                return;
            }

            $data['bot'] = $outcome['bot'];
            $token = trim($data['bot']['token']);
            $chatId = trim($data['bot']['chat_id']);

            if ($token === '') {
                $data['errors'][] = I18n::getTranslation(__("Token is required."));
            } elseif (! self::isTelegramTokenFormat($token)) {
                $data['errors'][] = I18n::getTranslation(__("Token format is invalid."));
            }

            if ($chatId === '') {
                $data['errors'][] = I18n::getTranslation(__("Chat id is required."));
            } elseif (! self::isTelegramChatIdFormat($chatId)) {
                $data['errors'][] = I18n::getTranslation(__("Chat id format is invalid."));
            }

            if (empty($data['errors'])) {
                $validCredentials = $this->validateTelegramCredentials($token, $chatId);
                if ($validCredentials !== true) {
                    $data['errors'][] = $validCredentials;
                }
            }

            if (empty($data['errors'])) {
                $db = Sgbd::sql(DB_DEFAULT);
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

    public static function evaluateAddRequest(array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::TELEGRAM_ADD_CSRF_SCOPE)) {
            return self::buildTelegramAddOutcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $bot = self::normalizeAddPayload($post);
        if ($bot === null) {
            return self::buildTelegramAddOutcome(400, 'Invalid telegram add payload');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'bot' => $bot,
        ];
    }

    public static function normalizeAddPayload(array $post): ?array
    {
        $source = $post['telegram_bot'] ?? [];
        if ($source === []) {
            return [
                'token' => '',
                'chat_id' => '',
            ];
        }

        if (! is_array($source)) {
            return null;
        }

        $bot = [
            'token' => '',
            'chat_id' => '',
        ];

        foreach ($source as $field => $value) {
            if (! is_string($field) || ! in_array($field, self::TELEGRAM_ADD_FIELDS, true) || ! is_scalar($value)) {
                return null;
            }

            $value = (string) $value;
            if (strlen($value) > self::TELEGRAM_ADD_MAX_FIELD_LENGTH) {
                return null;
            }

            $bot[$field] = $value;
        }

        return $bot;
    }

    public static function isTelegramTokenFormat(string $token): bool
    {
        return preg_match('/^[0-9]+:[A-Za-z0-9_-]{20,}$/', $token) === 1;
    }

    public static function isTelegramChatIdFormat(string $chatId): bool
    {
        return preg_match('/^-?[0-9]+$/', $chatId) === 1
            || preg_match('/^@[A-Za-z0-9_]{5,32}$/', $chatId) === 1;
    }

    private static function buildTelegramAddOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'bot' => null,
        ];
    }

    private static function sendTelegramAddError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Handle telegram state through `view`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for view.
 * @phpstan-return void
 * @psalm-return void
 * @see self::view()
 * @example /fr/telegram/view
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function view($param)
    {
        $bot = $this->getBotFromParam($param);

        $data = [
            'bot' => $bot,
        ];

        $this->set('data', $data);
    }

/**
 * Delete telegram state through `delete`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for delete.
 * @phpstan-return void
 * @psalm-return void
 * @see self::delete()
 * @example /fr/telegram/delete
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Retrieve `getBotFromParam`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return array Returned value for getBotFromParam.
 * @phpstan-return array
 * @psalm-return array
 * @throws \Throwable When the underlying operation fails.
 * @example getBotFromParam(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle `validateTelegramCredentials`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $token Input value for `token`.
 * @phpstan-param string $token
 * @psalm-param string $token
 * @param string $chatId Input value for `chatId`.
 * @phpstan-param string $chatId
 * @psalm-param string $chatId
 * @return mixed Returned value for validateTelegramCredentials.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example validateTelegramCredentials(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
            return I18n::getTranslation(__("Unable to contact Telegram API: ")) . $error;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $json = json_decode($response, true);

        if ($httpCode !== 200 || empty($json['ok'])) {
            $description = $json['description'] ?? I18n::getTranslation(__("Unknown error returned by Telegram."));
            return I18n::getTranslation(__("Telegram rejected the provided token/chat_id: ")) . $description;
        }

        return true;
    }

/**
 * Handle `broadcast`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $message Input value for `message`.
 * @phpstan-param string $message
 * @psalm-param string $message
 * @param string $parseMode Input value for `parseMode`.
 * @phpstan-param string $parseMode
 * @psalm-param string $parseMode
 * @return void Returned value for broadcast.
 * @phpstan-return void
 * @psalm-return void
 * @example broadcast(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Retrieve `getAllBots`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return array Returned value for getAllBots.
 * @phpstan-return array
 * @psalm-return array
 * @example getAllBots(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle `sendTelegramMessage`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param string $token Input value for `token`.
 * @phpstan-param string $token
 * @psalm-param string $token
 * @param string $chatId Input value for `chatId`.
 * @phpstan-param string $chatId
 * @psalm-param string $chatId
 * @param string $text Input value for `text`.
 * @phpstan-param string $text
 * @psalm-param string $text
 * @param string $parseMode Input value for `parseMode`.
 * @phpstan-param string $parseMode
 * @psalm-param string $parseMode
 * @return void Returned value for sendTelegramMessage.
 * @phpstan-return void
 * @psalm-return void
 * @example sendTelegramMessage(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
