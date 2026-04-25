<?php

declare(strict_types=1);

use App\Controller\Worker;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class WorkerUpdateSecurityTest extends TestCase
{
    public function testWorkerUpdateRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'worker.update');

        $outcome = Worker::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'nb_worker', 'value' => '4', 'pk' => '7'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://pmacontrol.test',
            ],
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('UPDATE worker_queue SET `nb_worker` = 4 WHERE id = 7', $outcome['sql']);
    }

    public function testWorkerUpdateRequestRejectsNonPost(): void
    {
        $outcome = Worker::evaluateUpdateRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testWorkerUpdateRequestRejectsExternalSourceAndMissingToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'worker.update');

        $external = Worker::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'nb_worker', 'value' => '4', 'pk' => '7'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $missingToken = Worker::evaluateUpdateRequest(
            ['name' => 'nb_worker', 'value' => '4', 'pk' => '7'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://pmacontrol.test',
            ],
            $session
        );

        $this->assertSame(403, $external['status']);
        $this->assertSame('Invalid request origin', $external['body']);
        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
    }

    public function testWorkerUpdateSqlIsRestrictedToKnownIntegerFields(): void
    {
        $this->assertSame(
            'UPDATE worker_queue SET `nb_worker` = 4 WHERE id = 7',
            Worker::buildWorkerUpdateSql(['name' => 'nb_worker', 'value' => '4', 'pk' => '7'])
        );

        $this->assertSame(
            'UPDATE worker_queue SET `queue_number` = 1200 WHERE id = 8',
            Worker::buildWorkerUpdateSql(['name' => 'queue_number', 'value' => '1200', 'pk' => '8'])
        );

        $this->assertSame(
            'UPDATE worker_queue SET `nb_worker` = 0 WHERE id = 7',
            Worker::buildWorkerUpdateSql(['name' => 'nb_worker', 'value' => '0', 'pk' => '7'])
        );

        $this->assertNull(Worker::buildWorkerUpdateSql(['name' => 'query', 'value' => 'SELECT 1', 'pk' => '7']));
        $this->assertNull(Worker::buildWorkerUpdateSql(['name' => 'nb_worker', 'value' => '4; DROP TABLE worker_queue', 'pk' => '7']));
        $this->assertNull(Worker::buildWorkerUpdateSql(['name' => 'nb_worker', 'value' => '4', 'pk' => '0']));
        $this->assertNull(Worker::buildWorkerUpdateSql(['name' => 'nb_worker', 'value' => '-1', 'pk' => '7']));
    }

    public function testWorkerUsesSharedCsrfLibraryAndInlineEditSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Worker.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Worker/index.view.php');
        $javascript = file_get_contents(__DIR__ . '/../../App/Webroot/js/Tree/index.js');
        $daemon = file_get_contents(__DIR__ . '/../../App/Controller/Daemon.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertIsString($javascript);
        $this->assertIsString($daemon);

        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString('use Glial\\Http\\Request;', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::WORKER_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('Request::isSameSite($server)', $controller);
        $this->assertStringContainsString('Csrf::validateToken($post, $session, self::WORKER_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringNotContainsString('isWorkerUpdateSourceSameSite', $controller);

        $this->assertStringContainsString('data-csrf-field="', $view);
        $this->assertStringContainsString('data-csrf-token="', $view);
        $this->assertStringContainsString('worker/update', $view);
        $this->assertStringContainsString("params[csrfField] = csrfToken;", $javascript);
        $this->assertStringContainsString('window.pmacontrolInitLineEdit', $javascript);
        $this->assertStringContainsString('window.pmacontrolInitLineEdit(this);', $daemon);
    }
}
