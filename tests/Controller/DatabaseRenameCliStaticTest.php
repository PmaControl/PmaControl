<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DatabaseRenameCliStaticTest extends TestCase
{
    public function testMoveDelegatesToSharedRenamer(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Database.php');

        $this->assertStringContainsString('use App\\Library\\Database\\Renamer;', $controller);
        $this->assertStringContainsString('Renamer::rename($db2, $OLD_DB, $NEW_DB', $controller);
        $this->assertStringContainsString('$serverRef = $db->sql_real_escape_string((string) $id_mysql_server);', $controller);
        $this->assertStringContainsString('if ($AP === "--force")', $controller);
    }

    public function testRenameHttpEndpointKeepsCsrfOnlyShape(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Database.php');
        $renameStart = strpos($controller, 'public function rename($param)');
        $moveStart = strpos($controller, 'public function move($param)');

        $this->assertNotFalse($renameStart);
        $this->assertNotFalse($moveStart);

        $renameBody = substr($controller, $renameStart, $moveStart - $renameStart);
        $this->assertStringContainsString('evaluateRenameRequest($_POST, $_SERVER, $_SESSION)', $renameBody);
        $this->assertStringNotContainsString('RenameCliRequest', $renameBody);
        $this->assertStringNotContainsString('--password', $renameBody);
    }

    public function testCliScriptUsesSharedParserAndRenamer(): void
    {
        $script = (string) file_get_contents(__DIR__ . '/../../bin/rename_database.php');

        $this->assertStringContainsString('RenameCliRequest::fromArgv', $script);
        $this->assertStringContainsString('Renamer::rename(', $script);
        $this->assertStringContainsString("'password' => ".'$request'."['password']", $script);
        $this->assertStringNotContainsString('$controller->move($request', $script);
    }

    public function testCliHelpDoesNotNeedBootstrap(): void
    {
        $command = PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/../../bin/rename_database.php') . ' --help 2>&1';
        exec($command, $lines, $exitCode);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Usage:', implode("\n", $lines));
    }
}
