<?php

declare(strict_types=1);

namespace App\Controller;

use App\Library\Debug;
use App\Library\Digest\DigestReportService;
use App\Library\Security\CsrfGuard;
use Glial\Security\Csrf;
use Glial\Synapse\Controller;

final class DigestReport extends Controller
{
    public const DIGEST_REPORT_SAVE_CSRF_SCOPE = 'digest_report.save';

    private const SESSION_RESULT_KEY = 'digest_report.result';

    public function index($param = []): void
    {
        Debug::parseDebug($param);

        if (CsrfGuard::isPost($_SERVER)) {
            $outcome = self::evaluateSaveRequest($_POST, $_SERVER, $_SESSION);
            $_SESSION[self::SESSION_RESULT_KEY] = self::buildSessionResult($outcome);

            if ($outcome['status'] === 200 && is_array($outcome['payload'])) {
                try {
                    $id = DigestReportService::saveSchedule($outcome['payload']);
                    $_SESSION[self::SESSION_RESULT_KEY] = [
                        'flash_type' => 'success',
                        'flash_message' => 'Digest report schedule saved #' . $id . '.',
                    ];
                } catch (\Throwable $exception) {
                    $_SESSION[self::SESSION_RESULT_KEY] = [
                        'flash_type' => 'error',
                        'flash_message' => 'Digest report schedule save failed: ' . self::cleanMessage($exception->getMessage()),
                    ];
                }
            }

            $this->redirectTo(LINK . 'DigestReport/index');
            return;
        }

        $this->title = 'Digest reports';
        $this->ariane = '> Settings > Digest reports';

        $data = DigestReportService::buildDashboardData();
        $data['digest_report_save_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['digest_report_save_csrf_token'] = Csrf::issueToken($_SESSION, self::DIGEST_REPORT_SAVE_CSRF_SCOPE);

        if (isset($_SESSION[self::SESSION_RESULT_KEY]) && is_array($_SESSION[self::SESSION_RESULT_KEY])) {
            $data = array_replace($data, $_SESSION[self::SESSION_RESULT_KEY]);
            unset($_SESSION[self::SESSION_RESULT_KEY]);
        }

        $this->set('data', $data);
    }

    public function run($param = []): void
    {
        Debug::parseDebug($param);
        $this->view = false;
        $this->layout_name = false;

        $request = self::evaluateRunRequest($param, PHP_SAPI);
        if ($request['status'] !== 200) {
            if (PHP_SAPI !== 'cli') {
                http_response_code($request['status']);
            }
            echo $request['body'] . "\n";
            return;
        }

        $payload = $request['payload'];
        $result = $payload['target'] === 'due'
            ? DigestReportService::runDueSchedules((bool)$payload['dry_run'])
            : DigestReportService::runScheduleId((int)$payload['schedule_id'], (bool)$payload['dry_run']);

        echo self::formatRunResult($result);
    }

    public static function evaluateSaveRequest(array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::DIGEST_REPORT_SAVE_CSRF_SCOPE)) {
            return self::outcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $validation = DigestReportService::normalizeSchedulePayload($post['digest_report'] ?? null);
        if (!$validation['valid']) {
            return self::outcome(400, implode(' ', $validation['errors']));
        }

        return self::outcome(200, '', [], $validation['payload']);
    }

    public static function evaluateRunRequest(array $param, string $sapi): array
    {
        if ($sapi !== 'cli') {
            return self::outcome(403, 'Digest report runner is CLI-only.');
        }

        $args = [];
        foreach ($param as $value) {
            if (is_scalar($value)) {
                $args[] = trim((string)$value);
            }
        }

        $dryRun = in_array('dry-run', $args, true) || in_array('--dry-run', $args, true);
        $args = array_values(array_filter($args, static function (string $value): bool {
            return $value !== '' && $value !== 'dry-run' && $value !== '--dry-run';
        }));

        $target = $args[0] ?? 'due';
        if ($target === 'due') {
            return self::outcome(200, '', [], ['target' => 'due', 'schedule_id' => null, 'dry_run' => $dryRun]);
        }

        if (ctype_digit($target) && (int)$target > 0) {
            return self::outcome(200, '', [], ['target' => 'id', 'schedule_id' => (int)$target, 'dry_run' => $dryRun]);
        }

        return self::outcome(400, 'Usage: php App/Webroot/index.php DigestReport run [due|schedule_id] [--dry-run]');
    }

    private static function buildSessionResult(array $outcome): array
    {
        if ($outcome['status'] === 200) {
            return ['flash_type' => 'success', 'flash_message' => 'Digest report schedule saved.'];
        }

        return ['flash_type' => 'error', 'flash_message' => $outcome['body']];
    }

    private static function formatRunResult(array $result): string
    {
        $lines = [
            strtoupper((string)$result['status']) . ': ' . (string)$result['message'],
            'Processed: ' . (string)$result['processed'],
        ];

        foreach (($result['items'] ?? []) as $item) {
            $lines[] = '- #' . (string)$item['id'] . ' ' . (string)$item['name'] . ': ' . (string)$item['status']
                . ' (' . count($item['recipients'] ?? []) . ' recipient(s))';
            foreach (($item['warnings'] ?? []) as $warning) {
                $lines[] = '  warning: ' . (string)$warning;
            }
            foreach (($item['errors'] ?? []) as $error) {
                $lines[] = '  error: ' . (string)$error;
            }
        }

        return implode("\n", $lines) . "\n";
    }

    private static function outcome(int $statusCode, string $message, array $headers = [], ?array $payload = null): array
    {
        return ['status' => $statusCode, 'body' => $message, 'headers' => $headers, 'payload' => $payload];
    }

    private function redirectTo(string $url): void
    {
        $this->view = false;
        $this->layout_name = false;
        header('Location: ' . $url, true, 303);
    }

    private static function cleanMessage(string $message): string
    {
        $message = preg_replace('/[\r\n\t]+/', ' ', $message) ?? '';
        $message = preg_replace('/\s+/', ' ', $message) ?? '';

        return mb_substr(trim($message), 0, 300);
    }
}
