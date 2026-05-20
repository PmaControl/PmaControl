<?php

declare(strict_types=1);

namespace App\Library\Security;

final class ControllerActionSelection
{
    public static function normalize($rawAction, array $allowedActions, string $defaultAction): string
    {
        if (!in_array($defaultAction, $allowedActions, true)) {
            throw new \InvalidArgumentException('Default action must be part of the allowed actions');
        }

        if (!is_scalar($rawAction)) {
            return $defaultAction;
        }

        $action = trim((string) $rawAction);
        if ($action === '') {
            return $defaultAction;
        }

        return in_array($action, $allowedActions, true) ? $action : $defaultAction;
    }
}
