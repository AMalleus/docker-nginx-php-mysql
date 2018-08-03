<?php

define('ROOT', dirname(dirname(__FILE__)));

require ROOT . '/vendor/autoload.php';

$app = new Slim\App();

$app->get('[/]', function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
    $params = $request->getQueryParams();

    $params['root'] = 1;

    return $response->withJson($params);
});

$app->group('/records', function() {
    $this->get('[/]', new \Controllers\Rest\Records\GetAllRecords());

    $this->get('/{recordId:\d+}[/]', new \Controllers\Rest\Records\GetOneRecord());

    $this->post('/create[/]', new \Controllers\Rest\Records\CreateRecord());
})
->add(new Middlewares\InitRecords());

$container = [];
$container['db'] = function () {

};

$app->run();
