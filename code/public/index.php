<?php

define('ROOT', dirname(dirname(__FILE__)));

require ROOT . '/vendor/autoload.php';

$app = new Slim\App(['settings' => [
    'displayErrorDetails' => true,
]]);

$app->get('[/]', function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
    $params = $request->getQueryParams();
    $params['rootFolder'] = 1;
    $params['yay!'] = true;

    return $response->withJson($params);
});

$app->group('/records', function() {
    $this->get('[/]', '\Controllers\Rest\Records\GetAllRecords');

    $this->get('/{recordId:\d+}[/]', '\Controllers\Rest\Records\GetOneRecord');

    $this->put('/{recordId:\d+}[/]', '\Controllers\Rest\Records\UpdateOneRecord');

    $this->delete('/{recordId:\d+}[/]', '\Controllers\Rest\Records\DeleteOneRecord');

    $this->post('/create[/]', '\Controllers\Rest\Records\CreateRecord');
})
->add('\Middlewares\InitRecords');

$container = $app->getContainer();

//$container['errorHandler'] = function() {
//    return new \Lib\ErrorHandler();
//};

$container['db'] = function () {
    return new \Database\MySQL();
};

$container['recordsModel'] = function ($container) {
    return new \Models\Records($container['db']);
};

$container['\Middlewares\InitRecords'] = function ($container) {
    return new \Middlewares\InitRecords($container);
};

$container['\Controllers\Rest\Records\GetAllRecords'] = function ($container) {
    return new \Controllers\Rest\Records\GetAllRecords($container['recordsModel']);
};

$container['\Controllers\Rest\Records\GetOneRecord'] = function ($container) {
    return new \Controllers\Rest\Records\GetOneRecord($container['recordsModel']);
};

$container['\Controllers\Rest\Records\CreateRecord'] = function ($container) {
    return new \Controllers\Rest\Records\CreateRecord($container['recordsModel']);
};

$container['\Controllers\Rest\Records\UpdateOneRecord'] = function ($container) {
    return new \Controllers\Rest\Records\UpdateOneRecord($container['recordsModel']);
};

$container['\Controllers\Rest\Records\DeleteOneRecord'] = function ($container) {
    return new \Controllers\Rest\Records\DeleteOneRecord($container['recordsModel']);
};

$app->run();
