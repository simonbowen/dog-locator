<?php

error_reporting(E_ALL ^ E_DEPRECATED);

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

use Gotify\Server;
use Gotify\Auth\Token;
use Gotify\Endpoint\Message;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$server = new Server(getenv('GOTIFY_SERVER'));
$auth = new Token(getenv('GOTIFY_APP_KEY'));

$app = AppFactory::create();
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));
$app->addErrorMiddleware(true, false, false);

$app->get('/', function (Request $request, Response $response, array $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'home.html.twig');
});

$app->post('/', function (Request $request, Response $response, array $args) use ($server, $auth) {
    $body = (array) $request->getParsedBody();
    $payload = json_encode($request->getParsedBody());

    // Create a message class instance
    $message = new Message($server, $auth);
    $map = sprintf("https://maps.google.com/?q=%s,%s", $body['latitude'], $body['longitude']);

    $messageBody = sprintf("Dog %s is at %s,%s \nMessage: %s \nMap: %s", "Rocky", $body['latitude'], $body['longitude'], $body['message'], $map);

    $message->create(
        title: 'Dog Located',
        message: $messageBody,
        priority: Message::PRIORITY_HIGH,
    );

    $response->getBody()->write(string: $payload);
    return $response->withHeader('Content-Type', 'application/json');
});

// Run application
$app->run();