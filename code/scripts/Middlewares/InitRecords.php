<?php

namespace Middlewares;

use Models\Records;
use Slim\Http\Request;
use Slim\Http\Response;

class InitRecords
{

    /** @var Records */
    private $model;

    public function __construct($container)
    {
        $this->model = $container['recordsModel'];
    }


    public function __invoke(Request $request, Response $response, callable $next)
    {
        try {
            $this->model->createTable();
        } catch (\Exception $e) {
            if (mb_strpos($e->getMessage(), sprintf('Table \'%s\' already exists', Records::TABLE)) === false) {
                throw new \Exception('Ошибка создания таблицы', 0, $e);
            }
        }

        return $next($request, $response);
    }
}