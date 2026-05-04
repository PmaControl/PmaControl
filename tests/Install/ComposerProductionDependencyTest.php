<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ComposerProductionDependencyTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function productionInstallers(): array
    {
        return [
            'install.sh' => ['install.sh'],
            'debian10' => ['install/debian10.sh'],
            'debian12' => ['install/debian12.sh'],
            'debian13' => ['install/debian13.sh'],
        ];
    }

    #[DataProvider('productionInstallers')]
    public function testProductionInstallersDefaultToNoDevComposerInstall(string $relativePath): void
    {
        $script = $this->readProjectFile($relativePath);

        self::assertStringContainsString(
            'composer_install_args=(--no-dev --no-interaction --prefer-dist --optimize-autoloader)',
            $script
        );
        self::assertStringContainsString('composer install "${composer_install_args[@]}"', $script);

        foreach (preg_split('/\R/', $script) ?: [] as $line) {
            if (preg_match('/^\s*#/', $line) || strpos($line, 'composer install') === false) {
                continue;
            }

            self::assertStringContainsString(
                '${composer_install_args[@]}',
                $line,
                $relativePath . ' must not call composer install without the hardened production args'
            );
        }
    }

    public function testInstallersExposeExplicitDevelopmentDependencyPath(): void
    {
        self::assertStringContainsString('PMACTRL_INSTALL_DEV_DEPS', $this->readProjectFile('install.sh'));
        self::assertStringContainsString('PMACTRL_INSTALL_DEV_DEPS', $this->readProjectFile('install/debian10.sh'));
        self::assertStringContainsString('if [[ $DEV_MOD -eq 1 ]]; then', $this->readProjectFile('install/debian12.sh'));
        self::assertStringContainsString('if [[ $DEV_MOD -eq 1 ]]; then', $this->readProjectFile('install/debian13.sh'));
    }

    public function testDocumentationSeparatesProductionAndDevelopmentComposerInstall(): void
    {
        $readme = $this->readProjectFile('README.md');
        $buildTest = $this->readProjectFile('docs/build_test.md');

        foreach ([$readme, $buildTest] as $document) {
            self::assertStringContainsString(
                'composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader',
                $document
            );
            self::assertStringContainsString('composer install --no-interaction', $document);
        }
    }

    private function readProjectFile(string $relativePath): string
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/' . $relativePath);

        self::assertIsString($contents);

        return $contents;
    }
}
