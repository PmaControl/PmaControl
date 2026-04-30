<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SecretDebugLeakTest extends TestCase
{
    public function testDebugOutputsUseCentralSecretRedactor(): void
    {
        $debug = file_get_contents(__DIR__ . '/../../App/Library/Debug.php');
        $basic = file_get_contents(__DIR__ . '/../../App/Webroot/Basic.php');

        $this->assertIsString($debug);
        $this->assertIsString($basic);
        $this->assertStringContainsString('use App\\Library\\Security\\SecretRedactor;', $debug);
        $this->assertStringContainsString('SecretRedactor::debugValue($string', $debug);
        $this->assertStringContainsString('SecretRedactor::text($sql)', $debug);
        $this->assertStringContainsString('use App\\Library\\Security\\SecretRedactor;', $basic);
        $this->assertStringContainsString('SecretRedactor::debugValue($var)', $basic);
    }

    public function testTicket507ControllerLeaksAreRemoved(): void
    {
        $ssh = file_get_contents(__DIR__ . '/../../App/Controller/Ssh.php');
        $deployRsaKey = file_get_contents(__DIR__ . '/../../App/Controller/DeployRsaKey.php');
        $demo = file_get_contents(__DIR__ . '/../../App/Controller/Demo.php');
        $mysql = file_get_contents(__DIR__ . '/../../App/Controller/Mysql.php');
        $aspirateur = file_get_contents(__DIR__ . '/../../App/Controller/Aspirateur.php');
        $user = file_get_contents(__DIR__ . '/../../App/Controller/User.php');

        $this->assertIsString($ssh);
        $this->assertIsString($deployRsaKey);
        $this->assertIsString($demo);
        $this->assertIsString($mysql);
        $this->assertIsString($aspirateur);
        $this->assertIsString($user);

        $this->assertStringNotContainsString('Debug::debug(Chiffrement::decrypt($key[\'private_key\'])', $ssh);
        $this->assertStringNotContainsString('Debug::debug($rsa)', $ssh);
        $this->assertStringNotContainsString('debug($data);', $ssh);
        $this->assertStringContainsString("Debug::debug('ssh key save failed', 'Ssh::save')", $ssh);

        $this->assertStringNotContainsString('Debug::debug($path_private_key)', $deployRsaKey);
        $this->assertStringNotContainsString('Debug::debug($priv_key)', $deployRsaKey);
        $this->assertStringNotContainsString('Debug::debug($param)', $deployRsaKey);
        $this->assertStringNotContainsString('Debug::debug(shell_exec("cat " . $file_name_pub_key))', $deployRsaKey);
        $this->assertStringNotContainsString('Debug::debug(Ssh::$ssh->exec("cat " . $dest_path))', $deployRsaKey);
        $this->assertStringNotContainsString('Debug::debug($data)', $deployRsaKey);

        $this->assertFileDoesNotExist(__DIR__ . '/../../App/Controller/MasterSlave.php');

        $this->assertStringNotContainsString('-p$mysql_password2', $demo);
        $this->assertStringNotContainsString('-p$password_slave', $demo);
        $this->assertStringContainsString('-p[redacted]', $demo);

        $this->assertStringNotContainsString('function uncrypt()', $mysql);
        $this->assertStringNotContainsString('debug(Crypt::decrypt($ob->passwd))', $mysql);

        $this->assertStringNotContainsString('Debug::debug($ob, "password")', $aspirateur);
        $this->assertStringContainsString('Debug::debug($user, "mysql.user")', $aspirateur);
        $this->assertStringNotContainsString('Debug::sql($sql4)', $aspirateur);
        $this->assertStringContainsString('Debug::sql(str_replace("\'".$password_hash_sql."\'", "\'[redacted]\'", $sql4))', $aspirateur);

        $this->assertStringNotContainsString('debug($password_non_hash)', $user);
    }

    public function testMydumperUsesSharedCliPasswordRedactor(): void
    {
        $mydumper = file_get_contents(__DIR__ . '/../../App/Library/Mydumper.php');

        $this->assertIsString($mydumper);
        $this->assertStringContainsString('use App\\Library\\Security\\SecretRedactor;', $mydumper);
        $this->assertStringContainsString('SecretRedactor::commandLinePasswords($log)', $mydumper);
    }
}
