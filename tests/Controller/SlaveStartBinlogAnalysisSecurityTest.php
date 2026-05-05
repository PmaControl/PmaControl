<?php

declare(strict_types=1);

use App\Controller\Slave;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class SlaveStartBinlogAnalysisSecurityTest extends TestCase
{
    public function testStartBinlogAnalysisRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'slave.binlog_analysis.start');

        $outcome = Slave::evaluateStartBinlogAnalysisRequest(
            ['12'],
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(
            [
                'id_mysql_server' => 12,
                'connection_name' => 'production_fr',
                'time_start' => '2026-04-15 10:00:00',
                'time_end' => '2026-04-15 11:00:00',
            ],
            $outcome['request']
        );
    }

    public function testStartBinlogAnalysisRequestRejectsNonPost(): void
    {
        $outcome = Slave::evaluateStartBinlogAnalysisRequest([], [], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['request']);
    }

    public function testStartBinlogAnalysisRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'slave.binlog_analysis.start');

        $outcome = Slave::evaluateStartBinlogAnalysisRequest(
            ['12'],
            [Csrf::DEFAULT_FIELD => $token, 'connection_name' => ['invalid']],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['request']);
    }

    public function testStartBinlogAnalysisRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'storage_area.update');
        $server = $this->sameSitePostServer();

        $missingToken = Slave::evaluateStartBinlogAnalysisRequest(
            ['12'],
            $this->validPost(null),
            $server,
            $session
        );
        $foreignScope = Slave::evaluateStartBinlogAnalysisRequest(
            ['12'],
            $this->validPost($foreignToken),
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testStartBinlogAnalysisPayloadRejectsInvalidValues(): void
    {
        $post = $this->validPost('token');

        $this->assertNull(Slave::normalizeStartBinlogAnalysisPayload([], $post));
        $this->assertNull(Slave::normalizeStartBinlogAnalysisPayload(['0'], $post));
        $this->assertNull(Slave::normalizeStartBinlogAnalysisPayload(['12 OR 1=1'], $post));
        $this->assertNull(Slave::normalizeStartBinlogAnalysisPayload(['12'], []));
        $this->assertNull(Slave::normalizeStartBinlogAnalysisPayload(['12'], array_replace($post, ['connection_name' => ['prod']])));
        $this->assertNull(Slave::normalizeStartBinlogAnalysisPayload(['12'], array_replace($post, ['time_start' => '2026-02-31 10:00:00'])));
        $this->assertNull(Slave::normalizeStartBinlogAnalysisPayload(['12'], array_replace($post, ['time_end' => 'not-a-date'])));
        $this->assertNull(Slave::normalizeStartBinlogAnalysisPayload(['12'], array_replace($post, ['time_end' => '2026-04-15 09:59:59'])));
    }

    public function testStartBinlogAnalysisPayloadNormalizesConnectionAndMinuteDatetime(): void
    {
        $request = Slave::normalizeStartBinlogAnalysisPayload(
            ['12'],
            [
                'connection_name' => "prod'; DROP TABLE --",
                'time_start' => '2026-04-15 10:00',
                'time_end' => '2026-04-15 11:00',
            ]
        );

        $this->assertSame(
            [
                'id_mysql_server' => 12,
                'connection_name' => 'prodDROPTABLE--',
                'time_start' => '2026-04-15 10:00:00',
                'time_end' => '2026-04-15 11:00:00',
            ],
            $request
        );
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSideEffects(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'slave.binlog_analysis.start');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');

        $outcome = Slave::evaluateStartBinlogAnalysisRequest(
            ['12'],
            $this->validPost($token),
            $server,
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['request']);
    }

    public function testStartBinlogAnalysisUsesSharedCsrfGuardAndAjaxSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Slave.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString("private const SLAVE_BINLOG_ANALYSIS_START_CSRF_SCOPE = 'slave.binlog_analysis.start'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::SLAVE_BINLOG_ANALYSIS_START_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::SLAVE_BINLOG_ANALYSIS_START_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('sendStartBinlogAnalysisError($outcome[', $controller);

        $this->assertStringContainsString('$slaveBinlogAnalysisStartCsrfField', $view);
        $this->assertStringContainsString('$slaveBinlogAnalysisStartCsrfToken', $view);
        $this->assertStringContainsString('formData.append(<?= json_encode($slaveBinlogAnalysisStartCsrfField) ?>', $view);
        $this->assertStringContainsString("fetch(LINK + 'slave/startBinlogAnalysis/' + serverId + '/ajax:true/'", $view);
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }

    private function validPost(?string $token): array
    {
        $post = [
            'connection_name' => 'production_fr',
            'time_start' => '2026-04-15 10:00:00',
            'time_end' => '2026-04-15 11:00:00',
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }
}
