<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Debian13InstallerSafetyTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        $path = dirname(__DIR__, 2) . '/install/debian13.sh';

        $this->script = file_get_contents($path);

        self::assertIsString($this->script);
    }

    public function testExistingCheckoutIsUpdatedWithoutDestructiveDeleteByDefault(): void
    {
        self::assertStringContainsString('if [[ -d "${repo_dir}/.git" ]]; then', $this->script);
        self::assertStringContainsString('git fetch origin "${GIT_BRANCH}"', $this->script);
        self::assertStringContainsString('git merge --ff-only "origin/${GIT_BRANCH}"', $this->script);
        self::assertStringContainsString('Resolve them manually or rerun with -r', $this->script);
    }

    public function testDestructiveReinstallRequiresExplicitFlag(): void
    {
        self::assertStringContainsString('FORCE_REINSTALL=0', $this->script);
        self::assertStringContainsString('F) FORCE_REINSTALL="1" ;;', $this->script);
        self::assertStringContainsString('rerun with -F for a destructive reinstall', $this->script);
        self::assertMatchesRegularExpression(
            '/if \[\[ -e "\$\{repo_dir\}" && \$FORCE_REINSTALL -eq 1 \]\]; then\s+rm -rf "\$\{repo_dir\}"/',
            $this->script
        );
        self::assertStringNotContainsString('rm -rf /srv/www/pmacontrol', $this->script);
    }

    public function testHardResetRequiresExplicitFlag(): void
    {
        self::assertStringContainsString('RESET_EXISTING_CHECKOUT=0', $this->script);
        self::assertStringContainsString('r) RESET_EXISTING_CHECKOUT="1" ;;', $this->script);
        self::assertMatchesRegularExpression(
            '/if \[\[ \$RESET_EXISTING_CHECKOUT -eq 1 \]\]; then\s+git checkout -B "\$\{GIT_BRANCH\}" "origin\/\$\{GIT_BRANCH\}"\s+git reset --hard "origin\/\$\{GIT_BRANCH\}"/',
            $this->script
        );
    }
}
