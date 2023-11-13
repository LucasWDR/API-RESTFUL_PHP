<?php

use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

use Slim\Testing\TestCase as SlimTestCase;

require __DIR__ . '/../vendor/autoload.php'; // Ajuste o caminho conforme a sua estrutura

class ProductTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        $this->app = new App();

        // Configure suas rotas ou carregue as configurações do seu aplicativo aqui
        $this->app->get('/products/{id}', function (Request $request, Response $response, $args) {
            $productId = $args['id'];
            // Faça algo com $productId e retorne uma resposta
            return $response->withJson(['id' => $productId]);
        });
    }

    public function testGetProductById()
    {
        $request = $this->createRequest('GET', '/products/1');
        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['id' => '123'], json_decode((string) $response->getBody(), true));
    }

    private function createRequest(string $method, string $uri): Request
    {
        return Request::createFromString($method, $uri);
    }
}