<?php

declare(strict_types=1);

use App\Library\Security\PayloadValidator;
use PHPUnit\Framework\TestCase;

final class PayloadValidatorTest extends TestCase
{
    public function testValidateAcceptsTypedSchemaAndKeepsSchemaOrder(): void
    {
        $payload = PayloadValidator::validate(
            [
                'csrf_token' => 'ignored',
                'enabled' => 'on',
                'ratio' => '1.5',
                'sql' => ' SELECT 1 ',
                'count' => '7',
            ],
            [
                'sql' => ['type' => 'string', 'max' => 20],
                'count' => ['type' => 'int', 'min' => 1, 'max' => 10],
                'ratio' => ['type' => 'float', 'min' => 1, 'max' => 2],
                'enabled' => ['type' => 'bool'],
                'mode' => ['type' => 'string', 'default' => 'read', 'values' => ['read', 'write']],
            ]
        );

        $this->assertSame(
            [
                'sql' => ' SELECT 1 ',
                'count' => 7,
                'ratio' => 1.5,
                'enabled' => true,
                'mode' => 'read',
            ],
            $payload
        );
    }

    public function testValidateRejectsMissingNonScalarAndInvalidValues(): void
    {
        $schema = [
            'name' => ['type' => 'string', 'pattern' => '/^[a-z]+$/', 'values' => ['alpha', 'beta']],
            'count' => ['type' => 'int', 'min' => 1, 'max' => 9],
        ];

        $this->assertNull(PayloadValidator::validate(['count' => '1'], $schema));
        $this->assertNull(PayloadValidator::validate(['name' => ['alpha'], 'count' => '1'], $schema));
        $this->assertNull(PayloadValidator::validate(['name' => 'gamma', 'count' => '1'], $schema));
        $this->assertNull(PayloadValidator::validate(['name' => 'alpha', 'count' => '0'], $schema));
        $this->assertNull(PayloadValidator::validate(['name' => 'alpha', 'count' => '10'], $schema));
        $this->assertNull(PayloadValidator::validate(['name' => 'alpha', 'count' => '2 OR 1=1'], $schema));
    }

    public function testValidateLeavesStringWhitespaceUnlessTrimIsEnabled(): void
    {
        $this->assertSame(
            ['name' => ' Bob '],
            PayloadValidator::validate(['name' => ' Bob '], ['name' => ['type' => 'string']])
        );
        $this->assertSame(
            ['name' => 'Bob'],
            PayloadValidator::validate(['name' => ' Bob '], ['name' => ['type' => 'string', 'trim' => true]])
        );
    }

    public function testNormalizeValueKeepsGroupedFormTrimAndListBehavior(): void
    {
        $this->assertSame('db1', PayloadValidator::normalizeValue(' db1 ', ['type' => 'string']));
        $this->assertSame(22, PayloadValidator::normalizeValue('', ['type' => 'int', 'default' => 22]));
        $this->assertSame(
            ['db1', 'db-2'],
            PayloadValidator::normalizeValue(
                [' db1 ', 'db-2'],
                [
                    'type' => 'list',
                    'min_items' => 1,
                    'max_items' => 3,
                    'item_type' => 'string',
                    'item_pattern' => '/^[A-Za-z0-9_-]{1,64}$/',
                ]
            )
        );

        $this->assertNull(PayloadValidator::normalizeValue(['db1', 'db1'], ['type' => 'list']));
        $this->assertNull(PayloadValidator::normalizeValue(['db 1'], ['type' => 'list', 'item_pattern' => '/^[A-Za-z0-9_-]+$/']));
    }

    public function testValidateSupportsOptionalFields(): void
    {
        $this->assertSame(
            ['required' => 'yes'],
            PayloadValidator::validate(
                ['required' => 'yes'],
                [
                    'required' => 'string',
                    'optional' => ['type' => 'string', 'required' => false],
                ]
            )
        );
    }
}
