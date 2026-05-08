<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class AuditFindingTest extends TestCase
{
    public function testRequiredFieldsAreEnforced(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Finding(Finding::SEVERITY_INFO, '', 'check.id', 'title');
    }

    public function testUnknownSeverityIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Finding('emergency', 'topology', 'check.id', 'title');
    }

    public function testToArrayPreservesEverySerializableField(): void
    {
        $f = new Finding(
            Finding::SEVERITY_CRITICAL,
            'hostgroup',
            'empty-default-hostgroup',
            'pmacontrol routed to hostgroup with 0 servers',
            "SELECT … HAVING servers = 0;",
            'UPDATE mysql_users SET default_hostgroup=...; LOAD MYSQL USERS TO RUNTIME;',
            '#148'
        );

        $this->assertSame([
            'severity' => 'critical',
            'category' => 'hostgroup',
            'check_id' => 'empty-default-hostgroup',
            'title'    => 'pmacontrol routed to hostgroup with 0 servers',
            'evidence' => "SELECT … HAVING servers = 0;",
            'fix'      => 'UPDATE mysql_users SET default_hostgroup=...; LOAD MYSQL USERS TO RUNTIME;',
            'ticket'   => '#148',
        ], $f->toArray());
    }

    public function testFromArrayRoundTripsThroughToArray(): void
    {
        $original = new Finding(
            Finding::SEVERITY_WARNING,
            'cluster',
            'asymmetric-peering',
            'self missing from peer list',
            '',
            '',
            null
        );

        $rebuilt = Finding::fromArray($original->toArray());

        $this->assertSame($original->toArray(), $rebuilt->toArray());
        $this->assertNull($rebuilt->ticket);
    }

    public function testFromArrayKeepsEmptyTicketStringAsNull(): void
    {
        $rebuilt = Finding::fromArray([
            'severity' => 'info',
            'category' => 'meta',
            'check_id' => 'x',
            'title'    => 't',
            'ticket'   => '',
        ]);
        $this->assertNull($rebuilt->ticket);
    }
}
