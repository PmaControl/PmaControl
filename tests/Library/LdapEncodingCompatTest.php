<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2).'/App/Library/LdapEncodingCompat.php';

final class LdapEncodingCompatTest extends TestCase
{
    public function testLatinOneUserAttributesAreConvertedToUtf8(): void
    {
        $entries = [
            'count' => 1,
            0 => [
                'givenname' => ['count' => 1, 0 => "S\xe9bastien"],
                'sn' => ['count' => 1, 0 => "D\xe9monts"],
                'mail' => ['count' => 1, 0 => 'sebastien@example.test'],
                'memberof' => ['count' => 1, 0 => "CN=Pr\xe9prod"],
                'dn' => "CN=S\xe9bastien",
            ],
        ];

        $normalized = \Glial\Auth\normalizeLdapEntriesEncoding($entries);

        $this->assertSame("S\xc3\xa9bastien", $normalized[0]['givenname'][0]);
        $this->assertSame("D\xc3\xa9monts", $normalized[0]['sn'][0]);
        $this->assertSame('sebastien@example.test', $normalized[0]['mail'][0]);
        $this->assertSame("CN=Pr\xe9prod", $normalized[0]['memberof'][0]);
        $this->assertSame("CN=S\xe9bastien", $normalized[0]['dn']);
    }

    public function testUtf8ValuesAreKeptAsIs(): void
    {
        $value = "S\xc3\xa9bastien";

        $this->assertSame($value, \Glial\Auth\normalizeLdapTextValue($value));
    }
}
