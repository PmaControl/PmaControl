<?php

declare(strict_types=1);

use App\Library\Security\IndexedRowsRequest;
use PHPUnit\Framework\TestCase;

final class IndexedRowsRequestTest extends TestCase
{
    public function testNormalizeAcceptsIndexedRows(): void
    {
        $rows = IndexedRowsRequest::normalize(
            [
                'link' => [
                    ['deploy' => 'on', 'id_mysql_server' => '7'],
                    ['id_mysql_server' => '8'],
                ],
            ],
            'link',
            $this->rules()
        );

        $this->assertSame(
            [
                ['deploy' => true, 'id_mysql_server' => 7],
                ['deploy' => false, 'id_mysql_server' => 8],
            ],
            $rows
        );
    }

    public function testNormalizeRejectsMalformedRows(): void
    {
        $this->assertNull(IndexedRowsRequest::normalize([], 'link', $this->rules()));
        $this->assertNull(IndexedRowsRequest::normalize(['link' => 'invalid'], 'link', $this->rules()));
        $this->assertNull(IndexedRowsRequest::normalize(['link' => []], 'link', $this->rules()));
        $this->assertNull(IndexedRowsRequest::normalize(['link' => ['row']], 'link', $this->rules()));
        $this->assertNull(IndexedRowsRequest::normalize(['link' => [['unexpected' => '1', 'id_mysql_server' => '7']]], 'link', $this->rules()));
        $this->assertNull(IndexedRowsRequest::normalize(['link' => [['deploy' => 'maybe', 'id_mysql_server' => '7']]], 'link', $this->rules()));
        $this->assertNull(IndexedRowsRequest::normalize(['link' => [['deploy' => 'on', 'id_mysql_server' => '0']]], 'link', $this->rules()));
        $this->assertNull(IndexedRowsRequest::normalize(['link' => [['deploy' => 'on', 'id_mysql_server' => '7 OR 1=1']]], 'link', $this->rules()));
    }

    public function testNormalizeRejectsTooManyRows(): void
    {
        $this->assertNull(IndexedRowsRequest::normalize(
            ['link' => [['id_mysql_server' => '7'], ['id_mysql_server' => '8']]],
            'link',
            $this->rules(),
            1
        ));
    }

    private function rules(): array
    {
        return [
            'deploy' => ['type' => 'bool', 'default' => false],
            'id_mysql_server' => ['type' => 'int', 'min' => 1],
        ];
    }
}
