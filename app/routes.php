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

  $app->group('/users', function (Group $group) {
    $group->get('', ListUsersAction::class);
    $group->get('/{id}', ViewUserAction::class);
  });


};
