<?php


error_reporting(E_ALL ^ E_DEPRECATED);

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use DI\Container;

use Simonbowen\Rocky\Notifier;
use Simonbowen\Rocky\Gotify;


$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__.'/../');
$dotenv->load();


$container = new Container();

$container->set('notifier', function () {
    $notifier = new Notifier();

    if (getenv('GOTIFY_SERVER') && getenv('GOTIFY_APP_KEY')) {
        $server = new \Gotify\Server(getenv('GOTIFY_SERVER'));
        $token = new \Gotify\Auth\Token(getenv('GOTIFY_APP_KEY'));

        print_r("Using Gotify server: " . getenv('GOTIFY_SERVER') . "\n");

        $gotify = new Gotify($server, $token);
        $notifier->addChannel($gotify);
    }

    return $notifier;
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);

$app->add(TwigMiddleware::create($app, $twig));
$app->addErrorMiddleware(true, false, false);

$app->get('/', function (Request $request, Response $response, array $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'home.html.twig');
});

$app->post('/', function (Request $request, Response $response, array $args)  {
    $body = (array) $request->getParsedBody();
    $payload = json_encode($request->getParsedBody());

    // Create a message class instance
    $map = sprintf("https://maps.google.com/?q=%s,%s", $body['latitude'], $body['longitude']);

    $messageBody = sprintf("Dog %s is at %s,%s \nMessage: %s \nMap: %s", "Rocky", $body['latitude'], $body['longitude'], $body['message'], $map);

    $this->get('notifier')->send(
        title: 'Dog Located',
        body: $messageBody
    );

    $response->getBody()->write(string: $payload);
    return $response->withHeader('Content-Type', 'application/json');
});

// Run application
$app->run();