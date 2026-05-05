<?php

declare(strict_types=1);

use App\Library\Security\GroupedFormRequest;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class GroupedFormRequestTest extends TestCase
{
    public function testEvaluateAcceptsValidGroupedPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'environment.add');

        $outcome = GroupedFormRequest::evaluate(
            [
                Csrf::DEFAULT_FIELD => $token,
                'environment' => [
                    'libelle' => ' Preprod ',
                    'class' => 'warning',
                ],
            ],
            $this->sameSitePostServer(),
            $session,
            'environment.add',
            'environment',
            [
                'libelle' => ['type' => 'string', 'required' => true, 'max' => 20],
                'class' => ['type' => 'enum', 'required' => true, 'values' => ['warning', 'danger']],
                'letter' => ['type' => 'string', 'default' => 'P', 'max' => 1],
            ],
            'Invalid environment add payload'
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['libelle' => 'Preprod', 'class' => 'warning', 'letter' => 'P'], $outcome['payload']);
    }

    public function testEvaluatePropagatesCsrfGuardFailure(): void
    {
        $outcome = GroupedFormRequest::evaluate(
            [],
            ['REQUEST_METHOD' => 'GET'],
            [],
            'environment.add',
            'environment',
            ['libelle' => ['required' => true]],
            'Invalid environment add payload'
        );

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['payload']);
    }

    public function testNormalizeRejectsMalformedUnexpectedOrInvalidFields(): void
    {
        $rules = [
            'libelle' => ['type' => 'string', 'required' => true, 'max' => 20],
            'class' => ['type' => 'enum', 'required' => true, 'values' => ['warning', 'danger']],
        ];

        $this->assertNull(GroupedFormRequest::normalize([], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => 'Preprod'], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => []], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => ['id' => '7', 'libelle' => 'Preprod', 'class' => 'warning']], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => ['libelle' => ['Preprod'], 'class' => 'warning']], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => ['libelle' => str_repeat('a', 21), 'class' => 'warning']], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => ['libelle' => 'Preprod', 'class' => 'owned']], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => ['libelle' => '', 'class' => 'warning']], 'environment', $rules));

        $this->assertSame(
            ['libelle' => 'Preprod', 'class' => 'warning'],
            GroupedFormRequest::normalize(['environment' => ['libelle' => ' Preprod ', 'class' => 'warning']], 'environment', $rules)
        );
    }

    public function testNormalizeSupportsIntegerRulesWithDefaultsAndBounds(): void
    {
        $rules = [
            'hostname' => ['type' => 'string', 'required' => true, 'max' => 255],
            'port' => ['type' => 'int', 'default' => 22, 'min' => 1, 'max' => 65535],
            'id_ssh_key' => ['type' => 'int', 'default' => 0, 'min' => 0],
            'is_active' => ['type' => 'enum', 'default' => '1', 'values' => ['0', '1']],
        ];

        $this->assertSame(
            ['hostname' => 'docker.local', 'port' => 22, 'id_ssh_key' => 0, 'is_active' => '1'],
            GroupedFormRequest::normalize(['docker_server' => ['hostname' => ' docker.local ', 'port' => '', 'id_ssh_key' => '']], 'docker_server', $rules)
        );
        $this->assertNull(GroupedFormRequest::normalize(['docker_server' => ['hostname' => 'docker.local', 'port' => '0']], 'docker_server', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['docker_server' => ['hostname' => 'docker.local', 'port' => '65536']], 'docker_server', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['docker_server' => ['hostname' => 'docker.local', 'port' => '22 OR 1=1']], 'docker_server', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['docker_server' => ['hostname' => 'docker.local', 'is_active' => 'yes']], 'docker_server', $rules));
    }

    public function testNormalizeSupportsListRulesAndPatterns(): void
    {
        $rules = [
            'id_mysql_server' => ['type' => 'int', 'required' => true, 'min' => 1],
            'list' => [
                'type' => 'list',
                'required' => true,
                'min_items' => 1,
                'max_items' => 3,
                'item_type' => 'string',
                'item_min' => 1,
                'item_max' => 64,
                'item_pattern' => '/^[A-Za-z0-9_-]{1,64}$/',
            ],
            'path' => ['type' => 'string', 'required' => true, 'pattern' => '#^/[A-Za-z0-9._/-]{1,255}$#'],
        ];

        $this->assertSame(
            ['id_mysql_server' => 7, 'list' => ['db1', 'db-2'], 'path' => '/mysql/backup'],
            GroupedFormRequest::normalize([
                'database' => [
                    'id_mysql_server' => '7',
                    'list' => [' db1 ', 'db-2'],
                    'path' => '/mysql/backup',
                ],
            ], 'database', $rules)
        );

        $this->assertNull(GroupedFormRequest::normalize(['database' => ['id_mysql_server' => '7', 'list' => [], 'path' => '/mysql/backup']], 'database', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['database' => ['id_mysql_server' => '7', 'list' => ['db1', 'db1'], 'path' => '/mysql/backup']], 'database', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['database' => ['id_mysql_server' => '7', 'list' => ['db 1'], 'path' => '/mysql/backup']], 'database', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['database' => ['id_mysql_server' => '7', 'list' => ['db1'], 'path' => 'relative/path']], 'database', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['database' => ['id_mysql_server' => '7', 'list' => 'db1', 'path' => '/mysql/backup']], 'database', $rules));
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
}
