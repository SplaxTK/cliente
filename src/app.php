<?php
require __DIR__ . '/../vendor/autoload.php';
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$usuarios = [
        ["id"=> 1, 
        "nome"=> "John Doe",
        "login"=> "johndoe",
        "email"=> "john.doe@example.com", 
        "senha"=> "123456",
        ],

        ["id"=> 2, 
        "nome"=> "Jane Smith", 
        "login"=> "janesmith",
        "email"=> "jane.smith@example.com", 
        "senha"=> "654321"
        ],

        ["id"=> 3, 
        "nome"=> "Alice Johnson", 
        "login"=> "alicejohnson",
        "email"=> "alice.johnson@example.com", 
        "senha"=> "987654"
        ],

        ["id"=> 4,
        "nome"=> "Bob Brown",
        "login"=> "bobbrown",
        "email"=> "bob.brown@example.com",
        "senha"=> "555555"
        ],

        ["id"=> 5,
        "nome"=> "Charlie Davis",
        "login"=> "charliedavis",
        "email"=> "charlie.davis@example.com",
        "senha"=> "111111"
        ]
];

// status
$app->get('/status', function ($request, $response) {
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

//GET
$app->get('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
$usuario = current(array_filter($usuarios, fn ($item) => $item['id'] === (int) $args['id'])) ?: null;
if (!$usuario) {
$response->getBody()->write(json_encode(['erro' => 'Usuário não encontrado']));
return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
}
$response->getBody()->write(json_encode($usuario));
return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});
 
$app->get('/usuarios', function ($request, $response) use (&$usuarios) {
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;
 
    if($nome) {
        $usuariosFiltrados = array_filter($usuarios, fn($item) => str_contains(mb_strtolower($item['nome']), mb_strtolower($nome)));
    } else {
        $usuariosFiltrados = $usuarios;
    }

    $response->getBody()->write(json_encode(array_values($usuariosFiltrados)));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

//POST
$app->post('/usuarios', function ($request, $response) use (&$usuarios) {
    $dados = $request->getParsedBody();
    $novoUsuario = ['id' => count($usuarios) + 1, 'nome' => $dados['nome'], 'email' => $dados['email'], 'idade' => $dados['idade'], 'login' => $dados['login'], 'senha' => $dados['senha']];
    $usuarios[] = $novoUsuario;
    $response->getBody()->write(json_encode($novoUsuario));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

//PUT
$app->put('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    $usuario = current(array_filter($usuarios, fn ($item) => $item['id'] === (int) $args['id'])) ?: null;
    if (!$usuario) {
        $response->getBody()->write(json_encode(['erro' => 'Usuário não encontrado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $dados = $request->getParsedBody();
    $usuario['nome'] = $dados['nome'];
    $usuario['email'] = $dados['email'];
    $usuario['login'] = $dados['login'];
    $usuario['senha'] = $dados['senha'];
    $response->getBody()->write(json_encode($usuario));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->put('/usuarios/{id}/senha', function ($request, $response, $args) use (&$usuarios) {
    $usuario = current(array_filter($usuarios, fn ($item) => $item['id'] === (int) $args['id'])) ?: null;
    if (!$usuario) {
        $response->getBody()->write(json_encode(['erro' => 'Usuário não encontrado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $dados = $request->getParsedBody();
    $usuario['senha'] = $dados['senha'];
    $response->getBody()->write(json_encode($usuario));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

//DELETE
$app->delete('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    $usuario = current(array_filter($usuarios, fn ($item) => $item['id'] === (int) $args['id'])) ?: null;
    if (!$usuario) {
        $response->getBody()->write(json_encode(['erro' => 'Usuário não encontrado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $usuarios = array_filter($usuarios, fn ($item) => $item['id'] !== (int) $args['id']);
    $response->getBody()->write(json_encode(['mensagem' => 'Usuário removido com sucesso']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->run();