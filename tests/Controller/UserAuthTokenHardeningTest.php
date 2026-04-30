<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UserAuthTokenHardeningTest extends TestCase
{
    public function testUserControllerUsesCryptographicHashedTokens(): void
    {
        $source = file_get_contents(__DIR__ . '/../../App/Controller/User.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('sha1(uniqid())', $source);
        $this->assertStringContainsString('AuthToken::generateToken()', $source);
        $this->assertStringContainsString('AuthToken::hashToken($confirmationToken)', $source);
        $this->assertStringContainsString('AuthToken::hashToken($resetToken)', $source);
        $this->assertStringContainsString('AuthToken::verifyToken(', $source);
        $this->assertStringContainsString('confirm_token_hash', $source);
        $this->assertStringContainsString('reset_token_hash', $source);
    }

    public function testUserControllerDoesNotBuildTokenLinksOverHttpServerName(): void
    {
        $source = file_get_contents(__DIR__ . '/../../App/Controller/User.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString("'http://' . \$_SERVER['SERVER_NAME']", $source);
        $this->assertStringNotContainsString("\"http://\" . \$_SERVER['SERVER_NAME']", $source);
        $this->assertStringContainsString('AuthToken::buildHttpsUrl(', $source);
        $this->assertStringContainsString("LINK . 'user/password_recover/'", $source);
    }

    public function testPasswordRecoverDoesNotPersistPlainPasswordBeforeHashing(): void
    {
        $source = file_get_contents(__DIR__ . '/../../App/Controller/User.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('$recover[\'user_main\'][\'password\'] = $outcome[\'password\'];', $source);
        $this->assertStringContainsString('$tmp[\'user_main\'][\'password\'] = $this->di[\'auth\']->hash_password', $source);
    }

    public function testConfirmationNoLongerPromotesGroupOnTokenConsumption(): void
    {
        $source = file_get_contents(__DIR__ . '/../../App/Controller/User.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString("UPDATE user_main SET is_valid = 1, key_auth ='',id_group=2", $source);
        $this->assertStringContainsString('confirm_token_hash = NULL', $source);
        $this->assertStringContainsString('confirm_token_expires_at = NULL', $source);
    }

    public function testSchemaAddsDedicatedConfirmationAndResetTokenColumns(): void
    {
        $migration = file_get_contents(__DIR__ . '/../../sql/incremental_v2/20260430_user_auth_tokens.sql');
        $fullSchema = file_get_contents(__DIR__ . '/../../sql/full/pmacontrol.sql');

        $this->assertIsString($migration);
        $this->assertIsString($fullSchema);
        foreach (['confirm_token_hash', 'confirm_token_expires_at', 'reset_token_hash', 'reset_token_expires_at'] as $column) {
            $this->assertStringContainsString($column, $migration);
            $this->assertStringContainsString($column, $fullSchema);
        }

        $this->assertStringContainsString('idx_user_main_confirm_token_expires_at', $migration);
        $this->assertStringContainsString('idx_user_main_reset_token_expires_at', $migration);
    }
}
