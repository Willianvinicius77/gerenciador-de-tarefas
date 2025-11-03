<?php
    declare(strict_types=1);
    // Ponto de entrada - roteamento simples, CORS e inclusão do controller
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    require_once __DIR__ . '/../src/Database.php';
    require_once __DIR__ . '/../src/Controller.php';

    // Inicializa o banco (cria tabela se não existir)
    $db = Database::getInstance();

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    // Remover prefixo se o script for servido em subpasta (ajuste conforme necessário)
    $base = '/';
    $path = '/' . trim(substr($uri, strlen($base)), '/');
    if ($path === '//') $path = '/';

    // Roteamento simples
    if ($path === '/' && $method === 'GET') {
        Controller::test();
    } elseif ($path === '/openapi.json' && $method === 'GET') {
        // Serve o arquivo openapi.json
        $spec = file_get_contents(__DIR__ . '/../openapi.json');
        header('Content-Type: application/json');
        echo $spec;
    } elseif ($path === '/tarefas' && $method === 'GET') {
        Controller::list();
    } elseif ($path === '/tarefas' && $method === 'POST') {
        Controller::create();
    } elseif (preg_match('#^/tarefas/([0-9]+)$#', $path, $matches)) {
        $id = (int)$matches[1];
        if ($method === 'PUT') {
            Controller::update($id);
        } elseif ($method === 'DELETE') {
            Controller::delete($id);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }