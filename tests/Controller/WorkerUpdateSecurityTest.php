<?php

declare(strict_types=1);

use App\Controller\Worker;
use PHPUnit\Framework\TestCase;

final class WorkerUpdateSecurityTest extends TestCase
{
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

        $this->assertStringContainsString('use App\\Library\\Csrf;', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::WORKER_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('Csrf::isSameSiteRequest($_SERVER)', $controller);
        $this->assertStringContainsString('Csrf::validateToken($_POST, $_SESSION, self::WORKER_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringNotContainsString('isWorkerUpdateSourceSameSite', $controller);

        $this->assertStringContainsString('data-csrf-token="', $view);
        $this->assertStringContainsString('worker/update', $view);
        $this->assertStringContainsString('params.csrf_token = csrfToken;', $javascript);
        $this->assertStringContainsString('window.pmacontrolInitLineEdit', $javascript);
        $this->assertStringContainsString('window.pmacontrolInitLineEdit(this);', $daemon);
    }
}
