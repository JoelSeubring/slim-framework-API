<?php
// DIC configurationm

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// elequent
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function($c)  use($capsule) {
    return $capsule;
};

// Override the default Not Found Handler
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('Page Not Found!');
    };
};

// Override the default Not Allow Handler
$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        return $c['response']
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-type', 'text/html')
            ->write('Method must be one of: ' . implode(', ', $methods));
    };
};

// Override the default Not Allow Handler
$container['phpErrorHandler'] = function ($c) {
    return function ($request, $response, $error) use ($c) {
        return $c['response']
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    };
};


/*
*=========Controllers Container Goes Here===========
*/
//Validator
$container['validator'] = function () {
    return new App\Validation\Validator;
};

//Home Controller
$container['HomeController'] = function ($c) {
    return new App\Controllers\HomeController($c);
};

//Task Controller
$container['TaskController'] = function ($c) {
    return new App\Controllers\TaskController($c);
};

//Auth Controller
$container['AuthController'] = function ($c) {
    return new App\Controllers\Auth\AuthController($c);
};

//CSRF protection middleware
$container['csrf'] = function ($c) {
    return new \Slim\Csrf\Guard;
};

//SignIn authentication
$container['auth'] = function ($c) {
    return new App\Auth\Auth;
};

//Middleware
$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
//$app->add($container->csrf);