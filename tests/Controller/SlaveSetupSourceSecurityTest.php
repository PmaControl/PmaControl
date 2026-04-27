<?php

declare(strict_types=1);

use App\Controller\Slave;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class SlaveSetupSourceSecurityTest extends TestCase
{
    public function testSetupSourceRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'slave.setup_source');

        $outcome = Slave::evaluateSetupSourceRequest(
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
                'master_host' => '10.68.68.180',
                'master_port' => 3307,
                'master_user' => 'repl',
                'master_password' => 'secret',
                'use_gtid' => true,
                'use_ssl' => true,
                'replicate_do_db' => 'db1,db2',
                'replicate_rewrite_db' => 'source->target',
            ],
            $outcome['request']
        );
    }

    public function testSetupSourceRequestRejectsNonPost(): void
    {
        $outcome = Slave::evaluateSetupSourceRequest([], [], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['request']);
    }

    public function testSetupSourceRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'slave.setup_source');

        $outcome = Slave::evaluateSetupSourceRequest(
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

    public function testSetupSourceRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'slave.binlog_analysis.start');
        $server = $this->sameSitePostServer();

        $missingToken = Slave::evaluateSetupSourceRequest(
            ['12'],
            $this->validPost(null),
            $server,
            $session
        );
        $foreignScope = Slave::evaluateSetupSourceRequest(
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

    public function testSetupSourcePayloadRejectsInvalidValues(): void
    {
        $post = $this->validPost('token');

        $this->assertNull(Slave::normalizeSetupSourcePayload([], $post));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['0'], $post));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12 OR 1=1'], $post));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12'], []));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12'], array_replace($post, ['connection_name' => ['prod']])));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12'], array_replace($post, ['connection_name' => '   '])));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12'], array_replace($post, ['master_host' => '   '])));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12'], array_replace($post, ['master_user' => '   '])));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12'], array_replace($post, ['master_password' => ['secret']])));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12'], array_replace($post, ['master_port' => '0'])));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12'], array_replace($post, ['master_port' => '65536'])));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12'], array_replace($post, ['master_port' => '3306abc'])));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12'], array_replace($post, ['replicate_do_db' => ['db1']])));
        $this->assertNull(Slave::normalizeSetupSourcePayload(['12'], array_replace($post, ['replicate_rewrite_db' => ['db1->db2']])));
    }

    public function testSetupSourcePayloadNormalizesScalarsAndDefaults(): void
    {
        $request = Slave::normalizeSetupSourcePayload(
            ['12'],
            [
                'connection_name' => "prod'; DROP TABLE --",
                'master_host' => '  10.68.68.180  ',
                'master_user' => '  repl  ',
                'master_password' => '',
                'replicate_do_db' => ' db1, db2 ',
                'replicate_rewrite_db' => ' source->target ',
            ]
        );

        $this->assertSame(
            [
                'id_mysql_server' => 12,
                'connection_name' => 'prodDROPTABLE--',
                'master_host' => '10.68.68.180',
                'master_port' => 3306,
                'master_user' => 'repl',
                'master_password' => '',
                'use_gtid' => false,
                'use_ssl' => false,
                'replicate_do_db' => 'db1, db2',
                'replicate_rewrite_db' => 'source->target',
            ],
            $request
        );
    }

    public function testExternalPostWouldPassLegacyMethodGateButIsRejectedBeforeSideEffects(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'slave.setup_source');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');

        $outcome = Slave::evaluateSetupSourceRequest(
            ['12'],
            $this->validPost($token),
            $server,
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['request']);
    }

    public function testSetupSourceUsesSharedCsrfGuardAndFormCarriesToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Slave.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $setupPosition = strpos($controller, 'public function setupSource');
        $evaluatePosition = strpos($controller, '$outcome = self::evaluateSetupSourceRequest($param, $_POST, $_SERVER, $_SESSION);', $setupPosition);
        $dbPosition = strpos($controller, '$db = Mysql::getDbLink($id_mysql_server);', $setupPosition);

        $this->assertStringContainsString("private const SLAVE_SETUP_SOURCE_CSRF_SCOPE = 'slave.setup_source'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::SLAVE_SETUP_SOURCE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::SLAVE_SETUP_SOURCE_CSRF_SCOPE)', $controller);
        $this->assertIsInt($setupPosition);
        $this->assertIsInt($evaluatePosition);
        $this->assertIsInt($dbPosition);
        $this->assertLessThan($dbPosition, $evaluatePosition);

        $this->assertStringContainsString('$slaveSetupSourceCsrfField', $view);
        $this->assertStringContainsString('$slaveSetupSourceCsrfToken', $view);
        $this->assertStringContainsString('<input type="hidden" name="<?= $slaveSetupSourceCsrfField ?>" value="<?= $slaveSetupSourceCsrfToken ?>">', $view);
        $this->assertStringContainsString('action="<?= LINK ?>slave/setupSource/<?= $data[', $view);
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
            'master_host' => '10.68.68.180',
            'master_port' => '3307',
            'master_user' => 'repl',
            'master_password' => 'secret',
            'use_gtid' => '1',
            'use_ssl' => '1',
            'replicate_do_db' => 'db1,db2',
            'replicate_rewrite_db' => 'source->target',
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }
}
