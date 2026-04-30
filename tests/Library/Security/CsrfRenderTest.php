<?php

declare(strict_types=1);

use App\Library\Security\CsrfRender;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class CsrfRenderTest extends TestCase
{
    public function testFieldsEscapesScopedValues(): void
    {
        $fields = CsrfRender::fields([
            'database_create_csrf_field' => 'csrf"name',
            'database_create_csrf_token' => "tok'en&raw",
        ], 'database_create');

        $this->assertSame('csrf&quot;name', $fields['field']);
        $this->assertSame('tok&#039;en&amp;raw', $fields['token']);
    }

    public function testFieldsUseDefaultFieldAndEmptyTokenWhenMissing(): void
    {
        $fields = CsrfRender::fields([], 'missing_scope');

        $this->assertSame(Csrf::DEFAULT_FIELD, $fields['field']);
        $this->assertSame('', $fields['token']);
    }

    public function testHiddenInputUsesEscapedValues(): void
    {
        $input = CsrfRender::hiddenInput([
            'worker_kill_csrf_field' => '_csrf_token',
            'worker_kill_csrf_token' => 'abc"123',
        ], 'worker_kill');

        $this->assertSame(
            '<input type="hidden" name="_csrf_token" value="abc&quot;123">',
            $input
        );
    }

    public function testAttributesUseLeadingSpaceForInlineRendering(): void
    {
        $attributes = CsrfRender::attributes([
            'tree_update_csrf_field' => '_csrf_token',
            'tree_update_csrf_token' => 'token<value>',
        ], 'tree_update');

        $this->assertSame(
            ' data-csrf-field="_csrf_token" data-csrf-token="token&lt;value&gt;"',
            $attributes
        );
    }

    public function testFieldAndTokenAccessors(): void
    {
        $data = [
            'client_update_csrf_field' => '_custom_csrf',
            'client_update_csrf_token' => 'token',
        ];

        $this->assertSame('_custom_csrf', CsrfRender::field($data, 'client_update'));
        $this->assertSame('token', CsrfRender::token($data, 'client_update'));
    }

    public function testInvalidUtf8DoesNotBlankTheOutput(): void
    {
        $fields = CsrfRender::fields([
            'invalid_csrf_field' => "_csrf_token\xC3(",
            'invalid_csrf_token' => "tok\xC3(en",
        ], 'invalid');

        $this->assertNotSame('', $fields['field']);
        $this->assertNotSame('', $fields['token']);
        $this->assertStringContainsString('(', $fields['field']);
        $this->assertStringContainsString('(', $fields['token']);
    }

    public function testAlreadyEscapedEntitiesAreNotDoubleEncoded(): void
    {
        $fields = CsrfRender::fields([
            'escaped_csrf_field' => '_csrf_token',
            'escaped_csrf_token' => 'already&amp;escaped',
        ], 'escaped');

        $this->assertSame('already&amp;escaped', $fields['token']);
    }
}
