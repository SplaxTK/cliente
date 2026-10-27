<?php
// tests/UsuariosTest.php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class UsuariosTest extends TestCase
{
    private function criarApp()
    {
        return require __DIR__ . '/../src/app.php';
    }

    private function criarRequest($metodo, $uri, $dados = null)
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest($metodo, $uri);

        if ($dados !== null) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withParsedBody($dados);
        }

        return $request;
    }

    public function testGetStatus(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('GET', '/status');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('ok', $corpo['status']);
    }

    public function testGetListaUsuarios(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('GET', '/usuarios');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertCount(5, $corpo);
        $this->assertEquals('John Doe', $corpo[0]['nome']);
    }

    public function testGetUsuarioPorId(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('GET', '/usuarios/2');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(2, $corpo['id']);
        $this->assertEquals('Jane Smith', $corpo['nome']);
        $this->assertEquals('janesmith', $corpo['login']);
        $this->assertEquals('jane.smith@example.com', $corpo['email']);
    }

    public function testGetUsuarioInexistente(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('GET', '/usuarios/99');
        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Usuário não encontrado', $corpo['erro']);
    }

    public function testPostCriaUsuario(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest(
            'POST',
            '/usuarios',
            [
                'nome'  => 'Eve Adams',
                'email' => 'eve.adams@example.com',
                'idade' => 28,
                'login' => 'eveadams',
                'senha' => '424242'
            ]
        );

        $response = $app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Eve Adams', $corpo['nome']);
        $this->assertEquals('eve.adams@example.com', $corpo['email']);
        $this->assertEquals('eveadams', $corpo['login']);
        $this->assertEquals(6, $corpo['id']);
    }

    public function testGetFiltraUsuariosPorNome(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest(
            'GET',
            '/usuarios?nome=jane'
        );

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $corpo);
        $this->assertEquals('Jane Smith', $corpo[0]['nome']);
    }

    public function testPutAtualizaUsuario(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest(
            'PUT',
            '/usuarios/1',
            [
                'nome'  => 'John Doe Silva',
                'email' => 'john.silva@example.com',
                'login' => 'johndoe',
                'senha' => '123456'
            ]
        );

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('John Doe Silva', $corpo['nome']);
        $this->assertEquals('john.silva@example.com', $corpo['email']);
        $this->assertEquals(1, $corpo['id']);
    }

    public function testPutUsuarioInexistente(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest(
            'PUT',
            '/usuarios/99',
            [
                'nome'  => 'Fantasma',
                'email' => 'fantasma@example.com',
                'login' => 'fantasma',
                'senha' => '000000'
            ]
        );

        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Usuário não encontrado', $corpo['erro']);
    }

    public function testPutAtualizaSenhaUsuario(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest(
            'PUT',
            '/usuarios/3/senha',
            ['senha' => 'novaSenha123']
        );

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('novaSenha123', $corpo['senha']);
        $this->assertEquals(3, $corpo['id']);
        $this->assertEquals('Alice Johnson', $corpo['nome']);
    }

    public function testPutSenhaUsuarioInexistente(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest(
            'PUT',
            '/usuarios/99/senha',
            ['senha' => 'qualquer']
        );

        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Usuário não encontrado', $corpo['erro']);
    }

    public function testDeleteRemoveUsuario(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('DELETE', '/usuarios/1');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Usuário removido com sucesso', $corpo['mensagem']);

        $request = $this->criarRequest('GET', '/usuarios/1');
        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteUsuarioInexistente(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('DELETE', '/usuarios/99');
        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Usuário não encontrado', $corpo['erro']);
    }
}