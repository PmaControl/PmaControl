<?php

declare(strict_types=1);

namespace App\Library\Security;

use Glial\Security\Csrf;

final class CsrfRender
{
    public static function fields(array $data, string $scope, string $defaultField = Csrf::DEFAULT_FIELD): array
    {
        return [
            'field' => self::escape($data[$scope.'_csrf_field'] ?? $defaultField),
            'token' => self::escape($data[$scope.'_csrf_token'] ?? ''),
        ];
    }

    public static function field(array $data, string $scope, string $defaultField = Csrf::DEFAULT_FIELD): string
    {
        return self::fields($data, $scope, $defaultField)['field'];
    }

    public static function token(array $data, string $scope): string
    {
        return self::fields($data, $scope)['token'];
    }

    public static function hiddenInput(array $data, string $scope, string $defaultField = Csrf::DEFAULT_FIELD): string
    {
        $csrf = self::fields($data, $scope, $defaultField);

        return '<input type="hidden" name="'.$csrf['field'].'" value="'.$csrf['token'].'">';
    }

    public static function attributes(array $data, string $scope, string $defaultField = Csrf::DEFAULT_FIELD): string
    {
        $csrf = self::fields($data, $scope, $defaultField);

        return ' data-csrf-field="'.$csrf['field'].'" data-csrf-token="'.$csrf['token'].'"';
    }

    private static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }
}
