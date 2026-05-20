<?php

declare(strict_types=1);

use App\Library\Security\InlineEditRequest;
use PHPUnit\Framework\TestCase;

final class InlineEditRequestTest extends TestCase
{
    public function testNormalizeAcceptsValidInlineEditPayload(): void
    {
        $this->assertSame(
            ['field' => 'name', 'value' => 'Primary', 'id' => 7],
            InlineEditRequest::normalize(['name' => 'name', 'value' => 'Primary', 'pk' => '7'], ['name'])
        );
    }

    public function testNormalizeRejectsMalformedPayload(): void
    {
        $this->assertNull(InlineEditRequest::normalize(['name' => 'name', 'value' => 'Primary'], ['name']));
        $this->assertNull(InlineEditRequest::normalize(['name' => ['name'], 'value' => 'Primary', 'pk' => '7'], ['name']));
        $this->assertNull(InlineEditRequest::normalize(['name' => 'id', 'value' => '7', 'pk' => '7'], ['name']));
        $this->assertNull(InlineEditRequest::normalize(['name' => 'name', 'value' => 'Primary', 'pk' => '0'], ['name']));
        $this->assertNull(InlineEditRequest::normalize(['name' => 'name', 'value' => 'Primary', 'pk' => '7 OR 1=1'], ['name']));
    }

    public function testNormalizeRejectsIntegerOverflowId(): void
    {
        $this->assertSame(
            ['field' => 'name', 'value' => 'Primary', 'id' => PHP_INT_MAX],
            InlineEditRequest::normalize(
                ['name' => 'name', 'value' => 'Primary', 'pk' => (string) PHP_INT_MAX],
                ['name']
            )
        );

        $this->assertNull(
            InlineEditRequest::normalize(
                ['name' => 'name', 'value' => 'Primary', 'pk' => (string) PHP_INT_MAX . '0'],
                ['name']
            )
        );

        $sameLengthOverflow = substr((string) PHP_INT_MAX, 0, -1) . ((int) substr((string) PHP_INT_MAX, -1) + 1);
        $this->assertNull(
            InlineEditRequest::normalize(
                ['name' => 'name', 'value' => 'Primary', 'pk' => $sameLengthOverflow],
                ['name']
            )
        );
    }

    public function testNormalizeAppliesValueLengthLimit(): void
    {
        $this->assertSame(
            ['field' => 'name', 'value' => 'abcd', 'id' => 7],
            InlineEditRequest::normalize(['name' => 'name', 'value' => 'abcd', 'pk' => '7'], ['name'], 4)
        );
        $this->assertNull(
            InlineEditRequest::normalize(['name' => 'name', 'value' => 'abcde', 'pk' => '7'], ['name'], 4)
        );
    }

    public function testNormalizePreservesValueWhitespace(): void
    {
        $this->assertSame(
            ['field' => 'name', 'value' => ' Primary ', 'id' => 7],
            InlineEditRequest::normalize(['name' => 'name', 'value' => ' Primary ', 'pk' => '7'], ['name'])
        );
    }
}
