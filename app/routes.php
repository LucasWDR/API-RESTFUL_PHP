<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Models\DB;

return function (App $app) {
  $app->options('/{routes:.*}', function (Request $request, Response $response) {
    // CORS Pre-Flight OPTIONS Request Handler
    return $response;
  });

  $app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('Hello world! php test');
    return $response;
  });

  $app->get('/products/all', function (Request $request, Response $response) {
    $queryParams = $request->getQueryParams();

    // obter numero de paginas e quantidade de itens por pagina 
    $pagina = isset($queryParams['pagina']) ? max(1, intval($queryParams['pagina'])) : 1;
    $porPagina = isset($queryParams['porPagina']) ? max(1, intval($queryParams['porPagina'])) : 5;

    $deslocamento = ($pagina - 1) * $porPagina;

    $sql = "SELECT * FROM products LIMIT :deslocamento, :porPagina";

    try {
      $db = new Db();
      $conn = $db->connect();

      // com base na conexão feita vai preparar a instrução sqguida pelo '$sql'
      $preparedStatement = $conn->prepare($sql);

      //bindParam usado para vincular um valor a varaivel
      $preparedStatement->bindParam(':deslocamento', $deslocamento, PDO::PARAM_INT);
      $preparedStatement->bindParam(':porPagina', $porPagina, PDO::PARAM_INT);
      $preparedStatement->execute();
      $productsAll = $preparedStatement->fetchAll(PDO::FETCH_OBJ);

      // Obtém o total de registros
      $totalRegistros = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();

      // Calcula o total das paginas
      $totalPaginas = ceil($totalRegistros / $porPagina);

      // Montar o array de resposta
      $paginatedProducts = [
        'pagina_atual' => $pagina,
        'total_paginas' => $totalPaginas,
        'total_registros' => $totalRegistros,
        'registros_por_pagina' => $porPagina,
        'registros' => $productsAll
      ];
      $response->getBody()->write(json_encode($paginatedProducts));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
    } catch (PDOException $e) {
      $error = array(
        "message" => $e->getMessage()
      );

      $response->getBody()->write(json_encode($error));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(500);
    }
  });

  $app->get('/products/{id}', function (Request $request, Response $response, $args) {
    $productId = $args['id'];

    // Realizando sql com base no id passado
    $sql = "SELECT * FROM products WHERE id = :id";

    try {
      $db = new Db();
      $conn = $db->connect();

      $preparedStatement = $conn->prepare($sql);
      $preparedStatement->bindParam(':id', $productId, PDO::PARAM_INT);
      $preparedStatement->execute();

      $product = $preparedStatement->fetch(PDO::FETCH_OBJ);

      // condição para produto não encontrado
      if (!$product) {
        $response->getBody()->write(json_encode(['error' => 'Produto não encontrado na base']));
        return $response
          ->withHeader('content-type', 'application/json')
          ->withStatus(404);
      }

      $db = null;

      $response->getBody()->write(json_encode($product));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
    } catch (PDOException $e) {
      $error = [
        'message' => $e->getMessage()
      ];

      $response->getBody()->write(json_encode($error));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(500);
    }
  });

  $app->post('/products/add', function (Request $request, Response $response, array $args) {
    // obter dados do body de solicitação 
    $data = $request->getParsedBody();

    $name = $data["name"];
    $description = $data["description"];
    $price = $data["price"];
    $amount = $data["amount"];

    $sql = "INSERT INTO products (name, description, price, amount) VALUES (:name, :description, :price, :amount)";

    try {
      $db = new Db();
      $conn = $db->connect();

      $preparedStatement = $conn->prepare($sql);
      $preparedStatement->bindParam(':name', $name);
      $preparedStatement->bindParam(':description', $description);
      $preparedStatement->bindParam(':price', $price);
      $preparedStatement->bindParam(':amount', $amount);

      $result = $preparedStatement->execute();

      $db = null;
      $response->getBody()->write(json_encode($result));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
    } catch (PDOException $e) {
      $error = array(
        "message" => $e->getMessage()
      );

      $response->getBody()->write(json_encode($error));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(500);
    }
  });

  $app->put(
    '/products/update/{id}',
    function (Request $request, Response $response, array $args) {
      $id = $request->getAttribute('id');
      
      // obter dados do body de solicitação 
      $data = $request->getParsedBody();

      $name = $data["name"];
      $description = $data["description"];
      $price = $data["price"];
      $amount = $data["amount"];

      $sql = "UPDATE products SET
           name = :name,
           description = :description,
           price = :price,
           amount = :amount
        WHERE id = $id";

      try {
        $db = new Db();
        $conn = $db->connect();

        $preparedStatement = $conn->prepare($sql);
        $preparedStatement->bindParam(':name', $name);
        $preparedStatement->bindParam(':description', $description);
        $preparedStatement->bindParam(':price', $price);
        $preparedStatement->bindParam(':amount', $amount);

        $result = $preparedStatement->execute();

        $db = null;
        echo "Update successful! ";
        $response->getBody()->write(json_encode($result));
        return $response
          ->withHeader('content-type', 'application/json')
          ->withStatus(200);
      } catch (PDOException $e) {
        $error = array(
          "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
          ->withHeader('content-type', 'application/json')
          ->withStatus(500);
      }
    }
  );

  $app->delete('/products/delete/{id}', function (Request $request, Response $response, array $args) {
    $id = $args["id"];

    //sql para selecionar produto por id com base no parametro para delete
    $sql = "DELETE FROM products WHERE id = $id";

    try {
      $db = new Db();
      $conn = $db->connect();

      $preparedStatement = $conn->prepare($sql);
      $result = $preparedStatement->execute();

      $db = null;
      $response->getBody()->write(json_encode($result));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
    } catch (PDOException $e) {
      $error = array(
        "message" => $e->getMessage()
      );

      $response->getBody()->write(json_encode($error));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(500);
    }
  });
};
