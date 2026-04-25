<?php

declare(strict_types=1);

use App\Controller\ProxySQL;
use PHPUnit\Framework\TestCase;

final class ProxySqlUpdateRequestTest extends TestCase
{
    public function testUpdateAllowsOnlyPostForHttpRequests(): void
    {
        $this->assertFalse(ProxySQL::isUpdateRequestAllowed(false, 'GET'));
        $this->assertFalse(ProxySQL::isUpdateRequestAllowed(false, null));
        $this->assertTrue(ProxySQL::isUpdateRequestAllowed(false, 'POST'));
        $this->assertTrue(ProxySQL::isUpdateRequestAllowed(true, 'GET'));
    }

    public function testUpdateRedirectUsesRefererWhenPresent(): void
    {
        $this->assertSame(
            '/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/',
            ProxySQL::getUpdateRedirectTarget('5', 'MYSQL_USERS', '/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/', '/base/')
        );
    }

    public function testUpdateRedirectIgnoresExternalReferer(): void
    {
        $this->assertSame(
            '/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/',
            ProxySQL::getUpdateRedirectTarget('5', 'MYSQL_USERS', 'https://example.invalid/phish', '/pmacontrol/fr/', 'pmacontrol.local')
        );
    }

    public function testUpdateRedirectAcceptsSameHostReferer(): void
    {
        $this->assertSame(
            'https://pmacontrol.local/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/',
            ProxySQL::getUpdateRedirectTarget(
                '5',
                'MYSQL_USERS',
                'https://pmacontrol.local/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/',
                '/base/',
                'pmacontrol.local'
            )
        );
    }

    public function testUpdateRedirectFallsBackToConfigPage(): void
    {
        $this->assertSame(
            '/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/',
            ProxySQL::getUpdateRedirectTarget('5', 'MYSQL_USERS', null, '/pmacontrol/fr/')
        );
    }

    public function testConfigViewDoesNotExposeUpdateAsGetLink(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../App/view/ProxySQL/config.view.php');

        $this->assertStringNotContainsString('<a href="\'.LINK.\'ProxySQL/update/', $source);
        $this->assertStringContainsString('<form method="post"', $source);
        $this->assertStringContainsString("LINK.'ProxySQL/update/'", $source);
    }

    public function testUpdateUsesSeeOtherForPostRedirectGet(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../App/Controller/ProxySQL.php');

        $this->assertStringContainsString(', true, 303);', $source);
    }
}
