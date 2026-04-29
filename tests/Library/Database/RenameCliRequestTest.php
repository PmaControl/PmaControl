<?php

declare(strict_types=1);

namespace Tests\Library\Database;

use App\Library\Database\RenameCliRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RenameCliRequestTest extends TestCase
{
    public function testServerModeSupportsOptions(): void
    {
        $request = RenameCliRequest::fromArgv([
            'rename_database.php',
            '--server-id=12',
            '--old=old_db',
            '--new=new_db',
            '--adjust-privileges',
            '--force',
        ]);

        $this->assertSame('server', $request['mode']);
        $this->assertSame('12', $request['server_id']);
        $this->assertSame('old_db', $request['old']);
        $this->assertSame('new_db', $request['new']);
        $this->assertTrue($request['adjust_privileges']);
        $this->assertTrue($request['force']);
    }

    public function testServerModeSupportsPositionals(): void
    {
        $request = RenameCliRequest::fromArgv([
            'rename_database.php',
            'server_01',
            'old_db',
            'new_db',
        ]);

        $this->assertSame('server', $request['mode']);
        $this->assertSame('server_01', $request['server_id']);
    }

    public function testDirectModeSupportsOptionsAndMysqlPwd(): void
    {
        $request = RenameCliRequest::fromArgv([
            'rename_database.php',
            '--host=127.0.0.1:3307',
            '--user=root',
            '--old=old_db',
            '--new=new_db',
            '--dry-run',
        ], ['MYSQL_PWD' => 'secret']);

        $this->assertSame('direct', $request['mode']);
        $this->assertSame('127.0.0.1', $request['host']);
        $this->assertSame(3307, $request['port']);
        $this->assertSame('root', $request['user']);
        $this->assertSame('secret', $request['password']);
        $this->assertTrue($request['dry_run']);
    }

    public function testDirectModeSupportsPositionalsWithMysqlPwd(): void
    {
        $request = RenameCliRequest::fromArgv([
            'rename_database.php',
            '[::1]:3306',
            'root',
            'old_db',
            'new_db',
        ], ['MYSQL_PWD' => 'secret']);

        $this->assertSame('direct', $request['mode']);
        $this->assertSame('::1', $request['host']);
        $this->assertSame(3306, $request['port']);
        $this->assertSame('secret', $request['password']);
    }

    public function testPortOptionOverridesHostPort(): void
    {
        $request = RenameCliRequest::fromArgv([
            'rename_database.php',
            '--host=127.0.0.1:3306',
            '--port=3308',
            '--user=root',
            '--old=old_db',
            '--new=new_db',
        ], ['MYSQL_PWD' => 'secret']);

        $this->assertSame(3308, $request['port']);
    }

    public function testRejectsInvalidDatabaseName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RenameCliRequest::fromArgv([
            'rename_database.php',
            '--server-id=12',
            '--old=old`db',
            '--new=new_db',
        ]);
    }

    public function testRejectsInvalidPort(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RenameCliRequest::fromArgv([
            'rename_database.php',
            '--host=127.0.0.1',
            '--port=70000',
            '--user=root',
            '--old=old_db',
            '--new=new_db',
        ], ['MYSQL_PWD' => 'secret']);
    }

    public function testRejectsUnknownOption(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RenameCliRequest::fromArgv([
            'rename_database.php',
            '--server-id=12',
            '--old=old_db',
            '--new=new_db',
            '--password-file=/tmp/secret',
        ]);
    }
}
