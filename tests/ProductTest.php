<?php

use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Http\Environment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\DB;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;
use DI\Container;
use Slim\Middleware\ErrorMiddleware;

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
            $response->getBody()->write(json_encode(['id' => $productId]));
        });
    }

    public function testGetProductById()
    {
        $request = $this->createRequest('GET', '/products/1');
        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['id' => '1'], json_decode((string) $response->getBody(), true));
    }

    public function testProduct() 
    {

        $sql =  "SELECT * FROM products";

         $db = new Db();
         $conn = $db->connect();

      // com base na conexão feita vai preparar a instrução sqguida pelo '$sql'
        $preparedStatement = $conn->prepare($sql);
        $preparedStatement->execute();
        $productsAll = $preparedStatement->fetchAll(PDO::FETCH_OBJ);

        $expected = [
            'pagina_atual' => 1,
            'total_paginas' => 2,
            'total_registros' => 7,
            'registros_por_pagina' => 5,
            'registros' => $productsAll
        ];

        $arrayCompared = array_merge($expected, $productsAll);

        $this->assertEquals($expected, $arrayCompared);
    }
}