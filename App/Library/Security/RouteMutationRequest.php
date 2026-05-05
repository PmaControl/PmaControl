<?php

declare(strict_types=1);

namespace App\Library\Security;

final class RouteMutationRequest
{
    public static function evaluate(
        array $post,
        array $server,
        array $session,
        array $param,
        string $scope,
        string $invalidIdMessage,
        string $idField = 'id_server'
    ): array {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, $scope)) {
            return self::outcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $id = self::normalizeRequestedId($post, $param, $idField);
        if ($id === null) {
            return self::outcome(400, $invalidIdMessage);
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'id' => $id,
        ];
    }

    public static function normalizeRequestedId(array $post, array $param, string $idField = 'id_server'): ?int
    {
        $routeHasId = array_key_exists(0, $param) && $param[0] !== null && $param[0] !== '';
        $routeId = $routeHasId ? PositiveIntegerSelection::normalizeSingle($param[0]) : null;
        if ($routeHasId && $routeId === null) {
            return null;
        }

        $postHasId = array_key_exists($idField, $post) && $post[$idField] !== null && $post[$idField] !== '';
        $postId = $postHasId ? PositiveIntegerSelection::normalizeSingle($post[$idField]) : null;
        if ($postHasId && $postId === null) {
            return null;
        }

        if ($routeId !== null && $postId !== null && $routeId !== $postId) {
            return null;
        }

        return $postId ?? $routeId;
    }

    private static function outcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'id' => null,
        ];
    }
}
