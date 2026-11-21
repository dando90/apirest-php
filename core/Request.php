<?php

namespace Core;

class Request
{
    public static function uri()
    {
        $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $basePath = rtrim(dirname($scriptName), '/');

        if ($basePath !== '' && ($uriPath === $basePath || strpos($uriPath, $basePath . '/') === 0)) {
            $uriPath = substr($uriPath, strlen($basePath));
        }

        return trim($uriPath, '/');
    }
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}
