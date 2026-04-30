<?php

namespace App\Controller;

use App\Library\Debug;
use App\Library\Security\CompareMainSelection;
use Glial\Synapse\Controller;

class CompareConfig extends Controller
{
    public function index($param): void
    {
        Debug::parseDebug($param);

        $indexRequest = self::evaluateIndexRequest($_GET, $_SERVER);
        if ($indexRequest['status'] !== 200) {
            $this->view = false;
            $this->layout_name = false;
            self::sendCompareConfigIndexError($indexRequest['status'], $indexRequest['body'], $indexRequest['headers']);
            return;
        }

        $route = CompareMainSelection::toRoute($indexRequest['selection']);

        $this->view = false;
        $this->layout_name = false;
        header('location: ' . LINK . 'compare/index' . ($route === '' ? '' : '/' . $route));
    }

    public static function evaluateIndexRequest(array $get, array $server): array
    {
        return CompareMainSelection::evaluate($get, $server);
    }

    private static function sendCompareConfigIndexError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }

        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }
}
