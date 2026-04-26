<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InstallerOwnershipScopeTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        $this->script = file_get_contents(__DIR__ . '/../install/debian13.sh');
    }

    public function testDebian13InstallerKeepsOwnershipChangeScopedToPmaControlCheckout(): void
    {
        $this->assertStringContainsString(
            'local repo_dir="/srv/www/pmacontrol"',
            $this->script
        );
        $this->assertStringContainsString(
            'chown -R www-data:www-data "${repo_dir}"',
            $this->script
        );
    }

    public function testDebian13InstallerDoesNotChownGlobalVarWwwTree(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/^\s*chown\s+-R\s+www-data:www-data\s+\/var\/www\b/m',
            $this->script
        );
    }
}
