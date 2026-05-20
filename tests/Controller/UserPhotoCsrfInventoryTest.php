<?php

declare(strict_types=1);

use App\Controller\User;
use PHPUnit\Framework\TestCase;

final class UserPhotoCsrfInventoryTest extends TestCase
{
    public function testPhotoActionDoesNotExposePostMutationSurface(): void
    {
        $method = new \ReflectionMethod(User::class, 'photo');
        $source = file(__DIR__ . '/../../App/Controller/User.php');

        $this->assertIsArray($source);

        $methodBody = implode(
            '',
            array_slice($source, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1)
        );

        $this->assertStringNotContainsString('REQUEST_METHOD', $methodBody);
        $this->assertStringNotContainsString('$_POST', $methodBody);
        $this->assertStringNotContainsString('$_FILES', $methodBody);
    }
}
