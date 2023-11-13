<?php

use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;


class MyTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        // Crie uma instância do aplicativo Slim
        $this->app = new App();

        // Defina a configuração de teste, por exemplo, mode 'testing'
        $this->app->getContainer()['settings']['displayErrorDetails'] = true;
        $this->app->getContainer()['settings']['mode'] = 'testing';

        // Defina suas rotas
        $this->app->get('/hello/{name}', function ($request, $response, $args) {
            return $response->write("Hello, " . $args['name']);
        });
    }

    public function testHelloRoute()
    {
        // Crie um ambiente de teste
        $environment = Environment::mock([
            'REQUEST_URI' => '/hello/John',
            'REQUEST_METHOD' => 'GET',
        ]);

        $request = Request::createFromEnvironment($environment);
        $response = new Response();

        // Execute a aplicação Slim
        $response = $this->app->process($request, $response);

        // Asserção: verifique se a resposta contém a string esperada
        $this->assertSame("Hello, John", (string) $response->getBody());
    }
}