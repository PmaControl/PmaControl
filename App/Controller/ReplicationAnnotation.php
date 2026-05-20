<?php

declare(strict_types=1);

namespace App\Controller;

use App\Library\Security\CsrfGuard;
use Glial\Sgbd\Sgbd;
use Glial\Synapse\Controller;

final class ReplicationAnnotation extends Controller
{
    public const SAVE_CSRF_SCOPE = 'replication.annotation.save';
    public const DELETE_CSRF_SCOPE = 'replication.annotation.delete';

    public function save($param)
    {
        $this->view = false;
        if ($failure = CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::SAVE_CSRF_SCOPE)) {
            $this->jsonError((int)$failure['status'], (string)$failure['body'], $failure['headers'] ?? []);
            return;
        }

        $serverId = max(0, (int)($_POST['id_mysql_server'] ?? 0));
        $connectionName = substr(trim((string)($_POST['connection_name'] ?? '')), 0, 64);
        $type = (string)($_POST['annotation_type'] ?? 'other');
        if (!in_array($type, ['deploy', 'migration', 'tuning', 'incident', 'other'], true)) {
            $type = 'other';
        }
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $time = trim((string)($_POST['annotation_time'] ?? ''));
        $ts = strtotime($time);
        if ($serverId < 0 || $title === '' || $ts === false) {
            $this->jsonError(400, 'Invalid annotation payload');
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "INSERT INTO replication_annotation
            (id_mysql_server, connection_name, annotation_type, title, description, annotation_time, created_by)
            VALUES (" . (int)$serverId . ",
                '" . $db->sql_real_escape_string($connectionName) . "',
                '" . $db->sql_real_escape_string($type) . "',
                '" . $db->sql_real_escape_string($title) . "',
                '" . $db->sql_real_escape_string($description) . "',
                '" . $db->sql_real_escape_string(date('Y-m-d H:i:s', $ts)) . "',
                '" . $db->sql_real_escape_string((string)($_SESSION['login'] ?? $_SESSION['user'] ?? '')) . "')";
        $db->sql_query($sql);

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => true], JSON_UNESCAPED_SLASHES);
    }

    public function delete($param)
    {
        $this->view = false;
        if ($failure = CsrfGuard::ensureOrFail($_POST, $_SERVER, $_SESSION, self::DELETE_CSRF_SCOPE)) {
            $this->jsonError((int)$failure['status'], (string)$failure['body'], $failure['headers'] ?? []);
            return;
        }

        $id = max(0, (int)($param[0] ?? $_POST['id'] ?? 0));
        if ($id <= 0) {
            $this->jsonError(400, 'Invalid annotation id');
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_query('DELETE FROM replication_annotation WHERE id = ' . (int)$id . ' LIMIT 1');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => true], JSON_UNESCAPED_SLASHES);
    }

    private function jsonError(int $status, string $message, array $headers = []): void
    {
        http_response_code($status);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $message], JSON_UNESCAPED_SLASHES);
    }
}
