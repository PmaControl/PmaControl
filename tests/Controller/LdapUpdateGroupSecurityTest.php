<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LdapUpdateGroupSecurityTest extends TestCase
{
    public function testUpdateGroupEscapesLoginBeforeLdapSearch(): void
    {
        $method = $this->extractUpdateGroupMethod();

        $this->assertStringContainsString('$login = isset($ob->login) ? (string) $ob->login : \'\';', $method);
        $this->assertStringContainsString('if ($login === \'\')', $method);
        $this->assertStringContainsString('$escapedLogin = self::escapeLdapFilterValue($login);', $method);
        $this->assertStringContainsString('"(samaccountname={$escapedLogin})"', $method);
        $this->assertStringContainsString('if ($results === false)', $method);
        $this->assertStringContainsString('!isset($entries[0][\'memberof\'])', $method);
    }

    public function testUpdateGroupDoesNotConcatenateRawLoginIntoSamaccountnameFilter(): void
    {
        $method = $this->extractUpdateGroupMethod();

        $this->assertDoesNotMatchRegularExpression(
            '/samaccountname[^\\n]*\\.\\s*\\$ob->login/',
            $method
        );
        $this->assertDoesNotMatchRegularExpression(
            '/samaccountname[^\\n]*\\.\\s*\\$login/',
            $method
        );
    }

    public function testAllDynamicSamaccountnameFiltersUseEscapedVariables(): void
    {
        $controller = $this->readController();

        preg_match_all('/ldap_search\\([^;]+samaccountname[^;]+;/s', $controller, $matches);

        $this->assertNotEmpty($matches[0]);
        foreach ($matches[0] as $ldapSearch) {
            $this->assertMatchesRegularExpression(
                '/\\$escaped(?:Command|Login)\\b/',
                $ldapSearch,
                $ldapSearch
            );
            $this->assertDoesNotMatchRegularExpression(
                '/samaccountname[^;]*\\.\\s*\\$(?:ob->login|login|command)\\b/',
                $ldapSearch,
                $ldapSearch
            );
        }
    }

    private function extractUpdateGroupMethod(): string
    {
        $controller = $this->readController();
        $start = strpos($controller, 'private function update_group($id_group)');
        $end = strpos($controller, 'public function change()', $start);

        $this->assertIsInt($start);
        $this->assertIsInt($end);

        return substr($controller, $start, $end - $start);
    }

    private function readController(): string
    {
        return (string) file_get_contents(__DIR__ . '/../../App/Controller/Ldap.php');
    }
}
