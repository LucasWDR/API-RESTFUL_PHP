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
    $sql = "SELECT * FROM products";

    try {
      $db = new Db();
      $conn = $db->connect();
      $stmt = $conn->query($sql);
      $products = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      $response->getBody()->write(json_encode($products));
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

    $sql = "SELECT * FROM products WHERE id = :id";

    try {
      $db = new Db();
      $conn = $db->connect();
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
      $stmt->execute();

      $product = $stmt->fetch(PDO::FETCH_OBJ);

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
    $data = $request->getParsedBody();
    $name = $data["name"];
    $description = $data["description"];
    $price = $data["price"];
    $amount = $data["amount"];

    $sql = "INSERT INTO products (name, description, price, amount) VALUES (:name, :description, :price, :amount)";

    try {
      $db = new Db();
      $conn = $db->connect();

      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':description', $description);
      $stmt->bindParam(':price', $price);
      $stmt->bindParam(':amount', $amount);

      $result = $stmt->execute();

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

  $app->put('/products/{id}', function (Request $request, Response $response, array $args) {
      $id = $request->getAttribute('id');
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

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':amount', $amount);

        $result = $stmt->execute();

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

  $app->delete('/products/{id}', function (Request $request, Response $response, array $args) {
    $id = $args["id"];

    $sql = "DELETE FROM products WHERE id = $id";

    try {
      $db = new Db();
      $conn = $db->connect();

      $stmt = $conn->prepare($sql);
      $result = $stmt->execute();

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
