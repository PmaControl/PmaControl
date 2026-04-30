<?php

declare(strict_types=1);

use App\Library\Security\RouteExposurePolicy;
use PHPUnit\Framework\TestCase;

/**
 * @see https://git.istosia.com/pmacontrol/pmacontrol/issues/517
 */
final class LabControllerNoHttpRouteTest extends TestCase
{
    public function testLegacyDemoLabControllerIsDeniedOverHttp(): void
    {
        foreach ($this->publicMethods('App/Controller/Demo.php') as $method) {
            $this->assertTrue(
                RouteExposurePolicy::isDeniedWebRoute('Demo', $method),
                'Demo/'.$method.' must stay blocked from HTTP until lab logic is extracted.'
            );
        }
    }

    public function testRemovedMasterSlaveControllerStaysBlockedAsTombstone(): void
    {
        $root = dirname(__DIR__, 2);

        $this->assertFileDoesNotExist($root.'/App/Controller/MasterSlave.php');
        $this->assertTrue(RouteExposurePolicy::isDeniedWebRoute('MasterSlave', 'index'));
        $this->assertTrue(RouteExposurePolicy::isDeniedWebRoute('MasterSlave', 'setUpDemo'));
        $this->assertNotSame('', RouteExposurePolicy::denialReason('MasterSlave', 'setUpDemo'));
    }

    public function testActiveControllerDocumentationDoesNotReferenceRemovedMasterSlaveController(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'documentation/controller/README.md',
            'documentation/controller/controllers_master.md',
            'documentation/controller/controllers_documentation.html',
            'documentation/Controller~Library/README.md',
        ] as $path) {
            $content = (string) file_get_contents($root.'/'.$path);

            $this->assertStringNotContainsString('App/Controller/MasterSlave.php', $content, $path);
            $this->assertStringNotContainsString('documentation/Controller~Library/MasterSlave.md', $content, $path);
            $this->assertStringNotContainsString('href="MasterSlave.md"', $content, $path);
            $this->assertDoesNotMatchRegularExpression('/href="#masterslave(?:-\d+)?"/', $content, $path);
            $this->assertStringNotContainsString('[MasterSlave](MasterSlave.md)', $content, $path);
        }
    }

    /**
     * @return list<string>
     */
    private function publicMethods(string $path): array
    {
        $content = (string) file_get_contents(dirname(__DIR__, 2).'/'.$path);
        $tokens = token_get_all($content);
        $methods = [];

        for ($i = 0, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];

            if (!is_array($token) || $token[0] !== T_FUNCTION) {
                continue;
            }

            $visibility = null;
            for ($j = $i - 1; $j >= 0; $j--) {
                $candidate = $tokens[$j];

                if (is_string($candidate) && $candidate === '}') {
                    break;
                }

                if (!is_array($candidate) || in_array($candidate[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                    continue;
                }

                if (in_array($candidate[0], [T_PUBLIC, T_PROTECTED, T_PRIVATE], true)) {
                    $visibility = $candidate[0];
                }

                break;
            }

            if ($visibility === T_PROTECTED || $visibility === T_PRIVATE) {
                continue;
            }

            for ($k = $i + 1; $k < $count; $k++) {
                $candidate = $tokens[$k];

                if (is_array($candidate) && $candidate[0] === T_STRING) {
                    $methods[] = $candidate[1];
                    break;
                }
            }
        }

        sort($methods);

        return array_values(array_unique($methods));
    }
}
