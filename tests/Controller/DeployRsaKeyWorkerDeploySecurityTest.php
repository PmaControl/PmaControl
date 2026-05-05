<?php

declare(strict_types=1);

use App\Controller\DeployRsaKey;
use PHPUnit\Framework\TestCase;

final class DeployRsaKeyWorkerDeploySecurityTest extends TestCase
{
    public function testWorkerDeployAllowsCli(): void
    {
        $outcome = DeployRsaKey::evaluateWorkerDeployRequest(true);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('', $outcome['body']);
        $this->assertSame([], $outcome['headers']);
    }

    public function testWorkerDeployRejectsWebRequests(): void
    {
        $outcome = DeployRsaKey::evaluateWorkerDeployRequest(false);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('CLI only', $outcome['body']);
        $this->assertSame([], $outcome['headers']);
    }

    public function testWorkerDeployGuardRunsBeforeQueueAndDeployment(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/DeployRsaKey.php');
        $workerStart = strpos($controller, 'public function workerDeploy()');
        $workerEnd = strpos($controller, 'public static function evaluateWorkerDeployRequest');

        $this->assertNotFalse($workerStart);
        $this->assertNotFalse($workerEnd);

        $workerBody = substr($controller, $workerStart, $workerEnd - $workerStart);
        $guardPosition = strpos($workerBody, "evaluateWorkerDeployRequest(defined('IS_CLI') && IS_CLI === true)");
        $queuePosition = strpos($workerBody, 'msg_get_queue(self::KEY_WORKER_DEPLOY)');
        $deployPosition = strpos($workerBody, "\$this->deploy2(\$data['server'], \$data['key']);");

        $this->assertIsInt($guardPosition);
        $this->assertIsInt($queuePosition);
        $this->assertIsInt($deployPosition);
        $this->assertLessThan($queuePosition, $guardPosition);
        $this->assertLessThan($deployPosition, $guardPosition);
    }
}
