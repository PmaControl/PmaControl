<?php

declare(strict_types=1);

use App\Library\Post;
use PHPUnit\Framework\TestCase;

final class PostTest extends TestCase
{
    private array $previousPost = [];

    protected function setUp(): void
    {
        $this->previousPost = $_POST;
    }

    protected function tearDown(): void
    {
        $_POST = $this->previousPost;
    }

    public function testGetToPostSkipsScalarTopLevelFields(): void
    {
        $_POST = [
            '_csrf_token' => 'secret-token',
            'backup_storage_area' => [
                'path' => '/srv/backup/mysql',
                'libelle' => 'Remote backup',
            ],
        ];

        $this->assertSame(
            'backup_storage_area:path:[DS]srv[DS]backup[DS]mysql/backup_storage_area:libelle:Remote backup',
            Post::getToPost()
        );
    }
}
