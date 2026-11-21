<?php
class Connection
{
    public static function make($config)
    {
        try{
            return new PDO(
                $config['connection'].';dbname='.$config['name'],
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (\Throwable $e) {
            error_log('[REST API] Database connection failed: ' . $e->getMessage());
            http_response_code(503);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'message' => 'Service Unavailable: impossible to process the request.'
            ]);
            exit;
        }
    }
}
