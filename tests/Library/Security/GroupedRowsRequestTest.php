<?php

declare(strict_types=1);

use App\Library\Security\GroupedRowsRequest;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class GroupedRowsRequestTest extends TestCase
{
    public function testEvaluateAcceptsValidGroupedRowsPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'docker.addContainer');

        $outcome = GroupedRowsRequest::evaluate(
            [
                Csrf::DEFAULT_FIELD => $token,
                'docker_container' => [
                    'count' => ['2'],
                    'id_software' => ['3'],
                    'major' => [' 11.4 '],
                    'id_image' => ['9'],
                    'label' => [' app-db '],
                ],
            ],
            $this->sameSitePostServer(),
            $session,
            'docker.addContainer',
            'docker_container',
            $this->rules(),
            'Invalid docker container payload'
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(
            [['count' => 2, 'id_software' => 3, 'major' => '11.4', 'id_image' => 9, 'label' => 'app-db']],
            $outcome['rows']
        );
    }

    public function testEvaluatePropagatesCsrfGuardFailure(): void
    {
        $outcome = GroupedRowsRequest::evaluate(
            [],
            ['REQUEST_METHOD' => 'GET'],
            [],
            'docker.addContainer',
            'docker_container',
            $this->rules(),
            'Invalid docker container payload'
        );

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['rows']);
    }

    public function testNormalizeRejectsMalformedColumnsAndInvalidValues(): void
    {
        $this->assertNull(GroupedRowsRequest::normalize([], 'docker_container', $this->rules()));
        $this->assertNull(GroupedRowsRequest::normalize(['docker_container' => 'invalid'], 'docker_container', $this->rules()));
        $this->assertNull(GroupedRowsRequest::normalize(['docker_container' => []], 'docker_container', $this->rules()));
        $this->assertNull(GroupedRowsRequest::normalize(['docker_container' => ['count' => '1']], 'docker_container', $this->rules()));
        $this->assertNull(GroupedRowsRequest::normalize(['docker_container' => $this->validRows(['unexpected' => ['1']])], 'docker_container', $this->rules()));
        $this->assertNull(GroupedRowsRequest::normalize(['docker_container' => $this->validRows(['label' => ['one', 'two']])], 'docker_container', $this->rules()));
        $this->assertNull(GroupedRowsRequest::normalize(['docker_container' => $this->validRows(['count' => ['0']])], 'docker_container', $this->rules()));
        $this->assertNull(GroupedRowsRequest::normalize(['docker_container' => $this->validRows(['count' => ['33']])], 'docker_container', $this->rules()));
        $this->assertNull(GroupedRowsRequest::normalize(['docker_container' => $this->validRows(['id_image' => ['9 OR 1=1']])], 'docker_container', $this->rules()));
        $this->assertNull(GroupedRowsRequest::normalize(['docker_container' => $this->validRows(['label' => [str_repeat('a', 65)]])], 'docker_container', $this->rules()));
    }

    public function testNormalizeRejectsMoreThanConfiguredMaxRows(): void
    {
        $rows = [
            'count' => ['1', '1', '1'],
            'id_software' => ['1', '1', '1'],
            'major' => ['11.4', '11.4', '11.4'],
            'id_image' => ['9', '9', '9'],
            'label' => ['', '', ''],
        ];

        $this->assertNull(GroupedRowsRequest::normalize(['docker_container' => $rows], 'docker_container', $this->rules(), 2));
    }

    private function rules(): array
    {
        return [
            'count' => ['type' => 'int', 'required' => true, 'default' => 1, 'min' => 1, 'max' => 32],
            'id_software' => ['type' => 'int', 'required' => true, 'default' => 0, 'min' => 0],
            'major' => ['type' => 'string', 'required' => true, 'default' => '', 'max' => 32],
            'id_image' => ['type' => 'int', 'required' => true, 'default' => 0, 'min' => 0],
            'label' => ['type' => 'string', 'required' => true, 'default' => '', 'max' => 64],
        ];
    }

    private function validRows(array $override = []): array
    {
        return array_replace([
            'count' => ['1'],
            'id_software' => ['2'],
            'major' => ['11.4'],
            'id_image' => ['9'],
            'label' => ['app-db'],
        ], $override);
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
