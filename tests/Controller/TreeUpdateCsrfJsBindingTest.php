<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #790: the CSRF token attached to .line-edit cells in
 * App/view/Tree/index.view.php is read by App/Webroot/js/Tree/index.js
 * inside the bootstrap-editable `params` callback. In that callback,
 * `this` is the Editable widget instance — NOT the DOM element — so
 * `$(this).data('csrf-token')` returned undefined and the AJAX POST to
 * /tree/update never carried a token, producing 403 "Invalid CSRF token"
 * on every save.
 *
 * The fix reads from the closure-captured `$element` variable instead.
 * This test pins that contract so the regression doesn't come back.
 */
final class TreeUpdateCsrfJsBindingTest extends TestCase
{
    public function testParamsCallbackReadsCsrfFromClosureCapturedElement(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 2).'/App/Webroot/js/Tree/index.js');

        $this->assertNotSame('', $js, 'Tree/index.js must exist.');

        // The closure-captured variable is the canonical source — match it.
        $this->assertMatchesRegularExpression(
            '/var\s+csrfToken\s*=\s*\$element\.data\(\s*[\'"]csrf-token[\'"]\s*\)/',
            $js,
            'csrfToken must be read from $element (closure), not from $(this) — see issue #790.'
        );
        $this->assertMatchesRegularExpression(
            '/var\s+csrfField\s*=\s*\$element\.data\(\s*[\'"]csrf-field[\'"]\s*\)/',
            $js,
            'csrfField must also be read from $element so the field name follows the token.'
        );

        // And the buggy form must not creep back in. `$(this).data('csrf-...')`
        // inside the editable params callback would re-introduce #790.
        $this->assertDoesNotMatchRegularExpression(
            '/\$\(\s*this\s*\)\.data\(\s*[\'"]csrf-(?:token|field)[\'"]\s*\)/',
            $js,
            'Reading the CSRF data attributes via $(this) breaks the params callback (this = widget instance, not the DOM element).'
        );
    }
}
